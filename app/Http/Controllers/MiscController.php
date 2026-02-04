<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use App\Helpers\NomaneHelper;
use App\Models\ProductHistoric;
use App\Models\PharmacyHistoric;
use App\Mail\ExceptionOccuredFront;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class MiscController extends Controller
{
    public function errorNotification(Request $request)
    {
        if ( ! isset( $request->errorDetails['context']['component'] ) )
            return;
        $content['component'] = $request->errorDetails['context']['component'];
        $content['action'] = $request->errorDetails['context']['action'];
        $content['message'] = $request->errorDetails['message'];
        $content['stack'] = $request->errorDetails['stack'];
        $content['timestamp'] = $request->errorDetails['timestamp'];
        $content['url'] = $request->errorDetails['context']['url'];
        $content['userAgent'] = $request->errorDetails['context']['userAgent'];
        $content['stack'] = explode("\n", $request->errorDetails['stack']);
        if (isset(Auth::user()->name)){
            $content['user'] = Auth::user()->name;
        } else {
            $content['user'] = 'No user logged';
        }
        Mail::to(env('EMAIL_FOR_APP_ERROR'))->send(new ExceptionOccuredFront($content));
    }

    public function productUpdate(Request $request)
    {
        $product = Product::find($request->id);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
        }

        // Obtener las diferencias entre los datos nuevos y existentes
        $differences = NomaneHelper::getModifiedProductFields($request->productData, $product);

        // Si hay diferencias, guardar el histórico
        if (count($differences)) {
            foreach ($differences as $field => $difference) {
                ProductHistoric::create([
                    'product_historic_product_id' => $product->id,
                    'product_historic_field_name' => $field,
                    'product_historic_old_value' => $difference['old'],
                    'product_historic_new_value' => $difference['new'],
                ]);
            }

            // Actualizar el producto con los nuevos datos
            $product->update($request->productData);

            // Si el producto tiene cambios que requieren revisión
            if (isset($differences['product_unit_price']) ||
                isset($differences['product_unit_price_pght']) ||
                isset($differences['product_status'])) {
                //$product->product_needs_review = true;
            }

            $product->save();
        }

        return response()->json([
            'status' => 'success',
            'product' => $product,
            'changes' => $differences
        ], 200);
    }

    public function productImport(Request $request)
    {
        $imported = 0;
        $notFound = 0;
        $inactiveProducts = array();

        if ( isset($request->products) ){
            foreach ($request->products as $productData) {
                $product = Product::where('product_cip13', $productData['product_cip13'])->first();

                if (!$product) {
                    $notFound++;
                    continue;
                }

                if ( $product->product_sub_status == Product::SUB_STATUS_INACTIVE ) {
                    $inactiveProducts[] = $product;
                } else {
                    unset($productData['product_sap_id']);
                    unset($productData['product_cip13']);
                    unset($productData['product_presentation']);

                    $product->fill($productData);
                    $product->save();
                    $imported++;
                }
            }

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'not_found' => $notFound,
                'inactiveProducts' => $inactiveProducts
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'imported' => $imported,
                'not_found' => $notFound,
                'inactiveProducts' => $inactiveProducts
            ], 204);
        }


    }
    public function productImportStock(Request $request)
    {
        $imported = 0;
        $notFound = 0;
        $inactiveProducts = array();

        if ( isset($request->products) ){
            foreach ($request->products as $productData) {
                $product = Product::where('product_cip13', $productData['product_cip13'])->first();

                if (!$product) {
                    $notFound++;
                    continue;
                }

                if ( $product->product_sub_status == Product::SUB_STATUS_INACTIVE ) {
                    $inactiveProducts[] = $product;
                    continue;
                } else {
                    unset($productData['product_sap_id']);
                    unset($productData['product_cip13']);
                    unset($productData['product_presentation']);
                    unset($productData['product_monthly_sales']);

                    $product->fill($productData);
                    $product->save();
                    $imported++;
                }
            }
            return response()->json([
                'success' => true,
                'imported' => $imported,
                'not_found' => $notFound,
                'inactiveProducts' => $inactiveProducts
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'imported' => $imported,
                'not_found' => $notFound,
                'inactiveProducts' => $inactiveProducts
            ], 204);
        }


    }

    public function pharmacyUpdate(Request $request)
    {
        $existingPharmacy = Pharmacy::find($request->id);

        if ($request->new_farm == "1"){
            $existingPharmacy->update($request->pharmacyData);
        } else {
            $differences = NomaneHelper::getModifiedFields($request->pharmacyData, $existingPharmacy);

            $only_iban = false;

            if ( isset($differences['pharmacy_iban'] ) && isset($differences['pharmacy_bank_name']) && count($differences) == 2 ) {
                $only_iban = true;

                $updateData = [
                    'pharmacy_iban' => $request->pharmacyData['pharmacy_iban'],
                    'pharmacy_bank_name' => $request->pharmacyData['pharmacy_bank_name']
                ];

                $existingPharmacy->update($updateData);
            }

            if ( ! isset($differences['pharmacy_bank_code']) &&
                     ! isset($differences['pharmacy_guichet_code'] ) &&
                     ! isset($differences['pharmacy_account_number']) &&
                     ! isset($differences['pharmacy_rib']) &&
                     ! $only_iban )
                {
                    if ( isset($differences['pharmacy_iban']) ) {
                        $updateData_2['pharmacy_iban'] = $request->pharmacyData['pharmacy_iban'];
                    }
                    if ( isset($differences['pharmacy_bank_name']) ) {
                        $updateData_2['pharmacy_bank_name'] = $request->pharmacyData['pharmacy_bank_name'];
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

            if (count($differences)  && ! $only_iban ) {
                foreach ($differences as $field => $difference) {
                    //Log::info("Field: $field, Old: {$difference['old']}, New: {$difference['new']}");
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

                // ToDo: En todos los casos ?
                $existingPharmacy->pharmacy_status = Pharmacy::BLOCKED;

                $existingPharmacy->save();
            }
        }
        return response()->json(['status' => 'success', 'pharmacy' => $existingPharmacy], 200);
    }

    public function ldapSync(Request $request)
    {
        $return = NomaneHelper::ldapSync();
        return response()->json(['ldapSync' => $return], ( count($return) ) ? 200 : 204);
    }
}




