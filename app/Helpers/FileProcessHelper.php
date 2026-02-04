<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Order;
use App\Mail\InfoMail;
use App\Models\Product;
use App\Models\Category;
use App\Models\Pharmacy;
use App\Models\FileStatus;
use App\Helpers\OrderSaver;
use App\Models\OrderCagedim;
use App\Helpers\NomaneHelper;
use App\Mail\TradePolicyEmail;
use App\Helpers\ExctractHelper;
use App\Models\ProductHistoric;
use App\Models\PharmacyHistoric;
use App\Models\ProductUnitsSell;
use App\Mail\MissingProductsMail;
use Illuminate\Support\Facades\DB;
use App\Mail\MissingPharmaciesMail;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BlockedOrdersReportMail;
use App\Models\ProductThresholdPrice;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class FileProcessHelper {

    public static function processPharmaciesIn ($contents){
        NomaneHelper::UsersActions();
         $pharmacy_array = ExctractHelper::extractAndProccesPharmaciesInTxt($contents);
        return $pharmacy_array;
    }

    public static function processProductsIn($contents, $file) {
        NomaneHelper::UsersActions();
    }

    public static function processCustomerSanitationIn($contents, $file) {
        NomaneHelper::UsersActions();

        $filePath = $file;

        $contents = Storage::disk('nomane_ftp_in_folders')->get($filePath);

        $tempFileName = 'customer_sanitation_' . time() . '.xlsx';
        Storage::disk('nomane_temp_folder')->put($tempFileName, $contents);
        $filePath = Storage::disk('nomane_temp_folder')->path($tempFileName);

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $usefulData = ExctractHelper::extractAndProccesCustomerSanitationInExcel($sheet, $spreadsheet);

        return $usefulData;
    }


    public static function processTradePolicyIn($contents, $file) {
        NomaneHelper::UsersActions();

        // ToDo: Delete sera con el archivo y resitro marcado en rojo

        $filePath = $file;

        $contents = Storage::disk('nomane_ftp_in_folders')->get($filePath);

        $tempFileName = 'trade_policy_' . time() . '.xlsx';
        Storage::disk('nomane_temp_folder')->put($tempFileName, $contents);
        $filePath = Storage::disk('nomane_temp_folder')->path($tempFileName);

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $usefulData = ExctractHelper::extractAndProccesTradePolicyInExcel('premium', $sheet, $spreadsheet);
        $sheet = $spreadsheet->getSheet(1);
        $usefulData_1 = ExctractHelper::extractAndProccesTradePolicyInExcel('standard', $sheet, $spreadsheet);
        //$usefulData = array_merge($usefulData, $usefulData_1);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // CIP code, SAP code, price level 1, discount 1, price  level 2, discount 2, price  level 3, discount
        $headers = [
            'CIP',
            'SAP',
            'Produit',
            'Price-pght',
            'Palier1',
            'Discount level 1',
            'Price level 1',
            'Palier2',
            'Discount level 2',
            'Price level 2',
            'Palier3',
            'Discount level 3',
            'Price level 3',
            'Offre',
            'Updated_at'
        ];
        foreach ($headers as $columnIndex => $header) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex + 1);
            $sheet->setCellValue($column . '1', $header);
        }
        $row = 2;
        if (isset($usefulData['prods']['update']['premium'])) {
            foreach ($usefulData['prods']['update']['premium'] as $product) {
                $rowData = [
                    $product['CIP'],
                    $product['SAP'],
                    $product['Produit'],
                    $product['Price-pght'],
                    $product['PALIER1'],
                    $product['DTO1'],
                    $product['PRICE1'],
                    $product['PALIER2'],
                    $product['DTO2'],
                    $product['PRICE2'],
                    $product['PALIER3'],
                    $product['DTO3'],
                    $product['PRICE3'],
                    $product['Offre'],
                    $product['Updated_at']
                ];

                foreach ($rowData as $columnIndex => $value) {
                    $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex + 1);
                    // Prefijo con apóstrofe para forzar formato de texto
                    //if (is_numeric($value)) {
                        $sheet->setCellValueExplicit(
                            $column . $row,
                            $value,
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                    //} else {
                    //    $sheet->setCellValue($column . $row, $value);
                    //}
                }
                $row++;
            }
        }
        // Aplicar formato
        $lastColumn = $sheet->getHighestColumn();
        //$lastRow = $sheet->getHighestRow();

        // Formato de encabezados
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0070C0']
            ]
        ]);
/*
        $newSheet = new Worksheet($spreadsheet, 'Standard');

        $headers = [
            'CIP',
            'SAP',
            'Produit',
            'Price-pght',
            'Palier1',
            'Discount level 1',
            'Price level 1',
            'Palier2',
            'Discount level 2',
            'Price level 2',
            'Palier3',
            'Discount level 3',
            'Price level 3',
            'Offre',
            'Updated_at'
        ];
        */
        /*
        foreach ($headers as $columnIndex => $header) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex + 1);
            $sheet->setCellValue($column . '1', $header);
        }
            */
        //$row = 2;
        if (isset($usefulData_1['prods']['update']['standard'])) {
            foreach ($usefulData_1['prods']['update']['standard'] as $product) {
                $rowData = [
                    $product['CIP'],
                    $product['SAP'],
                    $product['Produit'],
                    $product['Price-pght'],
                    $product['PALIER1'],
                    $product['DTO1'],
                    $product['PRICE1'],
                    $product['PALIER2'],
                    $product['DTO2'],
                    $product['PRICE2'],
                    $product['PALIER3'],
                    $product['DTO3'],
                    $product['PRICE3'],
                    $product['Offre'],
                    $product['Updated_at']
                ];

                foreach ($rowData as $columnIndex => $value) {
                    $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex + 1);
                    //if (is_numeric($value)) {
                        $sheet->setCellValueExplicit(
                            $column . $row,
                            $value,
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                    //} else {
                    //    $sheet->setCellValue($column . $row, $value);
                    //}
                }
                $row++;
            }
        }
        // Aplicar formato
        //$lastColumn = $sheet->getHighestColumn();
        //$lastRow = $sheet->getHighestRow();

        // Formato de encabezados
        /*
        $newSheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0070C0']
            ]
        ]);
*/

        $timestamp = date('Y-m-d_His');
        $baseFileName = "Rapport_des_produits_{$timestamp}.xlsx";
        //$fileName = "blockedOrdersOut/" . $baseFileName;

        $tempPath = storage_path("app/private/noName/temp/{$baseFileName}");

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

               // Enviar email con el archivo adjunto
        Mail::to(env('EMAIL_FOR_INFO'))
/*
            ->cc([
                'malika.bouallel@noName.com',
                'alina.velicu@noName.com',
                'valerie.gattelet-un@noName.com',
                'sylvie.tiber@noName.com',
                'pascal.dury@noName.com',
                'silvere.chapin@noName.com',
                'adel.boukraa@noName.com',
                'Marilyn.Gayffier@noName.com',
                'Sylvain.BERGERON@noName.com'
            ])*/
            ->send(new TradePolicyEmail($tempPath, $baseFileName));

        // Eliminar el archivo temporal
        unlink($tempPath);

        return $usefulData;
    }
