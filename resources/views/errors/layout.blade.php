@php
    use App\Models\SystemSetting;

    try {
        $appName  = SystemSetting::get('app_name') ?: config('app.name', 'Learmy');
        $primary  = SystemSetting::get('primary_color') ?: '#6366F1';
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
        $primary  = '#6366F1';
        $logoUrl  = null;
        $faviconUrl = null;
    }

    $hex = ltrim($primary, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    [$r, $g, $b] = strlen($hex) === 6
        ? [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))]
        : [99, 102, 241];
    $luminance = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
    $onPrimary = $luminance > 0.6 ? '#0f172a' : '#ffffff';
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: {{ $primary }};
            --on-primary: {{ $onPrimary }};
            --ink: #0f172a;
            --ink-soft: #64748b;
            --surface: #f8fafc;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        body {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: var(--ink); padding: 2rem; position: relative; overflow: hidden;
            background-color: #0f172a;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.25) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.2) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(59, 130, 246, 0.15) 0px, transparent 50%);
        }

        /* Ambient floating glow ORBs */
        .orb-1 {
            position: fixed; width: 35rem; height: 35rem; border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            filter: blur(100px); opacity: 0.25; top: -10rem; left: -10rem; pointer-events: none;
        }
        .orb-2 {
            position: fixed; width: 30rem; height: 30rem; border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            filter: blur(100px); opacity: 0.2; bottom: -10rem; right: -10rem; pointer-events: none;
        }

        .brand {
            position: fixed; top: 2rem; left: 2.25rem; z-index: 10;
            display: inline-flex; align-items: center; gap: 0.75rem;
            font-weight: 800; font-size: 1.35rem; color: #ffffff; text-decoration: none; letter-spacing: -0.025em;
        }
        .brand img { height: 2.25rem; max-width: 180px; object-fit: contain; display: block; }
        .brand-logo-fallback {
            width: 2.25rem; height: 2.25rem; border-radius: 0.65rem; display: inline-flex;
            align-items: center; justify-content: center; background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #ffffff; font-weight: 800; font-size: 1.15rem; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .card {
            position: relative; z-index: 5; background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 1.5rem;
            padding: 3.5rem 3rem; max-width: 480px; width: 100%; text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.1);
        }

        .code-badge {
            display: inline-block; font-size: clamp(4.5rem, 14vw, 6.5rem); font-weight: 800; line-height: 1;
            letter-spacing: -0.04em; margin-bottom: 0.75rem;
            background: linear-gradient(135deg, #ffffff 30%, #818cf8 100%);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
            text-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        h1 { font-size: 1.5rem; font-weight: 700; color: #ffffff; margin: 0 0 0.75rem; letter-spacing: -0.02em; }
        p { color: #94a3b8; font-size: 0.95rem; margin: 0 auto 2.25rem; line-height: 1.6; max-width: 34ch; }

        .actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1.65rem;
            border-radius: 0.75rem; text-decoration: none; font-weight: 600; font-size: 0.925rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; border: 0;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5); color: #ffffff;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
        }
        .btn-primary:hover {
            transform: translateY(-2px); box-shadow: 0 14px 26px -5px rgba(99, 102, 241, 0.5);
            background: linear-gradient(135deg, #4f46e5, #4338ca);
        }
        .btn-ghost {
            background: rgba(255, 255, 255, 0.05); color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.1); color: #ffffff;
            transform: translateY(-2px); border-color: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>
    <div class="orb-1"></div>
    <div class="orb-2"></div>

    <a href="{{ url('/') }}" class="brand" aria-label="{{ $appName }}">
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $appName }}">
        @else
            <span class="brand-logo-fallback">{{ strtoupper(substr($appName, 0, 1)) }}</span>
            <span>{{ $appName }}</span>
        @endif
    </a>

    <main class="card">
        <div class="code-badge">{{ $code }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        <div class="actions">
            <a href="{{ url('/app/dashboard') }}" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Go to Dashboard
            </a>
            <a href="javascript:history.back()" class="btn btn-ghost">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Go Back
            </a>
        </div>
    </main>
</body>
</html>
