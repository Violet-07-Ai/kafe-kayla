@extends('layouts.staff')

@section('title', 'Laporan Penjualan & Transaksi - Kafe Kayla')

@section('content')
<div class="space-y-6">

    <!-- Header & Date Filter Form -->
    <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h1 class="text-xl font-black text-stone-900 tracking-tight">Laporan Keuangan & Transaksi</h1>
                <p class="text-xs text-stone-500 font-medium">Statistik penjualan harian, bulanan, dan performa menu</p>
            </div>
            <button onclick="window.print()" class="self-start sm:self-auto bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold text-xs py-2 px-3.5 rounded-xl border border-stone-200 transition">
                🖨️ Cetak Laporan
            </button>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.reports') }}" class="flex flex-wrap items-end gap-3 pt-2 border-t border-stone-100 print:hidden">
            <div>
                <label class="block text-[11px] font-bold text-stone-600 mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="text-xs p-2.5 rounded-xl border border-stone-200 outline-none">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-stone-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="text-xs p-2.5 rounded-xl border border-stone-200 outline-none">
            </div>

            <button type="submit" class="bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs py-2.5 px-4 rounded-xl transition">
                Terapkan Filter
            </button>
        </form>
    </div>

    <!-- Financial Summary KPI -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-1">
            <span class="text-xs font-bold text-stone-400 uppercase tracking-wider">Total Pendapatan</span>
            <div class="text-2xl font-black text-stone-900">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
            <span class="text-xs text-emerald-600 font-bold">{{ $summary['completed_orders'] }} pesanan selesai</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-1">
            <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Pembayaran Tunai</span>
            <div class="text-2xl font-black text-amber-600">Rp {{ number_format($summary['cash_revenue'], 0, ',', '.') }}</div>
            <span class="text-xs text-stone-500 font-medium">Uang fisik kasir</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-1">
            <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Pembayaran QRIS</span>
            <div class="text-2xl font-black text-indigo-600">Rp {{ number_format($summary['qris_revenue'], 0, ',', '.') }}</div>
            <span class="text-xs text-stone-500 font-medium">Verifikasi gateway</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-1">
            <span class="text-xs font-bold text-stone-400 uppercase tracking-wider">Total Pesanan</span>
            <div class="text-2xl font-black text-stone-900">{{ $summary['total_orders'] }}</div>
            <span class="text-xs text-rose-600 font-bold">{{ $summary['cancelled_orders'] }} pesanan dibatalkan</span>
        </div>
    </div>

    <!-- Best Seller in Selected Period -->
    <div class="bg-white rounded-3xl p-5 border border-stone-200 shadow-sm space-y-3">
        <h3 class="font-extrabold text-sm text-stone-900">Menu Terlaris Periode Ini</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @forelse($topMenus as $idx => $item)
                <div class="p-3 bg-stone-50 rounded-2xl border border-stone-200 space-y-1">
                    <span class="text-[10px] font-black text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full inline-block">
                        #{{ $idx + 1 }} Terlaris
                    </span>
                    <h4 class="font-bold text-xs text-stone-900 truncate">{{ $item->name }}</h4>
                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="font-black text-stone-900">{{ $item->total_qty }} porsi</span>
                        <span class="text-[11px] text-stone-500">Rp {{ number_format($item->total_sales, 0, ',', '.') }}</span>
                    </div>
                </div>
            @empty
                <p class="text-xs text-stone-400 col-span-full py-4 text-center">Tidak ada transaksi pada periode ini.</p>
            @endforelse
        </div>
    </div>

    <!-- Detailed Transactions Table -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-stone-100 flex items-center justify-between">
            <h3 class="font-extrabold text-sm text-stone-900">Rincian Transaksi Pesanan</h3>
            <span class="text-xs text-stone-500 font-medium">Halaman {{ $orders->currentPage() }} dari {{ $orders->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-50 text-stone-500 font-bold uppercase tracking-wider text-[10px] border-b border-stone-200">
                    <tr>
                        <th class="py-3 px-4">No. Order</th>
                        <th class="py-3 px-4">Meja</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Item Dipesan</th>
                        <th class="py-3 px-4">Metode Bayar</th>
                        <th class="py-3 px-4">Status Bayar</th>
                        <th class="py-3 px-4">Status Pesanan</th>
                        <th class="py-3 px-4 font-black">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-stone-50 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-stone-900">{{ $order->order_number }}</td>
                            <td class="py-3.5 px-4 font-extrabold">Meja {{ $order->table->table_number }}</td>
                            <td class="py-3.5 px-4 text-stone-600">{{ $order->customer_name }}</td>
                            <td class="py-3.5 px-4 text-stone-600">
                                {{ $order->items->map(fn($i) => $i->quantity . 'x ' . $i->menu_name)->implode(', ') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold">
                                {{ $order->latestPayment?->payment_method === 'cash' ? '💵 Tunai' : '📱 QRIS' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold
                                    {{ $order->isPaid() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $order->isPaid() ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                    {{ $order->order_status === 'completed' ? 'bg-stone-100 text-stone-800' : '' }}
                                    {{ $order->order_status === 'cancelled' ? 'bg-rose-100 text-rose-800' : '' }}
                                    {{ !in_array($order->order_status, ['completed', 'cancelled']) ? 'bg-blue-100 text-blue-800' : '' }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-black text-stone-900 text-sm">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-stone-400">Tidak ada riwayat transaksi pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-stone-200 print:hidden">
            {{ $orders->withQueryString()->links() }}
        </div>
    </div>

</div>
@endsection
