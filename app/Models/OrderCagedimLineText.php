<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCagedimLineText extends Model
{
    protected $table = 'orders_cagedim_line_texts';
    
    protected $fillable = [
        'order_line_id',
        'text_type',
        'free_text'
    ];

    public function orderLine()
    {
        return $this->belongsTo(OrderCagedimLine::class, 'order_line_id');
    }
}