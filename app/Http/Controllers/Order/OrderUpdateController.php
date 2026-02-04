<?php

namespace App\Http\Controllers\Order;

use Carbon\Carbon;
use App\Models\Order;
use App\Mail\OrderMail;
use App\Models\Product;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use App\Helpers\NomaneHelper;
use App\Models\PharmacyHistoric;
use App\Models\ProductUnitsSell;
use App\Helpers\FileProcessHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class OrderUpdateController extends Controller
{
    public function getOrder(Request $request)
    {
        $order = Order::find($request->order_id);
        if (isset($order->id)){
            $pharmacy = Pharmacy::find($order->order_pharmacy_id);
            $order_detail = $order->items;
            foreach ($order_detail as $key => $value) {
                $product = Product::find($value->order_detail_product_id);
                $order_detail[$key]->product_presentation = $product->product_presentation;
                $order_detail[$key]->product_unit_price = $product->product_unit_price;
            }
            return response()->json(['status' => 'success', 'order' => $order, 'order_detail' => $order_detail, 'pharmacy' => $pharmacy], 200);
        } else {
            return response()->json(['status' => 'order_not_found'], 204);
        }
    }

    public function saveOrder(Request $request)
    {
        $order = Order::find($request->order_id);

        $order->order_urgent = (!empty($request->order_urgent) && $request->order_urgent == 1) ? 1 : 0;

        if (isset($order->id)){
            $existingPharmacy = Pharmacy::find($order->order_pharmacy_id);

            $differences = NomaneHelper::getModifiedFields($request->pharmacyData, $existingPharmacy);

            $only_iban = false;

            if ( isset($differences['pharmacy_iban'] ) && isset($differences['pharmacy_bank_name']) && count($differences) == 2 ) {
                $only_iban = true;

                $updateData = [
                    'pharmacy_iban' => $request->pharmacyData['pharmacy_iban'],
                    'pharmacy_bank_name' => $request->pharmacyData['pharmacy_bank_name']
                ];

                $existingPharmacy->update($updateData);
                unset($differences['pharmacy_iban']);
                unset($differences['pharmacy_bank_name']);
            }

            if (count($differences) && $request->modified_farm != "1" && ! $only_iban) {

                if ( ! isset($differences['pharmacy_bank_code']) &&
                     ! isset($differences['pharmacy_guichet_code'] ) &&
                     ! isset($differences['pharmacy_account_number']) &&
                     ! isset($differences['pharmacy_rib']) &&
                     ! $only_iban )
                {
                    if ( isset($differences['pharmacy_iban']) ) {
                        $updateData_2 = ['pharmacy_iban' => $request->pharmacyData['pharmacy_iban']];
                    }
                    if ( isset($differences['pharmacy_bank_name']) ) {
                        $updateData_2 = ['pharmacy_bank_name' => $request->pharmacyData['pharmacy_bank_name']];
                    }

                    if ( isset($differences['pharmacy_phone']) && ( $existingPharmacy->pharmacy_phone == '' || $existingPharmacy->pharmacy_phone == null ) ) {
                        $updateData_2['pharmacy_phone'] = $differences['pharmacy_phone']['new'];
                        unset($differences['pharmacy_phone']);
                    }

                    if ( isset($differences['pharmacy_email']) && ( $existingPharmacy->pharmacy_email == '' || $existingPharmacy->pharmacy_email == null ) ) {
                        $updateData_2['pharmacy_email'] = $differences['pharmacy_email']['new'];
                        unset($differences['pharmacy_email']);
                    }

                    if (isset($updateData_2))
                        $existingPharmacy->update($updateData_2);

                    unset($differences['pharmacy_iban']);
                    unset($differences['pharmacy_bank_name']);
                }

                foreach ($differences as $field => $difference) {
                    PharmacyHistoric::create([
                        'pharmacy_historic_pharmacy_id' => $existingPharmacy->id,
                        'pharmacy_historic_filed_name' => $field,
                        'pharmacy_historic_old_value' => $difference['old'],
                        'pharmacy_historic_new_value' => $difference['new'],
                    ]);
                }

                //$existingPharmacy->update($request->pharmacyData);
                $existingPharmacy->pharmacy_new_data = 1;
                $existingPharmacy->pharmacy_type = 'Z031';
                $existingPharmacy->pharmacy_sent_to_nomane = 0;

                $block_reason = FileProcessHelper::determineBlockReasonSimple($existingPharmacy);

                    if ($existingPharmacy->pharmacy_new_pharmacy == 1) { // OK
                        $order->order_block_reason = 'Client bloqué : Client sans code SAP';
                        $order->order_status = Order::BLOCKED;
                    } else if ($existingPharmacy->pharmacy_account_status != '') { // InActive
                        $order->order_block_reason = 'Client bloqué : avec le code:Z4/01 ou Z2';
                        $order->order_status = Order::BLOCKED;
                        //$existingPharmacy->pharmacy_account_status = 'Z2';
                        if (is_array($block_reason) && in_array('ADDRESS', $block_reason)) { // OK

                        } else if (is_array($block_reason) && in_array('SIREN', $block_reason)) { // OK

                        } else if (is_array($block_reason) && in_array('ID', $block_reason)) {

                        } else if (is_array($block_reason) && in_array('BANK', $block_reason)) {

                        }
                    } else if ( $existingPharmacy->pharmacy_account_status == '') { // Active
                        $order->order_block_reason = 'Client bloqué avec le code: Modification en cours';
                        $order->order_status = Order::BLOCKED;
                        //$existingPharmacy->pharmacy_account_status = 'Z4';
                        if (is_array($block_reason) && in_array('ADDRESS', $block_reason)) { // OK

                        } else if (is_array($block_reason) && in_array('SIREN', $block_reason)) { // OK

                        } else if (is_array($block_reason) && in_array('BANK', $block_reason)) { // OK

                        }
                    }

                // ToDo: En todos los casos ?
                $existingPharmacy->pharmacy_status = Pharmacy::BLOCKED;

                $existingPharmacy->save();
            } else {
                //if ( $existingPharmacy->pharmacy_status != Pharmacy::BLOCKED  && $request->modified_farm != "1"){ // For if it´s order update
                if ( $existingPharmacy->pharmacy_status != Pharmacy::BLOCKED){ // For if it´s order update
                    $order->order_status = Order::PENDING_EXPORT;
                    Mail::to($existingPharmacy->pharmacy_email)
                        //->bcc(['52dda429.NoName.onmicrosoft.com@amer.teams.ms','NomaneDigitalFrance@noName.com'])
                        ->send(new OrderMail($order, $existingPharmacy, $order->order_reference));
                } else {
                    if ( (string) $existingPharmacy->pharmacy_new_pharmacy == "1" ) { // OK
                        $order->order_block_reason = 'Client bloqué : Client sans code SAP';
                        $order->order_status = Order::BLOCKED;
                    } else if($existingPharmacy->pharmacy_account_status == 'Z2' ){
                            $order->order_status = Order::BLOCKED;
                            $order->order_block_reason = 'Client bloqué : avec le code:Z2';
                    } else if($existingPharmacy->pharmacy_account_status == '01' ){
                        $order->order_status = Order::BLOCKED;
                        $order->order_block_reason = 'Client bloqué : avec le code:01';
                    } else if($existingPharmacy->pharmacy_account_status == 'Z4'){
                            $order->order_status = Order::BLOCKED;
                            $order->order_block_reason = 'Client bloqué : avec le code:Z4';

                            $existingPharmacy->pharmacy_sent_to_nomane = 0;
                            $existingPharmacy->save();
                    } else if ($request->modified_farm == "1" && $existingPharmacy->pharmacy_account_status != Pharmacy::ACTIVE) {
                        $existingPharmacy->pharmacy_account_status = 'Z2';
                        $order->order_status = Order::BLOCKED;
                        $order->order_block_reason = 'Client bloqué : avec le code:Z2';
                    }
                }
            }

            foreach($order->items() as $product){
                if ($product->product_allocation && $product->product_allocation > 0) {
                    if ($product->product_sell_from_date && $product->product_sell_to_date){
                        $product_sell_from_date = $product->product_sell_from_date;
                        $product_sell_to_date = $product->product_sell_to_date;
                    } else if ($product->product_sell_from_date){
                        $product_sell_from_date = $product->product_sell_from_date;
                        $product_sell_to_date = null;
                    } else if ($product->product_sell_to_date){
                        $product_sell_from_date = null;
                        $product_sell_to_date = $product->product_sell_to_date;
                    } else {
                        $product_sell_from_date = Carbon::now()->format('Y-m') . '-01';
                        $product_sell_to_date = Carbon::now()->endOfMonth()->format('Y-m-d');
                    }

                    $existingRecord = ProductUnitsSell::where('product_units_sell_product_id', $product->id)
                        ->where('product_units_sell_date_start', $product_sell_from_date)
                        ->orWhere('product_units_sell_date_end', $product_sell_to_date)
                        ->first();

                    if ($existingRecord) {
                        $existingRecord->product_units_sell_units_sell += intval($request->quantity);
                        $existingRecord->save();
                    } else {
                        ProductUnitsSell::create([
                            'product_units_sell_product_id' => $request->product_id,
                            'product_units_sell_units_sell' => intval($request->quantity),
                            'product_units_sell_date_start' => $product_sell_from_date,
                            'product_units_sell_date_end' => $product_sell_to_date,
                            'product_units_sell_time_start' => '00:00:00',
                            'product_units_sell_time_end' => '23:59:59',
                        ]);
                    }
                }
            }

            Order::where('id', $request->order_id)
            ->update(['order_amount' => $order->getTotal()]);

            $order->save();
            return response()->json(['status' => 'success', 'order' => $order, 'pharmacy' => $existingPharmacy], 200);
        } else {
            return response()->json(['status' => 'order_not_found'], 204);
        }
    }
}
