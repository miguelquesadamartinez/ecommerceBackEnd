<?php

namespace App\Http\Controllers\Order;

use App\Helpers\OrderSaver;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Helpers\NomaneHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductThresholdPrice;
use Illuminate\Support\Facades\Log;

class OrderItemController extends Controller
{
    public function orderItemAdd(Request $request)
    {
        $newOrder = false;
        $order_id = $request->order_id;
        $all_order_detail = array();
        $productData = array();

        if($order_id == ""){
            $order = new Order();
            $order->order_pharmacy_id = $request->pharmacy_id;
            $order->order_amount = 0;
            $order->order_desired_delivery_date = date('Y-m-d');
            $order->order_status = Order::DRAFT;

            if(Auth::user() && isset(Auth::user()->user_type)) {
                if (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Manager') {
                    $order->order_source = 'CALL';
                } else if ( Auth::user()->user_type == 'Call') {
                    $order->order_source = 'CALL';
                } else if ( Auth::user()->user_type == 'Apm') {
                    $order->order_source = 'APM';
                } else {
                    $order->order_source = Auth::user()->user_type;
                }
            } else {
                $order->order_source = 'CALL';
            }

            $order->order_reference = NomaneHelper::geneateIniqueStringWithPrefix($order->order_source . '-');

            $order->order_user_id = Auth::user() && isset(Auth::user()->id) ? Auth::user()->id : 0;
            $order->save();
            $order_id = $order->id;
            $newOrder = true;
        } else {
            $order = Order::find($order_id);
        }

        OrderDetail::where('order_detail_order_id', '=', $order_id)
            ->where('order_detail_product_id', '=', $request->product_id)
            ->delete();

        // Totales antiguos
        $total_premium = 0;
        $total_biomed = 0;
        foreach($order->items as $item){
            $totals = OrderSaver::getOrderSeparatedTotals ($item->order_detail_product_id, $item->order_detail_quantity, $total_premium, $total_biomed);
            $total_biomed = $totals['total_biomed'];
            $total_premium = $totals['total_premium'];
        }
        // El producto que añadimos sumanos a totales
        $prod = Product::find($request->product_id);
        if ($prod->product_premium_offer == 1){
            $query = ProductThresholdPrice::query();
            $query = $query->where('product_threshold_price_product_id', $request->product_id)
                        ->where('product_threshold_price_threshold_from_premium', '>=', $request->quantity);
            $search = $request->quantity;
            $query = $query->where(function($query) use ($search) {
                $query = $query->where('product_threshold_price_threshold_to_premium', '<=', $search)
                        ->orWhere('product_threshold_price_threshold_to_premium', '=', null);
            });
            $productData = $query->get();
            if (count($productData)){
                $total_premium += ceil($productData[0]->product_threshold_price_price_premium * $request->quantity);
            }
        } else if ($prod->product_biomed_offer == 1){
            $query = ProductThresholdPrice::query();
            $query = $query->where('product_threshold_price_product_id', $request->product_id)
                    ->where('product_threshold_price_level', '=', 2);

            $productData = $query->get();

            if (count($productData)){
                $total_biomed += ceil($productData[0]->product_threshold_price_price_premium * $request->quantity);
            }
        } else {
            $query = ProductThresholdPrice::query();
            $query = $query->where('product_threshold_price_product_id', $request->product_id)
                        ->where('product_threshold_price_threshold_from', '>=', $request->quantity);
            $search = $request->quantity;
            $query = $query->where(function($query) use ($search) {
                $query = $query->where('product_threshold_price_threshold_to', '<=', $search)
                        ->orWhere('product_threshold_price_threshold_to', '=', null);
            });
            $productData = $query->get();
        }

        $productPrices = OrderSaver::updatePrices ($request->product_id, $productData, $total_premium, $total_biomed);

        $productDiscount = $productPrices['product_discount'];
        $productPrice = $productPrices['product_price'];
        $productPriceWithDto = $productPrices['product_price_with_dto'];

        $order_detail = OrderDetail::updateOrCreate(
            [
                'order_detail_order_id' => $order_id,
                'order_detail_product_id' => $request->product_id
            ],
            [
                'order_detail_price' => $productPrice,
                'order_detail_quantity' => $request->quantity,
                'order_detail_discount' => $productDiscount,
                'order_detail_price_with_dto' => $productPriceWithDto
            ]
        );

        $all_order_detail[$order_detail->id]['product'] = $prod;
        $all_order_detail[$order_detail->id]['items'] = $order_detail;

        $order = Order::find($order->id);

        foreach($order->items as $item){

            $query = ProductThresholdPrice::query();

            $prod_update = Product::find($item->order_detail_product_id);

            $query = OrderSaver::simpleCheck ($query, $item->order_detail_product_id, $item->order_detail_quantity, $item->order_detail_price_with_dto, $total_biomed);

            $productData = $query->get();

            $productPrices = OrderSaver::updatePrices ($item->order_detail_product_id, $productData, $total_premium, $total_biomed);

            $productDiscount = $productPrices['product_discount'];
            $productPrice = $productPrices['product_price'];
            $productPriceWithDto = $productPrices['product_price_with_dto'];

            $order_detail = OrderDetail::updateOrCreate(
                [
                    'order_detail_order_id' => $order_id,
                    'order_detail_product_id' => $item->order_detail_product_id
                ],
                [
                    'order_detail_discount' => $productDiscount,
                    'order_detail_price' => $productPrice,
                    'order_detail_price_with_dto' => $productPriceWithDto
                ]
            );

            $all_order_detail[$order_detail->id]['items'] = $order_detail;
            $all_order_detail[$order_detail->id]['product'] = $prod_update;
        }

        $order->order_status = Order::DRAFT;
        $order = Order::find($order_id);
        Order::where('id', $order_id)
            ->update(['order_amount' => $order->getTotal()]);

        $order->save();

        $order = Order::find($order->id);

        $countCategory = $order->items()
            ->join('products', 'order_details.order_detail_product_id', '=', 'products.id')
            ->join('categories', 'products.product_category_id', '=', 'categories.id')
            ->where('category_name', 'like', '%Génériques%')
            ->count();

        if ($countCategory === null) $countCategory = 0;

        return response()->json([
            'countCategory' => $countCategory,
            'newOrder' => $newOrder,
            'order_id' => $order_id,
            'order_detail' => $order_detail,
            'all_order_details' => $all_order_detail
        ], 200);
    }

