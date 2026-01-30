<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';
    protected $fillable = [
        'order_detail_order_id',
        'order_detail_product_id',
        'order_detail_price',
        'order_detail_quantity',
        'order_detail_discount',
        'order_detail_price_with_dto',
    ];

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_detail_order_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'order_detail_product_id');
    }
}
