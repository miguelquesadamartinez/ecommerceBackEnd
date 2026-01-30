<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Mail\VerifyPricesMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\ProductThresholdPrice;

class VerifyRegularOrderPrices extends Command
{
    protected $signature = 'orders:verify-regular-prices';
    protected $description = 'Check and update prices and discounts for today\'s regular orders';

    private function getModifiedFields($newData, $item)
    {
        $differences = [];
        $fieldsToCompare = [
            'order_detail_price' => 'Base price',
            'order_detail_discount' => 'Discount',
            'order_detail_price_with_dto' => 'Final price'
        ];

        foreach ($fieldsToCompare as $field => $label) {
            if (array_key_exists($field, $newData)) {
                $oldValue = $item->$field ?? '';
                $newValue = $newData[$field];

                if (($oldValue !== $newValue) &&
                    !($oldValue === '' && $newValue === '') &&
                    !($oldValue === '' && $newValue === null)) {
                    $differences[$field] = [
                        'label' => $label,
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            }
        }

        return $differences;
    }

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
        
        $this->info("Verifying " . $orders->count() . " orders from today");
        
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
            $total = 0;
            $changes = [];
            $missingProducts = [];
            
            // First calculate total of premium products
            foreach ($order->items as $item) {
                if (!isset($item->product)) {
                    $missingProducts[] = $item->id;
                    $this->warn("- Product not found for item {$item->id}, skipping...");
                    continue;
                }
                
                if ($item->product->product_premium_offer == 1) {
                    $threshold = $this->findThresholdPrice($item->product, $item->order_detail_quantity);
                    if ($threshold) {
                        $total += $threshold->product_threshold_price_price_premium * $item->order_detail_quantity;
                    }
                }
            }

            $isPremiumOrder = $total >= 200;

            // Check and update each line
            foreach ($order->items as $item) {
                if (!isset($item->product)) {
                    continue;
                }

                $product = $item->product;
                $threshold = $this->findThresholdPrice($product, $item->order_detail_quantity);
                $updateData = [];

                // Base price check
                if ($item->order_detail_price != $product->product_unit_price_pght) {
                    $updateData['order_detail_price'] = $product->product_unit_price_pght;
                }

                if ($threshold) {
                    // Discount check
                    $correctDiscount = $isPremiumOrder ? 
                        $threshold->product_threshold_price_discount_premium : 
                        $threshold->product_threshold_price_discount;

                    if ($item->order_detail_discount != $correctDiscount) {
                        $updateData['order_detail_discount'] = $correctDiscount;
                    }

                    // Final price check
                    $priceWithDiscount = $isPremiumOrder ? 
                        $threshold->product_threshold_price_price_premium : 
                        $threshold->product_threshold_price_price;

                    if ($item->order_detail_price_with_dto != $priceWithDiscount) {
                        $updateData['order_detail_price_with_dto'] = $priceWithDiscount;
                    }
                } else {
                    // Reset discounts if no threshold applies
                    if ($item->order_detail_discount != 0) {
                        $updateData['order_detail_discount'] = 0;
                    }
                    
                    if ($item->order_detail_price_with_dto != $product->product_unit_price_pght) {
                        $updateData['order_detail_price_with_dto'] = $product->product_unit_price_pght;
                    }
                }

                if (!empty($updateData)) {
                    // Get differences before update
                    $differences = $this->getModifiedFields($updateData, $item);
                    
                    // Update the item
                    $item->update($updateData);
                    
                    // Store changes for reporting
                    $changes[] = [
                        'item_id' => $item->id,
                        'product_name' => $product->product_name,
                        'product_cip13' => $product->product_cip13,
                        'differences' => $differences
                    ];
                }
            }

            // Update order total if there were changes
            if (!empty($changes)) {
                $oldTotal = $order->order_amount;
                $order->order_amount = $order->getTotal();
                $order->save();
                
                if ($oldTotal != $order->order_amount) {
                    $changes[] = [
                        'item_id' => 'total',
                        'differences' => [
                            'order_amount' => [
                                'label' => 'Order total',
                                'old' => $oldTotal,
                                'new' => $order->order_amount
                            ]
                        ]
                    ];
                }
            }

            DB::commit();

            $this->displayResults($order, $changes);

            return [
                'summary' => [
                    'id' => $order->id,
                    'reference' => $order->order_reference ?? 'N/A',
                    'changes' => $this->formatChangesForEmail($changes),
                    'isPremiumOrder' => $isPremiumOrder
                ],
                'missingProducts' => array_map(function($itemId) use ($order) {
                    return "Item {$itemId} in Order {$order->id}";
                }, $missingProducts)
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

    private function formatChangesForEmail($changes)
    {
        $formatted = [];
        foreach ($changes as $change) {
            if ($change['item_id'] === 'total') {
                $formatted[] = "Order total changed from {$change['differences']['order_amount']['old']} to {$change['differences']['order_amount']['new']}";
                continue;
            }

            $itemInfo = "Product {$change['product_name']} (CIP13: {$change['product_cip13']})";
            foreach ($change['differences'] as $field => $diff) {
                $formatted[] = "{$itemInfo}: {$diff['label']} changed from {$diff['old']} to {$diff['new']}";
            }
        }
        return $formatted;
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

    private function displayResults($order, $changes)
    {
        if (empty($changes)) {
            $this->info("✓ Order {$order->id}: All prices and discounts are correct");
        } else {
            $this->info("✓ Order {$order->id}: Updates completed");
            foreach ($changes as $change) {
                if ($change['item_id'] === 'total') {
                    $this->line("  - Order total updated: {$change['differences']['order_amount']['old']} → {$change['differences']['order_amount']['new']}");
                    continue;
                }

                $this->line("  - Product {$change['product_name']} (CIP13: {$change['product_cip13']}):");
                foreach ($change['differences'] as $field => $diff) {
                    $this->line("    * {$diff['label']}: {$diff['old']} → {$diff['new']}");
                }
            }
        }
    }
}


