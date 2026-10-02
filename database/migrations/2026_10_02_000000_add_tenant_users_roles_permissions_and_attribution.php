<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $directTables = [
        'companies', 'bookings', 'cars', 'customers', 'expenses', 'fund_types',
        'booking_payments', 'car_documents', 'partners', 'reservations', 'sources',
        'checklist_items', 'contracts', 'company_websites',
    ];

    private array $childTables = [
        'booking_inspections', 'inspection_items', 'customer_requirements', 'car_images',
    ];

    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_protected')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'name']);
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('module');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table): void {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('company_id')->nullable()->after('id')->index();
            $table->foreignId('role_id')->nullable()->after('company_id')->index();
            $table->boolean('is_active')->default(true)->after('is_admin');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });

        Schema::table('plans', function (Blueprint $table): void {
            $table->unsignedInteger('user_limit')->nullable()->after('car_limit');
        });

        foreach (array_merge($this->directTables, $this->childTables) as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->unsignedBigInteger('created_by')->nullable()->index();
                    $table->unsignedBigInteger('updated_by')->nullable()->index();
                });
            }
        }

        $this->seedPermissions();
        $this->backfillMembership();
        $this->backfillAttribution();
        $this->backfillPlanLimits();

        Schema::table('users', function (Blueprint $table): void {
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->restrictOnDelete();
        });

        foreach (array_merge($this->directTables, $this->childTables) as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
                    $table->foreign('updated_by')->references('id')->on('users')->restrictOnDelete();
                });
            }
        }
    }

    private function seedPermissions(): void
    {
        $now = now();
        $rows = collect(require config_path('keyfleet_permissions.php'))->map(
            fn (array $permission, string $key): array => [
                'key' => $key,
                'module' => $permission['module'],
                'description' => $permission['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
        )->values()->all();
        DB::table('permissions')->insert($rows);
    }

    private function backfillMembership(): void
    {
        $permissionIds = DB::table('permissions')->pluck('id');

        foreach (DB::table('companies')->orderBy('id')->get() as $company) {
            $members = DB::table('company_user')->where('company_id', $company->id)->orderBy('user_id')->pluck('user_id');
            if ($members->isEmpty()) {
                Log::warning('Tenant has no legacy owner; membership was not fabricated.', ['company_id' => $company->id]);

                continue;
            }

            foreach ($members as $userId) {
                $tenantCount = DB::table('company_user')->where('user_id', $userId)->count();
                if ($tenantCount === 1) {
                    DB::table('users')->where('id', $userId)->update(['company_id' => $company->id]);
                } else {
                    Log::warning('Legacy user belongs to multiple tenants; manual remediation required.', ['user_id' => $userId]);
                }
            }

            $ownerId = DB::table('users')->where('company_id', $company->id)->orderBy('id')->value('id');
            if (! $ownerId) {
                continue;
            }

            $ownerRoleId = $this->createRole($company->id, 'Owner', true, $permissionIds);
            DB::table('users')->where('id', $ownerId)->update(['role_id' => $ownerRoleId]);

            $otherIds = DB::table('users')->where('company_id', $company->id)->where('id', '!=', $ownerId)->pluck('id');
            if ($otherIds->isNotEmpty()) {
                $legacyRoleId = $this->createRole($company->id, 'Legacy User', false, $permissionIds);
                DB::table('users')->whereIn('id', $otherIds)->update(['role_id' => $legacyRoleId]);
            }
        }
    }

    private function createRole(int $companyId, string $name, bool $protected, $permissionIds): int
    {
        $roleId = DB::table('roles')->insertGetId([
            'company_id' => $companyId,
            'name' => $name,
            'description' => $protected ? 'Protected tenant owner with full access.' : 'Compatibility role for pre-migration users.',
            'is_protected' => $protected,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('permission_role')->insert($permissionIds->map(fn ($id) => [
            'permission_id' => $id,
            'role_id' => $roleId,
        ])->all());

        return $roleId;
    }

    private function backfillAttribution(): void
    {
        $owners = DB::table('users')->join('roles', 'roles.id', '=', 'users.role_id')
            ->where('roles.is_protected', true)->pluck('users.id', 'users.company_id');

        foreach ($this->directTables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            foreach ($owners as $companyId => $ownerId) {
                $column = $tableName === 'companies' ? 'id' : 'company_id';
                DB::table($tableName)->where($column, $companyId)->whereNull('created_by')
                    ->update(['created_by' => $ownerId, 'updated_by' => $ownerId]);
            }
            $this->reportUnattributed($tableName);
        }

        $this->backfillChild('booking_inspections', 'booking_id', 'bookings', $owners);
        $this->backfillChild('customer_requirements', 'customer_id', 'customers', $owners);
        $this->backfillChild('car_images', 'car_id', 'cars', $owners);

        foreach ($owners as $companyId => $ownerId) {
            $bookingIds = DB::table('bookings')->where('company_id', $companyId)->pluck('id');
            $inspectionIds = DB::table('booking_inspections')->whereIn('booking_id', $bookingIds)->pluck('id');
            DB::table('inspection_items')->whereIn('booking_inspection_id', $inspectionIds)->whereNull('created_by')
                ->update(['created_by' => $ownerId, 'updated_by' => $ownerId]);
        }
        $this->reportUnattributed('inspection_items');
    }

    private function backfillChild(string $child, string $foreignKey, string $parent, $owners): void
    {
        foreach ($owners as $companyId => $ownerId) {
            $parentIds = DB::table($parent)->where('company_id', $companyId)->pluck('id');
            DB::table($child)->whereIn($foreignKey, $parentIds)->whereNull('created_by')
                ->update(['created_by' => $ownerId, 'updated_by' => $ownerId]);
        }
        $this->reportUnattributed($child);
    }

    private function reportUnattributed(string $table): void
    {
        $count = DB::table($table)->whereNull('created_by')->count();
        if ($count) {
            Log::warning('Legacy records could not be confidently attributed.', compact('table', 'count'));
        }
    }

    private function backfillPlanLimits(): void
    {
        foreach (['DriveLite' => 1, 'RoadRunner' => 3, 'DrivePro' => 5, 'FleetMaster' => 10] as $name => $limit) {
            DB::table('plans')->whereRaw('LOWER(name) = ?', [strtolower($name)])->update(['user_limit' => $limit]);
        }
    }

    public function down(): void
    {
        foreach (array_merge($this->directTables, $this->childTables) as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropForeign(['created_by']);
                    $table->dropForeign(['updated_by']);
                    $table->dropColumn(['created_by', 'updated_by']);
                });
            }
        }
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn(['company_id', 'role_id', 'is_active', 'last_login_at']);
        });
        Schema::table('plans', fn (Blueprint $table) => $table->dropColumn('user_limit'));
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
