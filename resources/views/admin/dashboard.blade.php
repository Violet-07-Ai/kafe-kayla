@extends('layouts.staff')

@section('title', 'Admin Dashboard - Kafe Kayla')

@section('content')
<div class="space-y-6">

    <!-- Header Greeting -->
    <div class="bg-gradient-to-r from-stone-900 to-stone-800 text-white rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Panel Manajemen Kafe</span>
            <h1 class="text-2xl sm:text-3xl font-black mt-1">Halo, {{ auth()->user()->name }}! 👋</h1>
            <p class="text-xs text-stone-300 mt-1">Berikut ringkasan performa penjualan dan operasional Kafe Kayla hari ini.</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.reports') }}" class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-md transition">
                📊 Buka Laporan Lengkap
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-2">
            <span class="text-xs font-extrabold uppercase tracking-wider text-stone-400">Omzet Hari Ini</span>
            <div class="text-2xl font-black text-stone-900">Rp {{ number_format($stats['omzet_today'], 0, ',', '.') }}</div>
            <span class="text-xs text-emerald-600 font-bold block">{{ $stats['orders_today'] }} transaksi hari ini</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-2">
            <span class="text-xs font-extrabold uppercase tracking-wider text-stone-400">Omzet Bulan Ini</span>
            <div class="text-2xl font-black text-stone-900">Rp {{ number_format($stats['omzet_month'], 0, ',', '.') }}</div>
            <span class="text-xs text-indigo-600 font-bold block">{{ $stats['orders_month'] }} transaksi bulan ini</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-2">
            <span class="text-xs font-extrabold uppercase tracking-wider text-stone-400">Menu Aktif</span>
            <div class="text-2xl font-black text-amber-600">{{ $stats['total_menus'] }}</div>
            <a href="{{ route('admin.menus') }}" class="text-xs text-stone-500 hover:text-amber-700 font-bold block">Kelola Menu &rarr;</a>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-2">
            <span class="text-xs font-extrabold uppercase tracking-wider text-stone-400">Meja Aktif</span>
            <div class="text-2xl font-black text-purple-600">{{ $stats['total_tables'] }}</div>
            <a href="{{ route('admin.tables') }}" class="text-xs text-stone-500 hover:text-purple-700 font-bold block">Kelola Meja & QR &rarr;</a>
        </div>
    </div>

    <!-- Recent Orders & Best Sellers Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- 8 Recent Orders -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-5 border border-stone-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-stone-100">
                <h3 class="font-extrabold text-sm text-stone-900">Transaksi Pesanan Terbaru</h3>
                <a href="{{ route('cashier.dashboard') }}" class="text-xs font-bold text-amber-700 hover:underline">Buka Kasir &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-stone-400 font-bold border-b border-stone-100 uppercase tracking-wider text-[10px]">
                            <th class="py-2.5">No. Order</th>
                            <th class="py-2.5">Meja</th>
                            <th class="py-2.5">Pelanggan</th>
                            <th class="py-2.5">Total</th>
                            <th class="py-2.5">Status</th>
                            <th class="py-2.5">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-stone-50 transition">
                                <td class="py-3 font-mono font-bold text-stone-900">{{ $order->order_number }}</td>
                                <td class="py-3 font-extrabold">Meja {{ $order->table->table_number }}</td>
                                <td class="py-3 text-stone-600">{{ $order->customer_name }}</td>
                                <td class="py-3 font-extrabold text-stone-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold
                                        {{ $order->order_status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $order->order_status === 'in_kitchen' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                        {{ $order->order_status === 'pending_confirmation' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ $order->order_status === 'confirmed' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $order->order_status === 'ready_to_serve' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $order->order_status === 'cancelled' ? 'bg-rose-100 text-rose-800' : '' }}">
                                        {{ $order->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 text-stone-400 text-[11px]">{{ $order->created_at->format('H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-stone-400">Belum ada transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5 Best Seller Menus -->
        <div class="bg-white rounded-3xl p-5 border border-stone-200 shadow-sm space-y-4">
            <h3 class="font-extrabold text-sm text-stone-900 pb-2 border-b border-stone-100">
                ⭐ Menu Terlaris
            </h3>

            <div class="space-y-3">
                @forelse($bestSellers as $idx => $menu)
                    <div class="flex items-center justify-between p-2.5 rounded-2xl bg-stone-50 border border-stone-200/60">
                        <div class="flex items-center space-x-3">
                            <span class="w-6 h-6 rounded-full bg-amber-500 text-stone-950 font-black text-xs flex items-center justify-center shrink-0">
                                {{ $idx + 1 }}
                            </span>
                            <div>
                                <h4 class="font-bold text-xs text-stone-900">{{ $menu->name }}</h4>
                                <span class="text-[11px] text-stone-500 font-medium">Rp {{ number_format($menu->base_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <span class="text-xs font-black text-amber-800 bg-amber-100 px-2.5 py-1 rounded-xl">
                            {{ $menu->total_sold ?? 0 }} terjual
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-stone-400 italic">Belum ada data penjualan menu.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
