<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    /**
     * Kitchen Display System (KDS) View.
     */
    public function index()
    {
        $orders = Order::where('order_status', 'in_kitchen')
            ->with(['table', 'items.variations'])
            ->orderBy('sent_to_kitchen_at', 'asc')
            ->get();

        return view('kitchen.kds', [
            'orders' => $orders,
        ]);
    }

    /**
     * JSON API untuk live polling KDS Dapur.
     */
    public function apiOrders()
    {
        $orders = Order::where('order_status', 'in_kitchen')
            ->with(['table', 'items.variations'])
            ->orderBy('sent_to_kitchen_at', 'asc')
            ->get();

        return response()->json([
            'count' => $orders->count(),
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'table_number' => $order->table->table_number,
                    'customer_name' => $order->customer_name,
                    'elapsed_minutes' => $order->sent_to_kitchen_at ? $order->sent_to_kitchen_at->diffInMinutes(now()) : 0,
                    'sent_at' => $order->sent_to_kitchen_at ? $order->sent_to_kitchen_at->format('H:i') : '-',
                    'items' => $order->items->map(function ($item) {
                        return [
                            'name' => $item->menu_name,
                            'quantity' => $item->quantity,
                            'special_notes' => $item->special_notes,
                            'variations' => $item->variations->map(fn($v) => $v->variation_group_name . ': ' . $v->variation_option_name)->implode(', '),
                        ];
                    }),
                ];
            }),
        ]);
    }
}
