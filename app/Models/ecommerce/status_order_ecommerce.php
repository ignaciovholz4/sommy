<?php

namespace App\Models\ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class status_order_ecommerce extends Model
{
    use HasFactory;

    protected $table='status_orders_ecommerce';

    protected $primaryKey="status_id";

    public $timestamps=false;

    protected $fillable = [
        'status_name',
        'active',
    ];
}
