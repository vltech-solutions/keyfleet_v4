<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Filament\Notifications\Notification;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Authentication Failed')
                ->danger()
                ->send();

            return redirect()->to('/app/login');
        }

        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            Notification::make()
                ->title('Account Not Found')
                ->body('This Gmail account is not registered in Keyfleet.')
                ->danger()
                ->persistent()
                ->send();

            return redirect()->to('/app/login');
        }

        if (!$user->google_id) {
            $user->update(['google_id' => $googleUser->id]);
        }

        Auth::guard('web')->login($user);
        
        session()->regenerate();

        $tenant = $user->companies()->first(); 

        if ($tenant) {
            return redirect()->intended("/app/{$tenant->slug}");
        }

        return redirect()->intended('/app');
    }
}