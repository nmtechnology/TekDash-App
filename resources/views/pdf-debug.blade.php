<!DOCTYPE html>
<html>
<head>
    <title>PDF Access Troubleshooting</title>
    <style>
        body { font-family: sans-serif; margin: 40px; line-height: 1.6; }
        .card { background: #f8f9fa; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 4px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f1f1f1; padding: 10px; border-radius: 4px; overflow: auto; }
    </style>
</head>
<body>
    <h1>PDF Access Troubleshooting</h1>
    
    <div class="card">
        <h2>Storage Link Status</h2>
        @if(file_exists(public_path('storage')))
            <p class="success">✓ Storage symbolic link exists</p>
        @else
            <p class="error">✗ Storage symbolic link is missing. Run: php artisan storage:link</p>
        @endif
    </div>
    
    <div class="card">
        <h2>Filesystem Configuration</h2>
        <pre>Default Disk: {{ config('filesystems.default') }}</pre>
        <pre>Public Disk URL: {{ config('filesystems.disks.public.url') }}</pre>
    </div>
    
    <div class="card">
        <h2>Sample Files in Storage</h2>
        @php
            $files = [];
            if(is_dir(storage_path('app/public'))) {
                $files = array_slice(scandir(storage_path('app/public')), 2);
            }
        @endphp
        
        @if(count($files) > 0)
            <ul>
                @foreach($files as $file)
                    <li>
                        {{ $file }} 
                        @if(str_ends_with(strtolower($file), '.pdf'))
                            - <a href="{{ asset('storage/'.$file) }}" target="_blank">Test Link</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p>No files found in storage/app/public</p>
        @endif
    </div>
</body>
</html>
