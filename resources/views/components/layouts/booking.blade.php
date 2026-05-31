<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
{{-- <html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  class="dark"> --}}
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      class="{{ $company?->booking_form_dark_mode ? 'dark' : '' }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>
        {{ config('app.name', 'Keyfleet') }} - @yield('title', 'Smarter Car Rentals')
    </title>
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
<body class="flex flex-col min-h-screen text-gray-900 bg-white-100 dark:bg-gray-900 dark:text-gray-100">


    {{-- Main Content --}}
    <main class="flex-grow">
        {{ $slot }}
    </main>


    {{-- Livewire Notifications --}}
    @livewire('notifications')

    @livewireScripts
    @filamentScripts
    
    <footer class="py-2 text-center bg-gray-100 shadow-inner dark:bg-gray-800">
        <p class="text-xs text-gray-600 dark:text-gray-300">
        Powered by <a href="{{ url('/') }}" target="_blank" class="font-semibold text-[var(--tw-primary)]">KEYFLEET</a>
        </p>
    </footer>

</body>
</html>
