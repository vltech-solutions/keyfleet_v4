<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
      x-init="
          // Check if dark mode should be enabled
          if (darkMode) {
              document.documentElement.classList.add('dark');
              localStorage.setItem('theme', 'dark');
          } else {
              // Set dark as default if no preference exists
              if (!localStorage.getItem('theme') && !window.matchMedia('(prefers-color-scheme: dark)').matches) {
                  document.documentElement.classList.add('dark');
                  localStorage.setItem('theme', 'dark');
                  darkMode = true;
              }
          }
          
          $watch('darkMode', val => {
              if (val) {
                  document.documentElement.classList.add('dark');
                  localStorage.setItem('theme', 'dark');
              } else {
                  document.documentElement.classList.remove('dark');
                  localStorage.setItem('theme', 'light');
              }
          });
      "
      :class="darkMode ? 'dark' : ''">
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
        .dark .keyfleet-gradient {
            background-image: linear-gradient(to right, #111827, #0a0f1a);
        }
        
        /* Force dark mode on body by default */
        body {
            background-color: #111827;
            color: #f3f4f6;
        }
        
        .dark body {
            background-color: #111827;
            color: #f3f4f6;
        }
        
        /* Light mode overrides */
        body:not(.dark) {
            background-color: #ffffff;
            color: #111827;
        }
    </style>
</head>
<body class="min-h-screen bg-white dark:bg-gray-900 transition-colors duration-300">

    <x-ui.nav />

    {{ $slot }}

    <x-ui.footer />

    @livewire('notifications')
    @livewireScripts
    @filamentScripts
    
    <script>
        // Force dark mode on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (!localStorage.getItem('theme')) {
                localStorage.setItem('theme', 'dark');
                document.documentElement.classList.add('dark');
            }
        });
    </script>
</body>
</html>