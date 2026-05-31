<?php

namespace App\Filament\Admin\Resources\SubscriptionResource\Pages;

use App\Filament\Admin\Resources\SubscriptionResource;
use App\Services\ReferralRewardService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                 ->after(function ($record) {
                    \Log::info('✅ Referral reward triggered via modal for company: ' . $record->company_id);
                    ReferralRewardService::handleConversion($record);
                })
        ];
    }
}
