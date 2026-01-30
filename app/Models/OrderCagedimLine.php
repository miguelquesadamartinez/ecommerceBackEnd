<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCagedimLine extends Model
{
    protected $table = 'orders_cagedim_lines';
    
    protected $fillable = [
        'order_id',
        'product_no',
        'product_qualifier_code',
        'qty',
        'item_category',
        'discount_type',
        'discount_value'
    ];

    public function order()
    {
        return $this->belongsTo(OrderCagedim::class, 'order_id');
    }

    public function lineTexts()
    {
        return $this->hasMany(OrderCagedimLineText::class, 'order_line_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_no', 'product_cip13');
    }

    public function product_cip()
    {
        return $this->belongsTo(Product::class, 'product_cip13', 'product_no');
    }

    public function producto()
    {
        return $this->hasOne(Product::class, 'product_cip13', 'product_no');
    }
}
