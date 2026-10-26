<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariationOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'variation_group_id',
        'name',
        'extra_price',
        'is_available',
    ];

    protected $casts = [
        'extra_price' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(VariationGroup::class, 'variation_group_id');
    }
}
