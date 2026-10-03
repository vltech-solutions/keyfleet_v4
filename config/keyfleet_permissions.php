<?php

$groups = [
    'Dashboard' => ['dashboard.view'],
    'Bookings' => [
        'bookings.view', 'bookings.create', 'bookings.update', 'bookings.delete',
        'bookings.cancel', 'bookings.import', 'bookings.export', 'bookings.print',
        'bookings.payments',
    ],
    // 'Quotations' => [
    //     'quotations.view', 'quotations.create', 'quotations.update', 'quotations.delete',
    //     'quotations.import', 'quotations.export', 'quotations.convert', 'quotations.print',
    // ],
    'Reservations' => [
        'reservations.view', 'reservations.update', 'reservations.approve', 'reservations.cancel',
    ],
    'Customers' => [
        'customers.view', 'customers.create', 'customers.update', 'customers.delete',
        'customers.export', 'customers.requirements',
    ],
    'Fleet' => [
        'cars.view', 'cars.create', 'cars.update', 'cars.delete',
        'car_documents.view', 'car_documents.create', 'car_documents.update', 'car_documents.delete',
    ],
    'Finance' => [
        'expenses.view', 'expenses.create', 'expenses.update', 'expenses.delete',
        'expenses.import', 'expenses.export', 'fund_types.view', 'fund_types.create',
        'fund_types.update', 'fund_types.delete',
    ],
    'Partners' => [
        'partners.view', 'partners.create', 'partners.update', 'partners.delete', 'partners.tokens',
    ],
    'Settings' => [
        'sources.view', 'sources.create', 'sources.update', 'sources.delete',
        'settings.view', 'settings.update', 'contracts.manage',
    ],
    'Inspections' => [
        'checklist_items.view', 'checklist_items.create', 'checklist_items.update',
        'checklist_items.delete', 'inspections.view', 'inspections.manage', 'inspections.print',
    ],
    'Calendar' => ['calendar.view'],
    'Reports' => ['reports.view', 'reports.financial'],
    'Subscription' => ['subscription.view', 'subscription.manage'],
    'Access Control' => [
        'users.view', 'users.create', 'users.update', 'users.deactivate',
        'roles.view', 'roles.create', 'roles.update', 'roles.delete',
    ],
];

$permissions = [];

foreach ($groups as $module => $keys) {
    foreach ($keys as $key) {
        $permissions[$key] = [
            'module' => $module,
            'description' => str($key)->replace('.', ' ')->headline()->toString(),
        ];
    }
}

return $permissions;
