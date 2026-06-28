<?php

namespace App\Console\Commands;

use App\Models\Partners;
use Illuminate\Console\Command;

class GeneratePartnerTokens extends Command
{
    protected $signature = 'partner:generate-tokens';
    protected $description = 'Generate access tokens for all partners';

    public function handle()
    {
        $partners = Partners::whereNull('access_token')->get();
        
        foreach ($partners as $partner) {
            $token = $partner->generateAccessToken();
            $this->info("Generated token for {$partner->name}: {$token}");
        }
        
        $this->info("Tokens generated for {$partners->count()} partners");
    }
}