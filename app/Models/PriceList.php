<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'context',
        'value_type',
        'percentage',
        'description',
        'active',
        'applicable',
    ];

    public function items()
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function scopeSales($query)
    {
        return $query->where('context', 'sales');
    }

    public function scopePurchase($query)
    {
        return $query->where('context', 'purchase');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

