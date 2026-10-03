<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesTenantPage;
use App\Models\Contract;
use App\Models\ContractClause;
use App\Support\ContractDefaults;
use Filament\Facades\Filament;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ContractBuilder extends Page implements HasForms
{
    use AuthorizesTenantPage;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.contract-builder';

    protected static ?string $navigationLabel = 'Contract Builder';

    /*
    |--------------------------------------------------------------------------
    | Filament Page Title
    |--------------------------------------------------------------------------
    |
    | Keep this static.
    | Do not use this as Livewire/form state.
    |
    */
    protected static ?string $title = 'Contract Builder';

    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    public ?Contract $contract = null;

    public string $mode = 'simple';

    /*
    |--------------------------------------------------------------------------
    | IMPORTANT
    |--------------------------------------------------------------------------
    |
    | This is the editable Contract title.
    |
    | Do NOT name this property "$title" because Filament Page already
    | has a static $title property.
    |
    */
    public string $contractTitle = 'Vehicle Rental Agreement';

    public array $settings = [];

    public array $clauses = [];

    public ?string $editor = '';

    public bool $showClauseModal = false;

    public ?int $editingClauseIndex = null;

    public string $clauseTitle = '';

    public string $clauseBody = '';

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $companyId = Filament::getTenant()?->id;

        if (! $companyId) {
            abort(404);
        }

        $this->contract = Contract::query()
            ->with('clauses')
            ->where('company_id', $companyId)
            ->first();

        if (! $this->contract) {
            $this->contractTitle = 'Vehicle Rental Agreement';
            $this->settings = ContractDefaults::settings();
            $this->clauses = ContractDefaults::clauses();
            $this->editor = '';

            $this->form->fill([
                'contractTitle' => $this->contractTitle,
                'editor' => $this->editor,
            ]);

            return;
        }

        $this->mode = $this->contract->builder_mode ?: 'advanced';

        $this->contractTitle = $this->contract->title
            ?: 'Vehicle Rental Agreement';

        $this->editor = $this->contract->body ?? '';

        $this->settings = array_merge(
            ContractDefaults::settings(),
            $this->contract->settings ?? [],
        );

        $this->clauses = $this->contract->clauses
            ->map(fn (ContractClause $clause) => [
                'id' => $clause->id,
                'type' => $clause->type,
                'title' => $clause->title,
                'body' => $clause->body,
                'is_enabled' => $clause->is_enabled,
            ])
            ->values()
            ->toArray();

        if (empty($this->clauses)) {
            $this->clauses = ContractDefaults::clauses();
        }

        $this->form->fill([
            'contractTitle' => $this->contractTitle,
            'editor' => $this->editor,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('contractTitle')
                ->label('Contract Title')
                ->required()
                ->maxLength(255),

            TinyEditor::make('editor')
                ->label('Contract Template')
                ->fileAttachmentsDisk('public')
                ->fileAttachmentsVisibility('public')
                ->fileAttachmentsDirectory('uploads')
                ->profile('full')
                ->ltr()
                ->columnSpanFull()
                ->extraInputAttributes([
                    'class' => 'tiny-editor forced-light-mode',
                ])
                ->options([
                    'skin' => 'oxide',
                    'content_css' => 'default',
                    'visual' => false,
                    'height' => 650,
                ])
                ->required(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Builder Mode
    |--------------------------------------------------------------------------
    */

    public function setMode(string $mode): void
    {
        if (! in_array($mode, ['simple', 'advanced'], true)) {
            return;
        }

        $this->mode = $mode;
    }

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    public function toggleSetting(string $key): void
    {
        $defaults = ContractDefaults::settings();

        if (! array_key_exists($key, $defaults)) {
            return;
        }

        $this->settings[$key] = ! ($this->settings[$key] ?? false);
    }

    /*
    |--------------------------------------------------------------------------
    | Clauses
    |--------------------------------------------------------------------------
    */

    public function addClause(): void
    {
        $this->editingClauseIndex = null;

        $this->clauseTitle = '';

        $this->clauseBody = '';

        $this->showClauseModal = true;
    }

    public function editClause(int $index): void
    {
        if (! isset($this->clauses[$index])) {
            return;
        }

        $this->editingClauseIndex = $index;

        $this->clauseTitle = $this->clauses[$index]['title'] ?? '';

        $this->clauseBody = $this->clauses[$index]['body'] ?? '';

        $this->showClauseModal = true;
    }

    public function saveClause(): void
    {
        $title = trim($this->clauseTitle);

        $body = trim($this->clauseBody);

        if ($title === '' || $body === '') {
            Notification::make()
                ->title('Clause title and content are required.')
                ->warning()
                ->send();

            return;
        }

        if ($this->editingClauseIndex !== null) {
            $this->clauses[$this->editingClauseIndex]['title'] = $title;

            $this->clauses[$this->editingClauseIndex]['body'] = $body;
        } else {
            $this->clauses[] = [
                'type' => 'custom',
                'title' => $title,
                'body' => $body,
                'is_enabled' => true,
            ];
        }

        $this->showClauseModal = false;

        $this->editingClauseIndex = null;

        $this->clauseTitle = '';

        $this->clauseBody = '';
    }

    public function toggleClause(int $index): void
    {
        if (! isset($this->clauses[$index])) {
            return;
        }

        $this->clauses[$index]['is_enabled'] =
            ! ($this->clauses[$index]['is_enabled'] ?? true);
    }

    public function deleteClause(int $index): void
    {
        if (! isset($this->clauses[$index])) {
            return;
        }

        unset($this->clauses[$index]);

        $this->clauses = array_values($this->clauses);
    }

    public function moveClauseUp(int $index): void
    {
        if (
            $index <= 0 ||
            ! isset($this->clauses[$index])
        ) {
            return;
        }

        [$this->clauses[$index - 1], $this->clauses[$index]] = [
            $this->clauses[$index],
            $this->clauses[$index - 1],
        ];

        $this->clauses = array_values($this->clauses);
    }

    public function moveClauseDown(int $index): void
    {
        if (
            ! isset($this->clauses[$index]) ||
            $index >= count($this->clauses) - 1
        ) {
            return;
        }

        [$this->clauses[$index + 1], $this->clauses[$index]] = [
            $this->clauses[$index],
            $this->clauses[$index + 1],
        ];

        $this->clauses = array_values($this->clauses);
    }

    /*
    |--------------------------------------------------------------------------
    | Restore Defaults
    |--------------------------------------------------------------------------
    */

    public function restoreDefaults(): void
    {
        $this->settings = ContractDefaults::settings();

        $this->clauses = ContractDefaults::clauses();

        Notification::make()
            ->title('Default contract settings restored.')
            ->body('Click Save Contract to apply these changes.')
            ->success()
            ->send();
    }

    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        $companyId = Filament::getTenant()?->id;

        if (! $companyId) {
            Notification::make()
                ->title('Unable to determine your company.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        $this->contractTitle = $data['contractTitle']
            ?? $this->contractTitle;

        $this->editor = $data['editor']
            ?? $this->editor;

        if (isset($data['settings'])) {
            $this->settings = array_merge(
                $this->settings,
                $data['settings'],
            );
        }

        DB::transaction(function () use ($companyId): void {
            $contract = Contract::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                ],
                [
                    /*
                    |--------------------------------------------------------------------------
                    | DB column is still "title"
                    |--------------------------------------------------------------------------
                    |
                    | That's okay.
                    |
                    | Database:
                    |   title
                    |
                    | Livewire property:
                    |   contractTitle
                    |
                    */
                    'title' => $this->contractTitle,

                    'body' => $this->editor ?? '',

                    'builder_mode' => $this->mode,

                    'settings' => $this->settings,
                ],
            );

            /*
            |--------------------------------------------------------------------------
            | Save Clauses
            |--------------------------------------------------------------------------
            */

            $contract->clauses()->delete();

            foreach ($this->clauses as $index => $clause) {
                $contract->clauses()->create([
                    'type' => $clause['type'] ?? 'custom',

                    'title' => $clause['title'],

                    'body' => $clause['body'],

                    'sort_order' => $index,

                    'is_enabled' => $clause['is_enabled'] ?? true,
                ]);
            }

            $this->contract = $contract;
        });

        Cache::forget("contract_template_{$companyId}");

        Notification::make()
            ->title('Contract saved')
            ->body('Your contract template has been updated.')
            ->success()
            ->send();
    }
}