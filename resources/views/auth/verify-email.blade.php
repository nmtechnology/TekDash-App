<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col justify-center items-center">
        <div class="bg-white bg-opacity-30 backdrop-blur-md shadow-lg rounded-lg p-8 w-full max-w-md">
            <h1 class="text-2xl font-bold mb-4 text-center text-gray-800">Verify Your Email Address</h1>
            <p class="text-gray-700 mb-6 text-center">Before proceeding, please check your email for a verification link.</p>
            <p class="text-gray-700 text-center">If you did not receive the email, click the button below to request another.</p>

            <form method="POST" action="{{ route('verification.send') }}" class="mt-6 flex justify-center">
                @csrf
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Resend Verification Email
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('logout') }}" class="text-blue-600 hover:underline">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
