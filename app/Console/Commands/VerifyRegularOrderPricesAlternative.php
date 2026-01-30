<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Order;
use App\Mail\VerifyPricesMail;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\ProductThresholdPrice;

class VerifyRegularOrderPricesAlternative extends Command
{
    protected $signature = 'orders:verify-regular-prices-alternative';
    protected $description = 'Alternative method to check and update regular order prices and discounts';

    private const PREMIUM_THRESHOLD = 200;

    public function handle()
    {
        $today = Carbon::today();
        $notFoundProducts = [];
        $orderSummary = [];
        
        $date_order = Carbon::today()->format(app('global_format_date') . ' 00:00:00');

        $orders = Order::with(['items', 'items.product'])
            ->where('order_status', '=', Order::CONFIRMED)
            ->where('created_at', '>=', $date_order)
            ->get();
        
        $this->info("Starting verification of {$orders->count()} orders");
        
        foreach ($orders as $order) {
            $result = $this->processOrder($order);
            if ($result) {
                if (!empty($result['summary'])) {
                    $orderSummary[] = $result['summary'];
                }
                if (!empty($result['missingProducts'])) {
                    $notFoundProducts = array_merge($notFoundProducts, $result['missingProducts']);
                }
            }
        }

        $emailData = [
            'description' => $this->description,
            'orderSummary' => $orderSummary,
            'notFoundProducts' => $notFoundProducts
        ];

        Mail::to(env('EMAIL_FOR_INFO'))->send(new VerifyPricesMail($emailData));
    }

    private function processOrder($order)
    {
        $this->info("Processing order ID: {$order->id}");
        
        DB::beginTransaction();
        try {
            $orderAnalysis = $this->analyzeOrder($order);
            
            if ($orderAnalysis['hasErrors']) {
                $this->error("✗ Order {$order->id}: Contains errors, skipping");
                DB::rollBack();
                return null;
            }

            $updates = $this->updateOrderLines($order, $orderAnalysis);
            
            // Update order total if there were changes
            if (!empty($updates)) {
                $order->order_amount = $order->getTotal();
                $order->save();
                $updates[] = "Order total updated: {$order->order_amount}";
            }

            DB::commit();

            $this->displayResults($order, $updates);

            return [
                'summary' => [
                    'id' => $order->id,
                    'reference' => $order->order_reference ?? 'N/A',
                    'changes' => $updates
                ],
                'missingProducts' => array_map(function($itemId) use ($order) {
                    return "Item {$itemId} in Order {$order->id}";
                }, $orderAnalysis['missingProducts'])
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("✗ Error processing order {$order->id}: " . $e->getMessage());
            
            $emailData = [
                'description' => $this->description . ' call - 500',
                'orderSummary' => [],
                'notFoundProducts' => ["Error processing order {$order->id}: " . $e->getMessage()]
            ];
            
            Mail::to(env('EMAIL_FOR_APP_ERROR'))->send(new VerifyPricesMail($emailData));
            return null;
        }
    }

    private function analyzeOrder($order)
    {
        $analysis = [
            'totalAmount' => 0,
            'premiumTotal' => 0,
            'hasErrors' => false,
            'missingProducts' => [],
            'isPremiumOrder' => false,
            'lineAnalysis' => []
        ];

        foreach ($order->items as $item) {
            if (!$item->product) {
                $analysis['hasErrors'] = true;
                $analysis['missingProducts'][] = $item->id;
                $this->warn("- Missing product for item {$item->id}");
                continue;
            }

            $threshold = $this->findThresholdPrice($item->product, $item->order_detail_quantity);
            
            if ($threshold) {
                if ($item->product->product_premium_offer == 1) {
                    $lineTotal = $threshold->product_threshold_price_price_premium * $item->order_detail_quantity;
                    $analysis['premiumTotal'] += $threshold->product_threshold_price_price_premium * $item->order_detail_quantity;
                } else {
                    $lineTotal = $threshold->product_threshold_price_price * $item->order_detail_quantity;
                }
                $analysis['totalAmount'] += $lineTotal;

            } else {
                $lineTotal = $item->product->product_unit_price_pght * $item->order_detail_quantity;
                $analysis['totalAmount'] += $lineTotal;
            }

            $analysis['lineAnalysis'][$item->id] = [
                'threshold' => $threshold,
                'currentPrice' => $item->order_detail_price,
                'currentDiscount' => $item->order_detail_discount,
                'currentPriceWithDto' => $item->order_detail_price_with_dto,
                'basePrice' => $item->product->product_unit_price_pght
            ];
        }

        $analysis['isPremiumOrder'] = $analysis['premiumTotal'] >= self::PREMIUM_THRESHOLD;

        return $analysis;
    }

    private function findThresholdPrice($product, $quantity)
    {
        return ProductThresholdPrice::where('product_threshold_price_product_id', $product->id)
            ->where('product_threshold_price_threshold_from_premium', '<=', $quantity)
            ->where(function($query) use ($quantity) {
                $query->where('product_threshold_price_threshold_to_premium', '>=', $quantity)
                    ->orWhereNull('product_threshold_price_threshold_to_premium');
            })
            ->first();
    }

    private function updateOrderLines($order, $analysis)
    {
        $updates = [];

        foreach ($order->items as $item) {
            if (in_array($item->id, $analysis['missingProducts'])) {
                continue;
            }

            $lineAnalysis = $analysis['lineAnalysis'][$item->id];
            $threshold = $lineAnalysis['threshold'];
            $updateData = [];

            // Check base price
            if ($item->order_detail_price != $lineAnalysis['basePrice']) {
                $updateData['order_detail_price'] = $lineAnalysis['basePrice'];
                $this->line("- Updating base price for item {$item->id}: {$item->order_detail_price} → {$lineAnalysis['basePrice']}");
            }

            if ($threshold) {
                // Calculate correct discount and price with discount
                $correctDiscount = $analysis['isPremiumOrder'] ? 
                    $threshold->product_threshold_price_discount_premium : 
                    $threshold->product_threshold_price_discount;

                $correctPriceWithDto = $analysis['isPremiumOrder'] ? 
                    $threshold->product_threshold_price_price_premium : 
                    $threshold->product_threshold_price_price;

                if ($item->order_detail_discount != $correctDiscount) {
                    $updateData['order_detail_discount'] = $correctDiscount;
                    $this->line("- Updating discount for item {$item->id}: {$item->order_detail_discount} → {$correctDiscount}");
                }

                if ($item->order_detail_price_with_dto != $correctPriceWithDto) {
                    $updateData['order_detail_price_with_dto'] = $correctPriceWithDto;
                    $this->line("- Updating price with discount for item {$item->id}: {$item->order_detail_price_with_dto} → {$correctPriceWithDto}");
                }
            } else {
                // Reset discounts if no threshold applies
                if ($item->order_detail_discount != 0 || $item->order_detail_price_with_dto != $lineAnalysis['basePrice']) {
                    $updateData['order_detail_discount'] = 0;
                    $updateData['order_detail_price_with_dto'] = $lineAnalysis['basePrice'];
                    $this->line("- Resetting discounts for item {$item->id}");
                }
            }

            if (!empty($updateData)) {
                $item->update($updateData);
                $updates[] = "Item {$item->id} updated";
            }
        }

        return $updates;
    }

    private function displayResults($order, $updates)
    {
        if (empty($updates)) {
            $this->info("✓ Order {$order->id}: All prices and discounts are correct");
        } else {
            $this->info("✓ Order {$order->id}: Updates completed");
            foreach ($updates as $update) {
                $this->line("  - $update");
            }
        }
    }
}

