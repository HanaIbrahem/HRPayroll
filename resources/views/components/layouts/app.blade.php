<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"" data-theme="corporate">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <link rel="shortcut icon" href="{{ asset('logo-small.png') }}" type="image/x-icon">

    <title>{{ env('APP_NAME') }}</title>
</head>

<body class="font-inter bg-base-100 text-base-content">
    <div class="drawer lg:drawer-open h-screen">
        <input id="sidebar-toggle" type="checkbox" class="drawer-toggle" />

        <!-- Main Content -->
        <div class="drawer-content flex flex-col h-screen overflow-hidden">
          
            <!-- Header -->
           
            <x-layouts.header/>
           
            <!-- Page Content -->
            <main class="flex-1 overflow-y-scroll content-scroll p-4 md:p-6 space-y-6">
               {{ $slot }}
            </main>
        </div>

        <!-- Sidebar -->
     
        <x-layouts.sidebar/>
    </div>

    <x-toasts/>
    @livewireScripts

    
</body>

</html>
