<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Filament\Notifications\Notification;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On; 


class UserProfile extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $view = 'filament.pages.user-profile';
    protected static ?string $slug = 'user-profile';
    protected static ?string $title = '';

    public bool $connected = false;

    public static function shouldRegisterNavigation(): bool
    {
        return false; 
    }

    public $name;
    public $email;
    public $password = null;
    public $password_confirmation = null;

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = $user->name;
        $this->email = $user->email;

        $this->connected = Auth::user()?->google_token !== null;
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2) // 2 columns
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(table: 'users', column: 'email', ignorable: auth()->user()),

                    Forms\Components\TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->minLength(8)
                        ->maxLength(255)
                        ->dehydrateStateUsing(fn ($state) => $state === null ? null : Hash::make($state))
                        ->confirmed()
                        ->nullable(),

                    Forms\Components\TextInput::make('password_confirmation')
                        ->label('Confirm Password')
                        ->password()
                        ->nullable(),
                ]),
        ];
    }



    public function submit()
    {
        $data = $this->form->getState();

        $user = auth()->user();

        $user->name = $data['name'];
        $user->email = $data['email'];

        // Only update password if set
        if (!empty($data['password'])) {
            $user->password = $data['password']; // already hashed in form
        }

        $user->save();

        Notification::make()
            ->title('Great!')
            ->body('Profile updated successfully!')
            ->success()
            ->send();
    }

    public function connect()
    {
        $client = new GoogleClient();
        $client->setAuthConfig(config('services.google'));
        $client->addScope(Calendar::CALENDAR);
        $client->setRedirectUri(route('google.calendar.callback'));

        $client->setAccessType('offline'); 
        $client->setPrompt('consent'); 

        $authUrl = $client->createAuthUrl();
        // dd($authUrl);

        // Livewire 3: use $this->dispatch
        $this->dispatch('open-google-popup', $authUrl);
    }

    #[On('googleCalendarConnected')]
    public function googleCalendarConnected(): void
    {
        // Do whatever you need when the connection succeeds
        $user = Auth::user();
        $user->refresh(); // reload user data
        
        if($user->google_token) {
            Notification::make()
                ->title('Google Calendar connected successfully.')
                ->success()
                ->send();

            $this->dispatch('reload-page');
        }
        
    }

    public function syncBookingToGoogleCalendar(): void
    {
        $user = auth()->user();

        if (! $user->google_token) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'No Google token found for this user.',
            ]);
            return;
        }

        $tokenData = json_decode($user->google_token, true);

        $client = new GoogleClient();
        
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        $client->addScope(Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setAccessToken($tokenData);

        // Refresh if expired
        if ($client->isAccessTokenExpired() && $client->getRefreshToken()) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

            $user->update([
                'google_token' => json_encode($newToken),
            ]);
        }

        $service = new Calendar($client);

        $events = Booking::toGoogleEvents();

        foreach ($events as $item) {
        $booking = $item['booking'];
        $event   = $item['event'];

        $insertedEvent = $service->events->insert('primary', $event);

        // Save Google Calendar event ID back to booking
        Booking::withoutEvents(function () use ($booking, $insertedEvent) {
            $booking->google_event_id = $insertedEvent->id;
            $booking->save();
        });
    }

        Notification::make()
            ->title('Upcoming bookings synced to Google Calendar!')
            ->success()
            ->send();

    }
    
    public function disconnect()
    {
        $user = auth()->user();
        $user->update(['google_token' => null]);
        $this->connected = false;
        Notification::make()
            ->title('Google Calendar disconnected successfully.')
            ->success()
            ->send();
    }

}
