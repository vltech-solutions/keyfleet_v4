<?php

namespace Tests\Feature;

use App\Livewire\BookingCalendar;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\CalendarDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CalendarEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_data_is_tenant_and_range_scoped_and_expands_multi_day_bookings(): void
    {
        [$company, $user] = $this->tenantUser();
        [$car, $source] = $this->fleetFixture($company);
        $otherCompany = Company::create(['name' => 'Other', 'slug' => 'other', 'invoice_template' => 'invoice1.png']);
        [$otherCar, $otherSource] = $this->fleetFixture($otherCompany);

        $this->booking($company, $car, $source, '2026-10-03 09:00:00', '2026-10-05 09:00:00', 'approved');
        $this->booking($company, $car, $source, '2026-09-01 09:00:00', '2026-09-02 09:00:00', 'approved');
        $this->booking($otherCompany, $otherCar, $otherSource, '2026-10-03 09:00:00', '2026-10-05 09:00:00', 'approved');

        $events = app(CalendarDataService::class)->events(
            $company,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-10-31'),
            ['status' => 'approved'],
        );

        $this->assertCount(3, $events);
        $this->assertSame(['2026-10-03', '2026-10-04', '2026-10-05'], $events->pluck('date')->all());
        $this->assertTrue($events->every(fn (array $event) => $event['car_id'] === $car));
    }

    public function test_single_booking_ics_is_utf8_crlf_compliant_and_authorized(): void
    {
        [$company, $user] = $this->tenantUser();
        [$car, $source] = $this->fleetFixture($company);
        $booking = $this->booking($company, $car, $source, '2026-10-03 09:00:00', '2026-10-03 18:00:00', 'approved', 'Doe, John');

        $response = $this->actingAs($user)->get(route('calendar.booking.ics', $booking));

        $response->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=UTF-8');
        $content = $response->getContent();
        $this->assertStringContainsString("BEGIN:VCALENDAR\r\n", $content);
        $this->assertStringContainsString('UID:booking-'.$booking.'@', $content);
        $this->assertStringContainsString('DTSTART:', $content);
        $this->assertStringContainsString('SUMMARY:Test Car - Doe\\, John', $content);
    }

    public function test_feed_token_is_secure_revocable_and_reflects_current_permissions(): void
    {
        [$company, $user] = $this->tenantUser();
        [$car, $source] = $this->fleetFixture($company);
        $this->booking($company, $car, $source, now()->addDay(), now()->addDays(2), 'approved');

        $token = str_repeat('a', 64);
        $user->forceFill(['calendar_feed_token_hash' => hash('sha256', $token)])->saveQuietly();
        $url = route('calendar.feed', ['user' => $user, 'token' => $token]);

        $this->get($url)->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=UTF-8');
        $this->get(route('calendar.feed', ['user' => $user, 'token' => str_repeat('b', 64)]))->assertNotFound();

        $user->forceFill(['calendar_feed_token_hash' => null])->saveQuietly();
        $this->get($url)->assertNotFound();

        $user->forceFill(['calendar_feed_token_hash' => hash('sha256', $token), 'is_active' => false])->saveQuietly();
        $this->get($url)->assertNotFound();
    }

    public function test_period_navigation_moves_by_active_scope_across_month_boundaries(): void
    {
        $component = new BookingCalendar;
        $component->weekStartsAt = Carbon::SUNDAY;
        $component->weekEndsAt = Carbon::SATURDAY;
        $component->startsAt = Carbon::parse('2026-10-01');
        $component->endsAt = Carbon::parse('2026-10-31');
        $component->calculateGridStartsEnds();

        $component->calendarScope = 'week';
        $component->selectedDate = '2026-10-25';
        $component->goToNextPeriod();

        $this->assertSame('2026-11-01', $component->selectedDate);
        $this->assertSame('2026-11-01', $component->startsAt->toDateString());
        $this->assertTrue(Carbon::parse($component->selectedDate)->betweenIncluded(
            $component->gridStartsAt,
            $component->gridEndsAt,
        ));

        $component->calendarScope = 'day';
        $component->goToPreviousPeriod();

        $this->assertSame('2026-10-31', $component->selectedDate);
        $this->assertSame('2026-10-01', $component->startsAt->toDateString());

        $component->calendarScope = 'month';
        $component->goToNextPeriod();

        $this->assertSame('2026-11-01', $component->selectedDate);
        $this->assertSame('2026-11-01', $component->startsAt->toDateString());
    }

    private function tenantUser(): array
    {
        $company = Company::create([
            'name' => 'Calendar Tenant',
            'slug' => 'calendar-'.uniqid(),
            'invoice_template' => 'invoice1.png',
        ]);
        $permission = Permission::firstOrCreate(
            ['key' => 'calendar.view'],
            ['module' => 'Calendar', 'description' => 'View calendar'],
        );
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Calendar User '.uniqid(),
            'is_active' => true,
        ]);
        $role->permissions()->sync([$permission->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        return [$company, $user];
    }

    private function fleetFixture(Company $company): array
    {
        $source = DB::table('sources')->insertGetId([
            'source' => 'Direct',
            'company_id' => $company->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $car = DB::table('cars')->insertGetId([
            'name' => 'Test Car',
            'brand' => 'Toyota',
            'model' => 'Vios',
            'year' => '2026',
            'color' => 'White',
            'plate_number' => 'ABC-123',
            'company_id' => $company->id,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$car, $source];
    }

    private function booking(
        Company $company,
        int $car,
        int $source,
        Carbon|string $start,
        Carbon|string $end,
        string $status,
        string $renter = 'John Doe',
    ): int {
        return DB::table('bookings')->insertGetId([
            'car_id' => $car,
            'company_id' => $company->id,
            'source_id' => $source,
            'start_datetime' => Carbon::parse($start),
            'end_datetime' => Carbon::parse($end),
            'renter_name' => $renter,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
