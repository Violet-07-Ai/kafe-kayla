@extends('layouts.app')

@section('title', 'Kafe Kayla - Sistem Pemesanan Kafe Berbasis QR Code')

@section('content')
<div class="min-h-screen flex flex-col justify-between">

    <!-- Top Navigation -->
    <header class="bg-white/80 backdrop-blur-md border-b border-stone-200 sticky top-0 z-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-600 flex items-center justify-center font-black text-white text-xl shadow-md">
                    K
                </div>
                <div>
                    <span class="font-extrabold text-base text-stone-900 block leading-tight">Kafe Kayla</span>
                    <span class="text-[11px] text-stone-500 font-medium">QR Code Ordering System</span>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isCashier() ? route('cashier.dashboard') : route('kitchen.kds')) }}" 
                       class="bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-sm transition">
                        Buka Dashboard ({{ auth()->user()->role }}) &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-sm transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Portal Staf</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero & Demo Navigator -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16 space-y-12 flex-1">

        <!-- Hero Section -->
        <div class="text-center max-w-2xl mx-auto space-y-4">
            <span class="text-xs font-black uppercase tracking-wider text-amber-700 bg-amber-100 border border-amber-200 px-3.5 py-1.5 rounded-full inline-block">
                ✨ Proyek Demonstrasi Web Pemesanan Kafe
            </span>
            <h1 class="text-3xl sm:text-5xl font-black text-stone-900 tracking-tight leading-tight">
                Pesan Menu Mandiri Langsung dari Meja Kafe
            </h1>
            <p class="text-sm sm:text-base text-stone-600 leading-relaxed font-normal">
                Sistem pemesanan modern berbasis QR code unik per meja dengan integrasi kasir, Kitchen Display System (KDS) dapur, pembayaran Tunai & QRIS Sandbox, serta laporan omzet real-time.
            </p>
        </div>

        <!-- QR SIMULATION / MEJA CHOOSER -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200 shadow-xl space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-stone-100 gap-2">
                <div>
                    <h2 class="font-extrabold text-lg text-stone-900">1. Simulasi Pindai QR Code Pelanggan</h2>
                    <p class="text-xs text-stone-500 font-medium">Klik salah satu meja di bawah untuk mensimulasikan pelanggan yang memindai QR code fisik meja:</p>
                </div>
                <span class="text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full self-start sm:self-auto">
                    Tanpa Perlu Login
                </span>
            </div>

            <!-- Table Badges Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                @foreach($sampleTables as $table)
                    <a href="{{ $table->qr_url }}" 
                       class="group bg-stone-50 hover:bg-amber-600 hover:text-white p-4 rounded-2xl border border-stone-200 hover:border-amber-600 transition-all duration-200 text-center shadow-sm hover:shadow-lg hover:-translate-y-0.5">
                        <span class="text-2xl block mb-1">🪑</span>
                        <span class="font-black text-sm block group-hover:text-white text-stone-900">Meja {{ $table->table_number }}</span>
                        <span class="text-[11px] text-stone-400 group-hover:text-amber-100 block mt-0.5">{{ $table->capacity }} Kursi</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- 3 Role Portals Cards -->
        <div class="space-y-4">
            <h2 class="font-extrabold text-lg text-stone-900">2. Akses Antarmuka Staf Kafe</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Kasir Card -->
                <div class="bg-white rounded-3xl p-6 border border-stone-200 shadow-sm hover:shadow-md transition space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl font-black">
                            💵
                        </div>
                        <h3 class="font-extrabold text-base text-stone-900">Terminal Kasir (POS)</h3>
                        <p class="text-xs text-stone-500 leading-relaxed">
                            Konfirmasi pesanan masuk, catat pembayaran tunai, verifikasi QRIS, teruskan pesanan yang sudah lunas ke dapur, dan panggil pesanan siap saji.
                        </p>
                    </div>

                    <a href="{{ route('cashier.dashboard') }}" class="block w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-3 px-4 rounded-xl text-center shadow-md transition">
                        Buka Terminal Kasir &rarr;
                    </a>
                </div>

                <!-- Dapur Card -->
                <div class="bg-white rounded-3xl p-6 border border-stone-200 shadow-sm hover:shadow-md transition space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-2xl font-black">
                            🍳
                        </div>
                        <h3 class="font-extrabold text-base text-stone-900">Kitchen Display System (KDS)</h3>
                        <p class="text-xs text-stone-500 leading-relaxed">
                            Layar khusus koki dapur menampilkan pesanan yang diteruskan kasir secara berurutan, rincian variasi rasa, dan catatan khusus pelanggan secara real-time.
                        </p>
                    </div>

                    <a href="{{ route('kitchen.kds') }}" class="block w-full bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-3 px-4 rounded-xl text-center shadow-md transition">
                        Buka Display Dapur &rarr;
                    </a>
                </div>

                <!-- Admin Card -->
                <div class="bg-white rounded-3xl p-6 border border-stone-200 shadow-sm hover:shadow-md transition space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-800 flex items-center justify-center text-2xl font-black">
                            👑
                        </div>
                        <h3 class="font-extrabold text-base text-stone-900">Panel Administrator</h3>
                        <p class="text-xs text-stone-500 leading-relaxed">
                            Kelola menu & variasi, status ketersediaan, buat nomor meja & generate QR code siap cetak, kelola staf, serta pantau laporan penjualan & omzet.
                        </p>
                    </div>

                    <a href="{{ route('admin.dashboard') }}" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs py-3 px-4 rounded-xl text-center shadow-md transition">
                        Buka Dashboard Admin &rarr;
                    </a>
                </div>

            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-stone-200 py-6 text-center text-xs text-stone-400 font-medium">
        <div class="max-w-6xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} Kafe Kayla • Web Pemesanan Kafe Berbasis QR Code (Laravel 12 & MySQL)</p>
        </div>
    </footer>

</div>
@endsection
