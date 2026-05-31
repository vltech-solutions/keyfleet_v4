<?php

namespace App\Support;

class RequirementOptions
{
    public static function all(): array
    {
        return [
            'primary_id' => [
                'label' => 'Primary ID',
                'helper' => 'Upload a valid government-issued ID (e.g., Driver’s License, Passport, or PhilSys National ID).',
                'required' => true,
                'sample' => 'Driver’s License, Passport, or PhilSys National ID'
            ],
            'secondary_id' => [
                'label' => 'Secondary ID',
                'helper' => 'Upload a supporting ID (e.g., Company ID, Barangay Clearance, Police/NBI Clearance, Senior Citizen ID, UMID, or Cedula).',
                'required' => true,
                'sample' => 'Company ID, Barangay Clearance, Police/NBI Clearance, Senior Citizen ID, UMID, or Cedula'
            ],
            'proof_of_billing' => [
                'label' => 'Proof of Billing',
                'helper' => 'Upload a recent billing (e.g., Meralco, water, internet, or any billing statement) showing the same address as your primary ID.',
                'required' => true,
                'sample' => 'Meralco, water, internet, or any billing statement'
            ],
            'selfie_with_primary_id' => [
                'label' => 'Selfie with Primary ID',
                'helper' => 'Upload a clear selfie holding your primary ID.',
                'required' => true,
                'sample' => ''
            ],
            'additional_driver_license' => [
                'label' => 'Other Driver',
                'helper' => 'Upload Driver’s License if driver ≠ renter.',
                'required' => false,
                'sample' => ''
            ],
        ];
    }

    public static function list(?array $enabled = null): array
    {
        $all = collect(self::all());

        if ($enabled) {
            // Filter only the enabled keys
            $all = $all->only($enabled);
        }

        return $all->mapWithKeys(fn ($item, $key) => [$key => $item['label']])
                ->toArray();
    }

}