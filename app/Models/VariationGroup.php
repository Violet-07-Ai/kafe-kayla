<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariationGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'name',
        'is_required',
        'allow_multiple',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'allow_multiple' => 'boolean',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function options()
    {
        return $this->hasMany(VariationOption::class);
    }
}