    public function orderItemRemove (Request $request){
        $all_order_detail = array();
        $item = OrderDetail::where('order_detail_order_id', '=', $request->order_id)
            ->where('order_detail_product_id', '=', $request->product_id)
            ->delete();
        $order = Order::find($request->order_id);

        $order->order_status = Order::DRAFT;
        $order->save();

        $total_premium = 0;
        $total_biomed = 0;
        foreach($order->items as $item){

            $totals = OrderSaver::getOrderSeparatedTotals ($item->order_detail_product_id, $item->order_detail_quantity, $total_premium, $total_biomed);

            $total_biomed = $totals['total_biomed'];
            $total_premium = $totals['total_premium'];
        }

        $order = Order::find($order->id);

        foreach($order->items as $item){

            $prod_update = Product::find($item->order_detail_product_id);

            $query = ProductThresholdPrice::query();

            $query = OrderSaver::simpleCheck ($query, $item->order_detail_product_id, $item->order_detail_quantity, $item->order_detail_price_with_dto, $total_biomed);

            $productData = $query->get();

            $productPrices = OrderSaver::updatePrices ($item->order_detail_product_id, $productData, $total_premium, $total_biomed);

            $productDiscount = $productPrices['product_discount'];
            $productPrice = $productPrices['product_price'];
            $productPriceWithDto = $productPrices['product_price_with_dto'];

            $order_detail = OrderDetail::updateOrCreate(
                [
                    'order_detail_order_id' => $request->order_id,
                    'order_detail_product_id' => $item->order_detail_product_id
                ],
                [
                    'order_detail_discount' => $productDiscount,
                    'order_detail_price_with_dto' => $productPriceWithDto
                ]
            );

            $all_order_detail[$order_detail->id]['items'] = $order_detail;
            $all_order_detail[$order_detail->id]['product'] = $prod_update;
        }

        Order::where('id', $request->order_id)
            ->update(['order_amount' => $order->getTotal()]);

        $order = Order::find($request->order_id);

        $countCategory = $order->items()
            ->join('products', 'order_details.order_detail_product_id', '=', 'products.id')
            ->join('categories', 'products.product_category_id', '=', 'categories.id')
            ->where('category_name', 'like', '%Génériques%')
            ->count();

        if ($countCategory === null) $countCategory = 0;

        return response()->json([
            'countCategory' => $countCategory,
            'order_id' => $order->id,
            'product_id' => $request->product_id,
            'status' => 'Item deleted',
            'all_order_detail' => $all_order_detail
        ], 200);
    }
}

