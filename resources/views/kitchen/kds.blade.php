@extends('layouts.staff')

@section('title', 'Kitchen Display System (KDS) - Dapur Kafe Kayla')

@section('content')
<div x-data="kdsApp()" x-init="startPolling()" class="space-y-6">

    <!-- KDS Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-stone-900 text-white p-5 rounded-3xl shadow-xl border border-stone-800">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-2xl font-black border border-amber-500/30">
                🍳
            </div>
            <div>
                <h1 class="text-xl font-black tracking-tight">Kitchen Display System (KDS)</h1>
                <p class="text-xs text-stone-400 font-medium">Antrean Memasak & Meracik Minuman Dapur</p>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <div class="bg-stone-800/90 px-4 py-2 rounded-2xl border border-stone-700 flex items-center space-x-2">
                <span class="text-xs text-stone-400 font-semibold">Total Antrean:</span>
                <span class="text-lg font-black text-amber-400" x-text="ordersCount">{{ $orders->count() }}</span>
            </div>
            <button @click="refreshManual()" class="bg-stone-800 hover:bg-stone-700 text-stone-200 px-3.5 py-2 rounded-2xl text-xs font-bold border border-stone-700 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" :class="refreshing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Muat Ulang</span>
            </button>
        </div>
    </div>

    <!-- Active Orders Grid in Kitchen -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($orders as $order)
            <div class="bg-stone-900 text-stone-100 rounded-3xl p-5 border border-stone-800 shadow-xl flex flex-col justify-between space-y-4 relative overflow-hidden">
                <!-- Elapsed Time Banner -->
                @php
                    $elapsed = $order->sent_to_kitchen_at ? $order->sent_to_kitchen_at->diffInMinutes(now()) : 0;
                @endphp
                <div class="flex items-center justify-between pb-3 border-b border-stone-800">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-black px-3 py-1 rounded-xl bg-amber-500 text-stone-950 shadow-md">
                            Meja {{ $order->table->table_number }}
                        </span>
                        <span class="text-xs font-mono text-stone-400">{{ $order->order_number }}</span>
                    </div>

                    <!-- Timer Badge -->
                    <span class="text-xs font-extrabold px-2.5 py-1 rounded-xl {{ $elapsed >= 15 ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30 animate-pulse' : 'bg-stone-800 text-stone-300' }}">
                        ⏱ {{ $elapsed }}m lalu
                    </span>
                </div>

                <!-- Items Breakdown -->
                <div class="space-y-3 flex-1 py-1">
                    @foreach($order->items as $item)
                        <div class="bg-stone-800/80 rounded-2xl p-3 border border-stone-700/60 space-y-1.5">
                            <div class="flex items-start justify-between">
                                <span class="font-black text-sm text-amber-300">{{ $item->menu_name }}</span>
                                <span class="text-xs font-black bg-stone-700 text-white px-2 py-0.5 rounded-lg shrink-0">
                                    {{ $item->quantity }}x
                                </span>
                            </div>

                            @if($item->variations->count() > 0)
                                <div class="flex flex-wrap gap-1 pt-0.5">
                                    @foreach($item->variations as $v)
                                        <span class="text-[10px] font-semibold bg-stone-900 text-stone-300 px-2 py-0.5 rounded-md border border-stone-700">
                                            {{ $v->variation_group_name }}: {{ $v->variation_option_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @if($item->special_notes)
                                <div class="bg-amber-950/40 border border-amber-600/30 text-amber-200 text-xs p-2 rounded-xl font-medium mt-1">
                                    ⚠️ Catatan: <strong>{{ $item->special_notes }}</strong>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Read-only footer info -->
                <div class="pt-3 border-t border-stone-800 flex items-center justify-between text-xs text-stone-400 font-medium">
                    <span>{{ $order->customer_name }}</span>
                    <span>Diteruskan: {{ $order->sent_to_kitchen_at?->format('H:i') }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-stone-900 text-stone-400 rounded-3xl p-16 text-center border border-stone-800 shadow-xl space-y-2">
                <span class="text-4xl block">✨</span>
                <p class="text-base font-bold text-stone-300">Semua pesanan dapur sudah selesai!</p>
                <p class="text-xs text-stone-500">Menunggu pesanan lunas berikutnya yang diteruskan kasir...</p>
            </div>
        @endforelse
    </div>

</div>
@endsection

@push('scripts')
<script>
function kdsApp() {
    return {
        ordersCount: {{ $orders->count() }},
        refreshing: false,

        async refreshManual() {
            this.refreshing = true;
            window.location.reload();
        },

        startPolling() {
            setInterval(async () => {
                try {
                    const res = await fetch('{{ route('kitchen.api.orders') }}');
                    if (res.ok) {
                        const data = await res.json();
                        // If count changed or new orders arrived, reload KDS
                        if (data.count !== this.ordersCount) {
                            this.playChime();
                            setTimeout(() => window.location.reload(), 800);
                        }
                    }
                } catch(e) {}
            }, 5000);
        },

        playChime() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(440, audioCtx.currentTime);
                osc.frequency.setValueAtTime(659.25, audioCtx.currentTime + 0.15);
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
