<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Order;
use App\Mail\InfoMail;
use App\Mail\OrderMail;
use App\Models\Product;
use App\Models\Category;
use App\Models\Pharmacy;
use App\Models\FileStatus;
use App\Models\OrderCagedim;
use App\Mail\TradePolicyEmail;
use App\Models\PharmacyHistoric;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BlockedOrdersReportMail;
use App\Models\ProductThresholdPrice;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;

class ExctractHelper {

    public static function extractAndProccesPharmaciesInTxt($contents) {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        ini_set('memory_limit', '1000M');
        $contents = mb_convert_encoding($contents, 'UTF-8', mb_detect_encoding($contents, 'UTF-8, ISO-8859-1, ISO-8859-15', true));
        $contents = str_replace("\xEF\xBB\xBF", '', $contents);
        $contents = preg_replace('/\r\n|\r|\n/', "\n", $contents);
        $contents = preg_replace('/^\s*[\r\n]/m', '', $contents);
        $lines = preg_split('/\n/', $contents, -1, PREG_SPLIT_NO_EMPTY);
        $lines = array_filter($lines, 'trim');
        $pharmacy_array = [];
        $pharmacy = null;
        $lastPharmacy = null;
        $countUpdated = 0;
        $countCreated = 0;
        $lastLineTres = '';
        $oldStatus = '';
        $oldCode = '';

        foreach ($lines as $lineKey => $data) {
                $line = explode('~', $data);
                $line = array_map(function($value) {
                    return trim(mb_convert_encoding($value, 'UTF-8', 'auto'));
                }, $line);
                if ($line[0] == '1') {

                    $createdNew = false;

                    $pharmacy = Pharmacy::where('pharmacy_sap_id', '=', $line[1])->get();
                    if (isset($pharmacy[0]->id)) {
                        $pharmacy = $pharmacy[0];
                        $pharmacy->pharmacy_new_pharmacy = 0;
                        $countUpdated++;
                    } else {
                        $pharmacy = new Pharmacy();
                        $countCreated++;
                        $createdNew = true;
                        $pharmacy->pharmacy_new_pharmacy = 1;
                    }

                    $lastLineTres = $line[3];

                    $oldCode = $pharmacy->pharmacy_account_status;
                    $oldStatus = $pharmacy->pharmacy_status;

                    $pharmacy = self::fillEmptyFieldsFromHistoric($pharmacy);

                    $pharmacy->pharmacy_sap_id = $line[1];
                    $pharmacy->pharmacy_type = $line[2];
                    $pharmacy->pharmacy_account_status = $line[3];
                    if (
                            $pharmacy->pharmacy_account_status == '01' ||
                            $pharmacy->pharmacy_account_status == 'Z1' ||
                            $pharmacy->pharmacy_account_status == 'Z2' ||
                            $pharmacy->pharmacy_account_status == 'Z3' ||
                            $pharmacy->pharmacy_account_status == 'Z4' ||
                            $pharmacy->pharmacy_account_status == 'Z5'
                        ) {
                        $pharmacy->pharmacy_status = Pharmacy::BLOCKED;
                        $pharmacy->pharmacy_sent_to_nomane = 0;
                    } else {
                        $pharmacy->pharmacy_status = Pharmacy::ACTIVE;
                        $pharmacy->pharmacy_sent_to_nomane = 1;
                    }

                    $pharmacy->pharmacy_holder_name = $line[5];
                    $pharmacy->pharmacy_name = $line[4];

                    $pharmacy->pharmacy_name2 = $line[6];
                    $pharmacy->pharmacy_name3 = $line[7];
                    //$pharmacy->pharmacy_name4 = $line[7];

                    $pharmacy->pharmacy_address_street = $line[8];

                    $pharmacy->pharmacy_address_address1 = $line[9];
                    $pharmacy->pharmacy_address_address2 = $line[10];
                    $pharmacy->pharmacy_address_address3 = $line[11];

                    //$pharmacy->pharmacy_address_address1 = $line[10];
                    //$pharmacy->pharmacy_address_address2 = $line[11];

                    $pharmacy->pharmacy_city = $line[12];
                    $pharmacy->pharmacy_district = $line[13];
                    $pharmacy->pharmacy_region = $line[14];
                    $pharmacy->pharmacy_country = $line[15];
                    $pharmacy->pharmacy_zipcode = $line[16];
                    $pharmacy->pharmacy_po_box = $line[17];
                    $pharmacy->pharmacy_po_box_city = $line[18];
                    $pharmacy->pharmacy_po_box_region = $line[19];
                    $pharmacy->pharmacy_po_box_country = $line[20];
                    $pharmacy->pharmacy_po_box_zipcode = $line[21];
                    /*
                    if ($pharmacy->pharmacy_new_pharmacy == 1){
                        $pharmacy->pharmacy_phone = $line[22];
                        $pharmacy->pharmacy_fax = $line[23];
                    }
                    */
                    $pharmacy->pharmacy_new_data = 0;
                    $pharmacy->pharmacy_siren = $line[44];
                    $pharmacy->pharmacy_siret = $line[45];
                    if ($pharmacy->pharmacy_new_pharmacy == 1)
                        $pharmacy->pharmacy_refusal_lcr = 0;

                    $pharmacy->save();

                    $lastPharmacy = $pharmacy->id;

                } else if ($line[0] == '3') {

                    // exemple :3~3000214702~3000206250~0000070649B~61~
                    // code bank = 30002 / guichet 14702 / account = 0000070664B / key = 61
                    if ($line[5] != 'FR20'){
                        $pharmacy = Pharmacy::find($lastPharmacy);

                        //$pharmacy->pharmacy_bank_name = $line[1];
                        //$pharmacy->pharmacy_iban = $line[2];

                        if (isset($line[2])) {
                            $bankCode = substr($line[2], 0, 5);
                            $guichetCode = substr($line[2], 5, 5);

                            $pharmacy->pharmacy_bank_code = $bankCode;
                            $pharmacy->pharmacy_guichet_code = $guichetCode;
                        }
                        if (isset($line[3])) $pharmacy->pharmacy_account_number = $line[3];
                        if (isset($line[4])) $pharmacy->pharmacy_rib = $line[4];

                        $pharmacy->save();
                    }
                } else if ($line[0] == '4') {
                    // This is why data comes in diferent lines
                    $pharmacy = Pharmacy::find($lastPharmacy);
                    $pharmacy->pharmacy_cip13 = $line[11];

                    $pharmacy->save();

                    $pharmacy = Pharmacy::find($lastPharmacy);

                    if ($pharmacy->pharmacy_account_status == 'Z2' ||
                        $pharmacy->pharmacy_account_status == 'Z4' ||
                        $pharmacy->pharmacy_account_status == '01'){

                        $lastOrders = Order::where('order_pharmacy_id', $pharmacy->id)
                            ->whereIn('order_status', [Order::PENDING_EXPORT, Order::CONFIRMED, ORDER::ONHOLD, Order::BLOCKED])
                            ->latest()
                            ->get();


                        foreach ($lastOrders as $order) {
                            $order->order_status = Order::BLOCKED;

                            $order->order_block_reason = 'Client bloqué : avec le code: Z4/01 or Z2';
                            $order->save();
                        }

                        $lastOrders = OrderCagedim::where('sold_to', $pharmacy->pharmacy_cip13)
                            //->where('order_block_code', '=', '')
                            //->where('order_block_code', '!=', '')
                            ->where('order_block_code', '!=', 'Cancelled')
                            ->latest()
                            ->get()
                            ;

                        foreach ($lastOrders as $order) {
                            $tempOrder = OrderCagedim::find($order->id);

                            $tempOrder->order_block_code = 'Client bloqué : avec le code: Z4/01 or Z2';
                            $tempOrder->save();
                        }

                    }

                    if ($pharmacy->pharmacy_new_pharmacy == 1) {
                        $last_farm_data = Pharmacy::
                                            select( 'pharmacy_email')
                                            ->where('pharmacy_cip13', '=', $line[11])
                                            ->where('pharmacy_sap_id', '!=', $line[1])
                                            ->where('pharmacy_email', '!=', '')
                                            ->latest()
                                            ->get();

                        if (isset($last_farm_data[0]->pharmacy_email)) {
                            $pharmacy->update($last_farm_data[0]->toArray());
                        }

                        $last_farm_data = Pharmacy::
                                            select( 'pharmacy_phone')
                                            ->where('pharmacy_cip13', '=', $line[11])
                                            ->where('pharmacy_sap_id', '!=', $line[1])
                                            ->where('pharmacy_phone', '!=', '')
                                            ->latest()
                                            ->get();

                        if (isset($last_farm_data[0]->pharmacy_phone)) {
                            $pharmacy->update($last_farm_data[0]->toArray());
                        }
                    }

                    $last_farm_data = Pharmacy::
                                        select( 'pharmacy_bank_name',
                                                'pharmacy_iban',
                                                'pharmacy_bank_code',
                                                'pharmacy_account_number',
                                                'pharmacy_guichet_code',
                                                'pharmacy_rib')
                                        ->where('pharmacy_cip13', '=', $line[11])
                                        ->where('pharmacy_sap_id', '!=', $line[1])
                                        ->latest()
                                        ->get();

                    if (isset($last_farm_data[0]->pharmacy_iban)) {
                        $pharmacy->update($last_farm_data[0]->toArray());
                    }

                    $pharmacies_old = Pharmacy::where('pharmacy_cip13', '=', $line[11])
                        ->where('pharmacy_sap_id', '!=', $line[1])
                        ->get();

                    foreach ($pharmacies_old as $pharmacy_temp) {
                        $pharmacy_temp->pharmacy_sent_to_nomane = 1;
                        $pharmacy_temp->pharmacy_status = Pharmacy::INACTIVE_NEW_SAP;
                        $pharmacy_temp->save();
                    }

                    $last_farm_data = Pharmacy::
                                        select( 'id')
                                        ->where('pharmacy_cip13', '=', $line[11])
                                        ->where('pharmacy_sap_id', '!=', $line[1])
                                        ->latest();

                    if (isset($last_farm_data->id)) {
                        $orders_to_move_phrmacy = Order::where('order_pharmacy_id', $last_farm_data->id)
                        ->where('order_sent_to_nomane', '=', 0)
                        ->whereIn('order_status', [Order::ONHOLD, Order::BLOCKED])
                        ->get();

                        foreach ($orders_to_move_phrmacy as $order) {
                            $order->order_pharmacy_id = $pharmacy->id;
                            if ($pharmacy->pharmacy_status == 'Active') {
                                $order->order_status = Order::PENDING_EXPORT;
                                $order->order_sent_to_nomane = 0;
                            }
                            /* else {
                                $order->order_status = Order::BLOCKED;
                                $order->order_sent_to_nomane = 0;
                            }
                                */
                            $order->save();
                        }

                        $orders_to_move_phrmacy = OrderCagedim::where('sold_to', $last_farm_data->pharmacy_cip13)
                        ->where('order_sent_to_nomane', '=', 0)
                        ->where('order_block_code', '!= , Cancelled')
                        //->where('order_block_code', '!=', '')
                        ->get();

                        foreach ($orders_to_move_phrmacy as $order) {
                            $order->sold_to = $pharmacy->pharmacy_cip13;
                            $order->ship_to = $pharmacy->pharmacy_cip13;
                            if ($pharmacy->pharmacy_status == 'Active') {
                                $order->order_block_code = '';
                                $order->order_sent_to_nomane = 0;
                            } else {
                                $order->order_block_code = 'Client bloqué : avec le code:Z4/01';
                                $order->order_sent_to_nomane = 0;
                            }

                            $order->save();
                        }
                    }

                    if  (
                            (
                                ( $pharmacy->pharmacy_new_pharmacy == 1 )
                                ||
                                ( $oldCode != '' && $oldStatus == Pharmacy::BLOCKED )
                            )
                            && $lastLineTres == ''
                        )
                    {

                        $lastOrders = Order::where('order_pharmacy_id', $pharmacy->id)
                        ->whereIn('order_status', [Order::BLOCKED])
                        ->get();

                        foreach ($lastOrders as $orderA) {
                            $order = Order::find($orderA->id);

                            $order->order_status = Order::PENDING_EXPORT;
                            $order->order_block_reason = '';
                            $order->order_sent_to_nomane = 0;
                            $order->save();

                            if (isset($order->pharmacy->pharmacy_email) && $order->pharmacy->pharmacy_email != '') {
                                Mail::mailer('smtp')->to($order->pharmacy->pharmacy_email)
                                    //->bcc(['52dda429.NoName.onmicrosoft.com@amer.teams.ms','NomaneDigitalFrance@noName.com'])
                                    ->send(new OrderMail($order, $order->pharmacy, $order->order_reference));
                            }

                        }

                        $lastOrders = OrderCagedim::where('sold_to', $pharmacy->pharmacy_cip13)
                        ->where('order_block_code', '!= , Cancelled')
                        ->where('order_block_code', '!=', '')
                        //->latest()
                        ->get();

                        foreach ($lastOrders as $orderA) {
                            $order = OrderCagedim::find($orderA->id);

                            $order->order_block_code = '';
                            $order->order_sent_to_nomane = 0;
                            $order->save();

                            if (isset($pharmacy->pharmacy_email) && $pharmacy->pharmacy_email != '') {
                                Mail::mailer('smtp')->to($pharmacy->pharmacy_email)
                                    //->bcc(['52dda429.NoName.onmicrosoft.com@amer.teams.ms','NomaneDigitalFrance@noName.com'])
                                    ->send(new OrderMail($order, $pharmacy, $order->customer_po));
                            }

                        }
                    }

                    $pharmacy_array[] = $pharmacy;
                }
        }

        $text = $countCreated . ' notices de pharmacie et '. $countUpdated .' pharmacies mises à jour';
        Mail::to(env('EMAIL_FOR_INFO'))->sendNow(new InfoMail($text));

        return $pharmacy_array;
    }

