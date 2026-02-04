<?php

namespace App\Helpers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\InfoMail;
use App\Models\Product;
use App\Models\Pharmacy;
use App\Models\FileStatus;
use App\Models\ApiCallCronJob;
use App\Helpers\FileProcessHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\ProductThresholdPrice;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Exception\RequestException;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class NomaneHelper {
    public static function getInFiles ($folder){

        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        ini_set('memory_limit', '1000M');

        $files_processed = [];
        $return = [];

        if ($folder == "cagedimOrders") {
            $files = Storage::disk('nomane_ftp_in_folders')->files('PharmaML');
        } else if ($folder == "pharmacies") {
            $files = Storage::disk('nomane_ftp_in_folders')->files('Customer_Master');
        } else if ($folder == "cancelledOrders") {
            $files = Storage::disk('nomane_ftp_in_folders')->files('order_cancellation');
        } else {
            $files = Storage::disk('nomane_ftp_in_folders')->files($folder);
        }

        foreach ($files as $file) {

            $files_processed[] = $file;

            $file_status = new FileStatus();
            $file_status->file_status_filename = $file;
            $file_status->file_status_status = "Starting In process";
            $file_status->file_status_source = "NoName";
            $file_status->file_status_process = "In";
            $file_status->file_status_type = $folder;
            $file_status->save();

            if ($folder == "cagedimOrders") {
                $contents = Storage::disk('nomane_ftp_in_folders')->get($file);
            } else if ($folder == "pharmacies") {
                $contents = Storage::disk('nomane_ftp_in_folders')->get($file);
            } else {
                $contents = Storage::disk('nomane_ftp_in_folders')->get($file);
            }

            // Do stuff
            if ($folder == "pharmacies") {
                $return = FileProcessHelper::processPharmaciesIn($contents);
            } else if ($folder == "products") {
                $return = FileProcessHelper::processProductsIn($contents, $file);
            } else if ($folder == "tradePolicy") {
                $return = FileProcessHelper::processTradePolicyIn($contents, $file);
            } else if ($folder == "customerSanitation") {
                $return = FileProcessHelper::processCustomerSanitationIn($contents, $file);
            //} else if ($folder == "comercialConditions") {
            //    $return = FileProcessHelper::processComercialConditions($contents, $file);
            } else if ($folder == "productControlFile") {
                $return = FileProcessHelper::processProductControlFileIn($contents, $file);
            } else if ($folder == "priceControlFile") {
                $return = FileProcessHelper::processPriceControlFileIn($contents, $file);
            } else if ($folder == "procesUnavailableProducts") {
                $return = FileProcessHelper::processUnavailableProductsIn($contents, $file);
            } else if ($folder == "productsBackToStock") {
                $return = FileProcessHelper::processProductsBackToStockIn($contents, $file);
            } else if ($folder == "shortTermProducts") {
                $return = FileProcessHelper::processShortTermProductsIn($contents, $file);
            } else if ($folder == "productQuotes") {
                $return = FileProcessHelper::processProductQuotesIn($contents, $file);
            } else if ($folder == "blockedOrdersIn") {
                $return = FileProcessHelper::processBlockedOrdersIn($contents, $file);
            } else if ($folder == "cagedimOrders") {
                $return = FileProcessHelper::processCagedimOrdersIn($contents, $file);
            } else if ($folder == "pharmaMlOrders") {
                $return = FileProcessHelper::processPharmaMlOrdersIn($contents, $file);
            } else if ($folder == "productsUnderAllocation") {
                $return = FileProcessHelper::processProductsUnderAllocationIn($contents, $file);
            } else if ($folder == "cancelledOrders") {
                $return = FileProcessHelper::processCancelledOrdersIn($contents, $file);
            }

            $org_file = $file;
            $file = str_replace($folder."/", "", $file);

            $file_save = str_replace('./', '', NomaneHelper::insertCurrentDateBeforeExtension($file) );

            Storage::disk('nomane_ftp_in_folders')->copy($org_file, 'in/' . $folder."/processed/".$file_save);
            Log::info('Pharmacy test :');
            // ToDo: Take care
            //Storage::disk('nomane_ftp_in_folders')->delete($org_file);

            $file_status_update = FileStatus::find($file_status->id);
            $file_status_update->file_status_status = "Process ended";
            $file_status_update->save();
        }

        return $return;
        //return response()->json(['folder' =>  $folder, 'files_processed' => $files_processed], ( count($files_processed) ) ? 200 : 204);
    }

    public static function doOutFiles ($folder){
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        if ($folder == "productIntegrationCheck") {
            $return = FileProcessHelper::processProductIntegrationCheckOut($folder);
        } else if ($folder == "ordersSentToNomane") {
            $return = FileProcessHelper::processOrdersSentToNomaneOut($folder);
        } else if ($folder == "newCustomerOrChange") {
            $return = FileProcessHelper::processNewCustomerOrChangeOut($folder);
//        } else if ($folder == "ordersExportedToNomane") {
//            $return = FileProcessHelper::processOrdersExportedToNomaneOut($folder);
        } else if ($folder == "rollingOrderHistory") {
            $return = FileProcessHelper::processRollingOrderHistoryOut($folder);
        } else if ($folder == "weeklyOrderConfirmations") {
            $return = FileProcessHelper::processWeeklyOrderConfirmationsOut($folder);
        } else if ($folder == "monthlyActivityReporting") {
            $return = FileProcessHelper::processMonthlyActivityReportingOut($folder);
        } else if ($folder == "quarterlyActivityReporting") {
            $return = FileProcessHelper::processQuarterlyActivityReportingOut($folder);
        } else if ($folder == "blockedOrdersOut") {
            $return = FileProcessHelper::processBlockedOrdersOut($folder);
        } else if ($folder == "productAudit") {
            $return = FileProcessHelper::processProductAudit($folder);
        }

        return $return;
        //return response()->json(['folder' =>  $folder, 'files_processed' => $files_processed], ( count($files_processed) ) ? 200 : 204)
    }

    public static function UsersActions (){
        if(Auth::check()){
            $backtrace = debug_backtrace();
            Log::debug(Auth::user()->name . ' accesed: ' .  str_replace('App\\Http\\Controllers\\', '', $backtrace[1]['class']) . ' - Function: ' . $backtrace[1]['function'] );
        } else {
            $backtrace = debug_backtrace();
            Log::debug('No user logged accesed: ' .  str_replace('App\\Http\\Controllers\\', '', $backtrace[1]['class']) . ' - Function: ' . $backtrace[1]['function'] );
        }
    }

    public static function generateSecurePassword($length = 12)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@$!%*?&';
        $password = '';
        $charactersLength = strlen($characters);

        // Ensure the password contains at least one lowercase letter, one uppercase letter, one digit, and one special character
        $password .= $characters[rand(0, 25)]; // Lowercase letter
        $password .= $characters[rand(26, 51)]; // Uppercase letter
        $password .= $characters[rand(52, 61)]; // Digit
        $password .= $characters[rand(62, strlen($characters) - 1)]; // Special character

        // Fill the rest of the password length with random characters
        for ($i = 4; $i < $length; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }

        // Shuffle the password to ensure randomness
        return str_shuffle($password);
    }

    public static function ldapSync (){

        $ldapUsers = LdapUser::get();

        $ldapUsers = LdapUser::where('objectClass', '=', 'user')
        ->where('objectCategory', '=', 'person')
        ->get();

        foreach ($ldapUsers as $ldapUser) {

            LOG::debug('ldapUser');
            LOG::debug($ldapUser);

            $user = User::where('email', $ldapUser->mail[0])->first();

            if ($user){
                $user->name = $ldapUser->name[0];
                $user->samaccountname = $ldapUser->samaccountname[0];
                $user->objectguid = $ldapUser->getConvertedGuid();
                $user->active = 1;
                $user->save();
            } else {
                $tempPassword = NomaneHelper::generateSecurePassword(12);

                $user = User::create([
                    'name' => $ldapUser->name[0],
                    'email' => $ldapUser->mail[0],
                    'samaccountname' => $ldapUser->samaccountname[0],
                    'objectguid' => $ldapUser->getConvertedGuid(),
                    'password' => $tempPassword,
                    'active' => 1,
                    'is_admin' => 0,
                    'user_type' => 'Call',
                ]);
            }
        }
        return $ldapUsers;
    }

    public static function extractNumbers($string)
    {
        preg_match_all('/\d+/', $string, $matches);
        return $matches[0];
    }

    public static function processProductDiscountThresholdFromPriceControl($line, $product)
    {
        if (isset($line[1]) && $line[1] != '') {
            $product_price = $line[3];

            $product = Product::find($product->id);
            //$product->product_unit_price = $product_price;
            $product->product_unit_price_pght = $product_price;
            $product->save();

            ProductThresholdPrice::where('product_threshold_price_product_id', '=', $product->id)->delete();

            $thresholds = [
                ['from' => 5, 'to' => 7, 'discount' => 6, 'level' => 1],
                ['from' => 7, 'to' => 9, 'discount' => 8, 'level' => 2],
                ['from' => 9, 'to' => 11, 'discount' => 10, 'level' => 3],
                ['from' => 11, 'to' => 13, 'discount' => 12, 'level' => 4],
                ['from' => 13, 'to' => null, 'discount' => 14, 'level' => 5],
            ];

            foreach ($thresholds as $threshold) {
                if (isset($line[$threshold['from']]) && $line[$threshold['from']] != '') {
                    $from = intval($line[$threshold['from']]);
                    $to = (isset($line[$threshold['to']]) && $line[$threshold['to']] != "" ) ? intval($line[$threshold['to']]) - 1 : null;
                    $discount = $line[$threshold['discount']];
                    $price_threshold = $product_price * $discount / 100;

                    // ToDo: This by now seems not used, and the file not indicates if premium or standard

                    ProductThresholdPrice::create([
                        'product_threshold_price_product_id' => $product->id,
                        'product_threshold_price_level' => $threshold['level'],
                        'product_threshold_price_threshold_from' => $from,
                        'product_threshold_price_threshold_to' => $to,
                        'product_threshold_price_price' => round($price_threshold, 2),
                        'product_threshold_price_discount' => $discount,
                    ]);
                }
            }
        }
    }

    public static function processProductDiscountThresholdFromTradePolicy($offre, $line, $sheet, $spreadsheet, $key, $priceColumn, $thresholdColumn, $discountColumn, $productId, $levelDelete) {

        // JUJU

        if (isset($line[$priceColumn]) && $line[$priceColumn] != '') {
            $productsUpdated = [];
            $productsUpdated_ids = [];
            if ($priceColumn == 'N'){
                $thresholdLevel = 1;
            } else if ($priceColumn == 'Q'){
                $thresholdLevel = 2;
            } else if ($priceColumn == 'T'){
                $thresholdLevel = 3;
            } else {
                $thresholdLevel = 0;
            }

            // Price from the thresold column price
            $cell = $sheet->getCell($priceColumn . ($key + 1));
            $calculation = Calculation::getInstance($spreadsheet);
            $product_price_threshold = $calculation->calculateCellValue($cell);

            $product_price_threshold = round($product_price_threshold, 2);

            if (strpos($line[$thresholdColumn], '≥') !== false) {
                $temp_val = str_replace('≥ ', '', $line[$thresholdColumn]);
                $same_max_threshold = null;
            } else if (strlen($line[$thresholdColumn]) == 1) {
                $temp_val = $line[$thresholdColumn];
                $same_max_threshold = $temp_val;
            } else if($line[$thresholdColumn] == '' || $line[$thresholdColumn] == 'Si <800€ de Biosimilaires' || $line[$thresholdColumn] == 'Si >800€ de Biosimilaires') {
                if ($thresholdLevel == 1){
                    $temp_val = 200;
                    $same_max_threshold = 800;
                } else if ($thresholdLevel == 2){
                    $temp_val = 800;
                    $same_max_threshold = null;
                }
            } else {
                $temp_val = NomaneHelper::extractNumbers($line[$thresholdColumn]);
            }

            if ($line[$thresholdColumn] == 'NA') {
                return;
            }

            if (is_array($temp_val)) {
                $product_min_threshold = $temp_val[0];
                $product_max_threshold = (isset($temp_val[1])) ? $temp_val[1] : null;
            } else {
                $product_min_threshold = $temp_val;
                $product_max_threshold = $same_max_threshold;
            }
            $product_discount_threshold = $line[$discountColumn] * 100;

            $productTresold = ProductThresholdPrice::where('product_threshold_price_product_id', '=', $productId)
                                                    ->where('product_threshold_price_level', '=', $thresholdLevel)
                                                    ->get();



            if (isset($productTresold[0]->id)){
                $productTresold = ProductThresholdPrice::find($productTresold[0]->id);
                if ($offre == 'premium') {

                    if ( (string) $productTresold->product_threshold_price_threshold_from_premium != (string) $product_min_threshold
                        || ((string) $productTresold->product_threshold_price_threshold_to_premium != (string) $product_max_threshold )
                        || (string) $productTresold->product_threshold_price_price_premium != (string) $product_price_threshold
                        || (string) number_format($productTresold->product_threshold_price_discount_premium, 2) != (string) $product_discount_threshold
                        ){

                       /*
                        LOG::debug('productId');
                        LOG::debug($productId);

                        LOG::debug('product_min_threshold');
                        LOG::debug($product_min_threshold);
                        LOG::debug('productTresold[0]->product_threshold_price_threshold_from_premium');
                        LOG::debug($productTresold->product_threshold_price_threshold_from_premium);
                        LOG::debug('product_max_threshold');
                        LOG::debug($product_max_threshold);
                        LOG::debug('productTresold[0]->product_threshold_price_threshold_to_premium');
                        LOG::debug($productTresold->product_threshold_price_threshold_to_premium);
                        LOG::debug('product_price_threshold');
                        LOG::debug($product_price_threshold);
                        LOG::debug('productTresold[0]->product_threshold_price_price_premium');
                        LOG::debug($productTresold->product_threshold_price_price_premium);
                        LOG::debug('product_discount_threshold');
                        LOG::debug($product_discount_threshold);
                        LOG::debug('productTresold[0]->product_threshold_price_discount_premium');
                        LOG::debug($productTresold->product_threshold_price_discount_premium);
                        LOG::debug('############################################################');
                       */
                        $productsUpdated['update']['premium'][$productId] = $productId;
                        $productsUpdated_ids[$productId] = $productId;
                    }
                } else {
                    if ( (string) $productTresold->product_threshold_price_threshold_from != (string) $product_min_threshold
                        || ((string) $productTresold->product_threshold_price_threshold_to != (string) $product_max_threshold)
                        || (string) $productTresold->product_threshold_price_price != (string) $product_price_threshold
                        || (string) number_format($productTresold->product_threshold_price_discount, 2) != (string) number_format($product_discount_threshold, 2)
                        ){

/*
                        if ($productId == 96 ){
                            LOG::debug('entra comparacion standard level: ' . $thresholdLevel);
                            LOG::debug($productTresold->product_threshold_price_product_id);

                            LOG::debug('product_min_threshold');
                            LOG::debug($product_min_threshold);
                            LOG::debug('productTresold->product_threshold_price_threshold_from');
                            LOG::debug($productTresold->product_threshold_price_threshold_from);
                        }
                            */
                        /*
                        LOG::debug('product_max_threshold');
                        LOG::debug($product_max_threshold);
                        LOG::debug('productTresold[0]->product_threshold_price_threshold_to');
                        LOG::debug($productTresold->product_threshold_price_threshold_to);
                        LOG::debug('product_price_threshold');
                        LOG::debug($product_price_threshold);
                        LOG::debug('productTresold[0]->product_threshold_price_price');
                        LOG::debug($productTresold->product_threshold_price_price);
                        LOG::debug('product_discount_threshold');
                        LOG::debug(number_format($product_discount_threshold, 2));
                        LOG::debug('productTresold[0]->product_threshold_price_discount');
                        LOG::debug(number_format($productTresold->product_threshold_price_discount, 2));
*/
                        $productsUpdated['update']['standard'][$productId] = $productId;
                        $productsUpdated_ids[$productId] = $productId;

                    }
                }
            } else {
                if ($offre == 'premium') {
                    //$productsUpdated['new']['premium'][$productId] = $productId;
                    $productsUpdated['update']['premium'][$productId] = $productId;
                    $productsUpdated_ids[$productId] = $productId;
                } else {
                    //$productsUpdated['new']['standard'][$productId] = $productId;
                    $productsUpdated['update']['standard'][$productId] = $productId;
                    $productsUpdated_ids[$productId] = $productId;
                }
            }

            if ($offre == 'premium') {

                $check = ProductThresholdPrice::where('product_threshold_price_product_id', '=', $productId)
                    ->where('product_threshold_price_level', '=', $thresholdLevel)
                    ->get();

                if ( !count($check) ){
                    ProductThresholdPrice::updateOrCreate(
                        [
                            'product_threshold_price_product_id' => $productId,
                            'product_threshold_price_level' => $thresholdLevel,
                        ],
                        [
                            'product_threshold_price_threshold_from_premium' => $product_min_threshold,
                            'product_threshold_price_threshold_to_premium' => $product_max_threshold,
                            'product_threshold_price_price_premium' => $product_price_threshold,
                            'product_threshold_price_discount_premium' => $product_discount_threshold,
                            'product_threshold_price_threshold_from' => 0
                        ]
                    );
                } else {
                    ProductThresholdPrice::updateOrCreate(
                        [
                            'product_threshold_price_product_id' => $productId,
                            'product_threshold_price_level' => $thresholdLevel,
                        ],
                        [
                            'product_threshold_price_threshold_from_premium' => $product_min_threshold,
                            'product_threshold_price_threshold_to_premium' => $product_max_threshold,
                            'product_threshold_price_price_premium' => $product_price_threshold,
                            'product_threshold_price_discount_premium' => $product_discount_threshold
                        ]
                    );
                }

            } else {
                ProductThresholdPrice::updateOrCreate(
                    [
                        'product_threshold_price_product_id' => $productId,
                        'product_threshold_price_level' => $thresholdLevel,
                    ],
                    [
                        'product_threshold_price_threshold_from' => $product_min_threshold,
                        'product_threshold_price_threshold_to' => $product_max_threshold,
                        'product_threshold_price_price' => $product_price_threshold,
                        'product_threshold_price_discount' => $product_discount_threshold
                    ]
                );
            }

            $return['ids'] = $productsUpdated_ids;
            $return['prods'] = $productsUpdated ? $productsUpdated : null;

            //LOG::debug('productsUpdated_ids');
            //LOG::debug($productsUpdated_ids);
            //LOG::debug('productsUpdated');
            //LOG::debug($productsUpdated);
            //LOG::debug('return');
            //LOG::debug($return);


            return $return;
        } else {
            ProductThresholdPrice::where('product_threshold_price_product_id', '=', $productId)
                                ->where('product_threshold_price_level', '=',  $levelDelete)
                                ->delete();

            return null;
        }
    }

    public static function bindBuilderQuery($sqlArr){

        foreach($sqlArr as $key => $value) {
            $sqlTxt = str_replace('?', '%s', $value["query"]);
            $sqlTxt = sprintf($sqlTxt, ...$value["bindings"]);
            $num = $key + 1;
            //print_r($sqlTxt);
            //if (env('LOG_DEBUG'))
                NomaneHelper::DebuggerTxT("$num - $sqlTxt");
        }
    }

    public static function DebuggerTxT ($msg){
        echo("<HR>");
        echo($msg);
        echo("<HR>");
        //Log::debug($msg);
    }

    public static function insertCurrentDateBeforeExtension($file) {
        $currentDate = Carbon::now()->format(app('global_format_datetime_files'));

        $pathInfo = pathinfo($file);
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        $dirname = $pathInfo['dirname'];

        $newFilename = $filename . '-' . $currentDate . '.' . $extension;
        return $dirname . '/' . $newFilename;
    }

    public static function insertCurrentDateBeforeExtensionOrder($file) {
        $currentDate = Carbon::now()->format(app('global_format_for_orders'));

        $pathInfo = pathinfo($file);
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        $dirname = $pathInfo['dirname'];

        $newFilename = $filename . $currentDate . '.' . $extension;
        return $dirname . '/' . $newFilename;
    }

    public static function geneateIniqueStringWithPrefix($file) {
        return $file . Carbon::now()->format(app('global_format_for_random'));
    }

    public static function getModifiedFields(array $newData, Pharmacy $pharmacy): array
    {
        $differences = [];

        $fieldsAddress = [
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

        $fieldsToCompare = [
            'pharmacy_sap_id',
            'pharmacy_cip13',
            'pharmacy_type',
            'pharmacy_status',
            'pharmacy_name',
            'pharmacy_name2',
            'pharmacy_name3',
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
            'pharmacy_po_box',
            'pharmacy_po_box_city',
            'pharmacy_po_box_region',
            'pharmacy_po_box_country',
            'pharmacy_po_box_zipcode',
            'pharmacy_phone',
            'pharmacy_fax',
            'pharmacy_email',
            'pharmacy_holder_name',
            'pharmacy_bank_name',
            'pharmacy_iban',
            'pharmacy_bank_code',
            'pharmacy_account_number',
            'pharmacy_guichet_code',
            'pharmacy_rib',
            'pharmacy_siren',
            'pharmacy_siret'
        ];

        foreach ($fieldsToCompare as $field) {

            // Solo compara si el campo existe en los nuevos datos
            if (array_key_exists($field, $newData)) {
                $oldValue = $pharmacy->$field ?? '';
                $newValue = $newData[$field] ?? '';

                // Compara los valores, teniendo en cuenta valores null
                if ( ! array_key_exists($field, $fieldsAddress)) {
                    if (($oldValue !== $newValue) &&
                        !($oldValue === '' && $newValue === '') &&
                        !($oldValue === '' && $newValue === null)) {
                        $differences[$field] = [
                            'old' => $oldValue,
                            'new' => $newValue
                        ];
                    }
                } else {
                    if (($oldValue !== $newValue) &&
                        ($oldValue !== '')
                        ) {
                        $differences[$field] = [
                            'old' => $oldValue,
                            'new' => $newValue
                        ];
                    }
                }
            }
        }

        return $differences;
    }

    public static function getModifiedProductFields(array $newData, Product $product): array
    {
        $differences = [];

        $fieldsToCompare = [
            'product_sap_id',
            'product_cip13',
            'product_category_id',
            'product_name',
            'product_presentation',
            'product_unit_price',
            'product_unit_price_pght',
            'product_box_quantity',
            'product_bundle_quantity',
            'product_quote',
            'product_allocation',
            'product_min_order',
            'product_max_order',
            'product_active',
            'product_sell_from_date',
            'product_sell_to_date',
            'product_short_term',
            'product_expiration_date',
            'product_status'
        ];

        foreach ($fieldsToCompare as $field) {
            if (array_key_exists($field, $newData)) {
                $oldValue = $product->$field ?? '';
                $newValue = $newData[$field];

                if (($oldValue !== $newValue) &&
                    !($oldValue === '' && $newValue === '') &&
                    !($oldValue === '' && $newValue === null)) {
                    $differences[$field] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            }
        }

        return $differences;
    }

    public static function doApiCommandCall($url, $desc) {
        $startTime = microtime(true);
        $token = env('API_TOKEN');
        if (!$token) { throw new Exception('API token not found in environment variables'); }

        $timeout = env('CURL_MAX_EXECUTION_TIME', 3600);

        try {
            LOG::debug('successful 0');

            $response = Http::timeout($timeout)
                ->withToken($token)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->get(env('APP_URL_API') . $url);

            LOG::debug('successful 0.1: ' . $response->status());

            if ($response->successful()) {

                if ($url == '/in/pharmacies'){
                    LOG::debug('successful 1');
                }

                $data = $response->json();

                ApiCallCronJob::create([
                    'endpoint' => $url,
                    'method' => 'GET',
                    'status_code' => $response->status(),
                    'ip_address' => request()->ip(),
                    'duration_ms' => round((microtime(true) - $startTime) * 1000)
                ]);

                if ($desc != 'NO'){
                    $text = $desc . ' API call Successful';
                    Mail::to(env('EMAIL_FOR_INFO'))->send(new InfoMail($text));
                }
                return $data; // Retornamos los datos si la llamada fue exitosa
            } else {

                if ($url == '/in/pharmacies'){
                    LOG::debug('NO successful 1');
                }

                $data = $response->json();

                if ($url == '/in/pharmacies'){
                    LOG::debug('NO successful 2');
                    LOG::debug($data);
                    LOG::debug('Response status: ' . (string)$response->status());
                }

                ApiCallCronJob::create([
                    'endpoint' => $url,
                    'method' => 'GET',
                    'status_code' => $response->status(),
                    'error_message' => $data['message'] ? $data['message'] : 'No message',
                    'ip_address' => request()->ip(),
                    'duration_ms' => round((microtime(true) - $startTime) * 1000)
                ]);

                $msg = $data['message'] ? $data['message'] : 'No message';

                if ($url == '/in/pharmacies'){
                    LOG::debug('NO successful 3');
                }

                $text = $desc . ' API call - Ajax KO response - ' . $response->status() . ': ' . $msg;
                Mail::to(env('EMAIL_FOR_INFO'))->send(new InfoMail($text));
            }

            // Si llegamos aquí, la llamada no fue exitosa pero no lanzó excepción
            //throw new Exception('API call failed with status: ' . $response->status());

        } catch (Exception $e) {
            ApiCallCronJob::create([
                'endpoint' => $url,
                'method' => 'GET',
                'status_code' => 500,
                'error_message' => 'EXC: ' . $e->getMessage(),
                'ip_address' => request()->ip(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000)
            ]);

            $text = $desc . ' API call - EXC: Ajax KO exception - 500: ' . $e->getMessage();
            Mail::to(env('EMAIL_FOR_APP_ERROR'))->send(new InfoMail($text));
            //throw $e; // Relanzamos la excepción después de agotar los intentos
        }

    }
}





