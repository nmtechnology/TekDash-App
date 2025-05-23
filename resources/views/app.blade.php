<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM8e+4z5e5f5e5f5e5f5e5f5e5f5e5f5e5f5" crossorigin="anonymous">

        <!-- Scripts and Styles -->
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.js'])
         
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-gray-900 text-white">
        @inertia
    </body>
</html>
