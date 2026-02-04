<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PharmacyHistoric extends Model
{
    protected $fillable = [
        'pharmacy_historic_pharmacy_id',
        'pharmacy_historic_filed_name',
        'pharmacy_historic_old_value',
        'pharmacy_historic_new_value'
    ];

    public static function getLatestChanges($pharmacyId)
    {
        return PharmacyHistoric::where('pharmacy_historic_sent_to_nomane', '=', 0)
            ->where('pharmacy_historic_pharmacy_id', '=', $pharmacyId)
            ->get();
    }
}


