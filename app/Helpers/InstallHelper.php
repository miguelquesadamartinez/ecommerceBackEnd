<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class InstallHelper {

    public static function chnageTimeStampsType (){
        DB::statement('ALTER TABLE users ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE users ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE pharmacies ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE pharmacies ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE categories ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE categories ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE products ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE products ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE orders ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE orders ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE orders ALTER COLUMN order_sent_to_nomane_date DATETIME2 NULL;');
        DB::statement('ALTER TABLE orders ALTER COLUMN order_desired_delivery_date DATETIME2 NULL;');
        DB::statement('ALTER TABLE order_details ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE order_details ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE product_units_sells ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE product_units_sells ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE product_threshold_prices ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE product_threshold_prices ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN expires_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN last_used_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE file_statuses ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE file_statuses ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE api_call_cron_jobs ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE api_call_cron_jobs ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE pharmacy_historics ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE pharmacy_historics ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE orders_cagedim ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE orders_cagedim ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE orders_cagedim ALTER COLUMN order_sent_to_nomane_date DATETIME2 NULL;');
        DB::statement('ALTER TABLE orders_cagedim_header_texts ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE orders_cagedim_header_texts ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE orders_cagedim_lines ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE orders_cagedim_lines ALTER COLUMN updated_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE orders_cagedim_line_texts ALTER COLUMN created_at DATETIME2 NULL;');
        DB::statement('ALTER TABLE orders_cagedim_line_texts ALTER COLUMN updated_at DATETIME2 NULL;');
    }

    public static function createInitialFolders() {
        // Crear carpetas base usando Storage
        Storage::disk('nomane_ftp_in_folders')->makeDirectory('in');
        Storage::disk('nomane_ftp_out_folders')->makeDirectory('out');
        Storage::disk('nomane_temp_folder')->makeDirectory('temp');

        // Input folders
        $inFolders = [
            'pharmacies', 'products', 'tradePolicy', 'productControlFile',
            'priceControlFile', 'procesUnavailableProducts', 'productsBackToStock',
            'shortTermProducts', 'productQuotes', 'blockedOrdersIn',
            'cagedimOrders', 'pharmaMlOrders', 'customerSanitation'
        ];

        foreach ($inFolders as $folder) {
            Storage::disk('nomane_ftp_in_folders')->makeDirectory('in/' . $folder);
            Storage::disk('nomane_ftp_in_folders')->makeDirectory('in/' . $folder . '/processed');
            Storage::disk('nomane_ftp_in_folders')->makeDirectory('in/' . $folder . '/error');
        }

        // Output folders
        $outFolders = [
            'productIntegrationCheck', 'ordersSentToNomane', 'newCustomerOrChange',
            'weeklyOrderConfirmations', 'monthlyActivityReporting',
            'quarterlyActivityReporting', 'blockedOrdersOut', 'productAudit'
        ];

        foreach ($outFolders as $folder) {
            Storage::disk('nomane_ftp_out_folders')->makeDirectory('out/' . $folder);
        }
    }
}

