<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Events') — {{ config('app.name') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Fraunces:opsz,wght@9..144,300;9..144,500;9..144,700;9..144,900&display=swap" rel="stylesheet">

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Fraunces', 'serif'],
                        body: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        surface: {
                            DEFAULT: '#0c0c10',
                            50: '#12121a',
                            100: '#181822',
                            200: '#1e1e2a',
                            300: '#2a2a38',
                        },
                        lime: {
                            DEFAULT: '#c8f542',
                            dim: '#a3c934',
                            glow: 'rgba(200, 245, 66, 0.12)',
                        },
                        ink: {
                            DEFAULT: '#eeeef2',
                            secondary: '#9494a8',
                            muted: '#5c5c72',
                        },
                    },
                },
            },
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        body {
            font-family: 'Outfit', sans-serif;
            background: #0c0c10;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #12121a; }
        ::-webkit-scrollbar-thumb { background: #2a2a38; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #3a3a4a; }

        /* Fade-in animation */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up {
            animation: fadeUp 0.5s ease-out forwards;
            opacity: 0;
        }

        /* Stagger children */
        .stagger > *:nth-child(1) { animation-delay: 0.05s; }
        .stagger > *:nth-child(2) { animation-delay: 0.1s; }
        .stagger > *:nth-child(3) { animation-delay: 0.15s; }
        .stagger > *:nth-child(4) { animation-delay: 0.2s; }
        .stagger > *:nth-child(5) { animation-delay: 0.25s; }
        .stagger > *:nth-child(6) { animation-delay: 0.3s; }
        .stagger > *:nth-child(7) { animation-delay: 0.35s; }
        .stagger > *:nth-child(8) { animation-delay: 0.4s; }

        /* Slide-in for modal */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .animate-slide-up {
            animation: slideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Backdrop blur */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-fade-in {
            animation: fadeIn 0.2s ease-out forwards;
        }

        /* Grain texture */
        .grain::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.025'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 9999;
        }

        /* Glow dot on cards */
        .glow-dot {
            background: radial-gradient(circle, #c8f542 0%, transparent 70%);
        }
    </style>

    @stack('styles')
</head>
<body class="grain min-h-screen text-ink antialiased">

    {{-- Top Navigation --}}
    <nav class="sticky top-0 z-50 border-b border-surface-300/50 bg-surface/80 backdrop-blur-xl">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-lime/10 border border-lime/20 transition group-hover:bg-lime/20">
                    <svg class="h-4 w-4 text-lime" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="font-display text-lg font-700 tracking-tight text-ink">{{ config('app.name', 'Eventory') }}</span>
            </a>

            <div class="flex items-center gap-4">
                    <a href="/admin" class="rounded-lg border border-surface-300 px-4 py-2 text-sm text-ink-secondary transition hover:border-lime/30 hover:text-ink">
                        Login
                    </a>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="fixed top-20 right-6 z-50 flex items-center gap-3 rounded-xl border border-lime/20 bg-surface-100 px-5 py-3 shadow-2xl shadow-lime/5">
            <div class="h-2 w-2 rounded-full bg-lime"></div>
            <span class="text-sm font-medium text-ink">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="fixed top-20 right-6 z-50 flex items-center gap-3 rounded-xl border border-red-500/20 bg-surface-100 px-5 py-3 shadow-2xl">
            <div class="h-2 w-2 rounded-full bg-red-500"></div>
            <span class="text-sm font-medium text-ink">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="mx-auto max-w-6xl px-6 py-10">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-surface-300/30 mt-20">
        <div class="mx-auto max-w-6xl px-6 py-8 flex items-center justify-between">
            <span class="text-sm text-ink-muted">&copy; {{ date('Y') }} {{ config('app.name', 'Eventory') }}</span>
            <span class="text-xs text-ink-muted">Built with Laravel & Tailwind</span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
