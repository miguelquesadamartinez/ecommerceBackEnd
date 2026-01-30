<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Mail\InfoMail;
use App\Models\Product;
use App\Helpers\OrderSaver;
use App\Models\OrderCagedim;
use App\Mail\VerifyPricesMail;
use App\Models\OrderCagedimLine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\ProductThresholdPrice;

class VerifyCagedimOrderPrices extends Command
{
    protected $signature = 'orders:verify-cagedim-prices {order_id?}';
    protected $description = "Check and update prices and discounts for today's Cagedim orders.";

    public function handle()
    {
        $today = Carbon::today();
        $notFoundProducts = [];
        $orderSummary = [];

        $date_order = Carbon::today()->format(app('global_format_date') . ' 00:00:00');
        
        if ($this->argument('order_id')) {
            $orders = OrderCagedim::with(['lines', 'lines.product'])
                ->where('created_at', '>=', $date_order)
                ->where('id', '>=', $this->argument('order_id'))
                ->get();
        } else {
            $orders = OrderCagedim::with(['lines', 'lines.product'])
                ->where('created_at', '>=', $date_order)
                ->get();
        }

        $this->info("Verifying " . $orders->count() . " orders from today");
        
        foreach ($orders as $order) {
            $this->info("Processing order ID: {$order->id}");
            
            $updates = [];
            
            // First calculate total of premium products
            foreach ($order->lines as $line) {
                if (!isset($line->product)) {
                    $notFoundProducts[] = "Line {$line->id} in Order {$order->id}";
                    $this->warn("- Product not found for line {$line->id}, skipping...");
                    continue;
                }
                $product = $line->product;

                if ($line->qty < $product->product_min_order) {
                    $this->warn("- Quantity for line {$line->id} is below minimum order, updating quantity from {$line->qty} to {$product->product_min_order}");
                    $updates[] = sprintf(
                        "Line %d (Product: %s, CIP13: %s): is below minimum order, updating quantity from %d to %d ",
                        $line->id,
                        $product->product_name,
                        $product->product_cip13,
                        $line->qty,
                        $product->product_min_order
                    );
                    $line->qty = $product->product_min_order;
                    $line->save();
                }

                if ($line->qty > $product->product_max_order) {
                    $this->warn("- Quantity for line {$line->id} is above maximum order, updating quantity from {$line->qty} to {$product->product_max_order}");
                    $updates[] = sprintf(
                        "Line %d (Product: %s, CIP13: %s): is above maximum order, updating quantity from %d to %d ",
                        $line->id,
                        $product->product_name,
                        $product->product_cip13,
                        $line->qty,
                        $product->product_max_order
                    );
                    $line->qty = $product->product_max_order;
                    $line->save();
                }
            }

            $total_premium = 0;
            $total_biomed = 0;
            foreach($order->lines as $item){
                $totals = OrderSaver::getOrderSeparatedTotalsCIP ($item->product_no, $item->qty, $total_premium, $total_biomed);
                $total_biomed = $totals['total_biomed'];
                $total_premium = $totals['total_premium'];
            }
            foreach($order->lines as $item){
                if (!isset($line->product)) {
                    continue;
                }
                $query = ProductThresholdPrice::query();
                $query = OrderSaver::simpleCheckCIP ($query, $item->product_no, $item->qty, $total_biomed);
                $productData = $query->get();
                $productPrices = OrderSaver::updatePricesCIP ($item->product_no, $productData, $total_premium, $total_biomed);
                $productDiscount = $productPrices['product_discount'];
                $order_detail = OrderCagedimLine::updateOrCreate(
                    [
                        'order_id' => $item->order_id,
                        'product_no' => $item->product_no
                    ],
                    [
                        'discount_value' => $productDiscount
                    ]
                );
            }
        }
        $emailData = [
            'description' => $this->description,
            'orderSummary' => $orderSummary,
            'notFoundProducts' => $notFoundProducts
        ];
        //Mail::to(env('EMAIL_FOR_INFO'))->send(new VerifyPricesMail($emailData));
    }
}
