<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use App\Models\FundType;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function beforeSave(): void
    {
        $data = $this->form->getState();

        if (!empty($data['deduct_to_fund']) && !empty($data['fund_type_id'])) {
            $fundType = FundType::find($data['fund_type_id']);

            if (!$fundType) {
                $this->form->addError('fund_type_id', 'Selected fund type does not exist.');
                throw \Filament\Forms\ValidationException::withMessages([
                    'fund_type_id' => 'Selected fund type does not exist.',
                ]);
            }

            // Get original expense record (if editing)
            $originalAmount = $this->record?->amount ?? 0;
            $originalFundId = $this->record?->fund_type_id;

            $adjustedBalance = $fundType->balance;

            // If editing and fund type didn't change, add original amount back to balance
            if ($this->record && $data['fund_type_id'] == $originalFundId && $this->record->deduct_to_fund) {
                $adjustedBalance += $originalAmount;
            }

            // If editing and fund type changed, refund original amount to old fund
            if ($this->record && $data['fund_type_id'] != $originalFundId && $this->record->deduct_to_fund) {
                $oldFund = FundType::find($originalFundId);
                if ($oldFund) {
                    $oldFund->increment('balance', $originalAmount);
                }
            }

            if ($adjustedBalance < $data['amount']) {
                $this->form->addError('amount', 'Insufficient fund balance to cover this expense.');
                throw \Filament\Forms\ValidationException::withMessages([
                    'amount' => 'Insufficient fund balance to cover this expense.',
                ]);
            }
        }
    }
}
