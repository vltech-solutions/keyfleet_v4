<?php

namespace App\Support;

class ContractDefaults
{
    public static function settings(): array
    {
        return [
            'show_customer_information' => true,
            'show_vehicle_information' => true,
            'show_rental_schedule' => true,
            'show_rental_charges' => true,
            'show_other_drivers' => true,
            'show_security_deposit' => true,

            'show_renter_signature' => true,
            'show_representative_signature' => true,

            'header_text' => null,
            'footer_text' => null,
        ];
    }

    public static function clauses(): array
    {
        return [
            [
                'type' => 'rental_use',
                'title' => 'Rental Use',
                'body' => 'The renter agrees to use the vehicle responsibly and only for lawful purposes.',
            ],
            [
                'type' => 'fuel_policy',
                'title' => 'Fuel Policy',
                'body' => 'The vehicle must be returned with the agreed fuel level. Applicable fuel charges may be collected when necessary.',
            ],
            [
                'type' => 'late_return',
                'title' => 'Late Return',
                'body' => 'Additional charges may apply when the vehicle is returned beyond the agreed rental period.',
            ],
            [
                'type' => 'damage_accident',
                'title' => 'Damage and Accident',
                'body' => 'Any accident, damage, or incident involving the vehicle must be reported immediately to the rental company.',
            ],
            [
                'type' => 'unauthorized_driver',
                'title' => 'Authorized Drivers',
                'body' => 'Only drivers authorized under this rental agreement may operate the vehicle.',
            ],
            [
                'type' => 'cancellation',
                'title' => 'Cancellation',
                'body' => 'Cancellation and reservation fees are subject to the rental company’s applicable cancellation policy.',
            ],
        ];
    }
}