/*
    public static function processComercialConditions($contents, $file) {
        NomaneHelper::UsersActions();
    }
*/
    public static function processProductControlFileIn($contents, $file) {
        NomaneHelper::UsersActions();
        return ExctractHelper::extractAndProccesProductControlInCsv ($contents);
    }

    public static function processPriceControlFileIn($contents, $file) {
        NomaneHelper::UsersActions();
        return ExctractHelper::extractAndProccesPriceControlInCsv ($contents);
    }

    public static function processUnavailableProductsIn($contents, $file) {
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        $statusColumnIndex = null;
        $cip13ColumnIndex = null;

        // Encontrar los índices de las columnas
        foreach ($worksheet->getRowIterator(1, 1) as $row) {
            $cellIterator = $row->getCellIterator();
            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();
                if (strtolower($value) === 'statut') {
                    $statusColumnIndex = $cell->getColumn();
                }
                if (strpos(strtolower($value), 'cip13') !== false) {
                    $cip13ColumnIndex = $cell->getColumn();
                }
            }
        }

        if (!$statusColumnIndex || !$cip13ColumnIndex) {
            throw new \Exception("No se encontraron las columnas necesarias");
        }

        $updatedProducts = 0;
        $notFoundProducts = [];

        // Procesar cada fila
        for ($row = 2; $row <= $highestRow; $row++) {
            $status = $worksheet->getCell($statusColumnIndex . $row)->getValue();
            $cip13 = $worksheet->getCell($cip13ColumnIndex . $row)->getValue();
            $product = Product::where('product_cip13', $cip13)->first();

            if ($product) {
                if ($status === 'Indisponible') {
                    $oldStatus = $product->product_status;
                    $newStatus = 'Vendable';

                    // Solo crear histórico si el status ha cambiado
                    if ($oldStatus !== $newStatus) {
                        ProductHistoric::create([
                            'product_historic_product_id' => $product->id,
                            'product_historic_field_name' => 'product_status',
                            'product_historic_old_value' => $oldStatus,
                            'product_historic_new_value' => $newStatus,
                        ]);

                        $product->product_status = $newStatus;
                        $product->save();
                        $updatedProducts++;
                    }
                } else if ($status === 'Disponible') {
                    $oldStatus = $product->product_status;
                    $newStatus = 'Disponible';

                    // Solo crear histórico si el status ha cambiado
                    if ($oldStatus !== $newStatus) {
                        ProductHistoric::create([
                            'product_historic_product_id' => $product->id,
                            'product_historic_field_name' => 'product_status',
                            'product_historic_old_value' => $oldStatus,
                            'product_historic_new_value' => $newStatus,
                        ]);

                        $product->product_status = $newStatus;
                        $product->save();
                        $updatedProducts++;
                    }
                }
            } else {
                $notFoundProducts[] = $cip13;
            }
        }

        return [
            'success' => true,
            'updated_products' => $updatedProducts,
            'not_found_products' => $notFoundProducts
        ];

        NomaneHelper::UsersActions();
    }

    public static function processProductsBackToStockIn($contents, $file) {
        NomaneHelper::UsersActions();
    }

    public static function processShortTermProductsIn($contents, $file) {
        NomaneHelper::UsersActions();
    }

    public static function processProductQuotesIn($contents, $file) {
        NomaneHelper::UsersActions();
    }

    public static function processBlockedOrdersIn($contents, $file) {
        NomaneHelper::UsersActions();

        $contents = Storage::disk('nomane_ftp_in_folders')->get($file);

        $tempFileName = 'BlockedOrders' . time() . '.xlsx';
        Storage::disk('nomane_temp_folder')->put($tempFileName, $contents);
        $filePath = Storage::disk('nomane_temp_folder')->path($tempFileName);

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet(0);
        $usefulData = ExctractHelper::extractAndProccesBlockedOrdersInExcel ($sheet, $spreadsheet);

        Storage::disk('nomane_temp_folder')->delete($filePath);

        return $usefulData;
    }

    public static function processCagedimOrdersIn($contents, $file) {
        if (empty($contents)) {
            return response()->json([
                'success' => false,
                'error' => 'Empty XML content'
            ], 400);
        }

        $parseResult = XmlHelper::parseCegedimOrderXml($contents);

        if (!$parseResult['success']) {
            return response()->json([
                'success' => false,
                'error' => 'Error parsing XML: ' . $parseResult['error']
            ], 400);
        }

        $savedOrders = [];
        $allNotFoundPharmacies = [];
        $allNotFoundProducts = [];
        $cnt = 0;
        foreach ($parseResult['orders'] as $orderData) {
            $saveResult = OrderSaver::saveOrderPharmaML($orderData, $cnt);
            $cnt++;
            $savedOrders[] = $saveResult;
            if (!empty($saveResult['not_found_pharmacies'])) {
                $allNotFoundPharmacies = array_merge($allNotFoundPharmacies, $saveResult['not_found_pharmacies']);
            }

            if (!empty($saveResult['not_found_products'])) {
                $allNotFoundProducts = array_merge($allNotFoundProducts, $saveResult['not_found_products']);
            }
        }

        // Enviar un solo email con todas las farmacias no encontradas
        if (!empty($allNotFoundPharmacies)) {
            self::sendMissingPharmaciesReport($allNotFoundPharmacies);
        }

        // Enviar un solo email con todos los productos no encontrados
        if (!empty($allNotFoundProducts)) {
            self::sendMissingProductsReport($allNotFoundProducts);
        }

        return $savedOrders;
    }

    private static function sendMissingProductsReport(array $missingProducts): void
    {
        $subject = 'Produits non trouvés dans la base de données';
        $textContent = "Les produits suivants ont été détectés comme n'existant pas dans la base de données.";

        Mail::to(env('EMAIL_FOR_INFO'))
            ->send(new MissingProductsMail($subject, $textContent, $missingProducts));
    }

    private static function sendMissingPharmaciesReport(array $missingPharmacies): void
    {
        $subject = 'Pharmacies non trouvées dans la base de données dans l\'importation de Cagedim';
        $textContent = "Les pharmacies suivantes ont été détectées comme n'existant pas dans la base de données.";

        Mail::to(env('EMAIL_FOR_INFO'))
            ->send(new MissingPharmaciesMail($subject, $textContent, $missingPharmacies));
    }

    public static function processPharmaMlOrdersIn($xmlString, $file) {
        NomaneHelper::UsersActions();

    }

    public static function processProductsUnderAllocationIn($contents, $file) {
        NomaneHelper::UsersActions();
    }

    public static function processCancelledOrdersIn($contents, $file) {

        // JUJU

        NomaneHelper::UsersActions();
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $usefulData = [];
        $line = [];
        $lines = explode("\n", $contents);
        foreach ($lines as $lineKey => $line_csv) {

            if ($lineKey > 0){
                $line[] = str_getcsv($line_csv, ',');

                $orderReference = $line[0][1];

                $order_call = Order::where('order_reference', '=', $orderReference)->get();
                if (isset($order_call[0]->id)) {
                    $order_call[0]->order_status = Order::CANCELED;
                    $order_call[0]->save();
                    $usefulData['found'][] = $order_call[0]->id;
                }
                $order_cagedim = OrderCagedim::where('customer_po', '=', $orderReference)->get();
                if (isset($order_cagedim[0]->id)) {
                    $order_cagedim[0]->order_block_code = Order::CANCELED;
                    $order_cagedim[0]->save();
                    $usefulData['found'][] = $order_cagedim[0]->id;
                }
            }
        }
        return $usefulData;
    }

    // Out files

    public static function processProductIntegrationCheckOut($folder) {
        NomaneHelper::UsersActions();
    }

    public static function processOrdersSentToNomaneOut($folder) {
        NomaneHelper::UsersActions();

        // JUJU

        $date_order = Carbon::now()->subHours(2)->format(app('global_format_datetime'));

        // DESCOMENAR
        NomaneHelper::doApiCommandCall('/in/cancelled-orders', 'NO');

        // Obtener los pedidos que no han sido enviados aún
        $orders_cagedim = OrderCagedim::where('order_sent_to_nomane', 0)
            ->where('orders_cagedim.created_at', '<=', $date_order)
            ->whereIn('orders_cagedim.order_block_code', ['', 'On hold'])
            //->where('orders_cagedim.order_block_code', '!=', 'Cancelled')

            ->where('pharmacies.pharmacy_status','=', Pharmacy::ACTIVE)
            ->join('pharmacies', 'orders_cagedim.sold_to', '=', 'pharmacies.pharmacy_cip13')
            ->with(['lines' => function($query) {
                $query->select(
                    'orders_cagedim_lines.id',
                    'orders_cagedim_lines.order_id',
                    'product_no',
                    'product_qualifier_code',
                    'qty',
                    'item_category',
                    'discount_type',
                    'discount_value'
                );
            }, 'lines.product'])
            ->select(
                'orders_cagedim.id',
                'sales_org',
                'sold_to',
                'ship_to',
                'customer_po',
                'po_type',
                'order_block_code',
                'shipment_method',
                'delivery_priority',
                'po_date',
                'requested_delivery_date',
                'order_sent_to_nomane',
                'order_sent_to_nomane_date',
                'orders_cagedim.created_at',
                DB::raw("'cagedim' as order_source")
            )
            ->get();

            //LOG::debug('orders_cagedim');
            //LOG::debug($orders_cagedim);

        // Verificar si hay farmacias no encontradas en los pedidos de Cagedim
        $missingPharmacies = [];
        $missingProducts = [];
        $uniqueProducts = [];
        foreach ($orders_cagedim as $order) {
            // Verificar si el sold_to está vacío o es inválido
            $farm = Pharmacy::where('pharmacy_cip13', $order->sold_to)->latest()->get();
            if (count($farm) == 0) {
                $missingPharmacies[] = [
                    'cip_id' => $order->sold_to ?? 'No disponible',
                    'order_reference' => $order->customer_po ?? 'N/A'
                ];
            }

            // Verificar si hay productos no encontrados

            $juju_order = OrderCagedim::find($order->id);
            foreach ($juju_order->lines as $line) {
                if ( ! isset($line->product->id )) {
                    $cip13 = $line->product_no;

                    // Si este CIP13 ya está en la lista, actualizar la cantidad
                    if (isset($uniqueProducts[$cip13])) {
                        $uniqueProducts[$cip13]['quantity'] += $line->qty;
                    } else {
                        // Si no, añadir un nuevo elemento
                        $uniqueProducts[$cip13] = [
                            'cip13' => $cip13,
                            'order_reference' => $order->customer_po ?? 'N/A',
                            'quantity' => $line->qty
                        ];
                    }
                } else {
                    if ($line->product->product_quote) {
                        if ($line->qty > $line->product->product_quote  && $line->product->product_quote != 0 ) {
                            $line->qty = $line->product->product_quote;
                            $line->save();
                        }
                    }
                }
            }
        }

        foreach ($uniqueProducts as $product) {
            $missingProducts[] = $product;
        }

        // Si hay farmacias no encontradas, enviar un email
        if (!empty($missingPharmacies)) {
            self::sendMissingPharmaciesEmail($missingPharmacies);
        }

        // Si hay productos no encontrados, enviar un email
        if (!empty($missingProducts)) {
            self::sendMissingProductsEmail($missingProducts);
        }

        //$date_order = Carbon::now()->subHours(2)->format('Y-m-d H:i:s');

        $orders_call_center = Order::where('order_sent_to_nomane', 0)
            ->where('orders.updated_at', '<=', $date_order)
            ->whereIn('order_status', [Order::PENDING_EXPORT, Order::ONHOLD])
            ->join('pharmacies', 'orders.order_pharmacy_id', '=', 'pharmacies.id')
            ->with(['orderDetails' => function($query) {
                $query->join('products', 'order_details.order_detail_product_id', '=', 'products.id')
                    ->select(
                        'order_details.id',
                        'order_detail_order_id as order_id',
                        'products.product_cip13 as product_no',
                        DB::raw("'C13' as product_qualifier_code"),
                        'order_detail_quantity as qty',
                        DB::raw("'' as item_category"),
                        DB::raw("'ZC11' as discount_type"),
                        'order_detail_discount as discount_value'
                    );
            }, 'orderDetails.product'])
            ->select(
                'orders.id',
                DB::raw("'FR10' as sales_org"),
                'pharmacies.pharmacy_cip13 as sold_to',
                'pharmacies.pharmacy_cip13 as ship_to',
                'order_reference as customer_po',
                DB::raw("'0026' as po_type"),
                'order_block_reason as order_block_code',
                DB::raw("'02' as shipment_method"),
                DB::raw("'' as delivery_priority"),
                'orders.created_at as po_date',
                'order_desired_delivery_date as requested_delivery_date',
                'order_sent_to_nomane',
                DB::raw('NULL as order_sent_to_nomane_date'),
                'orders.created_at',
                'orders.order_source'
            )
            ->get();

            //LOG::debug('orders_call_center');
            //LOG::debug($orders_call_center);

        $uniqueProducts = [];
        foreach ($orders_call_center as $order) {
            $juju_order = Order::find($order->id);
            foreach ($juju_order->orderDetails as $detail) {
                if ( ! isset($detail->product->id )) {
                    $cip13 = $detail->product->product_cip13;
                    if (isset($uniqueProducts[$cip13])) {
                        $uniqueProducts[$cip13]['quantity'] += $detail->order_detail_quantity;
                    } else {
                        $uniqueProducts[$cip13] = [
                            'cip13' => $cip13,
                            'order_reference' => $order->order_reference ?? 'N/A',
                            'quantity' => $detail->order_detail_quantity
                        ];
                    }
                } else {
                    if ($detail->product->product_quote) {
                        if ($detail->order_detail_quantity > $detail->product->product_quote  && $detail->product->product_quote != 0 ) {
                            $detail->order_detail_quantity = $detail->product->product_quote;
                            $detail->save();
                        }
                    }
                }
            }
        }

        foreach ($uniqueProducts as $product) {
            $missingProducts[] = $product;
        }

        // Si hay productos no encontrados (después de verificar ambos tipos de pedidos), enviar un email
        if (!empty($missingProducts) && count($missingProducts) > 0) {
            self::sendMissingProductsEmail($missingProducts);
        }

        // Por cada pedido de cagedim o call center,
        // verificar las unidades vendidas por mes del producto
        // y si no supera product_units_sell_units_sell de la tabla ProductUnitsSell
        // incluir el pedido en la colección $orders

        $filteredOrders_cagedim = [];
        $filteredOrders_call_center = [];
        $currentMonth = Carbon::now()->format('Y-m');
        $currentMonthStart = $currentMonth . '-01';
        $currentMonthEnd = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Procesar pedidos de cagedim
        foreach ($orders_cagedim as $order) {
            $includeOrder = true;
            $tempProductUpdates = [];

            $tempOrder = OrderCagedim::find($order->id);

            foreach ($tempOrder->lines as $line) {
                if (!$line->product) {
                    $includeOrder = false;
                    continue;
                } else if ( $line->product->product_status != Product::STATUS_DISPONIBLE ) {
                    $includeOrder = false;
                    continue;
                }

                $productId = $line->product->id;

                $productUnitsSell = ProductUnitsSell::where('product_units_sell_product_id', $productId)
                    ->where('product_units_sell_date_start', '<=', $currentMonthEnd)
                    ->where(function($query) use ($currentMonthStart) {
                        $query->where('product_units_sell_date_end', '>=', $currentMonthStart)
                              ->orWhereNull('product_units_sell_date_end');
                    })
                    ->first();

                // Si no existe un registro, crear uno nuevo con límite 0
                if (!$productUnitsSell) {
                    $productUnitsSell = new ProductUnitsSell();
                    $productUnitsSell->product_units_sell_product_id = $productId;
                    $productUnitsSell->product_units_sell_units_sell = 0;
                    $productUnitsSell->product_units_sell_date_start = $currentMonthStart;
                    $productUnitsSell->product_units_sell_date_end = $currentMonthEnd;
                    $productUnitsSell->product_units_sell_time_start = '00:00:00';
                    $productUnitsSell->product_units_sell_time_end = '23:59:59';
                    $productUnitsSell->save();
                }

                // Verificar si añadir este pedido superaría el límite
                $currentSold = $productUnitsSell->product_units_sell_units_sell ?? 0;
                //$limit = $productUnitsSell->product_units_sell_limit ?? 0;
                $limit = $detail->product->product_allocation ?? 0;

                if ($limit > 0 && ($currentSold + $line->qty) > $limit) {
                    $includeOrder = false;
                    break;
                }

                // Guardar temporalmente las actualizaciones
                //if ($tempOrder->order_block_code != Order::ONHOLD) {
                    $tempProductUpdates[] = [
                        'product_units_sell' => $productUnitsSell,
                        'qty' => $line->qty
                    ];
                //}
            }

            if ($includeOrder) {
                // Actualizar las unidades vendidas en la base de datos
                foreach ($tempProductUpdates as $update) {
                    $update['product_units_sell']->product_units_sell_units_sell += $update['qty'];
                    $update['product_units_sell']->save();
                }

                $filteredOrders_cagedim[] = $order;
            }
        }

        foreach ($orders_call_center as $order) {
            $includeOrder = true;
            $tempProductUpdates = [];

            $tempOrder = Order::find($order->id);

            foreach ($tempOrder->orderDetails as $line) {
                if (!$line->product) {
                    $includeOrder = false;
                    continue;
                } else if ( $line->product->product_status != Product::STATUS_DISPONIBLE ) {
                    $includeOrder = false;
                    continue;
                }
            }

            foreach ($tempOrder->orderDetails as $detail) {
                if (!$detail->product) continue;
                $productId = $detail->product->id;
                $productUnitsSell = ProductUnitsSell::where('product_units_sell_product_id', $productId)
                    ->where('product_units_sell_date_start', '<=', $currentMonthEnd)
                    ->where(function($query) use ($currentMonthStart) {
                        $query->where('product_units_sell_date_end', '>=', $currentMonthStart)
                              ->orWhereNull('product_units_sell_date_end');
                    })
                    ->first();

                // Si no existe un registro, crear uno nuevo con límite 0
                if (!$productUnitsSell) {
                    $productUnitsSell = new ProductUnitsSell();
                    $productUnitsSell->product_units_sell_product_id = $productId;
                    $productUnitsSell->product_units_sell_units_sell = 0;
                    $productUnitsSell->product_units_sell_date_start = $currentMonthStart;
                    $productUnitsSell->product_units_sell_date_end = $currentMonthEnd;
                    $productUnitsSell->product_units_sell_time_start = '00:00:00';
                    $productUnitsSell->product_units_sell_time_end = '23:59:59';
                    $productUnitsSell->save();
                }

                // Verificar si añadir este pedido superaría el límite
                $currentSold = $productUnitsSell->product_units_sell_units_sell ?? 0;
                //$limit = $productUnitsSell->product_units_sell_limit ?? 0;
                $limit = $detail->product->product_allocation ?? 0;



                if ($limit > 0 && ($currentSold + $detail->order_detail_quantity) > $limit) {
                    $includeOrder = false;
                    break;
                }

                // Guardar temporalmente las actualizaciones
                 //if ($tempOrder->order_status != Order::ONHOLD) {
                    $tempProductUpdates[] = [
                        'product_units_sell' => $productUnitsSell,
                        'qty' => $detail->order_detail_quantity
                    ];
                //}
            }

            if ($includeOrder) {
                // Actualizar las unidades vendidas en la base de datos
                foreach ($tempProductUpdates as $update) {
                    $update['product_units_sell']->product_units_sell_units_sell += $update['qty'];
                    $update['product_units_sell']->save();
                }

                $filteredOrders_call_center[] = $order;
            }
        }

        $orders = array_merge($filteredOrders_cagedim, $filteredOrders_call_center);

        //LOG::debug('orders');
        //LOG::debug($orders);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // Crear el elemento raíz con ns0
        $root = $dom->createElementNS('http://schemas.noName.com/PET/FR/v1.0', 'ns0:CMC_Order');
        $dom->appendChild($root);

        foreach ($orders as $order) {
            if ( isset($order->order_source) && $order->order_source == 'CALL' || $order->order_source == 'Call' || $order->order_source == 'Apm' || $order->order_source == 'APM') {
                // Crear Order sin namespace
                $orderNode = $dom->createElement('Order');
                $root->appendChild($orderNode);

                // Añadir Header
                $header = $dom->createElement('Header');
                $orderNode->appendChild($header);

                $tempOrder = Order::find($order->id);

                // Añadir elementos del header
                $header->appendChild($dom->createElement('SalesOrg', 'FR10'));
                $header->appendChild($dom->createElement('SoldTo', !empty($tempOrder->pharmacy->pharmacy_sap_id) ? $tempOrder->pharmacy->pharmacy_sap_id : 'introuvable'));
                $header->appendChild($dom->createElement('ShipTo', !empty($tempOrder->pharmacy->pharmacy_sap_id) ? $tempOrder->pharmacy->pharmacy_sap_id : 'introuvable'));
                $header->appendChild($dom->createElement('CustomerPO', $tempOrder->order_reference));
                if ($tempOrder->order_source == 'Call'){
                    $header->appendChild($dom->createElement('POType', '0026'));
                } else if ($tempOrder->order_source == 'Apm') {
                    $header->appendChild($dom->createElement('POType', '0027'));
                } else {
                    $header->appendChild($dom->createElement('POType', '0026'));
                }
                $header->appendChild($dom->createElement('ShipmentMethod', '02'));
                $header->appendChild($dom->createElement('DeliveryPriority', ''));
                $header->appendChild($dom->createElement('PODate', Carbon::parse($tempOrder->created_at)->format('Y-m-d\TH:i:s')));
                $header->appendChild($dom->createElement('RequestedDeliveryDate', ''));

                // Añadir Lines
                foreach ($tempOrder->items as $line) {
                    $lineNode = $dom->createElement('Line');
                    $orderNode->appendChild($lineNode);

                    $lineNode->appendChild($dom->createElement('ProductNo', $line->product->product_cip13));
                    $lineNode->appendChild($dom->createElement('ProductQualiferCode', 'C13'));
                    $lineNode->appendChild($dom->createElement('Qty', $line->order_detail_quantity));
                    $lineNode->appendChild($dom->createElement('ItemCategory', ''));

                    if ($line->order_detail_discount > 0) {
                        $discountNode = $dom->createElement('Discount');
                        $lineNode->appendChild($discountNode);

                        $discountNode->appendChild($dom->createElement('Type', 'ZC11'));
                        $discountNode->appendChild($dom->createElement('Value', number_format($line->order_detail_discount, 2)));
                    } else {
                        $discountNode = $dom->createElement('Discount');
                        $lineNode->appendChild($discountNode);

                        $discountNode->appendChild($dom->createElement('Type', ''));
                        $discountNode->appendChild($dom->createElement('Value', '0.00'));
                    }
                }
                $order->order_sent_to_nomane = 1;
                $order->order_sent_to_nomane_date = Carbon::now();
                $order->order_status = Order::EXPORTED;
                $order->save();
            } else {
                $tempOrder = OrderCagedim::find($order->id);
                $pharmacy = Pharmacy::where('pharmacy_cip13', $tempOrder->sold_to)->first();
                if(isset($pharmacy) && $pharmacy->pharmacy_sap_id != '') {
                    // Crear Order sin namespace
                    $orderNode = $dom->createElement('Order');
                    $root->appendChild($orderNode);

                    $header = $dom->createElement('Header');
                    $orderNode->appendChild($header);

                    $header->appendChild($dom->createElement('SalesOrg', $tempOrder->sales_org));
                    $header->appendChild($dom->createElement('SoldTo', !empty($pharmacy->pharmacy_sap_id) ? $pharmacy->pharmacy_sap_id : 'introuvable'));
                    $header->appendChild($dom->createElement('ShipTo', !empty($pharmacy->pharmacy_sap_id) ? $pharmacy->pharmacy_sap_id : 'introuvable'));
                    $header->appendChild($dom->createElement('CustomerPO', $tempOrder->customer_po));
                    $header->appendChild($dom->createElement('POType', $tempOrder->po_type));
                    $header->appendChild($dom->createElement('ShipmentMethod', $tempOrder->shipment_method));
                    $header->appendChild($dom->createElement('DeliveryPriority', $tempOrder->delivery_priority));
                    $header->appendChild($dom->createElement('PODate', Carbon::parse($tempOrder->po_date)->format('Y-m-d\TH:i:s')));
                    $header->appendChild($dom->createElement('RequestedDeliveryDate', ''));

                    foreach ($tempOrder->lines as $line) {
                        $lineNode = $dom->createElement('Line');
                        $orderNode->appendChild($lineNode);
                        $lineNode->appendChild($dom->createElement('ProductNo', $line->product_no));
                        $lineNode->appendChild($dom->createElement('ProductQualiferCode', $line->product_qualifier_code));
                        $lineNode->appendChild($dom->createElement('Qty', $line->qty));
                        $lineNode->appendChild($dom->createElement('ItemCategory', $line->item_category));
                        if ($line->discount_value > 0) {
                            $discountNode = $dom->createElement('Discount');
                            $lineNode->appendChild($discountNode);
                            $discountNode->appendChild($dom->createElement('Type', 'ZC11'));
                            $discountNode->appendChild($dom->createElement('Value', number_format($line->discount_value, 2)));
                        } else {
                            $discountNode = $dom->createElement('Discount');
                            $lineNode->appendChild($discountNode);
                            $discountNode->appendChild($dom->createElement('Type', ''));
                            $discountNode->appendChild($dom->createElement('Value', '0.00'));
                        }
                    }
                    $tempOrder->order_sent_to_nomane = 1;
                    $tempOrder->order_block_code = '';
                    $tempOrder->order_sent_to_nomane_date = Carbon::now();
                    $tempOrder->save();
                }
                /* else {
                    $missingPharmacies[] = $tempOrder->sold_to;

                    if (!empty($missingPharmacies)) {
                        self::sendMissingPharmaciesEmail($missingPharmacies);
                    }
                }
                    */
            }
        }

        $fileName = NomaneHelper::insertCurrentDateBeforeExtensionOrder("Order/ORDER_FROM_CMC_TO_PET_.xml");

        $file_status = new FileStatus();
        $file_status->file_status_filename = $fileName;
        $file_status->file_status_status = "Starting Out process";
        $file_status->file_status_source = "NoName";
        $file_status->file_status_process = "Out";
        $file_status->file_status_type = "ordersSentToNomane";
        $file_status->save();

        // Al guardar el XML, eliminamos la declaración XML
        $xmlContent = $dom->saveXML($dom->documentElement);
        Storage::disk('nomane_ftp_out_folders')->put($fileName, $xmlContent);
        Storage::disk('nomane_temp_folder')->put($fileName, $xmlContent);

        $file_status->file_status_status = "Process ended";
        $file_status->save();

        // Actualizar a ONHOLD los pedidos que no pasaron el filtro
        $filteredOrderIds = collect($filteredOrders_call_center)->pluck('id')->toArray();

        foreach ($orders_call_center as $order) {
            if (!in_array($order->id, $filteredOrderIds)) {
                // Este pedido no pasó el filtro, actualizar a ONHOLD
                $orderToUpdate = Order::find($order->id);
                if ($orderToUpdate) {
                    $orderToUpdate->order_status = Order::ONHOLD;
                    $orderToUpdate->save();
                }
            }
        }

        $filteredOrderIds = collect($filteredOrders_cagedim)->pluck('id')->toArray();

        foreach ($orders_cagedim as $order) {
            if (!in_array($order->id, $filteredOrderIds)) {
                // Este pedido no pasó el filtro, actualizar a ONHOLD
                $orderToUpdate = OrderCagedim::find($order->id);
                if ($orderToUpdate) {
                    $orderToUpdate->order_block_code = Order::ONHOLD;
                    $orderToUpdate->save();
                }
            }
        }

        return $orders;
    }

    private static function sendMissingProductsEmail(array $missingProducts): void
    {
        $subject = 'Produits non trouvés dans les commandes envoyées à NoName';
        $textContent = "Les produits suivants ont été détectés comme n'existant pas dans la base de données.";

        Mail::to(env('EMAIL_FOR_INFO'))
            ->send(new MissingProductsMail($subject, $textContent, $missingProducts));
    }

    private static function sendMissingPharmaciesEmail(array $missingPharmacies): void
    {
        $subject = 'Pharmacies non trouvées dans les commandes Cagedim envoyées à NoName';
        $textContent = "Les pharmacies suivantes ont été détectées comme n'existant pas dans la base de données.";
        Mail::to(env('EMAIL_FOR_INFO'))
            ->send(new MissingPharmaciesMail($subject, $textContent, $missingPharmacies));
    }

    public static function processNewCustomerOrChangeOut($folder) {

        NomaneHelper::UsersActions();
        $usefulData = array();
        $old_address = array();


        $pharmacies_2 = Pharmacy::where('pharmacy_sent_to_nomane', '=', 0)
                                ->where('pharmacy_status', '=', Pharmacy::BLOCKED)
            ->select(
                     'pharmacies.id as id',
                     "pharmacy_cip13 as CODE CIP",
                     "pharmacy_name as DENOMINATION SOCIALE",
                     "pharmacy_name4 as DENOMINATION COMMERCIAL",
                     "pharmacy_address_street as ADRESSE",
                     "pharmacy_address_address1 as COMPLEMENT ADRESSE 1",
                     "pharmacy_address_address2 as COMPLEMENT ADRESSE 2",
                     "pharmacy_address_address3 as COMPLEMENT ADRESSE 3",
                     "pharmacy_zipcode as CODE POSTAL",
                     "pharmacy_city as VILLE",
                     "order_reference"
                     )
            ->join('orders', 'orders.order_pharmacy_id', '=', 'pharmacies.id')
            ->where('orders.order_status', '=', Order::BLOCKED)
            ->where('orders.order_block_reason', '=', 'Client bloqué : Client sans code SAP')
            ->distinct()
            ->get();

        foreach($pharmacies_2 as $pharmacy) {
            $pharmacy_array = $pharmacy->toArray();

            foreach ($pharmacy_array as $key => $value) {
                if ( $key != 'CODE CIP' || $key != 'id')
                    $old_address[$pharmacy_array['CODE CIP']][$key] = $value ;
            }

            $filePath = 'newCustomerOrChange/New_Customer_Or_Change_'.$pharmacy_array['CODE CIP'].'.xlsx';
            $fileName = 'New_Customer_Or_Change_'.$pharmacy_array['CODE CIP'].'.xlsx';
            $file_save = str_replace('./', '', NomaneHelper::insertCurrentDateBeforeExtension($fileName) );

            $file_status = new FileStatus();
            $file_status->file_status_filename = $file_save;
            $file_status->file_status_status = "Starting Out process";
            $file_status->file_status_source = "NoName";
            $file_status->file_status_process = "Out";
            $file_status->file_status_type = "newCustomerOrChange";
            $file_status->save();

            $tempFarm = Pharmacy::find($pharmacy->id);

            $emailSubject = 'NoName SAS DEMANDE DE CREATION CODE CIP: ' .$tempFarm->pharmacy_cip13;

            $order_reference = $pharmacy->order_reference;

            $emailText = 'Veuillez trouver en pièce jointe le RIB de l’officine et ci-dessous les informations concernant l’officine, commande n° ';
            $emailText = 'Ci-dessous les informations concernant l’officine, commande n° ' . $order_reference ;
            $emailText_2 = null;

            $usefulData[$pharmacy_array['CODE CIP']] = [
                    'CODE CLIENT SAP' => $pharmacy->pharmacy_sap_id,
                    'DENOMINATION SOCIALE' => $pharmacy->pharmacy_name,
                    'DENOMINATION COMMERCIAL' => $pharmacy->pharmacy_name4,
                    'CODE CIP' => $pharmacy->pharmacy_cip13,
                    'SIREN' => $pharmacy->pharmacy_siren,
                    'SIRET' => $pharmacy->pharmacy_siret,
                    'Titulaire' => $pharmacy->pharmacy_holder_name,
                    'ADRESSE' => $pharmacy->pharmacy_address_street,
                    'COMPLEMENT ADRESSE 1' => $pharmacy->pharmacy_address_address1,
                    'COMPLEMENT ADRESSE 2' => $pharmacy->pharmacy_address_address2,
                    'COMPLEMENT ADRESSE 3' => $pharmacy->pharmacy_address_address3,
                    'CODE POSTAL' => $pharmacy->pharmacy_zipcode,
                    'VILLE' => $pharmacy->pharmacy_city,
                    'Domiciliation (nom de la banque)' => $pharmacy->pharmacy_bank_name,
                    'IBAN OBLIGATOIRE' => $pharmacy->pharmacy_iban,
                    'BANK CODE' => $pharmacy->pharmacy_bank_code,
                    'GUICHET CODE' => $pharmacy->pharmacy_guichet_code,
                    'Compte' => $pharmacy->pharmacy_account_number,
                    'Clé Rib' => $pharmacy->pharmacy_rib
                ];

            $usefulData[$pharmacy_array['CODE CIP']]['ADRESSE_old_address'] = '';
            $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 1_old_address'] = '';
            $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 2_old_address'] = '';
            $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 3_old_address'] = '';
            $usefulData[$pharmacy_array['CODE CIP']]['CODE POSTAL_old_address'] = '';
            $usefulData[$pharmacy_array['CODE CIP']]['VILLE_old_address'] = '';

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['CODE CIP'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['CODE CIP'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['CODE CIP'] = $tempFarm->pharmacy_cip13;
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['CODE CLIENT SAP'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['CODE CLIENT SAP'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['CODE CLIENT SAP'] = $tempFarm->pharmacy_sap_id;
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['DENOMINATION SOCIALE'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['DENOMINATION SOCIALE'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['DENOMINATION SOCIALE'] = $tempFarm->pharmacy_name ? $tempFarm->pharmacy_name : '';
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['DENOMINATION COMMERCIAL'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['DENOMINATION COMMERCIAL'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['DENOMINATION COMMERCIAL'] = $tempFarm->pharmacy_name4 ? $tempFarm->pharmacy_name4 : '';
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['SIREN'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['SIREN'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['SIREN'] = $tempFarm->pharmacy_siren ? $tempFarm->pharmacy_siren : '';
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['SIRET'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['SIRET'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['SIRET'] = $tempFarm->pharmacy_siret ? $tempFarm->pharmacy_siret : '';
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['Titulaire'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['Titulaire'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['Titulaire'] = $tempFarm->pharmacy_holder_name ? $tempFarm->pharmacy_holder_name : '';
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['ADRESSE'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['ADRESSE'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['ADRESSE'] = $tempFarm->pharmacy_address_street ? $tempFarm->pharmacy_address_street : '';
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 1'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 1'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 1'] = $tempFarm->pharmacy_address_address1 ? $tempFarm->pharmacy_address_address1 : '';
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 2'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 2'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 2'] = $tempFarm->pharmacy_address_address2 ? $tempFarm->pharmacy_address_address2 : '';
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 3'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 3'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 3'] = $tempFarm->pharmacy_address_address3 ? $tempFarm->pharmacy_address_address3 : '';
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['CODE POSTAL'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['CODE POSTAL'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['CODE POSTAL'] = $tempFarm->pharmacy_zipcode;
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['VILLE'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['VILLE'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['VILLE'] = $tempFarm->pharmacy_city ? $tempFarm->pharmacy_city : '';
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['Domiciliation (nom de la banque)'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['Domiciliation (nom de la banque)'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['Domiciliation (nom de la banque)'] = $tempFarm->pharmacy_bank_name;
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['IBAN OBLIGATOIRE'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['IBAN OBLIGATOIRE'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['IBAN OBLIGATOIRE'] = $tempFarm->pharmacy_iban;
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['BANK CODE'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['BANK CODE'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['BANK CODE'] = $tempFarm->pharmacy_bank_code;
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['GUICHET CODE'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['GUICHET CODE'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['GUICHET CODE'] = $tempFarm->pharmacy_guichet_code ? $tempFarm->pharmacy_guichet_code : '';
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['Compte'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['Compte'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['Compte'] = $tempFarm->pharmacy_account_number ? $tempFarm->pharmacy_account_number : '';
            }

            if ( !$usefulData[$pharmacy_array['CODE CIP']]['Clé Rib'] ||
                    $usefulData[$pharmacy_array['CODE CIP']]['Clé Rib'] == ''  ){
                $usefulData[$pharmacy_array['CODE CIP']]['Clé Rib'] = $tempFarm->pharmacy_rib ? $tempFarm->pharmacy_rib : '';
            }

            GenerateHelper::generateExcelNewCustomerOrChange($usefulData[$pharmacy_array['CODE CIP']], $filePath, $fileName, $pharmacy_array['CODE CIP'], $emailSubject, $emailText, $emailText_2, $order_reference);

            $pharmacy->pharmacy_sent_to_nomane = 1;
            $pharmacy->save();

            $file_status_update = FileStatus::find($file_status->id);
            $file_status_update->file_status_status = "Process ended";
            $file_status_update->save();
        }


        $pharmacies = Pharmacy::where('pharmacy_sent_to_nomane', '=', 0)
                              ->where('pharmacy_new_data', '=', 1)
            ->select(
                     'id',
                     "pharmacy_cip13 as CODE CIP",
                     "pharmacy_name as DENOMINATION SOCIALE",
                     "pharmacy_name4 as DENOMINATION COMMERCIAL",
                     "pharmacy_address_street as ADRESSE",
                     "pharmacy_address_address1 as COMPLEMENT ADRESSE 1",
                     "pharmacy_address_address2 as COMPLEMENT ADRESSE 2",
                     "pharmacy_address_address3 as COMPLEMENT ADRESSE 3",
                     "pharmacy_zipcode as CODE POSTAL",
                     "pharmacy_city as VILLE"
                     )
            ->get();

        foreach ($pharmacies as $pharmacy) {
            $pharmacy_array = $pharmacy->toArray();

            foreach ($pharmacy_array as $key => $value) {
                if ( $key != 'CODE CIP' || $key != 'id')
                    $old_address[$pharmacy_array['CODE CIP']][$key] = $value ;
            }

            $filePath = 'newCustomerOrChange/New_Customer_Or_Change_'.$pharmacy_array['CODE CIP'].'.xlsx';
            $fileName = 'New_Customer_Or_Change_'.$pharmacy_array['CODE CIP'].'.xlsx';
            $file_save = str_replace('./', '', NomaneHelper::insertCurrentDateBeforeExtension($fileName) );

            $file_status = new FileStatus();
            $file_status->file_status_filename = $file_save;
            $file_status->file_status_status = "Starting Out process";
            $file_status->file_status_source = "NoName";
            $file_status->file_status_process = "Out";
            $file_status->file_status_type = "newCustomerOrChange";
            $file_status->save();

            $changes = PharmacyHistoric::getLatestChanges($pharmacy->id);

            $lastOrder = Order::where('order_pharmacy_id', $pharmacy->id)
                                ->where('order_status', '=', Order::BLOCKED)
                ->latest()
                ->get();

            if ( $lastOrder->isEmpty() ) {
                $tempFarm = Pharmacy::find($pharmacy->id);
                $lastOrder = OrderCagedim::where('sold_to', $tempFarm->pharmacy_cip13)
                                        ->where('order_block_code', '=', 'Client bloqué : avec le code:Z4/01')
                ->latest()
                ->get();
            }

            if ( $lastOrder->isEmpty() ) {
                continue;
            }

            $tempFarm = Pharmacy::find($pharmacy->id);

            if ($tempFarm->pharmacy_new_pharmacy){
                $emailSubject = 'NoName SAS DEMANDE DE CREATION CODE CIP: ' .$tempFarm->pharmacy_cip13;
            } else {
                $emailSubject = 'NoName : DEMANDE DE MODIFICATION  CODE CIP: ' .$tempFarm->pharmacy_cip13 . ' CODE SAP: ' .$tempFarm->pharmacy_sap_id . ' PHARMACIE: ' . $tempFarm->pharmacy_name;
            }

            foreach($changes as $change) {
                $change = $change->toArray();
            }

            $changes = $changes->toArray();

            $changesAnalysis = self::checkChangesInFields($changes);

            $emailText = 'Veuillez trouver en pièce jointe le RIB de l’officine et ci-dessous les informations concernant l’officine, commande n° ';
            $order_reference = $lastOrder[0]->order_reference ? $lastOrder[0]->order_reference : $lastOrder[0]->customer_po;

            $emailText_2 = null;

            if ($changesAnalysis['hasChanges']) {
                if ($pharmacy->pharmacy_new_pharmacy && $pharmacy->pharmacy_new_data) { // ok
                    if ($pharmacy->pharmacy_siret == '') { // ok
                        //$emailText = null;
                        $emailText_2 = 'Le nouveau SIRET est en cours d’établissement et vous sera transmis dès réception par l’officine.';
                    } else if ( ! $pharmacy->pharmacy_refusal_lcr) { // ok
                        //$emailText = null;
                        //$emailText_2 = null;
                    } else if ( $pharmacy->pharmacy_refusal_lcr) { // ok
                        $emailText = 'Ci-dessous les informations concernant l’officine, commande n° ' . $lastOrder->order_reference;
                        $emailText_2 = 'Le dossier envoyé ne contient que le template, sans le relevé d’identité bancaire, car le paiement par LCR a été refusé.';
                    }
                } else if ($pharmacy->pharmacy_status == Pharmacy::ACTIVE){
                    if ($changesAnalysis['categories']['hasAddressChanges']) { // ok
                        if ($pharmacy->pharmacy_siret == '') { // ok
                            //$emailText = null;
                            $emailText_2 = 'Le nouveau SIRET est en cours d’établissement et vous sera transmis dès réception par l’officine.';
                        } else if ( ! $pharmacy->pharmacy_refusal_lcr) { // ok
                            //$emailText = null;
                            //$emailText_2 = null;
                        } else if ( $pharmacy->pharmacy_refusal_lcr) { // ok
                            $emailText = 'Ci-dessous les informations concernant l’officine, commande n° ' . $lastOrder->order_reference;
                            $emailText_2 = 'Le dossier envoyé ne contient que le template, sans le relevé d’identité bancaire, car le paiement par LCR a été refusé.';
                        }
                    }
                    if ($changesAnalysis['categories']['hasPoBoxChanges']) {
                        $emailText = null;
                        $emailText_2 = null;
                    }
                    // Cambio de SIREN
                    if ($changesAnalysis['categories']['hasSirenChanges']) {
                        if ($pharmacy->pharmacy_siret == '') { // ok
                            //$emailText = null;
                            $emailText_2 = 'Le nouveau SIRET est en cours d’établissement et vous sera transmis dès réception par l’officine.';
                        } else if ( ! $pharmacy->pharmacy_refusal_lcr) { // ok
                            //$emailText = null;
                            //$emailText_2 = null;
                        } else if ( $pharmacy->pharmacy_refusal_lcr) { // ok
                            $emailText = 'Ci-dessous les informations concernant l’officine, commande n° ' . $lastOrder->order_reference;
                            $emailText_2 = 'Le dossier envoyé ne contient que le template, sans le relevé d’identité bancaire, car le paiement par LCR a été refusé.';
                        }
                    }
                    // Bank data
                    if ($changesAnalysis['categories']['hasBankChanges']) { // OK
                        //$emailText = null;
                        //$emailText_2 = null;
                    }
                } else if ($pharmacy->pharmacy_status == Pharmacy::BLOCKED){
                    if ($changesAnalysis['categories']['hasAddressChanges']) { // ok
                        if ($pharmacy->pharmacy_siret == '') { // ok
                            //$emailText = null;
                            $emailText_2 = 'Le nouveau SIRET est en cours d’établissement et vous sera transmis dès réception par l’officine.';
                        } else if ( ! $pharmacy->pharmacy_refusal_lcr) { // ok
                            //$emailText = null;
                            //$emailText_2 = null;
                        } else if ( $pharmacy->pharmacy_refusal_lcr) { // ok
                            $emailText = 'Ci-dessous les informations concernant l’officine, commande n° ' . $lastOrder->order_reference;
                            $emailText_2 = 'Le dossier envoyé ne contient que le template, sans le relevé d’identité bancaire, car le paiement par LCR a été refusé.';
                        }
                    }

                    if ($changesAnalysis['categories']['hasSirenChanges']) {
                        if ($pharmacy->pharmacy_siret == '') { // ok
                            //$emailText = null;
                            $emailText_2 = 'Le nouveau SIRET est en cours d’établissement et vous sera transmis dès réception par l’officine.';
                        } else if ( ! $pharmacy->pharmacy_refusal_lcr) { // ok
                            //$emailText = null;
                            //$emailText_2 = null;
                        } else if ( $pharmacy->pharmacy_refusal_lcr) { // ok
                            $emailText = 'Ci-dessous les informations concernant l’officine, commande n° ' . $lastOrder->order_reference;
                            $emailText_2 = 'Le dossier envoyé ne contient que le template, sans le relevé d’identité bancaire, car le paiement par LCR a été refusé.';
                        }
                    }
                }
            }

            // Verificar si hay registros en PharmacyHistoric
            $historicChanges = PharmacyHistoric::where('pharmacy_historic_pharmacy_id', $pharmacy->id)
                ->where('pharmacy_historic_sent_to_nomane', 0)
                ->get();

            if ($historicChanges->isNotEmpty()) {
                // Inicializar con los datos actuales de la farmacia

                $usefulData[$pharmacy_array['CODE CIP']] = [
                    'CODE CLIENT SAP' => $pharmacy->pharmacy_sap_id,
                    'DENOMINATION SOCIALE' => $pharmacy->pharmacy_name,
                    'DENOMINATION COMMERCIAL' => $pharmacy->pharmacy_name4,
                    'CODE CIP' => $pharmacy->pharmacy_cip13,
                    'SIREN' => $pharmacy->pharmacy_siren,
                    'SIRET' => $pharmacy->pharmacy_siret,
                    'Titulaire' => $pharmacy->pharmacy_holder_name,
                    'ADRESSE' => $pharmacy->pharmacy_address_street,
                    'COMPLEMENT ADRESSE 1' => $pharmacy->pharmacy_address_address1,
                    'COMPLEMENT ADRESSE 2' => $pharmacy->pharmacy_address_address2,
                    'COMPLEMENT ADRESSE 3' => $pharmacy->pharmacy_address_address3,
                    'CODE POSTAL' => $pharmacy->pharmacy_zipcode,
                    'VILLE' => $pharmacy->pharmacy_city,
                    'Domiciliation (nom de la banque)' => $pharmacy->pharmacy_bank_name,
                    'IBAN OBLIGATOIRE' => $pharmacy->pharmacy_iban,
                    'BANK CODE' => $pharmacy->pharmacy_bank_code,
                    'GUICHET CODE' => $pharmacy->pharmacy_guichet_code,
                    'Compte' => $pharmacy->pharmacy_account_number,
                    'Clé Rib' => $pharmacy->pharmacy_rib
                ];

                $fieldMapping = [
                    'pharmacy_sap_id' => 'CODE CLIENT SAP',
                    'pharmacy_name' => 'DENOMINATION SOCIALE',
                    'pharmacy_name4' => 'DENOMINATION COMMERCIAL',
                    'pharmacy_cip13' => 'CODE CIP',
                    'pharmacy_siren' => 'SIREN',
                    'pharmacy_siret' => 'SIRET',
                    'pharmacy_holder_name' => 'Titulaire',
                    'pharmacy_address_street' => 'ADRESSE',
                    'pharmacy_address_address1' => 'COMPLEMENT ADRESSE 1',
                    'pharmacy_address_address2' => 'COMPLEMENT ADRESSE 2',
                    'pharmacy_address_address3' => 'COMPLEMENT ADRESSE 3',
                    'pharmacy_zipcode' => 'CODE POSTAL',
                    'pharmacy_city' => 'VILLE',
                    //'pharmacy_type' => 'TYPE',
                    //'pharmacy_account_status' => 'ACCOUNT STATUS',
                    //'pharmacy_status' => 'STATUS',
                    'pharmacy_name2' => 'NAME 2',
                    'pharmacy_name3' => 'NAME 3',
                    //'pharmacy_district' => 'DISTRICT',
                    'pharmacy_region' => 'REGION',
                    //'pharmacy_country' => 'COUNTRY',
                    //'pharmacy_po_box' => 'PO BOX',
                    //'pharmacy_po_box_city' => 'PO BOX CITY',
                    //'pharmacy_po_box_region' => 'PO BOX REGION',
                    //'pharmacy_po_box_country' => 'PO BOX COUNTRY',
                    //'pharmacy_po_box_zipcode' => 'PO BOX ZIPCODE',
                    //'pharmacy_phone' => 'PHONE',
                    //'pharmacy_fax' => 'FAX',
                    //'pharmacy_email' => 'EMAIL',
                    'pharmacy_bank_name' => 'Domiciliation (nom de la banque)',
                    'pharmacy_iban' => 'IBAN OBLIGATOIRE',
                    'pharmacy_bank_code' => 'BANK CODE',
                    'pharmacy_guichet_code' => 'GUICHET CODE',
                    'pharmacy_account_number' => 'Compte',
                    'pharmacy_rib' => 'Clé Rib',
                    //'pharmacy_refusal_lcr' => 'REFUSAL LCR'
                ];

                $headerFields = [
                    'pharmacy_sap_id',
                    'pharmacy_name',
                    'pharmacy_name4',
                    'pharmacy_cip13',
                    'pharmacy_siren',
                    'pharmacy_siret',
                    'pharmacy_holder_name'
                ];

                $addressFields = [
                    'pharmacy_address_street',
                    'pharmacy_address_address1',
                    'pharmacy_address_address2',
                    'pharmacy_address_address3',
                    'pharmacy_zipcode',
                    'pharmacy_city'
                ];

                $bankFields = [
                    'pharmacy_bank_name',
                    'pharmacy_iban',
                    'pharmacy_bank_code',
                    'pharmacy_account_number',
                    'pharmacy_guichet_code',
                    'pharmacy_rib'
                ];

                // Encontrar el último campo de dirección que existe en los cambios
                $lastExistingField = null;
                foreach ($historicChanges as $historicChange) {
                    if (in_array($historicChange->pharmacy_historic_filed_name, $addressFields)) {
                        $lastExistingField = $historicChange->pharmacy_historic_filed_name;
                    }
                }

                $done_oldAddress = false;

                foreach ($historicChanges as $change) {
                    $fieldName = $change->pharmacy_historic_filed_name;
                    if (isset($fieldMapping[$fieldName]) && !empty($change->pharmacy_historic_new_value)) {
                        if (!isset($usefulData[$pharmacy_array['CODE CIP']])) {
                            $usefulData[$pharmacy_array['CODE CIP']] = [];
                        }
                        $usefulData[$pharmacy_array['CODE CIP']][$fieldMapping[$fieldName]] = $change->pharmacy_historic_new_value;

                        // Si es el último campo de dirección que existe, añadir old_address

                        if ($fieldName === $lastExistingField && isset($old_address[$pharmacy_array['CODE CIP']])) {
                            $done_oldAddress = true;
                            foreach ($old_address[$pharmacy_array['CODE CIP']] as $key => $value) {
                                if ($change->pharmacy_historic_old_value != '') {
                                    $usefulData[$pharmacy_array['CODE CIP']][$key.'_old_address'] = $value;
                                } else {
                                    $usefulData[$pharmacy_array['CODE CIP']][$key.'_old_address'] = '';
                                }
                            }
                        }
                    }

                    if ( ! $done_oldAddress && isset($old_address[$pharmacy_array['CODE CIP']])) {
                        foreach ($old_address[$pharmacy_array['CODE CIP']] as $key => $value) {
                            $usefulData[$pharmacy_array['CODE CIP']][$key.'_old_address'] = '';
                        }
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['CODE CIP'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['CODE CIP'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['CODE CIP'] = $tempFarm->pharmacy_cip13;
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['CODE CLIENT SAP'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['CODE CLIENT SAP'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['CODE CLIENT SAP'] = $tempFarm->pharmacy_sap_id ? $tempFarm->pharmacy_sap_id : '' ;
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['DENOMINATION SOCIALE'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['DENOMINATION SOCIALE'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['DENOMINATION SOCIALE'] = $tempFarm->pharmacy_name ? $tempFarm->pharmacy_name : '';
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['DENOMINATION COMMERCIAL'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['DENOMINATION COMMERCIAL'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['DENOMINATION COMMERCIAL'] = $tempFarm->pharmacy_name4 ? $tempFarm->pharmacy_name4 : '';
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['SIREN'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['SIREN'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['SIREN'] = $tempFarm->pharmacy_siren  ? $tempFarm->pharmacy_siren : '';
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['SIRET'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['SIRET'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['SIRET'] = $tempFarm->pharmacy_siret ? $tempFarm->pharmacy_siret : '';
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['Titulaire'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['Titulaire'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['Titulaire'] = $tempFarm->pharmacy_holder_name ? $tempFarm->pharmacy_holder_name : '';
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['ADRESSE'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['ADRESSE'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['ADRESSE'] = $tempFarm->pharmacy_address_street ? $tempFarm->pharmacy_address_street : '';
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 1'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 1'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 1'] = $tempFarm->pharmacy_address_address1 ? $tempFarm->pharmacy_address_address1 : '';
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 2'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 2'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 2'] = $tempFarm->pharmacy_address_address2 ? $tempFarm->pharmacy_address_address2 : '';
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 3'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 3'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['COMPLEMENT ADRESSE 3'] = $tempFarm->pharmacy_address_address3 ? $tempFarm->pharmacy_address_address3 : '';
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['CODE POSTAL'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['CODE POSTAL'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['CODE POSTAL'] = $tempFarm->pharmacy_zipcode ? $tempFarm->pharmacy_zipcode : '';
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['VILLE'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['VILLE'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['VILLE'] = $tempFarm->pharmacy_city ? $tempFarm->pharmacy_city : '';
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['Domiciliation (nom de la banque)'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['Domiciliation (nom de la banque)'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['Domiciliation (nom de la banque)'] = $tempFarm->pharmacy_bank_name;
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['IBAN OBLIGATOIRE'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['IBAN OBLIGATOIRE'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['IBAN OBLIGATOIRE'] = $tempFarm->pharmacy_iban;
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['BANK CODE'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['BANK CODE'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['BANK CODE'] = $tempFarm->pharmacy_bank_code;
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['GUICHET CODE'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['GUICHET CODE'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['GUICHET CODE'] = $tempFarm->pharmacy_guichet_code;
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['Compte'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['Compte'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['Compte'] = $tempFarm->pharmacy_account_number;
                    }

                    if ( !$usefulData[$pharmacy_array['CODE CIP']]['Clé Rib'] ||
                            $usefulData[$pharmacy_array['CODE CIP']]['Clé Rib'] == ''  ){
                        $usefulData[$pharmacy_array['CODE CIP']]['Clé Rib'] = $tempFarm->pharmacy_rib;
                    }

                    $change->pharmacy_historic_sent_to_nomane = 1;
                    $change->save();
                }

                GenerateHelper::generateExcelNewCustomerOrChange($usefulData[$pharmacy_array['CODE CIP']], $filePath, $fileName, $pharmacy_array['CODE CIP'], $emailSubject, $emailText, $emailText_2, $order_reference);
            }

            $pharmacy->pharmacy_new_data = 0;
            $pharmacy->pharmacy_sent_to_nomane = 1;
            $pharmacy->save();

            $file_status_update = FileStatus::find($file_status->id);
            $file_status_update->file_status_status = "Process ended";
            $file_status_update->save();
        }
        return $usefulData;
    }


/*
    public static function processOrdersExportedToNomaneOut($folder) {
        NomaneHelper::UsersActions();
    }
*/
    public static function processRollingOrderHistoryOut($folder) {
        NomaneHelper::UsersActions();

        $filename = 'Rapport_des_comandes_trimestriel.xlsx';
        $filename_two_week = str_replace('./', '', NomaneHelper::insertCurrentDateBeforeExtension($filename) );
        $file_save = str_replace('./', '', NomaneHelper::insertCurrentDateBeforeExtension($filename) );

        $file_status = new FileStatus();
        $file_status->file_status_filename = $file_save;
        $file_status->file_status_status = "Starting Out process";
        $file_status->file_status_source = "NoName";
        $file_status->file_status_process = "Out";
        $file_status->file_status_type = "twoWeekActivityReporting";
        $file_status->save();

        $usefulData[] = ['Référence commande', 'Statut', 'Nom de la pharmacie', 'Total', 'Source', 'Date de commande', 'CIP pharmacie', 'SAP pharmacie',
                             'Ville de la pharmacie', 'Nom de la campagne', 'CP', 'Date d\'export'];

        $orders = Order::where('orders.created_at', '>=', Carbon::now()->subMonths(3)->format(app('global_format_date') . ' 00:00:00'))
                        ->where('orders.order_status', '!=' , Order::DRAFT)
                ->get();

        foreach ($orders as $order) {
            $usefulData[] = [
                $order->order_reference,
                $order->order_status,
                $order->pharmacy->pharmacy_name,
                $order->order_amount,
                $order->order_source,
                Carbon::parse($order->created_at)->format(app('global_format_date_hour_minute_export')),
                $order->pharmacy->pharmacy_cip13,
                $order->pharmacy->pharmacy_sap_id,
                $order->pharmacy->pharmacy_city,
                'NoName',
                $order->pharmacy->pharmacy_zipcode,
                Carbon::parse($order->order_sent_to_nomane_date)->format(app('global_format_date_hour_minute_export'))
            ];
        }

        $orders = OrderCagedim::where('created_at', '>=', Carbon::now()->subMonths(3)->format(app('global_format_date') . ' 00:00:00'))
                ->get();

        foreach ($orders as $order) {
            if (isset($order->pharmacy_cip)){

                if ($order->order_block_code == 'Cancelled') {
                    $order_status = 'Cancelled';
                } else if ($order->order_block_code == 'On hold') {
                    $order_status = 'On hold';
                } else if ($order->order_block_code != '') {
                    $order_status = $order->order_block_code;
                } else {
                    $order_status = $order->order_sent_to_nomane == 1 ? 'Exported' : 'Pending to export';
                }

                $usefulData[] = [
                    $order->customer_po,
                    $order_status,
                    $order->pharmacy_cip->pharmacy_name,
                    $order->getTotal(),
                    'PharmaML',
                    Carbon::parse($order->created_at)->format(app('global_format_date_hour_minute_export')),
                    $order->pharmacy_cip->pharmacy_cip13,
                    $order->pharmacy_cip->pharmacy_sap_id,
                    $order->pharmacy_cip->pharmacy_city,
                    'NoName',
                    $order->pharmacy_cip->pharmacy_zipcode,
                    Carbon::parse($order->order_sent_to_nomane_date)->format(app('global_format_date_hour_minute_export'))
                ];
            }
        }

        GenerateHelper::generateExcelTwoWeeklyActivityReporting($usefulData, $filename_two_week);

        $file_status_update = FileStatus::find($file_status->id);
        $file_status_update->file_status_status = "Process ended";
        $file_status_update->save();

        return $usefulData;
    }

    public static function processWeeklyOrderConfirmationsOut($folder) {
        NomaneHelper::UsersActions();

        $filename = 'Weekly_Activity_Reporting.xlsx';
        $file_save = str_replace('./', '', NomaneHelper::insertCurrentDateBeforeExtension($filename) );

        $file_status = new FileStatus();
        $file_status->file_status_filename = $file_save;
        $file_status->file_status_status = "Starting Out process";
        $file_status->file_status_source = "NoName";
        $file_status->file_status_process = "Out";
        $file_status->file_status_type = "weeklyActivityReporting";
        $file_status->save();

        $usefulData[] = ['Référence commande', 'Statut', 'Nom de la pharmacie', 'Total', 'Source', 'Date de commande', 'CIP pharmacie',
                             'Ville de la pharmacie', 'Nom de la campagne', 'CP', 'Date d\'export'];

        $orders = Order::where('orders.order_sent_to_nomane_date', '>=', Carbon::now()->subWeeks(1)->startOfWeek()->format(app('global_format_date') . ' 00:00:00'))
                        ->where('orders.order_status', Order::EXPORTED)
                ->get();

        foreach ($orders as $order) {
            $usefulData[] = [
                $order->order_reference,
                $order->order_status,
                $order->pharmacy->pharmacy_name,
                $order->order_amount,
                $order->order_source,
                Carbon::parse($order->created_at)->format(app('global_format_date_hour_minute_export')),
                $order->pharmacy->pharmacy_cip13,
                $order->pharmacy->pharmacy_city,
                'NoName',
                $order->pharmacy->pharmacy_zipcode,
                Carbon::parse($order->order_sent_to_nomane_date)->format(app('global_format_date_hour_minute_export'))
            ];
        }

        $orders = OrderCagedim::where('order_sent_to_nomane_date', '>=', Carbon::now()->subWeeks(1)->startOfWeek()->format(app('global_format_date') . ' 00:00:00'))
                        ->where('order_sent_to_nomane', '=', 1)
                ->get();

        foreach ($orders as $order) {
            if (isset($order->pharmacy)){
                $usefulData[] = [
                    $order->customer_po,
                    'Exported',
                    $order->pharmacy->pharmacy_name,
                    $order->getTotal(),
                    'PharmaML',
                    Carbon::parse($order->created_at)->format(app('global_format_date_hour_minute_export')),
                    $order->pharmacy->pharmacy_cip13,
                    $order->pharmacy->pharmacy_city,
                    'NoName',
                    $order->pharmacy->pharmacy_zipcode,
                    Carbon::parse($order->order_sent_to_nomane_date)->format(app('global_format_date_hour_minute_export'))
                ];
            }
        }

        GenerateHelper::generateExcelWeeklyActivityReporting($usefulData, $filename);

        $file_status_update = FileStatus::find($file_status->id);
        $file_status_update->file_status_status = "Process ended";
        $file_status_update->save();

        return $usefulData;
    }

    public static function processMonthlyActivityReportingOut($folder) {
        NomaneHelper::UsersActions();

        $filename = 'Monthly_Activity_Reporting.xlsx';
        $file_save = str_replace('./', '', NomaneHelper::insertCurrentDateBeforeExtension($filename) );

        $file_status = new FileStatus();
        $file_status->file_status_filename = $file_save;
        $file_status->file_status_status = "Starting Out process";
        $file_status->file_status_source = "NoName";
        $file_status->file_status_process = "Out";
        $file_status->file_status_type = "monthlyActivityReporting";
        $file_status->save();

        $usefulData[] = ['Référence commande', 'Statut', 'Nom de la pharmacie', 'Total', 'Source', 'Date de commande', 'CIP pharmacie',
                             'Ville de la pharmacie', 'Nom de la campagne', 'CP', 'Date d\'export'];

        $orders = Order::where('orders.order_sent_to_nomane_date', '>=', Carbon::now()->startOfMonth()->subMonths(1)->startOfMonth()->format(app('global_format_date') . ' 00:00:00'))
                        ->where('orders.order_status', Order::EXPORTED)
                ->get();

        foreach ($orders as $order) {
            $usefulData[] = [
                $order->order_reference,
                $order->order_status,
                $order->pharmacy->pharmacy_name,
                $order->order_amount,
                $order->order_source,
                Carbon::parse($order->created_at)->format(app('global_format_date_hour_minute_export')),
                $order->pharmacy->pharmacy_cip13,
                $order->pharmacy->pharmacy_city,
                'NoName',
                $order->pharmacy->pharmacy_zipcode,
                Carbon::parse($order->order_sent_to_nomane_date)->format(app('global_format_date_hour_minute_export'))
            ];
        }

        $orders = OrderCagedim::where('order_sent_to_nomane_date', '>=', Carbon::now()->startOfMonth()->subMonths(1)->format(app('global_format_date') . ' 00:00:00'))
                        ->where('order_sent_to_nomane', '=', 1)
                ->get();

        foreach ($orders as $order) {
            if (isset($order->pharmacy)){
                $usefulData[] = [
                    $order->customer_po,
                    'Exported',
                    $order->pharmacy->pharmacy_name,
                    $order->getTotal(),
                    'PharmaML',
                    Carbon::parse($order->created_at)->format(app('global_format_date_hour_minute_export')),
                    $order->pharmacy->pharmacy_cip13,
                    $order->pharmacy->pharmacy_city,
                    'NoName',
                    $order->pharmacy->pharmacy_zipcode,
                    Carbon::parse($order->order_sent_to_nomane_date)->format(app('global_format_date_hour_minute_export'))
                ];
            }
        }

        GenerateHelper::generateExcelMonthlyActivityReporting($usefulData, $filename);

        $file_status_update = FileStatus::find($file_status->id);
        $file_status_update->file_status_status = "Process ended";
        $file_status_update->save();

        return $usefulData;
    }

    public static function processQuarterlyActivityReportingOut($folder) {
        NomaneHelper::UsersActions();
        $filename = 'Quarterly_Activity_Reporting.xlsx';
        $file_save = str_replace('./', '', NomaneHelper::insertCurrentDateBeforeExtension($filename) );

        $file_status = new FileStatus();
        $file_status->file_status_filename = $file_save;
        $file_status->file_status_status = "Starting Out process";
        $file_status->file_status_source = "NoName";
        $file_status->file_status_process = "Out";
        $file_status->file_status_type = "quarterlyActivityReporting";
        $file_status->save();

        $usefulData[] = ['Référence commande', 'Statut', 'Nom de la pharmacie', 'Total', 'Source', 'Date de commande', 'CIP pharmacie',
                             'Ville de la pharmacie', 'Nom de la campagne', 'CP', 'Date d\'export'];

        $orders = Order::where('orders.order_sent_to_nomane_date', '>=', Carbon::now()->startOfMonth()->subMonths(3)->format(app('global_format_date') . ' 00:00:00'))
                        ->where('orders.order_status', Order::EXPORTED)
                ->get();

        foreach ($orders as $order) {
            $usefulData[] = [
                $order->order_reference,
                $order->order_status,
                $order->pharmacy->pharmacy_name,
                $order->order_amount,
                $order->order_source,
                Carbon::parse($order->created_at)->format(app('global_format_date_hour_minute_export')),
                $order->pharmacy->pharmacy_cip13,
                $order->pharmacy->pharmacy_city,
                'NoName',
                $order->pharmacy->pharmacy_zipcode,
                Carbon::parse($order->order_sent_to_nomane_date)->format(app('global_format_date_hour_minute_export'))
            ];
        }

        $orders = OrderCagedim::where('order_sent_to_nomane_date', '>=', Carbon::now()->startOfMonth()->subMonths(3)->format(app('global_format_date') . ' 00:00:00'))
                        ->where('order_sent_to_nomane', '=', 1)
                ->get();

        foreach ($orders as $order) {
            if (isset($order->pharmacy_cip)){
                $usefulData[] = [
                    $order->customer_po,
                    'Exported',
                    $order->pharmacy_cip->pharmacy_name,
                    $order->getTotal(),
                    'PharmaML',
                    Carbon::parse($order->created_at)->format(app('global_format_date_hour_minute_export')),
                    $order->pharmacy_cip->pharmacy_cip13,
                    $order->pharmacy_cip->pharmacy_city,
                    'NoName',
                    $order->pharmacy_cip->pharmacy_zipcode,
                    Carbon::parse($order->order_sent_to_nomane_date)->format(app('global_format_date_hour_minute_export'))
                ];
            }
        }
        GenerateHelper::generateExcelQuarterlyActivityReporting($usefulData, $filename);

        $file_status_update = FileStatus::find($file_status->id);
        $file_status_update->file_status_status = "Process ended";
        $file_status_update->save();

        return $usefulData;
    }

    public static function processBlockedOrdersOut($folder) {

        NomaneHelper::UsersActions();

        $sentOrdersCount = 0;

        $filename = 'Daily_Activity_Reporting.xlsx';
        $filename_daily = str_replace('./', '', NomaneHelper::insertCurrentDateBeforeExtension($filename) );
        $file_save = str_replace('./', '', NomaneHelper::insertCurrentDateBeforeExtension($filename) );

        $file_status = new FileStatus();
        $file_status->file_status_filename = $file_save;
        $file_status->file_status_status = "Starting Out process";
        $file_status->file_status_source = "NoName";
        $file_status->file_status_process = "Out";
        $file_status->file_status_type = "blockedOrdersOut";
        $file_status->save();

        $usefulData[] = ['Référence commande', 'Statut', 'Nom de la pharmacie', 'Total', 'Source', 'Date de commande', 'CIP pharmacie', 'SAP pharmacie',
                             'Ville de la pharmacie', 'Nom de la campagne', 'CP', 'Date d\'export'];

/*
I forgot a control time, the blocked and sent order file leaves at 1pm, 3pm and 9.30pm.

- 13h : orders sent between 21h30 and 13h
-3pm: orders sent between 1pm and 3pm
- 21h30 : orders sent between 15h and 21h30

today we sent 2 orders at 13:00:04, but they only appeared in the order report file sent
after 15h and not in the one they received at 13h05xD

 */
        if (Carbon::now()->format('H') < '14' ){
            $data_from = Carbon::now()->subDay()->format(app('global_format_date') . ' 21:35:00');
            $data_to = Carbon::now()->format(app('global_format_date') . ' 13:05:00');
        } else {
            $data_from = Carbon::now()->format(app('global_format_date') . ' 13:05:00');
            $data_to = Carbon::now()->format(app('global_format_date') . ' 21:35:00');
        }

        $data_send_from = Carbon::now()->subDay()->format(app('global_format_date'));
        $data_send_to = Carbon::now()->format(app('global_format_date'));

        $orders = Order::whereBetween('orders.order_sent_to_nomane_date', [$data_from, $data_to])
            ->whereBetween('orders.updated_at', [$data_from, $data_to])
            ->where('orders.order_status', Order::EXPORTED)
            ->get();

        foreach ($orders as $order) {

            $sentOrdersCount +=1 ;

            $usefulData[] = [
                $order->order_reference,
                $order->order_status,
                $order->pharmacy->pharmacy_name,
                number_format($order->order_amount, 2, '.', ''),
                $order->order_source,
                Carbon::parse($order->created_at)->format(app('global_format_date_hour_minute_export')),
                $order->pharmacy->pharmacy_cip13,
                $order->pharmacy->pharmacy_sap_id,
                $order->pharmacy->pharmacy_city,
                'NoName',
                $order->pharmacy->pharmacy_zipcode,
                $order->order_sent_to_nomane_date
            ];
        }

        $orders = OrderCagedim::whereBetween('order_sent_to_nomane_date', [$data_from, $data_to])
                ->where('order_block_code', '=', '')
                ->whereNotNull('order_sent_to_nomane_date')
                ->where('order_sent_to_nomane', '=', 1)
                ->get();

        foreach ($orders as $order) {
            if (isset($order->pharmacy_cip)){
            $sentOrdersCount +=1 ;

                $usefulData[] = [
                    $order->customer_po,
                    'Exported',
                    $order->pharmacy_cip->pharmacy_name,
                    number_format($order->getTotal(), 2, '.', ''),
                    'PharmaML',
                    Carbon::parse($order->created_at)->format(app('global_format_date_hour_minute_export')),
                    $order->pharmacy_cip->pharmacy_cip13,
                    $order->pharmacy_cip->pharmacy_sap_id,
                    $order->pharmacy_cip->pharmacy_city,
                    'NoName',
                    $order->pharmacy_cip->pharmacy_zipcode,
                    $order->order_sent_to_nomane_date
                ];
            }
        }

        $orderSentReport = GenerateHelper::generateExcelWeeklyActivityForBlockedOrders($usefulData);

        $orders = Order::where('order_sent_to_nomane', 0)
                      ->whereIn('order_status', [Order::BLOCKED])
                      ->with(['pharmacy'])
                      ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Establecer los encabezados
        $headers = [
            'Num commande',
            'Nom de la pharmacie',
            'CIP client',
            'Code SAP',
            'Statut Code SAP',
            'Motif Initial',
            'Date de la commande',
            'Montant de la commande',
            'Purchase Order Type'
        ];
        foreach ($headers as $columnIndex => $header) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex + 1);
            $sheet->setCellValue($column . '1', $header);
        }

        $row = 2;
        foreach ($orders as $order) {
            //$block_reason = self::determineBlockReason($order->pharmacy);
            $rowData = [
                $order->order_reference,
                $order->pharmacy->pharmacy_name ?: ExctractHelper::getPharmacyNameFromHistoric($order->pharmacy->id),
                $order->pharmacy->pharmacy_cip13,
                $order->pharmacy->pharmacy_sap_id,
                $order->pharmacy->pharmacy_account_status,
                $order->order_block_reason,
                $order->updated_at->format('d-m-Y H:i:s'),
                number_format($order->order_amount, 2, '.', ''),
                '0026'
            ];

            foreach ($rowData as $columnIndex => $value) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex + 1);
                //$sheet->setCellValue($column . $row, $value);

                //if (is_numeric($value)) {
                    $sheet->setCellValueExplicit(
                        $column . $row,
                        $value,
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                    );
                //} else {
                //    $sheet->setCellValue($column . $row, $value);
                //}
            }
            $row++;
        }

        $orders_cagedim = OrderCagedim::where('order_sent_to_nomane', 0)
                      ->whereNotIn('order_block_code', ['Cancelled', '', 'On hold'])
                      //->where('order_block_code', '!=', 'On hold')
                      ->get();

        foreach ($orders_cagedim as $order) {
            //$block_reason = self::determineBlockReason($order->pharmacy);
            if (isset($order->pharmacy_cip)){
                $rowData = [
                    $order->customer_po,
                    $order->pharmacy_cip->pharmacy_name ?: ExctractHelper::getPharmacyNameFromHistoric($order->pharmacy->id),
                    $order->pharmacy_cip->pharmacy_cip13,
                    $order->pharmacy_cip->pharmacy_sap_id,
                    $order->pharmacy_cip->pharmacy_account_status,
                    $order->order_block_code,
                    $order->updated_at->format('d-m-Y H:i:s'),
                    number_format($order->getTotal(), 2, '.', ''),
                    '0029'
                ];

                foreach ($rowData as $columnIndex => $value) {
                    $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex + 1);
                    //$sheet->setCellValue($column . $row, $value);

                    //if (is_numeric($value)) {
                        $sheet->setCellValueExplicit(
                            $column . $row,
                            $value,
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                    //} else {
                    //    $sheet->setCellValue($column . $row, $value);
                    //}
                }
                $row++;
            }
        }

        // Aplicar formato
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Formato de encabezados
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0070C0']
            ]
        ]);

        // Formato para la columna de monto (formato numérico con 2 decimales)
        $sheet->getStyle('H2:H' . $lastRow)->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        // Auto-ajustar columnas
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Generar nombre del archivo
        $timestamp = date('Y-m-d_His');
        $baseFileName = "Rapport_des_commandes_bloquees_{$timestamp}.xlsx";
        $fileName = "blockedOrdersOut/" . $baseFileName;

        // Crear archivo temporal
        $tempPath = storage_path("app/private/noName/temp/{$baseFileName}");

        // Asegurarse de que el directorio temporal existe
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $fileContent = file_get_contents($tempPath);

        // Guardar el contenido en el disco nomane_ftp_out_folders
        //Storage::disk('nomane_ftp_out_folders')->put($fileName, $fileContent);

        // Enviar email con el archivo adjunto

        $totalOrderCount = $orders->count() + $orders_cagedim->count();

        Mail::to(env('EMAIL_FOR_APP_ORDER'))
/*
            ->cc([
                'malika.bouallel@noName.com',
                'alina.velicu@noName.com',
                'valerie.gattelet-un@noName.com',
                'sylvie.tiber@noName.com',
                'pascal.dury@noName.com',
                'silvere.chapin@noName.com',
                'adel.boukraa@noName.com',
                'Marilyn.Gayffier@noName.com',
                'Sylvain.BERGERON@noName.com'
            ])*/
            ->send(new BlockedOrdersReportMail($tempPath, $baseFileName, $totalOrderCount, $orderSentReport, $filename_daily, $sentOrdersCount));

        // Eliminar el archivo temporal
        unlink($tempPath);

        // Registrar el estado del archivo
        $file_status->file_status_status = "Process completed";
        $file_status->file_status_type = "blockedOrders";
        $file_status->save();

        // Marcar órdenes como enviadas
        //foreach ($orders as $order) {
            //$order->order_sent_to_nomane = 1;
            //$order->save();
        //}

        return [
            'success' => true,
            'file' => $fileName,
            'orders_count' => $orders->count()
        ];
    }

    public static function processProductAudit($folder) {
        NomaneHelper::UsersActions();

        // Obtener los cambios no enviados a NoName
        $changes = ProductHistoric::where('product_historic_sent_to_nomane', 0)
            ->with(['product' => function($query) {
                $query->with('category'); // Asumiendo que existe una relación con categoría para gamme/sous_gamme
            }])
            ->get();

        if ($changes->isEmpty()) {
            return [
                'success' => true,
                'message' => 'No hay cambios pendientes de enviar',
                'changes_count' => 0
            ];
        }

        // Preparar los datos para el CSV
        $csvData = [];

        // Añadir encabezados
        $csvData[] = [
            'PRODUIT_CIP13',
            'PRODUIT_LIBELLE',
            'GAMME',
            'SOUS_GAMME',
            'DISPO_RESPONSE_LIBELLE',
            'MIN_CONTROL',
            'MAX_CONTROL',
            'MULTIPLE_CONTROL',
            'MOTIF'
        ];

        // Añadir los datos de cada cambio
        foreach ($changes as $change) {
            if ($change->product) {
                $csvData[] = [
                    $change->product->product_cip13,
                    $change->product->product_name,
                    $change->product->category->category_name ?? '', // Asumiendo que existe esta relación
                    $change->product->category->subcategory_name ?? '', // Asumiendo que existe esta relación
                    $change->product_historic_new_value, // El nuevo estado
                    $change->product->product_min_order ?? '',
                    $change->product->product_max_order ?? '',
                    $change->product->product_bundle_quantity ?? '',
                    'Motif: ' . self::getMotifMessage($change->product_historic_field_name, $change->product_historic_old_value, $change->product_historic_new_value)
                ];
            }
        }

        // Generar nombre del archivo
        $timestamp = date('Y-m-d_His');
        $baseFileName = "Audit_modifications_produits_{$timestamp}.csv";
        $fileName = "productAudit/" . $baseFileName;

        // Crear archivo temporal
        $tempPath = storage_path("app/private/noName/temp/{$baseFileName}");

        // Asegurarse de que el directorio temporal existe
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }

        // Crear el archivo CSV
        $file = fopen($tempPath, 'w');

        // Establecer el delimitador como punto y coma (;) y codificación UTF-8 con BOM
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // Añadir BOM para UTF-8
        foreach ($csvData as $row) {
            fputcsv($file, $row, ';');
        }
        fclose($file);

        // Leer el contenido del archivo temporal
        $fileContent = file_get_contents($tempPath);

        // Guardar el contenido en el disco nomane_ftp_out_folders
        Storage::disk('nomane_ftp_out_folders')->put($fileName, $fileContent);

        // Eliminar el archivo temporal
        unlink($tempPath);

        // Registrar el estado del archivo
        $file_status = new FileStatus();
        $file_status->file_status_filename = $baseFileName;
        $file_status->file_status_status = "Process completed";
        $file_status->file_status_source = "NoName";
        $file_status->file_status_process = "Out";
        $file_status->file_status_type = "productAudit";
        $file_status->save();

        // Marcar los cambios como enviados
        foreach ($changes as $change) {
            $change->product_historic_sent_to_nomane = 1;
            $change->save();
        }

        return [
            'success' => true,
            'file' => $fileName,
            'changes_count' => count($changes)
        ];
    }

    private static function checkChangesInFields($changes) {
        $fieldsToCheck = [
            // Campos de identidad
            'pharmacy_siren',
            'pharmacy_siret',
            'pharmacy_holder_name',

            // Campos de dirección principal
            'pharmacy_name4',
            'pharmacy_address_street',
            'pharmacy_address_address1',
            'pharmacy_address_address2',
            'pharmacy_address_address3',
            'pharmacy_city',
            'pharmacy_district',
            'pharmacy_region',
            'pharmacy_country',
            'pharmacy_zipcode',

            // Campos de dirección postal (PO Box)
            'pharmacy_po_box',
            'pharmacy_po_box_city',
            'pharmacy_po_box_region',
            'pharmacy_po_box_country',
            'pharmacy_po_box_zipcode',

            // Campos bancarios
            'pharmacy_bank_name',
            'pharmacy_iban',
            'pharmacy_bank_code',
            'pharmacy_account_number',
            'pharmacy_guichet_code',
            'pharmacy_rib'
        ];

        $changedFields = [];

        foreach ($fieldsToCheck as $field) {
            if (is_array($changes)){
                foreach ($changes as $change) {
                    if ($field == $change['pharmacy_historic_filed_name']){
                        $changedFields[$field] = [
                            'old' => $change['pharmacy_historic_old_value'] ?? null,
                            'new' => $change['pharmacy_historic_new_value'] ?? null
                        ];
                    }
                }
            }
        }

        return [
            'hasChanges' => !empty($changedFields),
            'changes' => $changedFields,
            'categories' => [
                'hasSirenChanges' => self::hasSirenChanges($changedFields),
                'hasHolderChanges' => self::hasHolderChanges($changedFields),
                'hasAddressChanges' => self::hasAddressChanges($changedFields),
                'hasPoBoxChanges' => self::hasPoBoxChanges($changedFields),
                'hasBankChanges' => self::hasBankChanges($changedFields)
            ]
        ];
    }

    private static function hasSirenChanges($changes) {
        $identityFields = [
            'pharmacy_siren'
        ];
        return self::hasChangesInFields($changes, $identityFields);
    }

    private static function hasHolderChanges($changes) {
        $identityFields = [
            'pharmacy_holder_name'
        ];
        return self::hasChangesInFields($changes, $identityFields);
    }

    private static function hasAddressChanges($changes) {
        $addressFields = [
            'pharmacy_address_street',
            'pharmacy_address_address1',
            'pharmacy_address_address2',
            'pharmacy_address_address3',
            'pharmacy_city',
            'pharmacy_district',
            'pharmacy_region',
            'pharmacy_country',
            'pharmacy_zipcode'
        ];
        return self::hasChangesInFields($changes, $addressFields);
    }

    private static function hasPoBoxChanges($changes) {
        $poBoxFields = [
            'pharmacy_po_box',
            'pharmacy_po_box_city',
            'pharmacy_po_box_region',
            'pharmacy_po_box_country',
            'pharmacy_po_box_zipcode'
        ];
        return self::hasChangesInFields($changes, $poBoxFields);
    }

    private static function hasBankChanges($changes) {
        $bankFields = [
            'pharmacy_bank_name',
            'pharmacy_iban',
            'pharmacy_bank_code',
            'pharmacy_account_number',
            'pharmacy_guichet_code',
            'pharmacy_rib'
        ];
        return self::hasChangesInFields($changes, $bankFields);
    }

    private static function hasChangesInFields($changes, $fields) {
        foreach ($fields as $field) {
            if (array_key_exists($field, $changes)) {
                return true;
            }
        }
        return false;
    }

    private static function getMotifMessage($fieldName, $oldValue, $newValue): string
    {
        switch ($fieldName) {
            case 'product_status':
                return "Changement de statut: {$oldValue} -> {$newValue}";
            case 'product_unit_price':
                return "Modification du prix unitaire: {$oldValue}€ -> {$newValue}€";
            case 'product_unit_price_pght':
                return "Modification du prix PGHT: {$oldValue}€ -> {$newValue}€";
            case 'product_min_order':
                return "Modification de la quantité minimum: {$oldValue} -> {$newValue}";
            case 'product_max_order':
                return "Modification de la quantité maximum: {$oldValue} -> {$newValue}";
            case 'product_bundle_quantity':
                return "Modification du multiple de commande: {$oldValue} -> {$newValue}";
            case 'product_quote':
                return "Modification du quota: {$oldValue} -> {$newValue}";
            case 'product_allocation':
                return "Modification de l'allocation: {$oldValue} -> {$newValue}";
            case 'product_active':
                $oldStatus = $oldValue ? 'Actif' : 'Inactif';
                $newStatus = $newValue ? 'Actif' : 'Inactif';
                return "Changement de l'état d'activation: {$oldStatus} -> {$newStatus}";
            case 'product_sell_from_date':
                return "Modification de la date de début de vente: {$oldValue} -> {$newValue}";
            case 'product_sell_to_date':
                return "Modification de la date de fin de vente: {$oldValue} -> {$newValue}";
            case 'product_short_term':
                $oldStatus = $oldValue ? 'Oui' : 'Non';
                $newStatus = $newValue ? 'Oui' : 'Non';
                return "Modification du statut court terme: {$oldStatus} -> {$newStatus}";
            case 'product_expiration_date':
                return "Modification de la date d'expiration: {$oldValue} -> {$newValue}";
            case 'product_name':
                return "Modification du nom du produit: {$oldValue} -> {$newValue}";
            case 'product_presentation':
                return "Modification de la présentation: {$oldValue} -> {$newValue}";
            case 'product_box_quantity':
                return "Modification de la quantité par boîte: {$oldValue} -> {$newValue}";
            default:
                return "Modification du champ {$fieldName}: {$oldValue} -> {$newValue}";
        }
    }

    public static function determineBlockReason($pharmacy)
    {
        if ($pharmacy->pharmacy_new_pharmacy) {
            return 'Nouvelle pharmacie';
        }

        // Obtener los cambios históricos de la farmacia
        $historicChanges = PharmacyHistoric::where('pharmacy_historic_pharmacy_id', $pharmacy->id)
            ->where('pharmacy_historic_sent_to_nomane', 0)
            ->get();

        if ($historicChanges->isEmpty()) {
            return 'Modification des informations';
        }

        $addressFields = [
            'pharmacy_address_street',
            'pharmacy_address_address1',
            'pharmacy_address_address2',
            'pharmacy_address_address3',
            'pharmacy_zipcode',
            'pharmacy_city'
        ];

        $bankFields = [
            'pharmacy_bank_name',
            'pharmacy_iban',
            'pharmacy_bank_code',
            'pharmacy_account_number',
            'pharmacy_guichet_code',
            'pharmacy_rib'
        ];

        $identityFields = [
            'pharmacy_sap_id',
            'pharmacy_name',
            'pharmacy_cip13',
            'pharmacy_siren',
            'pharmacy_siret',
            'pharmacy_holder_name'
        ];

        $hasAddressChanges = false;
        $hasBankChanges = false;
        $hasIdentityChanges = false;
        $hasOtherChanges = false;

        foreach ($historicChanges as $change) {
            $fieldName = $change->pharmacy_historic_filed_name;

            if (in_array($fieldName, $addressFields)) {
                $hasAddressChanges = true;
            } else if (in_array($fieldName, $bankFields)) {
                $hasBankChanges = true;
            } else if (in_array($fieldName, $identityFields)) {
                $hasIdentityChanges = true;
            } else {
                $hasOtherChanges = true;
            }
        }

        $reasons = [];

        if ($hasIdentityChanges) {
            $reasons[] = 'identité';
        }

        if ($hasAddressChanges) {
            $reasons[] = 'adresse';
        }

        if ($hasBankChanges) {
            $reasons[] = 'coordonnées bancaires';
        }

        if ($hasOtherChanges) {
            $reasons[] = 'autres informations';
        }

        if (empty($reasons)) {
            return 'Modification des informations';
        }

        return 'Modification: ' . implode(', ', $reasons);
    }

    public static function determineBlockReasonSimple($pharmacy)
    {
        if ($pharmacy->pharmacy_new_pharmacy) {
            return 'Nouvelle pharmacie';
        }

        // Obtener los cambios históricos de la farmacia
        $historicChanges = PharmacyHistoric::where('pharmacy_historic_pharmacy_id', $pharmacy->id)
            ->where('pharmacy_historic_sent_to_nomane', 0)
            ->get();

        if ($historicChanges->isEmpty()) {
            return 'Modification des informations';
        }

        $addressFields = [
            'pharmacy_address_street',
            'pharmacy_address_address1',
            'pharmacy_address_address2',
            'pharmacy_address_address3',
            'pharmacy_zipcode',
            'pharmacy_city'
        ];

        $bankFields = [
            'pharmacy_bank_name',
            'pharmacy_iban',
            'pharmacy_bank_code',
            'pharmacy_account_number',
            'pharmacy_guichet_code',
            'pharmacy_rib'
        ];

        $identityFields = [
            'pharmacy_sap_id',
            'pharmacy_name',
            'pharmacy_name2',
            'pharmacy_name3',
            'pharmacy_name4',
            'pharmacy_cip13',
            'pharmacy_siret',
            'pharmacy_holder_name'
        ];

        $hasAddressChanges = false;
        $hasBankChanges = false;
        $hasIdentityChanges = false;
        $hasSirenChanged = false;
        $hasOtherChanges = false;

        foreach ($historicChanges as $change) {
            $fieldName = $change->pharmacy_historic_filed_name;

            if ($fieldName === 'pharmacy_siren') {
                $hasSirenChanged = true;
            } else if (in_array($fieldName, $addressFields)) {
                $hasAddressChanges = true;
            } else if (in_array($fieldName, $bankFields)) {
                $hasBankChanges = true;
            } else if (in_array($fieldName, $identityFields)) {
                $hasIdentityChanges = true;
            } else {
                $hasOtherChanges = true;
            }
        }

        $reasons = [];

        if ($hasSirenChanged) {
            $reasons[] = 'SIREN';
        }

        if ($hasIdentityChanges) {
            $reasons[] = 'ID';
        }

        if ($hasAddressChanges) {
            $reasons[] = 'ADDRESS';
        }

        if ($hasBankChanges) {
            $reasons[] = 'BANK';
        }

        if ($hasOtherChanges) {
            $reasons[] = 'OTHER';
        }

        if (empty($reasons)) {
            return null;
        }

        return $reasons;
        return 'Modification: ' . implode(', ', $reasons);
    }

}





