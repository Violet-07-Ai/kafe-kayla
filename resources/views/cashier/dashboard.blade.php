@extends('layouts.staff')

@section('title', 'Terminal Kasir POS - Kafe Kayla')

@section('content')
<div x-data="cashierApp()" x-init="startPolling()" class="space-y-6">

    <!-- Top KPI Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm">
            <span class="text-xs font-bold text-amber-700 block">Menunggu Konfirmasi</span>
            <span class="text-2xl font-black text-amber-600 mt-1 block" x-text="stats.pending_confirmation">{{ $stats['pending_confirmation'] }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm">
            <span class="text-xs font-bold text-blue-700 block">Dikonfirmasi</span>
            <span class="text-2xl font-black text-blue-600 mt-1 block">{{ $stats['confirmed'] }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm">
            <span class="text-xs font-bold text-indigo-700 block">Sedang di Dapur</span>
            <span class="text-2xl font-black text-indigo-600 mt-1 block">{{ $stats['in_kitchen'] }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm">
            <span class="text-xs font-bold text-emerald-700 block">Siap Disajikan</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ $stats['ready_to_serve'] }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm">
            <span class="text-xs font-bold text-stone-600 block">Tunai Hari Ini</span>
            <span class="text-base font-extrabold text-stone-900 mt-1 block">Rp {{ number_format($stats['total_cash_today'], 0, ',', '.') }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm">
            <span class="text-xs font-bold text-stone-600 block">QRIS Hari Ini</span>
            <span class="text-base font-extrabold text-stone-900 mt-1 block">Rp {{ number_format($stats['total_qris_today'], 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Filter Tabs & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 rounded-2xl border border-stone-200 shadow-sm">
        <div class="flex space-x-1 overflow-x-auto scrollbar-none">
            <button @click="activeFilter = 'all'"
                    :class="activeFilter === 'all' ? 'bg-stone-900 text-white' : 'text-stone-600 hover:bg-stone-100'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition">Semua</button>
            <button @click="activeFilter = 'pending_confirmation'"
                    :class="activeFilter === 'pending_confirmation' ? 'bg-amber-600 text-white' : 'text-stone-600 hover:bg-stone-100'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1">
                <span>Konfirmasi</span>
                @if($stats['pending_confirmation'] > 0)
                    <span class="w-2 h-2 rounded-full bg-rose-400 animate-ping"></span>
                @endif
            </button>
            <button @click="activeFilter = 'confirmed'"
                    :class="activeFilter === 'confirmed' ? 'bg-blue-600 text-white' : 'text-stone-600 hover:bg-stone-100'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition">Menunggu Bayar</button>
            <button @click="activeFilter = 'in_kitchen'"
                    :class="activeFilter === 'in_kitchen' ? 'bg-indigo-600 text-white' : 'text-stone-600 hover:bg-stone-100'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition">Di Dapur</button>
            <button @click="activeFilter = 'ready_to_serve'"
                    :class="activeFilter === 'ready_to_serve' ? 'bg-emerald-600 text-white' : 'text-stone-600 hover:bg-stone-100'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition">Siap Saji</button>
            <button @click="activeFilter = 'completed'"
                    :class="activeFilter === 'completed' ? 'bg-stone-800 text-white' : 'text-stone-600 hover:bg-stone-100'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition">Selesai</button>
        </div>

        <div class="flex items-center space-x-2 text-xs text-stone-500">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Live Polling Aktif (5s)</span>
        </div>
    </div>

    <!-- Orders Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($orders as $order)
            <div x-show="activeFilter === 'all' || activeFilter === '{{ $order->order_status }}'"
                 class="bg-white rounded-3xl p-5 border transition shadow-sm space-y-4
                     {{ $order->order_status === 'pending_confirmation' ? 'border-amber-400 ring-2 ring-amber-300/40 bg-amber-50/20' : 'border-stone-200' }}">

                <!-- Card Header -->
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-black uppercase px-2.5 py-1 rounded-full bg-stone-900 text-white">
                                Meja {{ $order->table->table_number }}
                            </span>
                            <span class="font-extrabold text-xs text-stone-700">{{ $order->order_number }}</span>
                        </div>
                        <p class="text-xs text-stone-500 font-medium mt-1">
                            {{ $order->customer_name }} • {{ $order->created_at->format('H:i') }} ({{ $order->created_at->diffForHumans() }})
                        </p>
                    </div>

                    <!-- Status Badge -->
                    <span class="text-[11px] font-extrabold px-2.5 py-1 rounded-full
                        {{ $order->order_status === 'pending_confirmation' ? 'bg-amber-100 text-amber-900 border border-amber-300' : '' }}
                        {{ $order->order_status === 'confirmed' ? 'bg-blue-100 text-blue-900 border border-blue-300' : '' }}
                        {{ $order->order_status === 'in_kitchen' ? 'bg-indigo-100 text-indigo-900 border border-indigo-300' : '' }}
                        {{ $order->order_status === 'ready_to_serve' ? 'bg-emerald-100 text-emerald-900 border border-emerald-300 animate-pulse' : '' }}
                        {{ $order->order_status === 'completed' ? 'bg-stone-100 text-stone-700' : '' }}
                        {{ $order->order_status === 'cancelled' ? 'bg-rose-100 text-rose-800' : '' }}">
                        {{ $order->status_label }}
                    </span>
                </div>

                <!-- Payment Status Indicator -->
                <div class="flex items-center justify-between p-2.5 rounded-xl text-xs font-semibold
                    {{ $order->isPaid() ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                    <div class="flex items-center space-x-1.5">
                        <span>{{ $order->latestPayment?->payment_method === 'cash' ? '💵 Tunai' : '📱 QRIS' }}</span>
                        <span>•</span>
                        <span>{{ $order->isPaid() ? 'LUNAS' : 'BELUM BAYAR' }}</span>
                    </div>
                    <span class="font-black text-sm">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>

                <!-- Order Items List -->
                <div class="space-y-2 py-2 border-y border-stone-100 max-h-48 overflow-y-auto text-xs scrollbar-thin">
                    @foreach($order->items as $item)
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="font-extrabold text-stone-900">{{ $item->quantity }}x</span>
                                <span class="font-semibold text-stone-800">{{ $item->menu_name }}</span>
                                @if($item->variations->count() > 0)
                                    <p class="text-[11px] text-stone-500 pl-4">
                                        {{ $item->variations->map(fn($v) => $v->variation_group_name . ': ' . $v->variation_option_name)->implode(', ') }}
                                    </p>
                                @endif
                                @if($item->special_notes)
                                    <p class="text-[11px] text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded pl-4 mt-0.5">
                                        Catatan: {{ $item->special_notes }}
                                    </p>
                                @endif
                            </div>
                            <span class="font-bold text-stone-700 shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                @if($order->cancellation_reason)
                    <div class="text-xs text-rose-800 bg-rose-50 p-2 rounded-xl">
                        <strong>Alasan Batal:</strong> {{ $order->cancellation_reason }}
                    </div>
                @endif

                <!-- Cashier Action Buttons -->
                <div class="space-y-2 pt-1">

                    <!-- 1. Pending Confirmation Actions -->
                    @if($order->order_status === 'pending_confirmation')
                        <div class="grid grid-cols-2 gap-2">
                            <form action="{{ route('cashier.orders.confirm', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2.5 px-3 rounded-xl shadow-sm transition">
                                    ✓ Konfirmasi
                                </button>
                            </form>
                            <button type="button" @click="openCancelModal({{ $order->id }}, '{{ $order->order_number }}')" 
                                    class="w-full bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs py-2.5 px-3 rounded-xl transition">
                                ✗ Tolak Pesanan
                            </button>
                        </div>
                    @endif

                    <!-- 2. Pay Cash Action -->
                    @if(!$order->isPaid() && $order->order_status !== 'cancelled')
                        @if($order->latestPayment?->payment_method === 'cash')
                            <form action="{{ route('cashier.orders.pay_cash', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-stone-900 font-extrabold text-xs py-2.5 px-3 rounded-xl shadow-sm transition flex items-center justify-center space-x-1">
                                    <span>💵</span>
                                    <span>Terima Tunai (Tandai Lunas)</span>
                                </button>
                            </form>
                        @else
                            <div class="text-[11px] text-center text-stone-500 py-1 bg-stone-50 rounded-xl border border-stone-200">
                                📱 Menunggu pelanggan menyelesaikan scan QRIS...
                            </div>
                        @endif
                    @endif

                    <!-- 3. Forward to Kitchen (PRD RULE: ONLY ACTIVE IF PAID) -->
                    @if(in_array($order->order_status, ['pending_confirmation', 'confirmed']))
                        @if($order->isPaid())
                            <form action="{{ route('cashier.orders.send_kitchen', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2.5 px-3 rounded-xl shadow-md transition flex items-center justify-center space-x-1">
                                    <span>🍳</span>
                                    <span>Teruskan ke Dapur</span>
                                </button>
                            </form>
                        @else
                            <button type="button" disabled 
                                    title="Selesaikan pembayaran terlebih dahulu sebelum meneruskan ke dapur"
                                    class="w-full bg-stone-100 text-stone-400 cursor-not-allowed font-bold text-xs py-2.5 px-3 rounded-xl border border-stone-200 flex items-center justify-center space-x-1">
                                <span>🔒</span>
                                <span>Teruskan ke Dapur (Kunci: Butuh Lunas)</span>
                            </button>
                        @endif
                    @endif

                    <!-- 4. Mark Ready to Serve & Call Number -->
                    @if($order->order_status === 'in_kitchen')
                        <form action="{{ route('cashier.orders.ready', $order) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2.5 px-3 rounded-xl shadow-md transition flex items-center justify-center space-x-1">
                                <span>🔔</span>
                                <span>Siap Disajikan & Panggil Nomor</span>
                            </button>
                        </form>
                    @endif

                    <!-- 5. Complete Order -->
                    @if($order->order_status === 'ready_to_serve')
                        <div class="space-y-1.5">
                            <button type="button" @click="callNumber('{{ $order->order_number }}', '{{ $order->table->table_number }}')"
                                    class="w-full bg-amber-100 hover:bg-amber-200 text-amber-900 font-bold text-xs py-2 px-3 rounded-xl transition flex items-center justify-center space-x-1">
                                <span>📢</span>
                                <span>Panggil Ulang Suara</span>
                            </button>
                            <form action="{{ route('cashier.orders.complete', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs py-2.5 px-3 rounded-xl shadow-md transition">
                                    ✓ Selesaikan Pesanan (Selesai Disajikan)
                                </button>
                            </form>
                        </div>
                    @endif

                </div>

            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-stone-200">
                <p class="text-stone-400 font-bold text-sm">Belum ada pesanan aktif saat ini.</p>
            </div>
        @endforelse
    </div>

    <!-- CANCEL ORDER MODAL -->
    <div x-cloak x-show="cancelModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="cancelModalOpen = false" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-3xl p-6 max-w-sm w-full z-10 shadow-2xl space-y-4">
            <h3 class="font-extrabold text-base text-stone-900">Batalkan Pesanan <span x-text="cancelOrderNumber"></span></h3>
            <form :action="'/kasir/pesanan/' + cancelOrderId + '/cancel'" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-600">Alasan Pembatalan (Wajib)</label>
                    <textarea name="cancellation_reason" required rows="3" placeholder="Contoh: Stok bahan habis, meja keliru..."
                              class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-rose-500 outline-none"></textarea>
                </div>
                <div class="flex space-x-2 pt-2">
                    <button type="button" @click="cancelModalOpen = false" class="w-1/2 bg-stone-100 font-bold text-xs py-2.5 rounded-xl text-stone-700">Tutup</button>
                    <button type="submit" class="w-1/2 bg-rose-600 font-bold text-xs py-2.5 rounded-xl text-white shadow-md">Batalkan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function cashierApp() {
    return {
        activeFilter: 'all',
        cancelModalOpen: false,
        cancelOrderId: null,
        cancelOrderNumber: '',
        stats: {
            pending_confirmation: {{ $stats['pending_confirmation'] }}
        },

        openCancelModal(id, num) {
            this.cancelOrderId = id;
            this.cancelOrderNumber = num;
            this.cancelModalOpen = true;
        },

        callNumber(orderNum, tableNum) {
            // Text to speech calling out order number
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(`Pesanan nomor ${orderNum}, meja ${tableNum}, siap disajikan.`);
                utterance.lang = 'id-ID';
                utterance.rate = 0.9;
                window.speechSynthesis.speak(utterance);
            }
        },

        startPolling() {
            // Poll for updates every 5s
            setInterval(async () => {
                try {
                    const res = await fetch('{{ route('cashier.api.orders') }}');
                    if (res.ok) {
                        const data = await res.json();
                        // If there are more pending orders than previously, play chime sound!
                        if (data.count_pending > this.stats.pending_confirmation) {
                            this.playChime();
                        }
                        this.stats.pending_confirmation = data.count_pending;
                    }
                } catch(e) {}
            }, 5000);
        },

        playChime() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
                osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.15); // A5
                gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.6);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.6);
            } catch(e) {}
        }
    }
}
</script>
@endpush
