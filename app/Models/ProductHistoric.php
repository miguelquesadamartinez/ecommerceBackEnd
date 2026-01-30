<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductHistoric extends Model
{
    protected $table = 'product_historics';
    protected $fillable = [
        'product_historic_product_id',
        'product_historic_field_name',
        'product_historic_old_value',
        'product_historic_new_value'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_historic_product_id');
    }
}
