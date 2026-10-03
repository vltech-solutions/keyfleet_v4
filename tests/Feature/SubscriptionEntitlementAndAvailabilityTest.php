<?php

namespace Tests\Feature;

use App\Filament\Pages\SubscriptionOverview;
use App\Filament\Pages\CompanyProfile;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\TenantUserResource;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use App\Services\TenantUserService;
use App\Services\VehicleAvailabilityService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SubscriptionEntitlementAndAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_entitlements_count_only_active_users_and_clamp_remaining_capacity(): void
    {
        $company = $this->companyWithPlan(3, 2);
        $role = $this->role($company, ['users.view', 'users.create', 'roles.view']);
        $owner = User::factory()->create(['company_id' => $company->id, 'role_id' => $role->id, 'is_active' => true]);
        User::factory()->create(['company_id' => $company->id, 'role_id' => $role->id, 'is_active' => false]);

        $this->actingAs($owner);
        Filament::setTenant($company);

        $this->assertSame(1, $company->activeUserCount());
        $this->assertSame(2, $company->remainingUserSeats());
        $this->assertTrue($company->hasMultiUserAccess());
        $this->assertTrue(TenantUserResource::canViewAny());
        $this->assertTrue(TenantUserResource::canCreate());
        $this->assertTrue(RoleResource::canViewAny());

        User::factory()->count(3)->create(['company_id' => $company->id, 'role_id' => $role->id, 'is_active' => true]);
        $this->assertSame(0, $company->remainingUserSeats());
        $this->assertFalse(TenantUserResource::canCreate());
        $this->assertTrue(TenantUserResource::canViewAny());
    }

    public function test_single_user_plan_hides_and_denies_access_control(): void
    {
        $company = $this->companyWithPlan(1, 2);
        $role = $this->role($company, ['users.view', 'users.create', 'users.update', 'roles.view', 'roles.create']);
        $owner = User::factory()->create(['company_id' => $company->id, 'role_id' => $role->id, 'is_active' => true]);
        $this->actingAs($owner);
        Filament::setTenant($company);

        $this->assertFalse(TenantUserResource::shouldRegisterNavigation());
        $this->assertFalse(TenantUserResource::canViewAny());
        $this->assertFalse(TenantUserResource::canCreate());
        $this->assertFalse(TenantUserResource::canEdit($owner));
        $this->assertFalse(RoleResource::shouldRegisterNavigation());
        $this->assertFalse(RoleResource::canCreate());
    }

    public function test_locked_user_creation_rechecks_the_limit(): void
    {
        $company = $this->companyWithPlan(2, 2);
        $role = $this->role($company, ['users.create']);
        User::factory()->count(2)->create(['company_id' => $company->id, 'role_id' => $role->id, 'is_active' => true]);

        $this->expectException(ValidationException::class);
        app(TenantUserService::class)->createUser($company, [
            'name' => 'Extra User',
            'email' => 'extra@example.test',
            'password' => 'password',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    #[DataProvider('overlapCases')]
    public function test_booking_overlap_rule(string $start, string $end, bool $available): void
    {
        [$company, $carId, $sourceId] = $this->fleetFixture();
        $this->booking($company->id, $carId, $sourceId, '2026-10-10 10:00:00', '2026-10-12 10:00:00');

        $this->assertSame(
            $available,
            app(VehicleAvailabilityService::class)->isAvailable($carId, $start, $end),
        );
    }

    public static function overlapCases(): array
    {
        return [
            'starts inside' => ['2026-10-11 10:00:00', '2026-10-13 10:00:00', false],
            'ends inside' => ['2026-10-09 10:00:00', '2026-10-11 10:00:00', false],
            'fully inside' => ['2026-10-10 12:00:00', '2026-10-11 12:00:00', false],
            'surrounds' => ['2026-10-09 10:00:00', '2026-10-13 10:00:00', false],
            'adjacent after' => ['2026-10-12 10:00:00', '2026-10-13 10:00:00', true],
            'non overlapping' => ['2026-10-13 10:00:00', '2026-10-14 10:00:00', true],
        ];
    }

    public function test_cancelled_booking_and_inactive_reservations_do_not_block(): void
    {
        [$company, $carId, $sourceId, $customerId] = $this->fleetFixture();
        $booking = $this->booking($company->id, $carId, $sourceId, '2026-10-10 10:00:00', '2026-10-12 10:00:00');
        $booking->update(['status' => 'cancelled']);
        Reservation::create([
            'customer_id' => $customerId,
            'start_date' => '2026-10-10 10:00:00',
            'end_date' => '2026-10-12 10:00:00',
            'destination' => 'Test',
            'pickup_option' => 'pickup',
            'selected_car_id' => $carId,
            'source_id' => $sourceId,
            'company_id' => $company->id,
            'reservation_number' => 100001,
            'datetime_cancelled' => now(),
        ]);
        Reservation::create([
            'customer_id' => $customerId,
            'start_date' => '2026-10-10 10:00:00',
            'end_date' => '2026-10-12 10:00:00',
            'destination' => 'Test',
            'pickup_option' => 'pickup',
            'selected_car_id' => $carId,
            'source_id' => $sourceId,
            'company_id' => $company->id,
            'reservation_number' => 100003,
            'datetime_declined' => now(),
        ]);

        $this->assertTrue(app(VehicleAvailabilityService::class)->isAvailable(
            $carId, '2026-10-11 10:00:00', '2026-10-11 12:00:00',
        ));
    }

    public function test_pending_reservation_blocks_but_excludes_itself(): void
    {
        [$company, $carId, $sourceId, $customerId] = $this->fleetFixture();
        $reservation = Reservation::create([
            'customer_id' => $customerId,
            'start_date' => '2026-10-10 10:00:00',
            'end_date' => '2026-10-12 10:00:00',
            'destination' => 'Test',
            'pickup_option' => 'pickup',
            'selected_car_id' => $carId,
            'source_id' => $sourceId,
            'company_id' => $company->id,
            'reservation_number' => 100002,
        ]);
        $availability = app(VehicleAvailabilityService::class);

        $this->assertFalse($availability->isAvailable($carId, $reservation->start_date, $reservation->end_date));
        $this->assertTrue($availability->isAvailable(
            $carId, $reservation->start_date, $reservation->end_date, null, $reservation->id,
        ));
    }

    public function test_limited_staff_cannot_access_subscription_overview(): void
    {
        $company = $this->companyWithPlan(3, 10);
        $role = $this->role($company, ['bookings.view']);
        $staff = User::factory()->create([
            'company_id' => $company->id, 'role_id' => $role->id, 'is_active' => true,
        ]);
        $this->actingAs($staff);
        Filament::setTenant($company);
        $this->assertFalse(SubscriptionOverview::canAccess());
        $this->assertFalse(CompanyProfile::canView($company));
    }

    public function test_reservation_approval_rechecks_conflicting_booking(): void
    {
        [$company, $carId, $sourceId, $customerId] = $this->fleetFixture();
        $reservation = $this->reservation($company->id, $carId, $sourceId, $customerId);
        $this->booking($company->id, $carId, $sourceId, '2026-10-11 10:00:00', '2026-10-13 10:00:00');
        $this->expectException(ValidationException::class);
        app(VehicleAvailabilityService::class)->approveReservation(
            $reservation,
            $this->bookingAttributes($company->id, $carId, $sourceId, $customerId),
        );
    }

    public function test_reservation_approval_excludes_itself_and_links_booking(): void
    {
        [$company, $carId, $sourceId, $customerId] = $this->fleetFixture();
        $role = $this->role($company, ['reservations.approve']);
        $user = User::factory()->create([
            'company_id' => $company->id, 'role_id' => $role->id, 'is_active' => true,
        ]);
        $this->actingAs($user);
        Filament::setTenant($company);
        $reservation = $this->reservation($company->id, $carId, $sourceId, $customerId);
        $booking = app(VehicleAvailabilityService::class)->approveReservation(
            $reservation,
            $this->bookingAttributes($company->id, $carId, $sourceId, $customerId),
        );
        $this->assertSame($booking->id, $reservation->fresh()->booking_id);
        $this->assertSame($reservation->start_date->toDateTimeString(), $booking->start_datetime->toDateTimeString());
        $this->assertSame($reservation->end_date->toDateTimeString(), $booking->end_datetime->toDateTimeString());
    }

    private function reservation(int $companyId, int $carId, int $sourceId, int $customerId): Reservation
    {
        return Reservation::create([
            'customer_id' => $customerId,
            'start_date' => '2026-10-10 10:00:00',
            'end_date' => '2026-10-12 10:00:00',
            'destination' => 'Test',
            'pickup_option' => 'pickup',
            'selected_car_id' => $carId,
            'source_id' => $sourceId,
            'company_id' => $companyId,
            'reservation_number' => 200001,
        ]);
    }

    private function bookingAttributes(int $companyId, int $carId, int $sourceId, int $customerId): array
    {
        return [
            'company_id' => $companyId, 'car_id' => $carId, 'source_id' => $sourceId,
            'customer_id' => $customerId, 'renter_name' => 'Customer', 'renter_address' => 'Address',
            'contact_number' => '09000000000', 'start_datetime' => '2026-10-10 10:00:00',
            'end_datetime' => '2026-10-12 10:00:00', 'status' => 'approved',
        ];
    }

    private function companyWithPlan(int $userLimit, int $carLimit): Company
    {
        $company = Company::create([
            'name' => 'Tenant '.uniqid(),
            'slug' => 'tenant-'.uniqid(),
            'invoice_template' => 'invoice1.png',
        ]);
        $plan = Plan::create([
            'name' => 'Plan '.uniqid(),
            'car_limit' => $carLimit,
            'user_limit' => $userLimit,
            'is_active' => true,
        ]);
        $price = PlanPrice::create(['plan_id' => $plan->id, 'billing_cycle' => 'monthly', 'price' => 100]);
        Subscription::create([
            'company_id' => $company->id,
            'plan_price_id' => $price->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);
        return $company;
    }

    private function role(Company $company, array $permissions): Role
    {
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Role '.uniqid(),
            'is_active' => true,
        ]);
        $ids = collect($permissions)->map(fn (string $key): int => Permission::firstOrCreate(
            ['key' => $key],
            ['module' => 'Test', 'description' => $key],
        )->id);
        $role->permissions()->sync($ids);
        return $role;
    }

    private function fleetFixture(): array
    {
        $company = $this->companyWithPlan(3, 10);
        $carId = DB::table('cars')->insertGetId([
            'name' => 'Test Car', 'brand' => 'Brand', 'model' => 'Model', 'year' => '2026',
            'color' => 'Black', 'plate_number' => 'TEST-'.uniqid(), 'company_id' => $company->id,
            'is_available' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $sourceId = DB::table('sources')->insertGetId([
            'source' => 'Test', 'company_id' => $company->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $customerId = DB::table('customers')->insertGetId([
            'customer_name' => 'Customer', 'address' => 'Address', 'contact_number' => '09000000000',
            'company_id' => $company->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return [$company, $carId, $sourceId, $customerId];
    }

    private function booking(int $companyId, int $carId, int $sourceId, string $start, string $end): Booking
    {
        $id = DB::table('bookings')->insertGetId([
            'company_id' => $companyId,
            'customer_id' => DB::table('customers')->where('company_id', $companyId)->value('id'),
            'car_id' => $carId,
            'source_id' => $sourceId,
            'renter_name' => 'Customer',
            'renter_address' => 'Address',
            'contact_number' => '09000000000',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Booking::findOrFail($id);
    }
}
