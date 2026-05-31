<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Plan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PlanDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Plan Distribution';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $today = Carbon::today()->toDateString();

        // Get all plans in stable order
        $plans = Plan::orderBy('name')->get();

        // Query subscriptions once: count DISTINCT company_id per plan for active subscriptions
        $counts = DB::table('subscriptions')
            ->select('plan_id', DB::raw('COUNT(DISTINCT company_id) as companies_count'))
            ->whereNotNull('plan_id') // exclude planless if you want
            // ->where('starts_at', '<=', $today)
            ->where('ends_at', '>', $today)
            ->groupBy('plan_id')
            ->pluck('companies_count', 'plan_id'); // keyed by plan_id

        // Map plans to data (ensure zero when no row)
        $data = $plans->map(function ($plan) use ($counts) {
            return (int) ($counts[$plan->id] ?? 0);
        })->toArray();

        // Labels
        $labels = $plans->pluck('name')->toArray();

        // Simple palette (will repeat if more plans than colors)
        $palette = [
            '#6366F1', // Indigo
            '#22C55E', // Green
            '#F59E0B', // Amber
            '#EF4444', // Red
            '#3B82F6', // Blue
            '#A855F7', // Violet
            '#F43F5E', // Rose
            '#06B6D4', // Cyan
        ];
        $backgroundColor = [];
        foreach ($plans as $i => $plan) {
            $backgroundColor[] = $palette[$i % count($palette)];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Active companies per plan',
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