    public static function extractAndProccesCustomerSanitationInExcel ($sheet, $spreadsheet){

        $data = [];
        foreach ($sheet->getRowIterator() as $rowIndex => $row) {
            if ($rowIndex > 4) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);
                $rowData = [];
                $code_blockage = '';
                foreach ($cellIterator as $key => $cell) {
                    if ($key != 'I') {
                        $rowData[$key] = $cell->getValue();
                    } else {
                        $cell = $sheet->getCell('I' . ($rowIndex + 1));
                        $calculation = Calculation::getInstance($spreadsheet);

                        // Error: Unable to access External Workbook
                        $rowData[$key] = $calculation->calculateCellValue($cell);
                    }
                }
                $data[] = $rowData;
            }
        }



        $usefulData = [];
        foreach ($data as $key => $line) {
            if ( !isset($line['A']) ) {
                $line['A'] = '';
            }
            if ($line['A'] instanceof RichText) {
                $usefulData[$key]['CIP'] = $line["A"]->getPlainText();
            } else {
                $usefulData[$key]['CIP'] = $line['A'];
            }
            if ($line['B'] instanceof RichText) {
                $usefulData[$key]['SAP'] = $line["B"]->getPlainText();
            } else {
                $usefulData[$key]['SAP'] = $line["B"];
            }
            if ( !isset($line['I']) ) {
                $line['I'] = '';
            }
            if ($line['I'] instanceof RichText) {
                $usefulData[$key]['CODE_BLOCKAGE'] = $line["I"]->getPlainText();
            } else {
                //$usefulData[$key]['CODE_BLOCKAGE'] = $code_blockage;
                $usefulData[$key]['CODE_BLOCKAGE'] = $line["I"];
            }
        }

        foreach ($usefulData as $key => $line) {

            if ( $line['CIP'] != '' && $line['SAP'] != '' ) {
                $pharmacy = Pharmacy::where('pharmacy_cip13', (string) $line['CIP'])
                                        ->where('pharmacy_sap_id', (string) $line['SAP'])
                                        ->first();
            } else if ($line['SAP'] != '') {
                $pharmacy = Pharmacy::where('pharmacy_sap_id', (string) $line['SAP'])->first();
            }
            if ( $pharmacy ) {
                $pharmacy->pharmacy_account_status = $line['CODE_BLOCKAGE'];
                if ($line['CODE_BLOCKAGE'] != ''){
                    $pharmacy->pharmacy_status = Pharmacy::BLOCKED;
                } else {
                    $pharmacy->pharmacy_status = Pharmacy::ACTIVE;
                }
                $pharmacy->save();
            }
        }
        return $usefulData;
    }

    public static function extractAndProccesTradePolicyInExcel ($offre, $sheet, $spreadsheet){

        // JUJU

        $data = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            $rowData = [];
            foreach ($cellIterator as $key => $cell) {
                $rowData[$key] = $cell->getValue();
            }
            $data[] = $rowData;
        }
        $usefulData = [];
        $productName = '';
        $productsUpdated =[];
        $arrayExcelProducts =[];
        foreach ($data as $key => $line) {
            //if ( isset($line["U"]) && ( $line["U"] == "In" || $line["U"] == "Change" || $line["U"] == "Out")){
                if ( ( isset($line["B"]) && $line["B"] != "" && $line["B"] != 'SAP ID')){
                    $usefulData[$key] = $line;
                    if ($line["H"] instanceof RichText) {
                        $productName = $line["H"]->getPlainText();
                    } else {
                        $productName = $line["H"];
                    }
                    $usefulData[$key]['productName'] = $productName;
                }
            //}
        }
        foreach ($usefulData as $key => $line) {
            $cellB = $sheet->getCell('B' . ($key + 1));
            $color = self::getExcelCellColor($cellB);
/*
            $style = $cellB->getStyle();
            $font = $style->getFont();
            $isStrikethrough = $font->getStrikethrough();

            if($isStrikethrough) {
                $product_status = Product::STATUS_INDISPONIBLE;
            } else {
                $product_status = Product::STATUS_DISPONIBLE;
            }
*/
            if ( isset($line["U"]) && $line["U"] == "Out" ) {
                $product_sub_status = Product::SUB_STATUS_INACTIVE;
            } else {
                $product_sub_status = Product::SUB_STATUS_ACTIVE;
            }

            /*
            if($color == 'FF0000' ) {
                $product_status = Product::STATUS_INDISPONIBLE;
            } else {
                $product_status = Product::STATUS_DISPONIBLE;
            }
            */

            if (!isset($colorsArray[$color])) {
                $colorsArray[$color] = 1;
            } else {
                $colorsArray[$color]++;
            }

            // FF0000 Rojo - 0095FF Azul - C9C9C9 Gris - A9D18E Verde claro - C5E0B4 - Amarillo ? - 92D050 - Color carne
            //if ( $color == '0095FF' || $color == 'A9D18E' ) {

            if ( $line['A'] == 'Génériques' || $line['A'] == 'Génériques TFR' || $line['A'] == 'Génériques NR' || $line['A'] == 'Génériques Princeps TFR') {
                $premium = 1;
            } else {
                $premium = 0;
            }

            if ( $line['A'] == 'Biomédicament') {
                $biomed = 1;
            } else {
                $biomed = 0;
            }

            // PGHT price calculation
            $cell = $sheet->getCell('K' . ($key + 1));
            $calculation = Calculation::getInstance($spreadsheet);
            $product_unit_price_pght = $calculation->calculateCellValue($cell);

            if ( $line['A'] != 'NR' ) {
                $category = Category::where('category_name', $line['A'])->first();
                if ( ! $category ) {
                    $category = Category::create([
                        'category_name' => $line['A'],
                        'category_active' => 1,
                    ]);
                }
                $category_id = $category->id;
            } else {
                $category_id = null;
            }

            $product = Product::where('product_sap_id', $line['B'])->first();
            if ( ! $product ) {
                $product = Product::create([
                    'product_sap_id' => $line['B'],
                    'product_cip13' => $line['C'],
                    'product_category_id' => $category_id,
                    'product_name' => $line['productName'],
                    'product_presentation' => $line['I'],

                    'product_unit_price' => $line['J'],
                    'product_unit_price_pght' => $product_unit_price_pght,

                    'product_box_quantity' => (is_int($line['F'])) ? $line['F'] : null,
                    'product_bundle_quantity' => (is_int($line['G'])) ? $line['G'] : null,
                    'product_min_order' => $line['D'],
                    'product_max_order' => $line['E'],
                    'product_active' => 1,
                    'product_premium_offer' => $premium,
                    'product_biomed_offer' => $biomed,
                    'product_status' => Product::STATUS_DISPONIBLE,
                    'product_sub_status' => $product_sub_status,
                ]);
            } else {
                $product->product_name = $line['productName'];
                $product->product_category_id = $category_id;
                $product->product_presentation = $line['I'];
                $product->product_unit_price = $line['J'];
                $product->product_unit_price_pght = $product_unit_price_pght;
                $product->product_box_quantity = (is_int($line['F'])) ? $line['F'] : null;
                $product->product_bundle_quantity = (is_int($line['G'])) ? $line['G'] : null;
                $product->product_min_order = $line['D'];
                $product->product_max_order = $line['E'];
                $product->product_active = 1;
                $product->product_premium_offer = $premium;
                $product->product_biomed_offer = $biomed;
                $product->product_status = $product->product_status;
                $product->product_sub_status = $product_sub_status;
                $product->save();
            }

            $res = NomaneHelper::processProductDiscountThresholdFromTradePolicy($offre, $line, $sheet, $spreadsheet, $key, 'N', 'L', 'M', $product->id, 1);
            if( isset($res['prods']['update']['premium'][$product->id]) ) {
                foreach($res['prods']['update']['premium'] as $key_2 => $product_juju){
                    $productsUpdated['prods']['update']['premium'][$key_2] = $key_2;
                }
            }
            if( isset($res['prods']['update']['standard'][$product->id]) ){
                foreach($res['prods']['update']['standard'] as $key_2 => $product_juju){
                    $productsUpdated['prods']['update']['standard'][$key_2] = $key_2;
                }
            }
            $res = NomaneHelper::processProductDiscountThresholdFromTradePolicy($offre, $line, $sheet, $spreadsheet, $key, 'Q', 'O', 'P', $product->id, 2);
            if( isset($res['prods']['update']['premium'][$product->id]) ) {
                foreach($res['prods']['update']['premium'] as $key_2 => $product_juju){
                    $productsUpdated['prods']['update']['premium'][$key_2] = $key_2;
                }
            }

            if( isset($res['prods']['update']['standard'][$product->id]) ){
                foreach($res['prods']['update']['standard'] as $key_2 => $product_juju){
                    $productsUpdated['prods']['update']['standard'][$key_2] = $key_2;
                }
            }
            $res = NomaneHelper::processProductDiscountThresholdFromTradePolicy($offre, $line, $sheet, $spreadsheet, $key, 'T', 'R', 'S', $product->id, 3);
            if( isset($res['prods']['update']['premium'][$product->id]) ) {
                foreach($res['prods']['update']['premium'] as $key_2 => $product_juju){
                    $productsUpdated['prods']['update']['premium'][$key_2] = $key_2;
                }
            }
            if( isset($res['prods']['update']['standard'][$product->id]) ){
                foreach($res['prods']['update']['standard'] as $key_2 => $product_juju){
                    $productsUpdated['prods']['update']['standard'][$key_2] = $key_2;
                }
            }
        }

        if (isset($productsUpdated['prods']['update']['premium'])) {

            foreach ($productsUpdated['prods']['update']['premium'] as $key => $product) {
                $productThreshold = ProductThresholdPrice::where('product_threshold_price_product_id', '=', $key)->get();
                foreach ($productThreshold as $threshold) {
                    // (CIP code, SAP code, price level 1, discount 1, price  level 2, discount 2, price  level 3, discount 3)
                    $product = Product::find($key);
                    if ($threshold->product_threshold_price_level == 1) {
                        $arrayExcelProducts['prods']['update']['premium'][$key]['CIP'] = $product->product_cip13;
                        $arrayExcelProducts['prods']['update']['premium'][$key]['SAP'] = $product->product_sap_id;
                        $arrayExcelProducts['prods']['update']['premium'][$key]['Produit'] = $product->product_presentation;
                        $arrayExcelProducts['prods']['update']['premium'][$key]['Price-pght'] = $product->product_unit_price_pght;
                        $arrayExcelProducts['prods']['update']['premium'][$key]['Updated_at'] = Carbon::parse($threshold->updated_at)->format('d-m-Y H:i:s');
                        $arrayExcelProducts['prods']['update']['premium'][$key]['PRICE1'] = $threshold->product_threshold_price_price_premium;
                        $arrayExcelProducts['prods']['update']['premium'][$key]['DTO1'] = $threshold->product_threshold_price_discount_premium;
                        $arrayExcelProducts['prods']['update']['premium'][$key]['Offre'] = 'Premium';
                        $arrayExcelProducts['prods']['update']['premium'][$key]['PRICE2'] = '';
                        $arrayExcelProducts['prods']['update']['premium'][$key]['DTO2'] = '';
                        $arrayExcelProducts['prods']['update']['premium'][$key]['PRICE3'] = '';
                        $arrayExcelProducts['prods']['update']['premium'][$key]['DTO3'] = '';
                        $arrayExcelProducts['prods']['update']['premium'][$key]['PALIER1'] = $threshold->product_threshold_price_threshold_from_premium;
                        $arrayExcelProducts['prods']['update']['premium'][$key]['PALIER2'] = '';
                        $arrayExcelProducts['prods']['update']['premium'][$key]['PALIER3'] = '';
                    } else if ($threshold->product_threshold_price_level == 2) {
                        $arrayExcelProducts['prods']['update']['premium'][$key]['PRICE2'] = $threshold->product_threshold_price_price_premium;
                        $arrayExcelProducts['prods']['update']['premium'][$key]['DTO2'] = $threshold->product_threshold_price_discount_premium;
                        $arrayExcelProducts['prods']['update']['premium'][$key]['PALIER2'] = $threshold->product_threshold_price_threshold_from_premium;
                    } else if ($threshold->product_threshold_price_level == 3) {
                        $arrayExcelProducts['prods']['update']['premium'][$key]['PRICE3'] = $threshold->product_threshold_price_price_premium;
                        $arrayExcelProducts['prods']['update']['premium'][$key]['DTO3'] = $threshold->product_threshold_price_discount_premium;
                        $arrayExcelProducts['prods']['update']['premium'][$key]['PALIER3'] = $threshold->product_threshold_price_threshold_from_premium;
                    }
                }
            }
        }
        if (isset($productsUpdated['prods']['update']['standard'])) {

            foreach ($productsUpdated['prods']['update']['standard'] as $key => $product) {
                $productThreshold = ProductThresholdPrice::where('product_threshold_price_product_id', '=', $key)->get();
                foreach ($productThreshold as $threshold) {
                    // (CIP code, SAP code, price level 1, discount 1, price  level 2, discount 2, price  level 3, discount 3)
                    $product = Product::find($key);
                    if ($threshold->product_threshold_price_level == 1) {
                        $arrayExcelProducts['prods']['update']['standard'][$key]['CIP'] = $product->product_cip13;
                        $arrayExcelProducts['prods']['update']['standard'][$key]['SAP'] = $product->product_sap_id;
                        $arrayExcelProducts['prods']['update']['standard'][$key]['Produit'] = $product->product_presentation;
                        $arrayExcelProducts['prods']['update']['standard'][$key]['Price-pght'] = $product->product_unit_price_pght;
                        $arrayExcelProducts['prods']['update']['standard'][$key]['Updated_at'] = Carbon::parse($threshold->updated_at)->format('d-m-Y H:i:s');
                        $arrayExcelProducts['prods']['update']['standard'][$key]['PRICE1'] = $threshold->product_threshold_price_price;
                        $arrayExcelProducts['prods']['update']['standard'][$key]['DTO1'] = $threshold->product_threshold_price_discount;
                        $arrayExcelProducts['prods']['update']['standard'][$key]['Offre'] = 'Standard';
                        $arrayExcelProducts['prods']['update']['standard'][$key]['PRICE2'] = '';
                        $arrayExcelProducts['prods']['update']['standard'][$key]['DTO2'] = '';
                        $arrayExcelProducts['prods']['update']['standard'][$key]['PRICE3'] = '';
                        $arrayExcelProducts['prods']['update']['standard'][$key]['DTO3'] = '';
                        $arrayExcelProducts['prods']['update']['standard'][$key]['PALIER1'] = $threshold->product_threshold_price_threshold_from;
                        $arrayExcelProducts['prods']['update']['standard'][$key]['PALIER2'] = '';
                        $arrayExcelProducts['prods']['update']['standard'][$key]['PALIER3'] = '';
                    } else if ($threshold->product_threshold_price_level == 2) {
                        $arrayExcelProducts['prods']['update']['standard'][$key]['PRICE2'] = $threshold->product_threshold_price_price;
                        $arrayExcelProducts['prods']['update']['standard'][$key]['DTO2'] = $threshold->product_threshold_price_discount;
                        $arrayExcelProducts['prods']['update']['standard'][$key]['PALIER2'] = $threshold->product_threshold_price_threshold_from;
                    } else if ($threshold->product_threshold_price_level == 3) {
                        $arrayExcelProducts['prods']['update']['standard'][$key]['PRICE3'] = $threshold->product_threshold_price_price;
                        $arrayExcelProducts['prods']['update']['standard'][$key]['DTO3'] = $threshold->product_threshold_price_discount;
                        $arrayExcelProducts['prods']['update']['standard'][$key]['PALIER3'] = $threshold->product_threshold_price_threshold_from;
                    }
                }
            }
        }

        return $arrayExcelProducts;
    }

    public static function getExcelCellColor($cell) {
        // Obtener el estilo de relleno
        $fillType = $cell->getStyle()->getFill()->getFillType();

        if ($fillType == \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID) {
            // Obtener el color en formato ARGB
            $color = $cell->getStyle()->getFill()->getStartColor()->getARGB();

            // Si queremos solo el valor RGB (sin el canal alfa)
            $rgb = substr($color, 2); // Elimina los primeros 2 caracteres (FF del canal alfa)

            return $rgb;
        }

        return null;
    }

    public static function extractAndProccesProductControlInCsv ($contents){
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $usefulData = [];
        $line = [];
        $lines = explode("\n", $contents);
        foreach ($lines as $lineKey => $line_csv) {
            $line[] = str_getcsv($line_csv, ';');
            if ( isset($line[$lineKey][0]) && $line[$lineKey][0] != 'PRODUIT_CIP13' ) {
                $product = Product::where('product_cip13', $line[$lineKey][0])->first();
                if ( isset($product[0]->id) ) {
                    $product->product_min_order = ($line[$lineKey][5] != '') ? $line[$lineKey][5] : '1';
                    $product->product_max_order = $line[$lineKey][6];
                    $product->save();
                    $usefulData['found'][] = $line[$lineKey][0];
                } else {
                    $usefulData['not_found'][] = $line[$lineKey][0];
                }
            }
        }
        return $usefulData;
    }

    public static function extractAndProccesPriceControlInCsv ($contents){
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $usefulData = [];
        $line = [];
        $cnt = 0;
        $lines = explode("\n", $contents);
        foreach ($lines as $lineKey => $line_csv) {
            $line[] = str_getcsv($line_csv, ';');
            $cnt++;
            if ( isset($line[$lineKey][1]) && $line[$lineKey][1] != "" && $line[$lineKey][0] != 'TARIF' ) {
                $product = Product::where('product_cip13', $line[$lineKey][1])->first();
                if ( isset($product[0]->id) ) {
                    NomaneHelper::processProductDiscountThresholdFromPriceControl($line[$lineKey], $product);
                    $usefulData['found'][] = $line[$lineKey][1];
                } else {
                    $usefulData['not_found'][] = $line[$lineKey][1];
                }
            }
        }
        return $usefulData;
    }

    public static function extractAndProccesBlockedOrdersInExcel ($sheet, $spreadsheet){
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $data = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            $rowData = [];
            foreach ($cellIterator as $key => $cell) {
                $rowData[$key] = $cell->getValue();
            }
            $data[] = $rowData;
        }

        $usefulData = [];
        foreach ($data as $key => $line) {
            if ( $key > 1 && isset($line["A"]) && $line["A"] != "" ) {
                $order = Order::where('order_reference', $line["A"])->first();
                if ( $order ) {
                    $order->order_blocked = 1;
                    $order->order_status = Order::BLOCKED;
                    $order->order_block_reason = $line["E"];
                    $order->save();
                    $usefulData['found'][] = $line["A"];
                } else {
                    $usefulData['not_found'][] = $line["A"];
                }
            }
        }
        return $usefulData;
    }

    /**
     * Obtiene el valor más reciente de un campo específico de una farmacia desde el histórico
     *
     * @param int $pharmacyId ID de la farmacia
     * @param string $fieldName Nombre del campo
     * @return string|null Valor más reciente del campo o null si no existe
     */
    public static function getLatestFieldValueFromHistoric($pharmacyId, $fieldName)
    {
        $latestValue = PharmacyHistoric::where('pharmacy_historic_pharmacy_id', $pharmacyId)
            ->where('pharmacy_historic_filed_name', $fieldName)
            ->orderBy('created_at', 'desc')
            ->first();

        return $latestValue ? $latestValue->pharmacy_historic_new_value : null;
    }

    /**
     * Obtiene los valores más recientes de todos los campos de una farmacia desde el histórico
     *
     * @param int $pharmacyId ID de la farmacia
     * @return array Arreglo asociativo con los valores más recientes de los campos
     */
    public static function getAllLatestFieldsFromHistoric($pharmacyId, $fieldsToCheck)
    {
        $latestFields = [];

        foreach ($fieldsToCheck as $field) {
            $latestValue = PharmacyHistoric::where('pharmacy_historic_pharmacy_id', $pharmacyId)
                ->where('pharmacy_historic_filed_name', $field)
                //->orderBy('created_at', 'desc')
                ->latest()
                ->get();

            if (isset($latestValue[0]) && !empty($latestValue[0]->pharmacy_historic_new_value)) {
                $latestFields[$field] = $latestValue[0]->pharmacy_historic_new_value;
            }
        }

        foreach ($latestFields as $key => $record) {
            if (!isset($latestFields[$key])) {
                $latestFields[$key] = $record;
            }
        }

        return $latestFields;
    }

    public static function getPharmacyNameFromHistoric($pharmacyId)
    {
        // Buscar el último cambio de nombre en el histórico
        $lastNameChange = PharmacyHistoric::where('pharmacy_historic_pharmacy_id', $pharmacyId)
            ->where('pharmacy_historic_filed_name', 'pharmacy_name')
            ->latest()
            ->get();

        if (count($lastNameChange) > 0)
            return $lastNameChange[0] ? $lastNameChange[0]->pharmacy_historic_new_value : 'Sin nombre';
        else
            return 'Sin nombre';
    }

    /**
     * Completa los campos vacíos de una farmacia con los valores más recientes del histórico
     *
     * @param Pharmacy $pharmacy Objeto de farmacia a completar
     * @return Pharmacy Farmacia con campos completados
     */
    public static function fillEmptyFieldsFromHistoric(Pharmacy $pharmacy)
    {
        $fieldsToCheck = [
            'pharmacy_email',
            'pharmacy_phone',
            'pharmacy_holder_name',
            'pharmacy_bank_name',
            'pharmacy_iban',
            'pharmacy_bank_code',
            'pharmacy_account_number',
            'pharmacy_guichet_code',
            'pharmacy_rib'
        ];

        $historicFields = self::getAllLatestFieldsFromHistoric($pharmacy->id, $fieldsToCheck);

        // Completar campos vacíos con valores del histórico
        foreach ($fieldsToCheck as $field) {
            // Si el campo está vacío y existe un valor en el histórico
            if (empty($pharmacy->$field) && ( isset($historicFields[$field]) && !empty($historicFields[$field]) )) {
                $pharmacy->$field = $historicFields[$field];
            }
        }

        return $pharmacy;
    }
}


