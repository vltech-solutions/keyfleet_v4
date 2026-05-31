<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name', 'Keyfleet') }} - @yield('title', 'Smarter Car Rentals')</title>
    <meta name="description" content="@yield('description', 'Run your rental business smarter with Keyfleet.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @filamentStyles

    <style>
        .keyfleet-gradient {
            background-image: linear-gradient(to right, #0047AB, #0a66c2);
        }
    </style>
</head>
<body class="min-h-screen">

    <x-ui.nav />

    {{ $slot }}

    <x-ui.footer />

    @livewire('notifications')
    @livewireScripts
    @filamentScripts
</body>
</html>
