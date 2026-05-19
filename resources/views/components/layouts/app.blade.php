<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AI Auto Grader' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,600;0,700;0,800;0,900;1,400&family=Source+Sans+3:wght@300..700&family=Vollkorn:ital,wght@0,400;0,600;1,400&family=Noto+Sans:wght@400;500;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        window.AppConfig = {
            baseUrl: @json(rtrim(config('app.url'), '/')),
        };
    </script>
</head>

<body class="antialiased">
    <div class="app-shell" x-data="{ sidebarOpen: false }">
        {{-- Topbar --}}
        <header class="topbar">
            <div class="flex items-center gap-4 flex-1">
                {{-- Mobile sidebar toggle --}}
                <button id="sidebar-toggle" class="lg:hidden text-txt-inverse" aria-label="Toggle sidebar">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                {{-- Logo --}}
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 text-txt-inverse hover:text-txt-inverse">
                    <div
                        class="w-8 h-8 bg-brand-accent rounded-md flex items-center justify-center font-heading font-black text-body-sm">
                        AI</div>
                    <span class="font-heading font-bold text-body-lg tracking-wide hidden sm:inline">AI AUTO GRADER</span>
                </a>
            </div>

            {{-- User Menu --}}
            <div class="flex items-center gap-4" x-data="{ open: false }">
                <button @click="open = !open"
                    class="flex items-center gap-2 text-txt-inverse hover:text-brand-accent transition-colors">
                    @if (Auth::user()->avatar)
                        <img src="{{ Auth::user()->avatar }}" alt="" class="w-8 h-8 rounded-full">
                    @else
                        <div
                            class="w-8 h-8 rounded-full bg-brand-accent flex items-center justify-center font-heading font-bold text-caption">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                    @endif
                    <span class="text-body-sm hidden md:inline">{{ Auth::user()->name }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false" x-transition
                    class="absolute right-4 top-14 bg-surface-panel rounded-lg shadow-lg border border-border py-2 w-48 z-50">
                    <div class="px-4 py-2 border-b border-border-subtle">
                        <p class="text-body-sm font-medium text-txt-primary">{{ Auth::user()->name }}</p>
                        <p class="text-caption text-txt-muted">{{ Auth::user()->email }}</p>
                    </div>
                    <a href="{{ route('dashboard') }}"
                        class="block px-4 py-2 text-body-sm text-txt-secondary hover:bg-surface-panel-muted">Dashboard</a>
                    @if (Auth::user()->isAdmin())
                        <a href="{{ route('admin.analytics.index') }}"
                            class="block px-4 py-2 text-body-sm text-txt-secondary hover:bg-surface-panel-muted">Admin
                            Panel</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2 text-body-sm text-status-danger-text hover:bg-status-danger-bg">Sign
                            Out</button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Sidebar --}}
        <aside id="sidebar" class="sidebar transition-transform lg:translate-x-0 -translate-x-full">
            <nav class="flex-1 p-4 space-y-1">
                <p class="px-4 py-2 text-caption font-heading font-semibold text-txt-muted uppercase tracking-wider">
                    Main</p>

                <a href="{{ route('dashboard') }}"
                    class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('ai.evaluator') }}"
                    class="nav-item {{ request()->routeIs('ai.evaluator*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    AI Autograder
                </a>

                @if (Auth::user()->isAdmin())
                    <p
                        class="px-4 py-2 mt-4 text-caption font-heading font-semibold text-txt-muted uppercase tracking-wider">
                        Administration</p>

                    <a href="{{ route('admin.users.index') }}"
                        class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        User Management
                    </a>

                    <a href="{{ route('admin.stories.index') }}"
                        class="nav-item {{ request()->routeIs('admin.stories.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Story Management
                    </a>

                    <a href="{{ route('admin.config.index') }}"
                        class="nav-item {{ request()->routeIs('admin.config.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Configuration
                    </a>

                    <a href="{{ route('admin.audit.index') }}"
                        class="nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Audit Logs
                    </a>

                    <a href="{{ route('admin.analytics.index') }}"
                        class="nav-item {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Analytics
                    </a>
                @endif
            </nav>
        </aside>

        {{-- Mobile overlay --}}
        <div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/40 z-20 lg:hidden"></div>

        {{-- Main Content --}}
        <main class="page-shell">
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="alert-success mx-6 mt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('success') }}
                    <button @click="show = false" class="ml-auto">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert-danger mx-6 mt-4" x-data="{ show: true }" x-show="show">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('error') }}
                    <button @click="show = false" class="ml-auto">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-danger mx-6 mt-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>

</html>
