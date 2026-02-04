<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\OrderCagedim;
use App\Mail\VerifyPricesMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\ProductThresholdPrice;

class VerifyCagedimOrderPricesAlternative extends Command
{
    protected $signature = 'orders:verify-cagedim-prices-alternative';
    protected $description = 'Alternative method to check and update order prices and discounts';

    private const PREMIUM_THRESHOLD = 200;

    public function handle()
    {
        $today = Carbon::today();
        $notFoundProducts = [];
        $orderSummary = [];

        $date_order = Carbon::today()->format(app('global_format_date') . ' 00:00:00');
        
        $orders = OrderCagedim::with(['lines', 'lines.product'])
            ->where('created_at', '>=', $date_order)
            ->get();
        
        $this->info("Starting verification of {$orders->count()} orders");
        
        foreach ($orders as $order) {
            $result = $this->processOrder($order);
            if ($result) {
                $orderSummary[] = $result['summary'];
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
            
            DB::commit();

            $this->displayResults($order, $updates);

            // Prepare summary for email
            return [
                'summary' => [
                    'id' => $order->id,
                    'reference' => $order->order_reference ?? 'N/A',
                    'changes' => $updates
                ],
                'missingProducts' => array_map(function($lineId) use ($order) {
                    return "Line {$lineId} in Order {$order->id}";
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

        foreach ($order->lines as $line) {
            if (!$line->product) {
                $analysis['hasErrors'] = true;
                $analysis['missingProducts'][] = $line->id;
                $this->warn("- Missing product for line {$line->id}");
                continue;
            }

            $threshold = $this->findThresholdPrice($line->product, $line->qty);
            if ($line->product->product_premium_offer != 0) {    
                $lineTotal = $threshold ? 
                    $threshold->product_threshold_price_price * $line->qty :
                    $line->product->product_unit_price_pght * $line->qty;
            } else {
                $lineTotal = $threshold ? 
                    $threshold->product_threshold_price_price_premium * $line->qty :
                    $line->product->product_unit_price_pght * $line->qty;
            }
            $analysis['totalAmount'] += $lineTotal;

            if ($line->product->product_premium_offer == 1 && $threshold) {
                $analysis['premiumTotal'] += $threshold->product_threshold_price_price_premium * $line->qty;
            }

            $analysis['lineAnalysis'][$line->id] = [
                'threshold' => $threshold,
                'currentDiscount' => $line->discount_value,
                'currentType' => $line->discount_type
            ];
        }

        $analysis['isPremiumOrder'] = $analysis['premiumTotal'] >= self::PREMIUM_THRESHOLD;

        return $analysis;
    }

    private function findThresholdPrice($product, $quantity)
    {
        if ($product->product_premium_offer != 1) {
            return ProductThresholdPrice::where('product_threshold_price_product_id', $product->id)
                ->where('product_threshold_price_threshold_from', '<=', $quantity)
                ->where(function($query) use ($quantity) {
                    $query->where('product_threshold_price_threshold_to', '>=', $quantity)
                        ->orWhereNull('product_threshold_price_threshold_to');
                })
                ->first();
        } else {
            return ProductThresholdPrice::where('product_threshold_price_product_id_premium', $product->id)
                ->where('product_threshold_price_threshold_from_premium', '<=', $quantity)
                ->where(function($query) use ($quantity) {
                    $query->where('product_threshold_price_threshold_to_premium', '>=', $quantity)
                        ->orWhereNull('product_threshold_price_threshold_to_premium');
                })
                ->first();
        }
    }

    private function updateOrderLines($order, $analysis)
    {
        $updates = [];

        foreach ($order->lines as $line) {
            if (in_array($line->id, $analysis['missingProducts'])) {
                continue;
            }

            $lineAnalysis = $analysis['lineAnalysis'][$line->id];
            $threshold = $lineAnalysis['threshold'];
            $updateData = [];

            if ($threshold) {
                // Calculate correct discount based on order analysis
                $correctDiscount = $analysis['isPremiumOrder'] ? 
                    $threshold->product_threshold_price_discount_premium : 
                    $threshold->product_threshold_price_discount;

                $correctType = $analysis['isPremiumOrder'] ? 'premium' : 'ZC11';

                if ($line->discount_value != $correctDiscount) {
                    $updateData['discount_value'] = $correctDiscount;
                    $updates[] = sprintf(
                        "Line %d (Product: %s, CIP13: %s): Discount updated from %.2f to %.2f",
                        $line->id,
                        $line->product->product_name,
                        $line->product->product_cip13,
                        $line->discount_value,
                        $correctDiscount
                    );
                }

                if ($line->discount_type != $correctType) {
                    $updateData['discount_type'] = $correctType;
                    $updates[] = sprintf(
                        "Line %d (Product: %s, CIP13: %s): Discount type changed from '%s' to '%s'",
                        $line->id,
                        $line->product->product_name,
                        $line->product->product_cip13,
                        $line->discount_type,
                        $correctType
                    );
                }
            } else {
                // Reset discounts if no threshold applies
                if ($line->discount_value != 0) {
                    $updateData['discount_value'] = 0;
                    $updates[] = sprintf(
                        "Line %d (Product: %s, CIP13: %s): Discount removed (from %.2f to 0)",
                        $line->id,
                        $line->product->product_name,
                        $line->product->product_cip13,
                        $line->discount_value
                    );
                }
                if ($line->discount_type != 'ZC11') {
                    $updateData['discount_type'] = 'ZC11';
                    $updates[] = sprintf(
                        "Line %d (Product: %s, CIP13: %s): Set to ZC11 discount type (from '%s')",
                        $line->id,
                        $line->product->product_name,
                        $line->product->product_cip13,
                        $line->discount_type
                    );
                }
            }

            if (!empty($updateData)) {
                $line->update($updateData);
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



