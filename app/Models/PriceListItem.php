<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'price_list_id',
        'applicable_id',
        'applicable_type',
        'amount_price',
        'value_type',
        'percentage',
        'purchase_price',
        'purchase_value_type',
        'purchase_percentage',
    ];

    public function list()
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }

    public function product()
    {
        return $this->belongsTo(Articulo::class, 'product_id', 'idarticulo');
    }

    public function getEffectivePrice(float $basePrice): float
    {
        $effective = $basePrice;
        $list = $this->list;

        if ($list && $list->active) {
            if ($list->type === 'amount' && $this->amount_price !== null) {
                $effective = (float) $this->amount_price;

            } elseif ($list->type === 'product_percentage' && $this->percentage !== null && $this->value_type) {
                $delta = ($basePrice * ((float) $this->percentage) / 100);
                $effective = $this->value_type === 'discount'
                    ? ($basePrice - $delta)
                    : ($basePrice + $delta);

            } elseif ($list->type === 'general_percentage' && $list->percentage !== null && $list->value_type) {
                $delta = ($basePrice * ((float) $list->percentage) / 100);
                $effective = $list->value_type === 'discount'
                    ? ($basePrice - $delta)
                    : ($basePrice + $delta);
            }
        }

        return round($effective, 2);
    }

}