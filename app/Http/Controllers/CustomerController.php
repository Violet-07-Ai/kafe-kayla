<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemVariation;
use App\Models\Payment;
use App\Models\Table;
use App\Models\VariationOption;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Tampilkan menu digital pelanggan berdasarkan scan QR meja.
     */
    public function showMenu(string $tableNumber, Request $request)
    {
        $token = $request->query('token', session('customer_table_token'));

        $table = Table::where('table_number', $tableNumber)
            ->where('is_active', true)
            ->first();

        if (!$table || ($table->table_token !== $token)) {
            return view('customer.invalid_table', [
                'tableNumber' => $tableNumber,
            ]);
        }

        // Simpan meja aktif di session pelanggan
        session([
            'customer_table_id' => $table->id,
            'customer_table_number' => $table->table_number,
            'customer_table_token' => $table->table_token,
        ]);

        $categories = Category::where('is_active', true)
            ->with(['menus' => function ($query) {
                $query->where('is_available', true)
                    ->with(['variationGroups.options' => function ($q) {
                        $q->where('is_available', true);
                    }]);
            }])
            ->orderBy('sort_order')
            ->get();

        return view('customer.menu', [
            'table' => $table,
            'categories' => $categories,
        ]);
    }

    /**
     * Proses checkout dan pembuatan pesanan oleh pelanggan.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'table_number' => ['required', 'string'],
            'table_token' => ['required', 'string'],
            'customer_name' => ['nullable', 'string', 'max:60'],
            'payment_method' => ['required', 'in:cash,qris'],
            'cart_items' => ['required', 'array', 'min:1'],
            'cart_items.*.menu_id' => ['required', 'integer', 'exists:menus,id'],
            'cart_items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'cart_items.*.special_notes' => ['nullable', 'string', 'max:255'],
            'cart_items.*.selected_options' => ['nullable', 'array'],
            'cart_items.*.selected_options.*' => ['integer', 'exists:variation_options,id'],
        ]);

        // Validasi meja & token
        $table = Table::where('table_number', $validated['table_number'])
            ->where('table_token', $validated['table_token'])
            ->where('is_active', true)
            ->firstOrFail();

        return DB::transaction(function () use ($validated, $table) {
            $totalAmount = 0;
            $itemsData = [];

            // Hitung ulang harga dan variasi di sisi server (Server-side validation)
            foreach ($validated['cart_items'] as $item) {
                $menu = Menu::where('id', $item['menu_id'])
                    ->where('is_available', true)
                    ->first();

                if (!$menu) {
                    abort(422, 'Salah satu menu yang dipilih sedang tidak tersedia.');
                }

                $itemUnitPrice = (float) $menu->base_price;
                $itemVariations = [];

                if (!empty($item['selected_options'])) {
                    $options = VariationOption::whereIn('id', $item['selected_options'])
                        ->where('is_available', true)
                        ->with('group')
                        ->get();

                    foreach ($options as $opt) {
                        // Pastikan opsi ini milik menu yang bersangkutan
                        if ($opt->group->menu_id === $menu->id) {
                            $itemUnitPrice += (float) $opt->extra_price;
                            $itemVariations[] = [
                                'group_name' => $opt->group->name,
                                'option_name' => $opt->name,
                                'extra_price' => (float) $opt->extra_price,
                            ];
                        }
                    }
                }

                $quantity = (int) $item['quantity'];
                $itemSubtotal = $itemUnitPrice * $quantity;
                $totalAmount += $itemSubtotal;

                $itemsData[] = [
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'quantity' => $quantity,
                    'unit_price' => $itemUnitPrice,
                    'subtotal' => $itemSubtotal,
                    'special_notes' => $item['special_notes'] ?? null,
                    'variations' => $itemVariations,
                ];
            }

            // Generate nomor pesanan unik
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $order = Order::create([
                'order_number' => $orderNumber,
                'table_id' => $table->id,
                'customer_name' => !empty($validated['customer_name']) ? trim($validated['customer_name']) : 'Tamu Meja ' . $table->table_number,
                'total_amount' => $totalAmount,
                'order_status' => 'pending_confirmation',
            ]);

            foreach ($itemsData as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $item['menu_id'],
                    'menu_name' => $item['menu_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'special_notes' => $item['special_notes'],
                ]);

                foreach ($item['variations'] as $v) {
                    OrderItemVariation::create([
                        'order_item_id' => $orderItem->id,
                        'variation_group_name' => $v['group_name'],
                        'variation_option_name' => $v['option_name'],
                        'extra_price' => $v['extra_price'],
                    ]);
                }
            }

            // Inisialisasi Pembayaran
            $paymentMethod = $validated['payment_method'];
            $qrString = null;
            $transactionId = 'TRX-' . time() . '-' . rand(100, 999);

            if ($paymentMethod === 'qris') {
                // String payload QRIS simulator standar untuk demo
                $qrString = '00020101021226600016ID.CO.QRIS.WWW01189360099990000000000215' . $transactionId . '51440014ID.LINKAJA.WWW0215' . $transactionId . '52045812530336054' . str_pad((string)(int)$totalAmount, 10, '0', STR_PAD_LEFT) . '5802ID5910KAFE KAYLA6007BANDUNG6304ABCD';
            }

            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'amount' => $totalAmount,
                'transaction_id' => $transactionId,
                'qr_string' => $qrString,
                'payment_payload' => [
                    'merchant' => 'Kafe Kayla',
                    'order_number' => $orderNumber,
                    'created_at' => now()->toISOString(),
                ],
            ]);

            return response()->json([
                'success' => true,
                'order_number' => $orderNumber,
                'redirect_url' => route('customer.order.status', ['orderNumber' => $orderNumber]),
            ]);
        });
    }

    /**
     * Halaman pelacakan status pesanan pelanggan.
     */
    public function orderStatus(string $orderNumber, QrCodeService $qrCodeService)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['table', 'items.variations', 'latestPayment'])
            ->firstOrFail();

        $qrDataUri = null;
        if ($order->latestPayment && $order->latestPayment->payment_method === 'qris' && !empty($order->latestPayment->qr_string)) {
            $qrDataUri = $qrCodeService->generateDataUri($order->latestPayment->qr_string, 260);
        }

        return view('customer.status', [
            'order' => $order,
            'payment' => $order->latestPayment,
            'qrDataUri' => $qrDataUri,
        ]);
    }

    /**
     * Endpoint API JSON untuk polling status pemesanan.
     */
    public function apiOrderStatus(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('latestPayment')
            ->firstOrFail();

        return response()->json([
            'order_number' => $order->order_number,
            'order_status' => $order->order_status,
            'status_label' => $order->status_label,
            'is_paid' => $order->isPaid(),
            'payment_status' => $order->latestPayment?->payment_status ?? 'pending',
            'payment_method' => $order->latestPayment?->payment_method ?? 'cash',
            'cancellation_reason' => $order->cancellation_reason,
            'updated_at' => $order->updated_at->format('H:i:s'),
        ]);
    }

    /**
     * Simulasi pembayaran QRIS Sandbox untuk kemudahan demonstrasi lokal.
     */
    public function simulateQris(Request $request, string $orderNumber)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:success,failed,expired'],
        ]);

        $order = Order::where('order_number', $orderNumber)
            ->with('latestPayment')
            ->firstOrFail();

        $payment = $order->latestPayment;

        if (!$payment) {
            abort(404, 'Pembayaran tidak ditemukan.');
        }

        if ($payment->payment_status === 'paid') {
            return back()->with('info', 'Pesanan ini sudah berstatus lunas.');
        }

        if ($validated['action'] === 'success') {
            $payment->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);
            $message = 'Simulasi pembayaran QRIS berhasil! Status pembayaran kini LUNAS.';
        } elseif ($validated['action'] === 'failed') {
            $payment->update([
                'payment_status' => 'failed',
            ]);
            $message = 'Simulasi pembayaran QRIS gagal.';
        } else {
            $payment->update([
                'payment_status' => 'expired',
            ]);
            $message = 'Simulasi pembayaran QRIS kedaluwarsa.';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'payment_status' => $payment->payment_status,
            ]);
        }

        return back()->with('status_message', $message);
    }
}
