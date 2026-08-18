<?php

namespace App\Models\ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class payment_methods extends Model
{
    use HasFactory;

    protected $table='payment_methods';

    protected $primaryKey="payment_method_id";

    public $timestamps=false;

    protected $fillable = [
        'method_name',
        'status',
    ];
}
