<?php

namespace Tests\Unit;

use App\Services\AdminAnalyticsService;
use Carbon\Carbon;
use Tests\TestCase;

class AdminAnalyticsServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_this_month_compares_with_the_previous_calendar_month(): void
    {
        Carbon::setTestNow('2026-10-03 12:00:00');

        $service = app(AdminAnalyticsService::class);
        $range = $service->resolveRange('this_month');
        $previous = $service->previousRange($range['start'], $range['end'], 'this_month');

        $this->assertSame('2026-10-01 00:00:00', $range['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-31 23:59:59', $range['end']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-01 00:00:00', $previous['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-30 23:59:59', $previous['end']->format('Y-m-d H:i:s'));
    }

    public function test_custom_dates_are_normalized_and_reversed_safely(): void
    {
        Carbon::setTestNow('2026-10-03 12:00:00');

        $range = app(AdminAnalyticsService::class)->resolveRange(
            'custom',
            '2026-10-15',
            '2026-10-01',
        );

        $this->assertSame('2026-10-01 00:00:00', $range['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-15 23:59:59', $range['end']->format('Y-m-d H:i:s'));
    }
}
