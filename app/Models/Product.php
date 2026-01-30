<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    const STATUS_DISPONIBLE = 'Disponible';
    const STATUS_INDISPONIBLE = 'Indisponible';
    const STATUS_DISCONTINUED = 'Discontinued';

    const SUB_STATUS_ACTIVE = 'Actif';
    const SUB_STATUS_INACTIVE = 'Inactif';

    protected $table = 'products';
    protected $fillable = [
        'product_sap_id',
        'product_cip13',
        'product_category_id',
        'product_name',
        'product_presentation',
        'product_unit_price',
        'product_unit_price_pght',
        'product_box_quantity',
        'product_bundle_quantity',
        'product_quote',
        'product_allocation',
        'product_min_order',
        'product_max_order',
        'product_active',
        'product_sell_from_date',
        'product_sell_from_time',
        'product_sell_to_date',
        'product_sell_to_time',
        'product_short_term',
        'product_expiration_date',
        'product_status'
    ];

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'product_category_id');
    }

    public function getAll()
    {
        return Product::all();
    }

    public function search(?string $search, ?array $filters = null)
    {
        $products = Product::with('laboratory', 'category', 'promotions',); //, 'promotion_product'
        $products->orderBy('name', 'ASC');

        if(!empty($search))
        {
            $products = $products->where('name', 'LIKE', "%$search%")
                ->orWhere('ean', 'LIKE', "%$search%")
                ->orWhere('sku', 'LIKE', "%$search%")
                ->orWhere('national_code', 'LIKE', "%$search%");
        }

        if(!empty($filters))
        {
            foreach($filters as $key => $filter)
            {
                if(!empty($filter))
                    $products->where($key, $filter);
            }
        }

        return $products->paginate(20);
    }

}
