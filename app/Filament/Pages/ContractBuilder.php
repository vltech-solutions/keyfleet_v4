<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use App\Models\Contract;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class ContractBuilder extends Page implements HasForms
{
    use InteractsWithForms;
    public ?string $editor = '';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.contract-builder';

    public $contract;

    public function mount(): void
    {
        $companyId = Filament::getTenant()?->id;

        $this->contract = Contract::where('company_id', $companyId)->first();

        if ($this->contract) {
            $this->form->fill([
                'editor' => $this->contract->body,
            ]);
        } else {
            $this->form->fill(); 
        }
    }

    protected function getFormSchema(): array
    {
        return [
            TinyEditor::make('editor')
                ->fileAttachmentsDisk('public')
                ->fileAttachmentsVisibility('public')
                ->fileAttachmentsDirectory('uploads')
                ->profile('full')
                ->ltr() 
                ->columnSpan('full')
                ->extraInputAttributes([
                    'style' => 'height: 100%; overflow-y: auto;',
                    'class' => 'h-full tiny-editor forced-light-mode'
                ])
                ->options([
                    'skin' => 'oxide',
                    'content_css' => 'default',
                    'visual' => false,
                ])
                
                ->required()
        ];
    }

    public function save()
    {
        $data = $this->form->getState();
        $companyId = Filament::getTenant()?->id;

        if ($this->contract) {
            $this->contract->body = $data['editor'];
            $this->contract->save();
            Notification::make()
                ->title('Contract updated for your company.')
                ->success()
                ->send();
        } else {
            Contract::create([
                'company_id' => $companyId,
                'title' => 'Contract',
                'body' => $data['editor'],
            ]);
            Notification::make()
                ->title('Contract saved for your company.')
                ->success()
                ->send();
        }

        Cache::put("contract_template_{$companyId}", $data['editor']);
    }

}
