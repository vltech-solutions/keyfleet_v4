<?php
namespace App\Livewire;

use Livewire\Component;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Company;
use App\Models\FundType;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\Registered;

class TenantRegister extends Component implements HasForms
{
    use InteractsWithForms;

    public $company_name;
    public $name;
    public $email;
    public $password;

    public array $data = [
        'company' => ['name' => ''],
        'user' => ['name' => '', 'email' => '', 'password' => ''],
    ];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('company.name')
                    ->label('Company Name')
                    ->required()
                    ->unique(table: Company::class, column: 'name'),

                TextInput::make('user.name')
                    ->label('Full Name')
                    ->required(),

                TextInput::make('user.email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(table: User::class, column: 'email'),

                TextInput::make('user.password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->minLength(8),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $validated = $this->form->validate(); 

        $tenant = Company::create([
            'name' => $validated['data']['company']['name'],
            'slug' => Str::slug($validated['data']['company']['name']),
            'invoice_template' => 'invoice1.png',
            'primary_color' => '#0455da',
            'advance_booking_form' => true
        ]);

        $user = User::create([
            'name' => $validated['data']['user']['name'],
            'email' => $validated['data']['user']['email'],
            'password' => Hash::make($validated['data']['user']['password']),
        ]);

        $tenant->users()->attach($user->id);

        $freeTrialPrice = PlanPrice::whereHas('plan', function ($query) {
            $query->where('name', 'FREE TRIAL - Beta');
        })->first();

        if (!$freeTrialPrice) {
            throw new \Exception('FREE TRIAL plan price not found.');
        }

        $startAt = now();
        $endAt = match ($freeTrialPrice->billing_cycle) {
            'monthly' => $startAt->copy()->addMonth(),
            'annually' => $startAt->copy()->addYear(),
            '14days' => $startAt->copy()->addDays(14),
            default => throw new \Exception('Unknown billing cycle: ' . $freeTrialPrice->billing_cycle),
        };

        Subscription::create([
            'company_id' => $tenant->id,
            'plan_price_id' => $freeTrialPrice->id,
            'starts_at' => $startAt->toDateString(),
            'ends_at' => $endAt->toDateString(),
        ]);

        Notification::make()
            ->title('Registration Successful!')
            ->body('Thank you! Your free trial has been started.')
            ->success()
            ->send();


        

        // event(new Registered($user));

        $this->dispatch('redirect-after-delay');
    }

    public function render()
    {
        return view('livewire.tenant-register')
            ->layout('components.layouts.guest');
    }
}
