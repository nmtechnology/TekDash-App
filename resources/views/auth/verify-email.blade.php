<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Email Verification - {{ config('app.name', 'TekDash') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col justify-center items-center">
        <div class="mb-5 logo-container">
            <!-- Application logo -->
            <img src="https://www.nmtechnology.us/build/assets/nm-logo-rmbg-f8bd446d.webp" alt="nmtis-logo" class="animated-logo">
        </div>
        
        <style>
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            perspective: 1000px;
        }
        
        .animated-logo {
            width: 130px;
            height: 70px;
            animation: spin-pause 15s linear infinite;
            transform-style: preserve-3d;
        }
        
        @keyframes spin-pause {
            0%, 40% {
                transform: rotateY(0deg);
            }
            100% {
                transform: rotateY(360deg);
            }
        }
        
        .glossy-card {
            background: linear-gradient(135deg, rgba(17, 24, 39, 0.95), rgba(31, 41, 55, 0.85));
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
        }
        </style>
        <div class="glossy-card p-8 w-full max-w-md">
            <h1 class="text-2xl font-bold mb-4 text-center text-lime-400">Verify Your Email Address</h1>
            
            @if (session('status'))
                <div class="mb-4 text-sm font-medium text-green-500 bg-green-100 bg-opacity-20 p-3 rounded">
                    {{ session('status') }}
                </div>
            @endif
            
            <p class="text-gray-300 mb-6 text-center">Before proceeding, please check your email for a verification link.</p>
            <p class="text-gray-300 text-center">If you did not receive the email, click the button below to request another.</p>

            <form method="POST" action="{{ route('verification.send') }}" class="mt-6 flex justify-center">
                @csrf
                <button type="submit" class="px-6 py-3 bg-lime-600 text-white rounded-lg hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition">
                    Resend Verification Email
                </button>
            </form>

            <div class="mt-6 text-center">
                <form method="POST" action="{{ route('logout') }}" class="inline-block">
                    @csrf
                    <button type="submit" class="text-lime-400 hover:text-lime-300 transition hover:underline">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
