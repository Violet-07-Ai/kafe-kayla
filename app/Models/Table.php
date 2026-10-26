<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_number',
        'table_token',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($table) {
            if (empty($table->table_token)) {
                $table->table_token = Str::random(32);
            }
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getQrUrlAttribute(): string
    {
        return route('customer.menu', [
            'table_number' => $this->table_number,
            'token' => $this->table_token,
        ]);
    }
}
