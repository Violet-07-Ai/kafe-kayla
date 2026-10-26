<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'table_id',
        'customer_name',
        'total_amount',
        'order_status',
        'cancellation_reason',
        'confirmed_at',
        'sent_to_kitchen_at',
        'ready_to_serve_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'sent_to_kitchen_at' => 'datetime',
        'ready_to_serve_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function isPaid(): bool
    {
        return $this->payments()->where('payment_status', 'paid')->exists();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->order_status) {
            'pending_confirmation' => 'Menunggu Konfirmasi Kasir',
            'confirmed' => 'Dikonfirmasi Kasir',
            'in_kitchen' => 'Diteruskan ke Dapur',
            'ready_to_serve' => 'Siap Disajikan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->order_status,
        };
    }
}
