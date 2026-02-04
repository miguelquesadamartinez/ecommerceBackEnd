<?php

namespace App\Helpers;

use App\Mail\InfoMail;
use App\Mail\OrderMail;
use App\Models\Product;
use App\Models\Pharmacy;
use App\Models\OrderCagedim;
use App\Models\OrderCagedimLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\OrderCagedimLineText;
use Illuminate\Support\Facades\Mail;
use App\Models\ProductThresholdPrice;
use App\Models\OrderCagedimHeaderText;
use Illuminate\Support\Facades\Artisan;

class OrderSaver
{
    /**
     * Save order from XML data
     *
     * @param array $orderData
     * @return array
     */
    public static function saveOrderPharmaML(array $orderData, int $cnt): array
    {
        DB::beginTransaction();

        $header = $orderData['header'];

        $notFoundPharmacies = [];
        $notFoundProducts = [];

        $pharmacy = Pharmacy::where('pharmacy_cip13', $header['sold_to'])
        ->where('pharmacy_status', '!=', 'Inactive New SAP')
        ->latest();

        if (! isset($pharmacy->pharmacy_cip13 ) ) {
            $notFoundPharmacies[] = [
                'cip_id' => $header['sold_to'],
                'order_reference' => $header['customer_po'] ?? 'N/A'
            ];
        }

        if ( isset($pharmacy->pharmacy_cip13) ) {
            $existingOrder = OrderCagedim::where('customer_po', $header['customer_po'])
                                        ->where('sold_to', $pharmacy->pharmacy_cip13)
                                        ->first();
        } else {
            $existingOrder = OrderCagedim::where('customer_po', $header['customer_po'])
                                        ->first();
        }

        if ($existingOrder) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Un ordre avec la même référence client existe déjà',
                'order_id' => $existingOrder->id,
                'not_found_pharmacies' => $notFoundPharmacies,
                'not_found_products' => $notFoundProducts
            ];
        }


        $order = new OrderCagedim();
        $order->sales_org = $header['sales_org'];
        $order->sold_to = $header['sold_to'];
        $order->ship_to = $header['ship_to'];
        $order->customer_po = $header['customer_po'];
        $order->po_type = $header['po_type'];
        $order->order_block_code = $header['order_block_code'] ?? null;
        $order->shipment_method = $header['shipment_method'];
        $order->delivery_priority = $header['delivery_priority'];
        $order->po_date = date('Y-m-d', strtotime($header['po_date']));
        $order->requested_delivery_date = date('Y-m-d', strtotime($header['requested_delivery_date']));
        $order->save();

        // Guardar las líneas del pedido
        if (!empty($orderData['lines'])) {
            foreach ($orderData['lines'] as $lineData) {
                $line = new OrderCagedimLine();
                $line->order_id = $order->id;
                $line->product_no = $lineData['product_no'];
                $line->product_qualifier_code = $lineData['product_qualifier_code'];
                $line->qty = $lineData['qty'];
                $line->item_category = $lineData['item_category'] ?? null;
                $line->discount_type = $lineData['discount_type'] ?? null;
                $line->discount_value = $lineData['discount_value'] ?? null;
                $line->save();

                $product = Product::where('product_cip13', $lineData['product_no'])->first();

                if (!$product) {
                    $notFoundProducts[] = [
                        'cip13' => $lineData['product_no'],
                        'order_reference' => $header['customer_po'] ?? 'N/A',
                        'quantity' => $lineData['qty']
                    ];
                }
            }
        }

        // Guardar los textos del encabezado
        if (!empty($orderData['header_texts'])) {
            foreach ($orderData['header_texts'] as $textData) {
                $headerText = new OrderCagedimHeaderText();
                $headerText->order_id = $order->id;
                $headerText->text_id = $textData['text_id'];
                $headerText->text_line = $textData['text_line'];
                $headerText->text_content = $textData['text_content'];
                $headerText->save();
            }
        }

        // Guardar los textos de las líneas
        if (!empty($orderData['line_texts'])) {
            foreach ($orderData['line_texts'] as $textData) {
                $lineText = new OrderCagedimLineText();
                $lineText->order_id = $order->id;
                $lineText->item_no = $textData['item_no'];
                $lineText->text_id = $textData['text_id'];
                $lineText->text_line = $textData['text_line'];
                $lineText->text_content = $textData['text_content'];
                $lineText->save();
            }
        }

        DB::commit();

        Artisan::call('orders:verify-cagedim-prices', ['order_id' => $order->id]);

        $order = OrderCagedim::find($order->id);

        // ToDo: Cambiar esto
        //if ( isset($pharmacy->pharmacy_email ) ) { // Este NO es el bueno
        if ( isset($pharmacy->pharmacy_email ) && $pharmacy->pharmacy_status != Pharmacy::BLOCKED ) { // Este es el BUENO
            if( $order->order_block_code == '' ) {
                if ($cnt % 2 == 0) {
                    Mail::mailer('smtp')->to($pharmacy->pharmacy_email)
                        ->bcc(['52dda429.NoName.onmicrosoft.com@amer.teams.ms','NomaneDigitalFrance@noName.com'])
                        //->queue(new OrderMail($order, $pharmacy, $order->customer_po));
                        ->send(new OrderMail($order, $pharmacy, $order->customer_po));
                } else {
                    Mail::mailer('smtp2')->to($pharmacy->pharmacy_email)
                        ->bcc(['52dda429.NoName.onmicrosoft.com@amer.teams.ms','NomaneDigitalFrance@noName.com'])
                        //->queue(new OrderMail($order, $pharmacy, $order->customer_po));
                        ->send(new OrderMail($order, $pharmacy, $order->customer_po));
                }
            }
        } else if ( isset( $pharmacy->pharmacy_account_status ) && ($pharmacy->pharmacy_account_status == 'Z4' || $pharmacy->pharmacy_account_status == '01' )) {
            $order->order_block_code = 'Client bloqué : avec le code:Z4/01';
            $order->save();
            if ($cnt % 2 == 0) {
                $text = 'Commande de pharmacie bloquée - CIP: ' . $pharmacy->pharmacy_cip13;
                //Mail::mailer('smtp')->to(env('EMAIL_FOR_INFO'))->queue(new InfoMail($text));
                Mail::mailer('smtp')->to(env('EMAIL_FOR_INFO'))->send(new InfoMail($text));
            } else {
                $text = 'Commande de pharmacie bloquée - CIP: ' . $pharmacy->pharmacy_cip13;
                //Mail::mailer('smtp2')->to(env('EMAIL_FOR_INFO'))->queue(new InfoMail($text));
                Mail::mailer('smtp2')->to(env('EMAIL_FOR_INFO'))->send(new InfoMail($text));
            }
        }
        return [
            'success' => true,
            'message' => 'Ordre enregistré avec succès',
            'order_id' => $order->id,
            'not_found_pharmacies' => $notFoundPharmacies,
            'not_found_products' => $notFoundProducts
        ];
    }

    public static function getOrderSeparatedTotals ($order_detail_product_id, $order_detail_quantity, $total_premium, $total_biomed) {
        $prod = Product::find($order_detail_product_id);
        // Count total amount for premium products / generic for existing order
        if ($prod->product_premium_offer == 1){
            $query = ProductThresholdPrice::query();
            $query = $query->where('product_threshold_price_product_id', $order_detail_product_id)
                        ->where('product_threshold_price_threshold_from_premium', '<=', $order_detail_quantity);
            $search = $order_detail_quantity;
            $query = $query->where(function($query) use ($search) {
                $query = $query->where('product_threshold_price_threshold_to_premium', '>=', $search)
                        ->orWhere('product_threshold_price_threshold_to_premium', '=', null);
            });
            $productData = $query->get();
            if (count($productData)){
                $total_premium += ceil($productData[0]->product_threshold_price_price_premium * $order_detail_quantity);
            }
        }
        if ($prod->product_biomed_offer == 1){
            $query = ProductThresholdPrice::query();
            $query = $query->where('product_threshold_price_product_id', $order_detail_product_id)
                    ->where('product_threshold_price_level', '=', 2);
            $productData = $query->get();
            if (count($productData)){
                $total_biomed += ceil($productData[0]->product_threshold_price_price_premium * $order_detail_quantity);
            }
        }
        return [
            'total_premium' => $total_premium,
            'total_biomed' => $total_biomed
        ];
    }

    public static function getOrderSeparatedTotalsCIP ($cip, $order_detail_quantity, $total_premium, $total_biomed) {
        $prod = Product::where('product_cip13', $cip)->get();
        $prod = $prod[0];
        // Count total amount for premium products / generic for existing order
        if ($prod->product_premium_offer == 1){
            $query = ProductThresholdPrice::query();
            $query = $query->where('product_threshold_price_product_id', $prod->id)
                        ->where('product_threshold_price_threshold_from_premium', '<=', $order_detail_quantity);
            $search = $order_detail_quantity;
            $query = $query->where(function($query) use ($search) {
                $query = $query->where('product_threshold_price_threshold_to_premium', '>=', $search)
                        ->orWhere('product_threshold_price_threshold_to_premium', '=', null);
            });
            $productData = $query->get();
            if (count($productData)){
                $total_premium += ceil($productData[0]->product_threshold_price_price_premium * $order_detail_quantity);
            }
        }
        if ($prod->product_biomed_offer == 1){
            $query = ProductThresholdPrice::query();
            $query = $query->where('product_threshold_price_product_id', $prod->id)
                    ->where('product_threshold_price_level', '=', 2);
            $productData = $query->get();
            if (count($productData)){
                $total_biomed += ceil($productData[0]->product_threshold_price_price_premium * $order_detail_quantity);
            }
        }
        return [
            'total_premium' => $total_premium,
            'total_biomed' => $total_biomed
        ];
    }

    public static function updatePrices ($order_detail_product_id, $productData, $total_premium, $total_biomed) {
        $product = Product::find($order_detail_product_id);
        if (count($productData)){
            if ($product->product_premium_offer == 1){
                if ($total_premium >= 200){
                    $productDiscount = $productData[0]->product_threshold_price_discount_premium;
                    $productPrice = $product->product_unit_price_pght;
                    $productPriceWithDto = $productData[0]->product_threshold_price_price_premium;
                } else {
                    $productDiscount = $productData[0]->product_threshold_price_discount;
                    $productPrice = $product->product_unit_price_pght;
                    $productPriceWithDto = $productData[0]->product_threshold_price_price;
                }
            } else if ($product->product_biomed_offer == 1){
                if ($total_biomed >= 800){
                    $productDiscount = $productData[0]->product_threshold_price_discount_premium;
                    $productPrice = $product->product_unit_price_pght;
                    $productPriceWithDto = $productData[0]->product_threshold_price_price_premium;
                } else {
                    $productDiscount = $productData[0]->product_threshold_price_discount;
                    $productPrice = $product->product_unit_price_pght;
                    $productPriceWithDto = $productData[0]->product_threshold_price_price;
                }
            } else {
                if ($total_premium >= 200){
                    $productDiscount = $productData[0]->product_threshold_price_discount_premium;
                    $productPrice = $product->product_unit_price_pght;
                    $productPriceWithDto = $productData[0]->product_threshold_price_price_premium;
                } else {
                    $productDiscount = $productData[0]->product_threshold_price_discount;
                    $productPrice = $product->product_unit_price_pght;
                    $productPriceWithDto = $productData[0]->product_threshold_price_price;
                }
            }

        } else {
            $productDiscount = 0;
            $productPrice = $product->product_unit_price_pght;
            $productPriceWithDto = $productPrice;
        }

        return [
            'product_discount' => $productDiscount,
            'product_price' => $productPrice,
            'product_price_with_dto' => $productPriceWithDto
        ];
    }

    public static function updatePricesCIP ($cip, $productData, $total_premium, $total_biomed) {

        $product = Product::where('product_cip13', $cip)->get();
        $product = $product[0];

        if (count($productData)){
            if ($product->product_premium_offer == 1){
                if ($total_premium >= 200){
                    $productDiscount = $productData[0]->product_threshold_price_discount_premium;
                    $productPrice = $product->product_unit_price_pght;
                    $productPriceWithDto = $productData[0]->product_threshold_price_price_premium;
                } else {
                    $productDiscount = $productData[0]->product_threshold_price_discount;
                    $productPrice = $product->product_unit_price_pght;
                    $productPriceWithDto = $productData[0]->product_threshold_price_price;
                }
            } else if ($product->product_biomed_offer == 1){
                if ($total_biomed >= 800){
                    $productDiscount = $productData[0]->product_threshold_price_discount_premium;
                    $productPrice = $product->product_unit_price_pght;
                    $productPriceWithDto = $productData[0]->product_threshold_price_price_premium;
                } else {
                    $productDiscount = $productData[0]->product_threshold_price_discount;
                    $productPrice = $product->product_unit_price_pght;
                    $productPriceWithDto = $productData[0]->product_threshold_price_price;
                }
            } else {
                if ($total_premium >= 200){
                    $productDiscount = $productData[0]->product_threshold_price_discount_premium;
                    $productPrice = $product->product_unit_price_pght;
                    $productPriceWithDto = $productData[0]->product_threshold_price_price_premium;
                } else {
                    $productDiscount = $productData[0]->product_threshold_price_discount;
                    $productPrice = $product->product_unit_price_pght;
                    $productPriceWithDto = $productData[0]->product_threshold_price_price;
                }
            }

        } else {
            $productDiscount = 0;
            $productPrice = $product->product_unit_price_pght;
            $productPriceWithDto = $productPrice;
        }

        return [
            'product_discount' => $productDiscount,
            'product_price' => $productPrice,
            'product_price_with_dto' => $productPriceWithDto
        ];
    }

    public static function simpleCheck ($query, $order_detail_product_id, $order_detail_quantity, $order_detail_price_with_dto, $total_biomed) {
        $prod = Product::find($order_detail_product_id);
        if ($prod->product_premium_offer == 1){
            $query = ProductThresholdPrice::query();
            $query = $query->where('product_threshold_price_product_id', $order_detail_product_id)
                        ->where('product_threshold_price_threshold_from_premium', '<=', $order_detail_quantity);
            $search = $order_detail_quantity;
            $query = $query->where(function($query) use ($search) {
                $query = $query->where('product_threshold_price_threshold_to_premium', '>=', $search)
                        ->orWhere('product_threshold_price_threshold_to_premium', '=', null);
            });
        } else if ($prod->product_biomed_offer == 1){
            $query = ProductThresholdPrice::query();
            if ($total_biomed >= 800){
                $query = $query->where('product_threshold_price_product_id', $order_detail_product_id)
                        ->where('product_threshold_price_level', '=', 2);
            } else {
                $query = $query->where('product_threshold_price_product_id', $order_detail_product_id)
                        ->where('product_threshold_price_level', '=', 1);
            }
        } else {
            $query = ProductThresholdPrice::query();
            $query = $query->where('product_threshold_price_product_id', $order_detail_product_id)
                        ->where('product_threshold_price_threshold_from', '<=', $order_detail_quantity);
            $search = $order_detail_quantity;
            $query = $query->where(function($query) use ($search) {
                $query = $query->where('product_threshold_price_threshold_to', '>=', $search)
                        ->orWhere('product_threshold_price_threshold_to', '=', null);
            });
        }

        return $query;
    }

    // Esta no usa el total premium, porque el biomed es para comprobar cual thresold usar
    public static function simpleCheckCIP ($query, $cip, $order_detail_quantity, $total_biomed) {

        $prod = Product::where('product_cip13', $cip)->get();
        $prod = $prod[0];
        if ($prod->product_premium_offer == 1){
            $query = ProductThresholdPrice::query();
            $query = $query->where('product_threshold_price_product_id', $prod->id)
                        ->where('product_threshold_price_threshold_from_premium', '<=', $order_detail_quantity);
            $search = $order_detail_quantity;
            $query = $query->where(function($query) use ($search) {
                $query = $query->where('product_threshold_price_threshold_to_premium', '>=', $search)
                        ->orWhere('product_threshold_price_threshold_to_premium', '=', null);
            });
        } else if ($prod->product_biomed_offer == 1){
            $query = ProductThresholdPrice::query();
            if ($total_biomed >= 800){
                $query = $query->where('product_threshold_price_product_id', $prod->id)
                        ->where('product_threshold_price_level', '=', 2);
            } else {
                $query = $query->where('product_threshold_price_product_id', $prod->id)
                        ->where('product_threshold_price_level', '=', 1);
            }
        } else {
            $query = ProductThresholdPrice::query();
            $query = $query->where('product_threshold_price_product_id', $prod->id)
                        ->where('product_threshold_price_threshold_from', '<=', $order_detail_quantity);
            $search = $order_detail_quantity;
            $query = $query->where(function($query) use ($search) {
                $query = $query->where('product_threshold_price_threshold_to', '>=', $search)
                        ->orWhere('product_threshold_price_threshold_to', '=', null);
            });
        }

        return $query;
    }
}
