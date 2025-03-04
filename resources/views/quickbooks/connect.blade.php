<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $title ?? 'Connect to QuickBooks' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        {{ $message ?? 'Connect your QuickBooks account to enable invoice creation' }}
                    </h3>
                    
                    <div class="mt-8 flex justify-center">
                        <a href="#" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            Connect to QuickBooks
                        </a>
                    </div>
                    
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                        You'll be redirected to QuickBooks to authorize access to your account.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
