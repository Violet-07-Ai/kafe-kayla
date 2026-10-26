@extends('layouts.app')

@section('title', 'Status Pesanan ' . $order->order_number . ' | Kafe Kayla')

@section('content')
<div x-data="orderStatusTracker('{{ $order->order_number }}', '{{ $order->order_status }}', '{{ $payment?->payment_status }}')"
     x-init="startPolling()"
     class="min-h-screen pb-16">

    <!-- Top Header -->
    <header class="bg-white sticky top-0 z-20 border-b border-stone-200 px-4 py-3 shadow-sm">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <a href="{{ route('customer.menu', ['table_number' => $order->table->table_number, 'token' => $order->table->table_token]) }}" 
               class="text-xs font-bold text-amber-700 hover:text-amber-800 flex items-center space-x-1">
                <span>&larr;</span>
                <span>Menu Kafe</span>
            </a>
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span class="text-xs font-bold text-stone-600">Pelacakan Langsung</span>
            </div>
        </div>
    </header>

    <main class="max-w-md mx-auto px-4 py-5 space-y-5">

        <!-- Status Card with Visual Stepper -->
        <div class="bg-white rounded-3xl p-5 border border-stone-200 shadow-sm space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                        Meja {{ $order->table->table_number }}
                    </span>
                    <h2 class="text-base font-extrabold text-stone-900 mt-2" x-text="orderNumber"></h2>
                    <p class="text-xs text-stone-500" x-text="'Atas nama: ' + '{{ $order->customer_name }}'"></p>
                </div>

                <!-- Dynamic Status Badge -->
                <div class="text-right">
                    <span class="px-3 py-1.5 rounded-full text-xs font-extrabold shadow-sm inline-block"
                          :class="{
                              'bg-amber-100 text-amber-900 border border-amber-300': currentStatus === 'pending_confirmation',
                              'bg-blue-100 text-blue-900 border border-blue-300': currentStatus === 'confirmed',
                              'bg-indigo-100 text-indigo-900 border border-indigo-300': currentStatus === 'in_kitchen',
                              'bg-emerald-100 text-emerald-900 border border-emerald-300 animate-bounce': currentStatus === 'ready_to_serve',
                              'bg-stone-100 text-stone-800 border border-stone-300': currentStatus === 'completed',
                              'bg-rose-100 text-rose-900 border border-rose-300': currentStatus === 'cancelled'
                          }"
                          x-text="statusLabel"></span>
                </div>
            </div>

            <!-- Cancelled Notice if Cancelled -->
            <template x-if="currentStatus === 'cancelled'">
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-xs text-rose-800 space-y-1">
                    <p class="font-bold">Pesanan Dibatalkan oleh Kasir</p>
                    <p x-text="'Alasan: ' + (cancellationReason || '{{ $order->cancellation_reason ?? 'Tidak ada keterangan.' }}')"></p>
                </div>
            </template>

            <!-- Stepper Progress Bar (if not cancelled) -->
            <template x-if="currentStatus !== 'cancelled'">
                <div class="pt-2">
                    <div class="relative flex items-center justify-between">
                        <!-- Connecting Line -->
                        <div class="absolute left-4 right-4 top-1/2 -translate-y-1/2 h-1 bg-stone-100 z-0">
                            <div class="h-1 bg-amber-500 transition-all duration-500"
                                 :style="'width: ' + progressPercentage + '%'"></div>
                        </div>

                        <!-- Step 1: Diterima -->
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition"
                                 :class="stepIndex >= 1 ? 'bg-amber-600 text-white' : 'bg-white text-stone-400 border border-stone-200'">
                                1
                            </div>
                            <span class="text-[10px] font-bold text-stone-600 mt-1">Diterima</span>
                        </div>

                        <!-- Step 2: Dikonfirmasi -->
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition"
                                 :class="stepIndex >= 2 ? 'bg-amber-600 text-white' : 'bg-white text-stone-400 border border-stone-200'">
                                2
                            </div>
                            <span class="text-[10px] font-bold text-stone-600 mt-1">Kasir</span>
                        </div>

                        <!-- Step 3: Dapur -->
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition"
                                 :class="stepIndex >= 3 ? 'bg-amber-600 text-white' : 'bg-white text-stone-400 border border-stone-200'">
                                3
                            </div>
                            <span class="text-[10px] font-bold text-stone-600 mt-1">Dapur</span>
                        </div>

                        <!-- Step 4: Siap Saji -->
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition"
                                 :class="stepIndex >= 4 ? 'bg-emerald-600 text-white animate-pulse' : 'bg-white text-stone-400 border border-stone-200'">
                                4
                            </div>
                            <span class="text-[10px] font-bold text-stone-600 mt-1">Siap</span>
                        </div>

                        <!-- Step 5: Selesai -->
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition"
                                 :class="stepIndex >= 5 ? 'bg-stone-900 text-white' : 'bg-white text-stone-400 border border-stone-200'">
                                5
                            </div>
                            <span class="text-[10px] font-bold text-stone-600 mt-1">Selesai</span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- PAYMENT STATUS & QRIS DISPLAY -->
        <div class="bg-white rounded-3xl p-5 border border-stone-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="text-xl">{{ $order->latestPayment?->payment_method === 'cash' ? '💵' : '📱' }}</span>
                    <div>
                        <h3 class="font-extrabold text-sm text-stone-900">
                            {{ $order->latestPayment?->payment_method === 'cash' ? 'Pembayaran Tunai' : 'Pembayaran QRIS' }}
                        </h3>
                        <p class="text-[11px] text-stone-500">Total: Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                    </div>
                </div>

                <span class="text-xs font-extrabold px-3 py-1 rounded-full shadow-sm"
                      :class="{
                          'bg-emerald-100 text-emerald-900 border border-emerald-300': currentPaymentStatus === 'paid',
                          'bg-amber-100 text-amber-900 border border-amber-300': currentPaymentStatus === 'pending',
                          'bg-rose-100 text-rose-900 border border-rose-300': currentPaymentStatus === 'failed',
                          'bg-stone-200 text-stone-700 border border-stone-300': currentPaymentStatus === 'expired'
                      }"
                      x-text="paymentStatusLabel"></span>
            </div>

            <!-- Detail QRIS -->
            @if($order->latestPayment && $order->latestPayment->payment_method === 'qris')
                <div x-show="currentPaymentStatus === 'pending'" class="text-center py-2 space-y-3">
                    <p class="text-xs text-stone-600 font-medium">
                        Pindai kode QRIS di bawah ini melalui aplikasi BCA Mobile, GoPay, OVO, Dana, atau ShopeePay:
                    </p>

                    @if($qrDataUri)
                        <div class="p-3 bg-stone-50 border border-stone-200 rounded-2xl inline-block shadow-inner">
                            <img src="{{ $qrDataUri }}" alt="QRIS Kafe Kayla" class="w-48 h-48 mx-auto">
                        </div>
                    @endif

                    <!-- SIMULATOR CONTROL (PRD Sandbox requirement) -->
                    <div class="bg-amber-50/80 border border-amber-200/90 rounded-2xl p-3.5 space-y-2 mt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-extrabold text-amber-900 uppercase tracking-wide">🧪 Simulator Sandbox QRIS</span>
                            <span class="text-[10px] text-amber-700 bg-amber-200/60 px-2 py-0.5 rounded-full">Demo Mode</span>
                        </div>
                        <p class="text-[11px] text-stone-600 text-left leading-relaxed">
                            Uji transaksi QRIS sandbox langsung tanpa saldo riil:
                        </p>
                        <div class="grid grid-cols-3 gap-2 pt-1">
                            <form action="{{ route('customer.order.simulate_qris', $order->order_number) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="success">
                                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] py-2 rounded-xl shadow-sm transition">
                                    ✓ Lunas
                                </button>
                            </form>
                            <form action="{{ route('customer.order.simulate_qris', $order->order_number) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="failed">
                                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px] py-2 rounded-xl shadow-sm transition">
                                    ✗ Gagal
                                </button>
                            </form>
                            <form action="{{ route('customer.order.simulate_qris', $order->order_number) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="expired">
                                <button type="submit" class="w-full bg-stone-600 hover:bg-stone-700 text-white font-bold text-[11px] py-2 rounded-xl shadow-sm transition">
                                    ⏳ Expired
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div x-show="currentPaymentStatus === 'paid'" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-center space-y-1">
                    <p class="font-extrabold text-sm text-emerald-800">Pembayaran QRIS Berhasil Diverifikasi!</p>
                    <p class="text-xs text-emerald-700">Pesanan Anda siap diproses dan diteruskan ke dapur.</p>
                </div>
            @else
                <!-- Detail Tunai -->
                <div x-show="currentPaymentStatus === 'pending'" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 space-y-2">
                    <p class="font-extrabold text-xs text-amber-900">Petunjuk Pembayaran Tunai:</p>
                    <p class="text-xs text-stone-600 leading-relaxed">
                        Silakan menuju ke kasir dan sebutkan nomor pesanan <strong>{{ $order->order_number }}</strong> serta siapkan uang tunai sebesar <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>.
                    </p>
                    <p class="text-[11px] text-amber-800 italic">
                        *Pesanan akan segera diteruskan ke dapur setelah kasir mencatat pembayaran lunas.
                    </p>
                </div>

                <div x-show="currentPaymentStatus === 'paid'" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-center space-y-1">
                    <p class="font-extrabold text-sm text-emerald-800">Pembayaran Tunai Diterima Kasir!</p>
                    <p class="text-xs text-emerald-700">Terima kasih, pembayaran Anda telah tercatat lunas.</p>
                </div>
            @endif
        </div>

        <!-- ORDER ITEMS SUMMARY -->
        <div class="bg-white rounded-3xl p-5 border border-stone-200 shadow-sm space-y-3">
            <h3 class="font-extrabold text-sm text-stone-900 pb-2 border-b border-stone-100">
                Daftar Item Pesanan
            </h3>

            <div class="space-y-3 divide-y divide-stone-100">
                @foreach($order->items as $item)
                    <div class="pt-2 first:pt-0 flex items-start justify-between">
                        <div class="space-y-0.5">
                            <h4 class="font-bold text-xs text-stone-900">
                                {{ $item->quantity }}x {{ $item->menu_name }}
                            </h4>
                            @if($item->variations->count() > 0)
                                <p class="text-[11px] text-stone-500">
                                    {{ $item->variations->map(fn($v) => $v->variation_group_name . ': ' . $v->variation_option_name)->implode(', ') }}
                                </p>
                            @endif
                            @if($item->special_notes)
                                <p class="text-[11px] text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md inline-block">
                                    Catatan: {{ $item->special_notes }}
                                </p>
                            @endif
                        </div>
                        <span class="font-bold text-xs text-stone-800 shrink-0">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="pt-3 border-t border-stone-200 flex items-center justify-between">
                <span class="font-extrabold text-xs text-stone-600">Total Tagihan</span>
                <span class="font-black text-base text-stone-900">
                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                </span>
            </div>
        </div>

    </main>

