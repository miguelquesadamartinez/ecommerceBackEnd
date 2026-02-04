<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Pharmacy;
use Illuminate\Support\Facades\DB;
use App\Models\OrderDetail;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const DRAFT                 = 'Draft';
    const CONFIRMED             = 'Confirmed';
    const CANCELED              = 'Cancelled';
    const ONHOLD                = 'On hold';
    const BLOCKED               = 'Blocked';
    const BLOCKED_ALLOCATION    = 'Blocked Allocation';
    const PENDING_EXPORT        = 'Pending to export';
    const EXPORTED              = 'Exported';

    protected $table = 'orders';
    protected $fillable = [
        'order_user_id',
        'order_pharmacy_id',
        'order_amount',
        'order_desired_delivery_date',
        'order_status',
        'order_blocked',
        'order_block_type',
        'order_sent_to_nomane',
        'order_source',
        'order_retain_from_date',
        'order_retain_from_time',
        'order_retain_to_date',
        'order_retain_to_time'
    ];

    public function pharmacy()
    {
        return $this->hasOne(Pharmacy::class, 'id', 'order_pharmacy_id');
    }

    public function items()
    {
        return $this->hasMany(OrderDetail::class, 'order_detail_order_id', 'id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_detail_order_id', 'id');
    }

    public function selectTodayOrders($id){

        //$date = Carbon::now()->format(app('global_format_date'), strtotime('-1 day'));

        $date = Carbon::now()->format(app('global_format_date'));

        return DB::table('order_details')
            ->leftjoin('orders', 'orders.id', '=', 'order_detail.order_detail_order_id')
            ->leftjoin('pharmacies', 'orders.order_pharmacy_id', '=', 'pharmacies.id')
            ->leftjoin('products', 'products.id', '=', 'order_detail.order_detail_product_id')
            ->where([
                    ['orders.updated_at', '>=', $date . ' 00:00:00:000000'],
                    ['products.products_laboratory_id', '=', $id]
                ])->get();

    }

    public function getTotal() : float
    {
        $total = 0.0;
        foreach($this->items as $item){
            if ($item->order_detail_discount == 0) {
                $total = $total + ( $item->order_detail_price * $item->order_detail_quantity );
            } else {
                $total = $total + ( $item->order_detail_price_with_dto * $item->order_detail_quantity ) ;
            }
        }
        return round($total, 2);
    }

    public function getTotalProducts() : int
    {
        $total = 0;
        foreach($this->items as $item){
            $total++;
        }
        return $total;
    }
}
