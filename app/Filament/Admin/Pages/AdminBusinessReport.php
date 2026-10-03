<?php

namespace App\Filament\Admin\Pages;

use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\Plan;
use App\Services\AdminAnalyticsService;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Computed;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminBusinessReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Business Intelligence';

    protected static ?string $navigationLabel = 'SaaS Reports';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.admin.pages.business-report';

    public string $reportType = 'revenue';

    public string $period = 'this_month';

    public string $planId = 'all';

    public string $agentId = 'all';

    public string $subscriptionStatus = 'all';

    public string $commissionStatus = 'all';

    public ?string $customStart = null;

    public ?string $customEnd = null;

    public function mount(): void
    {
        $this->customStart ??= now()->startOfMonth()->toDateString();
        $this->customEnd ??= now()->endOfMonth()->toDateString();
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    #[Computed]
    public function plans(): array
    {
        return Plan::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    #[Computed]
    public function agents(): array
    {
        return Agent::query()->with('user:id,name,email')->get()
            ->mapWithKeys(fn (Agent $agent) => [
                $agent->id => $agent->user?->name ?? $agent->user?->email ?? 'Agent #'.$agent->id,
            ])->all();
    }

    #[Computed]
    public function range(): array
    {
        return app(AdminAnalyticsService::class)->resolveRange(
            $this->period,
            $this->customStart,
            $this->customEnd,
        );
    }

    #[Computed]
    public function report(): array
    {
        $analytics = app(AdminAnalyticsService::class);

        if ($this->reportType === 'agents') {
            $rows = $analytics->agentPerformance($this->range['start'], $this->range['end']);

            return [
                'rows' => $rows,
                'totals' => [
                    'referrals' => $rows->sum('referrals'),
                    'conversions' => $rows->sum('conversions'),
                    'attributed_revenue' => $rows->sum('attributed_revenue'),
                    'commission_total' => $rows->sum('commissions'),
                ],
            ];
        }

        if ($this->reportType === 'commissions') {
            return $analytics->commissionReport(
                $this->range['start'],
                $this->range['end'],
                $this->agentId === 'all' ? null : (int) $this->agentId,
                $this->commissionStatus,
            );
        }

        return $analytics->subscriptionReport(
            $this->range['start'],
            $this->range['end'],
            $this->planId === 'all' ? null : (int) $this->planId,
            $this->subscriptionStatus,
        );
    }

    public function exportCsv(): StreamedResponse
    {
        $fileName = 'keyfleet-saas-'.$this->reportType.'-'.$this->range['start']->format('Ymd').'-'.$this->range['end']->format('Ymd').'.csv';

        return response()->streamDownload(function () {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");

            if ($this->reportType === 'agents') {
                fputcsv($stream, ['Agent', 'Program', 'Status', 'Referrals', 'Conversions', 'Conversion Rate', 'Attributed Revenue', 'Commission Expense']);
                foreach ($this->report['rows'] as $row) {
                    fputcsv($stream, [$row['name'], $row['program'], $row['status'], $row['referrals'], $row['conversions'], $row['conversion_rate'], $row['attributed_revenue'], $row['commissions']]);
                }
            } elseif ($this->reportType === 'commissions') {
                fputcsv($stream, ['Earned At', 'Agent', 'Subscriber', 'Subscription', 'Attributed Revenue', 'Rate', 'Commission', 'Status', 'Payable At', 'Paid At']);
                foreach ($this->report['rows'] as $commission) {
                    fputcsv($stream, [
                        $commission->earned_at?->format('Y-m-d H:i'),
                        $commission->agent?->user?->name ?? $commission->agent?->user?->email,
                        $commission->company?->name,
                        $commission->subscription_id,
                        $commission->commissionable_amount,
                        $commission->commission_rate,
                        $commission->commission_amount,
                        $commission->status,
                        $commission->payable_at?->format('Y-m-d H:i'),
                        $commission->paid_at?->format('Y-m-d H:i'),
                    ]);
                }
            } else {
                fputcsv($stream, ['Paid At', 'Subscriber', 'Plan', 'Billing Cycle', 'Gross Billings', 'Net Amount', 'Refund', 'Gateway Fee', 'Discount', 'Status']);
                foreach ($this->report['rows'] as $subscription) {
                    fputcsv($stream, [
                        $subscription->paid_at?->format('Y-m-d H:i'),
                        $subscription->company?->name,
                        $subscription->plan?->name,
                        $subscription->planPrice?->billing_cycle,
                        $subscription->total_due,
                        $subscription->net_amount,
                        $subscription->refund_amount,
                        $subscription->paymongo_fee,
                        $subscription->discount_amount,
                        $this->subscriptionState($subscription),
                    ]);
                }
            }

            fclose($stream);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function subscriptionState(mixed $subscription): string
    {
        if ((float) $subscription->refund_amount > 0) {
            return 'Refunded';
        }
        if ($subscription->starts_at?->lte(today()) && $subscription->ends_at?->gte(today())) {
            return 'Active';
        }

        return $subscription->ends_at?->lt(today()) ? 'Expired' : 'Scheduled';
    }

    public function commissionStatusOptions(): array
    {
        return [
            'all' => 'All commission states',
            AgentCommission::STATUS_PENDING => 'Holding',
            AgentCommission::STATUS_PAYABLE => 'Payable',
            AgentCommission::STATUS_PAID => 'Paid',
            AgentCommission::STATUS_REVERSED => 'Reversed',
        ];
    }
}
