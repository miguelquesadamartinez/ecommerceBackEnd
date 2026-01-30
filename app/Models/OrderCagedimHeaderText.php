<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCagedimHeaderText extends Model
{
    protected $table = 'orders_cagedim_header_texts';
    
    protected $fillable = [
        'order_id',
        'text_type',
        'free_text'
    ];

    public function order()
    {
        return $this->belongsTo(OrderCagedim::class, 'order_id');
    }
}