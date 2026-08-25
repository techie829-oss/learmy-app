@php
    use App\Models\SystemSetting;

    try {
        $appName  = SystemSetting::get('app_name') ?: config('app.name', 'Learmy');
        $logoPath = SystemSetting::get('app_logo_path');
        $logoUrl  = $logoPath
            ? \Illuminate\Support\Facades\Storage::disk(SystemSetting::get('app_logo_disk', 'public'))->url($logoPath)
            : null;
        $faviconPath = SystemSetting::get('app_favicon_path');
        $faviconUrl  = $faviconPath
            ? \Illuminate\Support\Facades\Storage::disk(SystemSetting::get('app_favicon_disk', 'public'))->url($faviconPath)
            : null;
    } catch (\Throwable) {
        $appName  = config('app.name', 'Learmy');
        $logoUrl  = null;
        $faviconUrl = null;
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} · {{ $title }} – {{ $appName }}</title>
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @else
        <link rel="icon" type="image/png" href="/logonew.png">
        <link rel="alternate icon" href="/favicon.ico" sizes="any">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        body {
            display: flex; flex-direction: column; justify-content: space-between;
            color: #0f172a; padding: 2.5rem 1.5rem; background-color: #f8fafc;
        }

        .header {
            width: 100%; max-width: 1200px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
        }
        .brand {
            display: inline-flex; align-items: center; gap: 0.65rem;
            font-weight: 800; font-size: 1.25rem; color: #0f172a; text-decoration: none; letter-spacing: -0.02em;
        }
        .brand img { height: 2rem; max-width: 160px; object-fit: contain; display: block; }
        .brand-fallback {
            width: 2rem; height: 2rem; border-radius: 0.5rem; display: inline-flex;
            align-items: center; justify-content: center; background: #4f46e5;
            color: #ffffff; font-weight: 800; font-size: 1rem;
        }

        .main {
            width: 100%; max-width: 440px; margin: auto; text-align: center;
        }
        .card {
            background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem;
            padding: 2.5rem 2rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .icon-box {
            width: 3.5rem; height: 3.5rem; margin: 0 auto 1.25rem; border-radius: 0.75rem;
            background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center;
        }
        .code-subtitle {
            font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;
            color: #4f46e5; margin-bottom: 0.25rem;
        }
        h1 { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem; letter-spacing: -0.02em; }
        p { color: #64748b; font-size: 0.875rem; margin: 0 auto 2rem; line-height: 1.6; max-width: 32ch; }

        .actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1.25rem;
            border-radius: 0.5rem; text-decoration: none; font-weight: 600; font-size: 0.875rem;
            transition: background 0.15s ease; cursor: pointer; border: 0;
        }
        .btn-primary { background: #4f46e5; color: #ffffff; }
        .btn-primary:hover { background: #4338ca; }
        .btn-ghost { background: #ffffff; color: #334155; border: 1px solid #cbd5e1; }
        .btn-ghost:hover { background: #f1f5f9; }

        .footer { text-align: center; font-size: 0.75rem; color: #94a3b8; }

        @media (prefers-color-scheme: dark) {
            body { background-color: #0f172a; color: #f8fafc; }
            .brand { color: #ffffff; }
            .card { background: #1e293b; border-color: #334155; }
            .icon-box { background: rgba(99, 102, 241, 0.15); color: #818cf8; }
            .code-subtitle { color: #818cf8; }
            h1 { color: #ffffff; }
            p { color: #94a3b8; }
            .btn-ghost { background: #1e293b; color: #e2e8f0; border-color: #475569; }
            .btn-ghost:hover { background: #334155; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="{{ url('/') }}" class="brand" aria-label="{{ $appName }}">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $appName }}">
            @else
                <span class="brand-fallback">{{ strtoupper(substr($appName, 0, 1)) }}</span>
                <span>{{ $appName }}</span>
            @endif
        </a>
    </div>

    <main class="main">
        <div class="card">
            <div class="icon-box">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
            </div>
            <div class="code-subtitle">Error {{ $code }}</div>
            <h1>{{ $title }}</h1>
            <p>{{ $message }}</p>
            <div class="actions">
                <a href="{{ url('/app/dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                <a href="javascript:history.back()" class="btn btn-ghost">Go Back</a>
            </div>
        </div>
    </main>

    <div class="footer">
        &copy; {{ date('Y') }} {{ $appName }}. All rights reserved.
    </div>
</body>
</html>
