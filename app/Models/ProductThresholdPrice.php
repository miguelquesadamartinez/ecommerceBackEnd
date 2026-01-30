<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductThresholdPrice extends Model
{
    protected $table = 'product_threshold_prices';
    protected $fillable = [
        'product_threshold_price_product_id',
        'product_threshold_price_level',
        'product_threshold_price_threshold_from',
        'product_threshold_price_threshold_to',
        'product_threshold_price_price',
        'product_threshold_price_discount',
        'product_threshold_price_threshold_from_premium',
        'product_threshold_price_threshold_to_premium',
        'product_threshold_price_discount_premium',
        'product_threshold_price_price_premium',
    ];
}
