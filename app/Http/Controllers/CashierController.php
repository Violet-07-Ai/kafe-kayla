<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierController extends Controller
{
    /**
     * Dashboard POS Kasir.
     */
    public function dashboard()
    {
        $today = now()->startOfDay();

        $stats = [
            'pending_confirmation' => Order::where('order_status', 'pending_confirmation')->count(),
            'confirmed' => Order::where('order_status', 'confirmed')->count(),
            'in_kitchen' => Order::where('order_status', 'in_kitchen')->count(),
            'ready_to_serve' => Order::where('order_status', 'ready_to_serve')->count(),
            'completed_today' => Order::where('order_status', 'completed')->where('created_at', '>=', $today)->count(),
            'total_cash_today' => Payment::where('payment_method', 'cash')->where('payment_status', 'paid')->where('paid_at', '>=', $today)->sum('amount'),
            'total_qris_today' => Payment::where('payment_method', 'qris')->where('payment_status', 'paid')->where('paid_at', '>=', $today)->sum('amount'),
        ];

        $orders = Order::with(['table', 'items.variations', 'latestPayment'])
            ->orderByRaw("
                CASE order_status
                    WHEN 'pending_confirmation' THEN 1
                    WHEN 'confirmed' THEN 2
                    WHEN 'in_kitchen' THEN 3
                    WHEN 'ready_to_serve' THEN 4
                    WHEN 'completed' THEN 5
                    WHEN 'cancelled' THEN 6
                END
            ")
            ->latest()
            ->take(50)
            ->get();

        return view('cashier.dashboard', [
            'stats' => $stats,
            'orders' => $orders,
        ]);
    }

    /**
     * JSON API untuk live polling pesanan di kasir.
     */
    public function apiOrders()
    {
        $orders = Order::with(['table', 'items.variations', 'latestPayment'])
            ->whereIn('order_status', ['pending_confirmation', 'confirmed', 'in_kitchen', 'ready_to_serve'])
            ->latest()
            ->get();

        return response()->json([
            'count_pending' => $orders->where('order_status', 'pending_confirmation')->count(),
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'table_number' => $order->table->table_number,
                    'customer_name' => $order->customer_name,
                    'total_amount' => number_format($order->total_amount, 0, ',', '.'),
                    'order_status' => $order->order_status,
                    'status_label' => $order->status_label,
                    'payment_method' => $order->latestPayment?->payment_method,
                    'payment_status' => $order->latestPayment?->payment_status,
                    'is_paid' => $order->isPaid(),
                    'created_at' => $order->created_at->format('H:i:s'),
                    'items_count' => $order->items->sum('quantity'),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'name' => $item->menu_name,
                            'quantity' => $item->quantity,
                            'notes' => $item->special_notes,
                            'variations' => $item->variations->map(fn($v) => $v->variation_group_name . ': ' . $v->variation_option_name)->implode(', '),
                        ];
                    }),
                ];
            }),
        ]);
    }

    /**
     * Konfirmasi pesanan oleh kasir.
     */
    public function confirmOrder(Order $order)
    {
        if ($order->order_status !== 'pending_confirmation') {
            return back()->with('error', 'Status pesanan tidak valid untuk dikonfirmasi.');
        }

        $order->update([
            'order_status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return back()->with('success', "Pesanan {$order->order_number} berhasil dikonfirmasi.");
    }

    /**
     * Batalkan/tolak pesanan oleh kasir dengan alasan.
     */
    public function cancelOrder(Order $order, Request $request)
    {
        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:255'],
        ]);

        if (in_array($order->order_status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Pesanan ini sudah selesai atau telah dibatalkan.');
        }

        $order->update([
            'order_status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
            'cancelled_at' => now(),
        ]);

        return back()->with('warning', "Pesanan {$order->order_number} telah dibatalkan.");
    }

    /**
     * Verifikasi penerimaan pembayaran tunai oleh kasir.
     */
    public function verifyCashPayment(Order $order)
    {
        $payment = $order->latestPayment;

        if (!$payment) {
            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $payment->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'cashier_id' => Auth::id(),
        ]);

        return back()->with('success', "Pembayaran tunai sebesar Rp " . number_format($order->total_amount, 0, ',', '.') . " berhasil diverifikasi lunas.");
    }

    /**
     * Teruskan pesanan yang sudah lunas ke dapur.
     */
    public function sendToKitchen(Order $order)
    {
        // Validasi aturan PRD: hanya boleh diteruskan jika sudah LUNAS
        if (!$order->isPaid()) {
            return back()->with('error', 'Pesanan tidak dapat diteruskan ke dapur karena pembayaran belum lunas!');
        }

        if (!in_array($order->order_status, ['confirmed', 'pending_confirmation'])) {
            return back()->with('error', 'Status pesanan tidak dapat diteruskan ke dapur.');
        }

        $order->update([
            'order_status' => 'in_kitchen',
            'sent_to_kitchen_at' => now(),
        ]);

        return back()->with('success', "Pesanan {$order->order_number} berhasil diteruskan ke antrean dapur.");
    }

    /**
     * Tandai pesanan siap disajikan & panggil nomor pesanan.
     */
    public function markReady(Order $order)
    {
        $order->update([
            'order_status' => 'ready_to_serve',
            'ready_to_serve_at' => now(),
        ]);

        return back()->with('success', "Pesanan {$order->order_number} (Meja {$order->table->table_number}) siap disajikan! Nomor pesanan telah dipanggil.");
    }

    /**
     * Selesaikan pesanan.
     */
    public function markCompleted(Order $order)
    {
        $order->update([
            'order_status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', "Pesanan {$order->order_number} telah selesai disajikan.");
    }
}
