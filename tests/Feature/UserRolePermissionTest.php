<?php

namespace Tests\Feature;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Role;
use App\Models\Source;
use App\Models\Subscription;
use App\Models\User;
use App\Services\TenantUserService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_and_users_are_tenant_scoped_and_a_user_has_one_role(): void
    {
        $companyA = $this->company('Tenant A');
        $companyB = $this->company('Tenant B');
        $roleA = $this->role($companyA, ['users.view']);
        $roleB = $this->role($companyB, ['users.view']);
        $user = User::factory()->create([
            'company_id' => $companyA->id,
            'role_id' => $roleA->id,
            'is_active' => true,
        ]);

        $this->assertSame($roleA->id, $user->role_id);
        $this->assertCount(1, $user->getTenants(Filament::getCurrentPanel()));

        $this->expectException(ValidationException::class);
        $user->update(['role_id' => $roleB->id]);
    }

    public function test_a_user_without_booking_update_permission_cannot_edit_even_with_the_record_url(): void
    {
        $company = $this->company('Tenant A');
        $role = $this->role($company, ['bookings.view']);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $booking = new Booking(['company_id' => $company->id]);
        $booking->id = 100;
        $booking->exists = true;

        $this->actingAs($user);
        Filament::setTenant($company);

        $this->assertFalse(BookingResource::canEdit($booking));
    }

    public function test_api_direct_lookup_cannot_read_another_tenants_booking(): void
    {
        $companyA = $this->company('Tenant A');
        $companyB = $this->company('Tenant B');
        $role = $this->role($companyA, ['bookings.view']);
        $user = User::factory()->create([
            'company_id' => $companyA->id,
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $carId = DB::table('cars')->insertGetId([
            'name' => 'Other Car', 'brand' => 'Brand', 'model' => 'Model',
            'year' => '2026', 'color' => 'Black', 'plate_number' => 'OTHER-1',
            'company_id' => $companyB->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $sourceId = DB::table('sources')->insertGetId([
            'source' => 'Other Tenant Source', 'company_id' => $companyB->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $bookingId = DB::table('bookings')->insertGetId([
            'car_id' => $carId, 'company_id' => $companyB->id,
            'start_datetime' => now(), 'end_datetime' => now()->addDay(),
            'source_id' => $sourceId,
            'renter_name' => 'Other Tenant Customer',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/bookings/'.$bookingId)->assertNotFound();
    }

    public function test_record_attribution_tracks_creator_and_last_updater_and_keeps_inactive_creator(): void
    {
        $company = $this->company('Tenant A');
        $role = $this->role($company, ['sources.create', 'sources.update']);
        $creator = User::factory()->create([
            'company_id' => $company->id, 'role_id' => $role->id, 'is_active' => true,
        ]);
        $updater = User::factory()->create([
            'company_id' => $company->id, 'role_id' => $role->id, 'is_active' => true,
        ]);

        $this->actingAs($creator);
        $source = Source::create(['source' => 'Referral', 'company_id' => $company->id]);
        $this->assertSame($creator->id, $source->created_by);
        $this->assertSame($creator->id, $source->updated_by);

        $this->actingAs($updater);
        $source->update(['source' => 'Updated Referral']);
        $this->assertSame($creator->id, $source->fresh()->created_by);
        $this->assertSame($updater->id, $source->fresh()->updated_by);

        $creator->forceFill(['is_active' => false])->saveQuietly();
        $this->assertSame($creator->name, $source->fresh()->creator->name);
        $this->assertFalse($source->fresh()->creator->is_active);
    }

    public function test_the_last_owner_cannot_be_deactivated(): void
    {
        $company = $this->company('Tenant A');
        $ownerRole = $this->role($company, array_keys(config('keyfleet_permissions')), true);
        $owner = User::factory()->create([
            'company_id' => $company->id, 'role_id' => $ownerRole->id, 'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);
        $owner->update(['is_active' => false]);
    }

    public function test_the_last_owner_cannot_be_reassigned_to_a_non_owner_role(): void
    {
        $company = $this->company('Tenant A');
        $ownerRole = $this->role($company, array_keys(config('keyfleet_permissions')), true);
        $staffRole = $this->role($company, ['bookings.view']);
        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $ownerRole->id,
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);
        $owner->update(['role_id' => $staffRole->id]);
    }

    #[DataProvider('planLimits')]
    public function test_subscription_user_limits(string $planName, int $limit): void
    {
        $company = $this->company($planName.' Tenant');
        $plan = Plan::create(['name' => $planName, 'car_limit' => 10, 'user_limit' => $limit]);
        $price = PlanPrice::create(['plan_id' => $plan->id, 'billing_cycle' => 'monthly', 'price' => 100]);
        Subscription::create([
            'company_id' => $company->id, 'plan_price_id' => $price->id,
            'starts_at' => now(), 'ends_at' => now()->addMonth(),
        ]);
        $role = $this->role($company, ['users.view']);

        User::factory()->count($limit)->create([
            'company_id' => $company->id, 'role_id' => $role->id, 'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);
        app(TenantUserService::class)->assertSeatAvailable($company);
    }

    public static function planLimits(): array
    {
        return [
            'DriveLite' => ['DriveLite', 1],
            'RoadRunner' => ['RoadRunner', 3],
            'DrivePro' => ['DrivePro', 5],
            'FleetMaster' => ['FleetMaster', 10],
        ];
    }

    private function company(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'slug' => str($name)->slug(),
            'invoice_template' => 'invoice1.png',
        ]);
    }

    private function role(Company $company, array $permissions, bool $protected = false): Role
    {
        $role = Role::create([
            'company_id' => $company->id,
            'name' => $protected ? 'Owner' : 'Test Role '.uniqid(),
            'is_protected' => $protected,
            'is_active' => true,
        ]);

        $ids = collect($permissions)->map(function (string $key): int {
            $definition = config('keyfleet_permissions.'.$key, ['module' => 'Test', 'description' => $key]);

            return Permission::firstOrCreate(
                ['key' => $key],
                ['module' => $definition['module'], 'description' => $definition['description']],
            )->id;
        });
        $role->permissions()->sync($ids);

        return $role;
    }
}
