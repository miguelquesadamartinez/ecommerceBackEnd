<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnitsSell extends Model
{
    protected $table = 'product_units_sells';

    protected $fillable = [
        'product_units_sell_product_id',
        'product_units_sell_units_sell',
        'product_units_sell_date_start',
        'product_units_sell_date_end',
        'product_units_sell_time_start',
        'product_units_sell_time_end'
    ];
}
