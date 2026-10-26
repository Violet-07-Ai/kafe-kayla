<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff Portal - Kafe Kayla')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fdf8f6',
                            100: '#f2e8e5',
                            500: '#9c5138',
                            600: '#7e3e29',
                            700: '#632e1d',
                            800: '#4a2113',
                            900: '#32150a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="bg-stone-100 text-stone-800 min-h-screen flex flex-col antialiased">

    <!-- Staff Header / Navbar -->
    <header class="bg-stone-900 text-white shadow-lg sticky top-0 z-40 border-b border-stone-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand & Role Badge -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <div class="w-9 h-9 rounded-xl bg-amber-600 flex items-center justify-center font-black text-white text-lg shadow-md">
                            K
                        </div>
                        <span class="font-bold text-lg tracking-tight">Kafe Kayla</span>
                    </a>
                    <span class="text-xs uppercase font-extrabold px-2.5 py-1 rounded-full 
                        {{ auth()->user()->role === 'admin' ? 'bg-purple-900 text-purple-200 border border-purple-700' : '' }}
                        {{ auth()->user()->role === 'cashier' ? 'bg-emerald-900 text-emerald-200 border border-emerald-700' : '' }}
                        {{ auth()->user()->role === 'kitchen' ? 'bg-amber-900 text-amber-200 border border-amber-700' : '' }}">
                        {{ auth()->user()->role }}
                    </span>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1">
                    @if(in_array(auth()->user()->role, ['cashier', 'admin']))
                        <a href="{{ route('cashier.dashboard') }}" 
                           class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('cashier.*') ? 'bg-stone-800 text-white shadow-inner' : 'text-stone-300 hover:text-white hover:bg-stone-800/60' }}">
                            🖥️ Terminal Kasir
                        </a>
                    @endif

                    @if(in_array(auth()->user()->role, ['kitchen', 'admin']))
                        <a href="{{ route('kitchen.kds') }}" 
                           class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('kitchen.*') ? 'bg-stone-800 text-white shadow-inner' : 'text-stone-300 hover:text-white hover:bg-stone-800/60' }}">
                            🍳 KDS Dapur
                        </a>
                    @endif

                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" 
                           class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-stone-800 text-white shadow-inner' : 'text-stone-300 hover:text-white hover:bg-stone-800/60' }}">
                            📊 Ringkasan
                        </a>
                        <a href="{{ route('admin.menus') }}" 
                           class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.menus*') ? 'bg-stone-800 text-white shadow-inner' : 'text-stone-300 hover:text-white hover:bg-stone-800/60' }}">
                            ☕ Menu & Variasi
                        </a>
                        <a href="{{ route('admin.tables') }}" 
                           class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.tables*') ? 'bg-stone-800 text-white shadow-inner' : 'text-stone-300 hover:text-white hover:bg-stone-800/60' }}">
                            🪑 Meja & QR
                        </a>
                        <a href="{{ route('admin.staff') }}" 
                           class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.staff*') ? 'bg-stone-800 text-white shadow-inner' : 'text-stone-300 hover:text-white hover:bg-stone-800/60' }}">
                            👥 Staf
                        </a>
                        <a href="{{ route('admin.reports') }}" 
                           class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.reports*') ? 'bg-stone-800 text-white shadow-inner' : 'text-stone-300 hover:text-white hover:bg-stone-800/60' }}">
                            📈 Laporan
                        </a>
                    @endif
                </nav>

                <!-- User profile & Logout -->
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-stone-300 hidden sm:inline">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs bg-stone-800 hover:bg-rose-900/80 text-stone-300 hover:text-white px-3 py-1.5 rounded-xl border border-stone-700 transition flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div class="md:hidden border-t border-stone-800 px-4 py-2 flex overflow-x-auto space-x-2 scrollbar-none">
            @if(in_array(auth()->user()->role, ['cashier', 'admin']))
                <a href="{{ route('cashier.dashboard') }}" class="shrink-0 px-3 py-1 rounded-lg text-xs font-semibold {{ request()->routeIs('cashier.*') ? 'bg-amber-600 text-white' : 'bg-stone-800 text-stone-300' }}">Kasir</a>
            @endif
            @if(in_array(auth()->user()->role, ['kitchen', 'admin']))
                <a href="{{ route('kitchen.kds') }}" class="shrink-0 px-3 py-1 rounded-lg text-xs font-semibold {{ request()->routeIs('kitchen.*') ? 'bg-amber-600 text-white' : 'bg-stone-800 text-stone-300' }}">Dapur</a>
            @endif
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="shrink-0 px-3 py-1 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-amber-600 text-white' : 'bg-stone-800 text-stone-300' }}">Ringkasan</a>
                <a href="{{ route('admin.menus') }}" class="shrink-0 px-3 py-1 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.menus*') ? 'bg-amber-600 text-white' : 'bg-stone-800 text-stone-300' }}">Menu</a>
                <a href="{{ route('admin.tables') }}" class="shrink-0 px-3 py-1 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.tables*') ? 'bg-amber-600 text-white' : 'bg-stone-800 text-stone-300' }}">Meja</a>
                <a href="{{ route('admin.staff') }}" class="shrink-0 px-3 py-1 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.staff*') ? 'bg-amber-600 text-white' : 'bg-stone-800 text-stone-300' }}">Staf</a>
                <a href="{{ route('admin.reports') }}" class="shrink-0 px-3 py-1 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.reports*') ? 'bg-amber-600 text-white' : 'bg-stone-800 text-stone-300' }}">Laporan</a>
            @endif
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center justify-between mb-4 shadow-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 font-bold">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center justify-between mb-4 shadow-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-rose-400 hover:text-rose-600 font-bold">&times;</button>
            </div>
        @endif

        @if(session('warning'))
            <div x-data="{ show: true }" x-show="show" class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-2xl flex items-center justify-between mb-4 shadow-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span class="text-sm font-semibold">{{ session('warning') }}</span>
                </div>
                <button @click="show = false" class="text-amber-400 hover:text-amber-600 font-bold">&times;</button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
