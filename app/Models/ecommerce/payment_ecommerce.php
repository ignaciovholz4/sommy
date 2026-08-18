<?php

namespace App\Models\ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class payment_ecommerce extends Model
{
    use HasFactory;

    protected $table='payment_ecommerce';

    protected $primaryKey="order_id";

    public $timestamps=false;

    protected $fillable = [
        'order_id',
        'payment_method_id',
        'payment_date',
        'total',
        'status_payment',
        'status',
        'mp_preference_id',
        'mp_payment_id',
        'mp_status',
        'paid_at',
    ];
}