</div>
@endsection

@push('scripts')
<script>
function orderStatusTracker(orderNumber, initialStatus, initialPaymentStatus) {
    return {
        orderNumber: orderNumber,
        currentStatus: initialStatus,
        currentPaymentStatus: initialPaymentStatus,
        cancellationReason: '',
        pollingTimer: null,

        get statusLabel() {
            const map = {
                'pending_confirmation': 'Menunggu Konfirmasi Kasir',
                'confirmed': 'Dikonfirmasi Kasir',
                'in_kitchen': 'Diteruskan ke Dapur',
                'ready_to_serve': 'Siap Disajikan!',
                'completed': 'Selesai',
                'cancelled': 'Dibatalkan'
            };
            return map[this.currentStatus] || this.currentStatus;
        },

        get paymentStatusLabel() {
            const map = {
                'pending': 'Menunggu Pembayaran',
                'paid': 'Lunas',
                'failed': 'Gagal',
                'expired': 'Kedaluwarsa'
            };
            return map[this.currentPaymentStatus] || this.currentPaymentStatus;
        },

        get stepIndex() {
            const map = {
                'pending_confirmation': 1,
                'confirmed': 2,
                'in_kitchen': 3,
                'ready_to_serve': 4,
                'completed': 5,
                'cancelled': 0
            };
            return map[this.currentStatus] || 1;
        },

        get progressPercentage() {
            if (this.currentStatus === 'cancelled') return 0;
            return ((this.stepIndex - 1) / 4) * 100;
        },

        startPolling() {
            // Poll status every 3 seconds
            this.pollingTimer = setInterval(() => {
                this.checkStatus();
            }, 3000);
        },

        async checkStatus() {
            try {
                const res = await fetch('/api/pesanan/' + this.orderNumber + '/status');
                if (res.ok) {
                    const data = await res.json();
                    const prevStatus = this.currentStatus;
                    this.currentStatus = data.order_status;
                    this.currentPaymentStatus = data.payment_status;
                    this.cancellationReason = data.cancellation_reason;

                    // Trigger sound/alert when ready to serve
                    if (prevStatus !== 'ready_to_serve' && this.currentStatus === 'ready_to_serve') {
                        if ('Notification' in window && Notification.permission === 'granted') {
                            new Notification('Pesanan Siap Disajikan!', { body: 'Pesanan ' + this.orderNumber + ' siap diambil/disajikan.' });
                        }
                    }

                    // Stop polling if completed or cancelled
                    if (['completed', 'cancelled'].includes(this.currentStatus)) {
                        clearInterval(this.pollingTimer);
                    }
                }
            } catch (err) {
                console.error('Error polling status:', err);
            }
        }
    }
}
</script>
@endpush
