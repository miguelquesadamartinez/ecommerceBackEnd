<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;


class OrderCagedim extends Model
{
    protected $table = 'orders_cagedim';
    
    protected $fillable = [
        'sales_org',
        'sold_to',
        'ship_to',
        'customer_po',
        'po_type',
        'order_block_code',
        'shipment_method',
        'delivery_priority',
        'po_date',
        'requested_delivery_date'
    ];

    protected $dates = [
        'po_date',
        'requested_delivery_date'
    ];

    public function headerTexts()
    {
        return $this->hasMany(OrderCagedimHeaderText::class, 'order_id');
    }

    public function lines()
    {
        return $this->hasMany(OrderCagedimLine::class, 'order_id');
    }

    public function orderLines()
    {
        return $this->hasMany(OrderCagedimLine::class, 'order_id');
    }

    public function pharmacy()
    {
        return $this->hasOne(Pharmacy::class, 'pharmacy_sap_id', 'sold_to');
    }

    public function pharmacy_cip()
    {
        return $this->hasOne(Pharmacy::class, 'pharmacy_cip13', 'sold_to')->where('pharmacy_status', '!=', 'Inactive New SAP')->latest();
    }

    public function getTotal() : float
    {
        $total = 0.0;
        foreach($this->lines as $item){
            if ($item->discount_value == 0) {
                $total = $total + ( $item->product->product_unit_price_pght * $item->qty );
            } else {
                $total = $total + ( ( $item->product->product_unit_price_pght - 
                ($item->product->product_unit_price_pght * $item->discount_value / 100 ) ) * $item->qty ); 
            }
        }
        return round($total, 2);
    }

    public function getTotalProducts() : int
    {
        $total = 0;
        foreach($this->lines as $item){
            $total++;
        }
        return $total;
    }
}