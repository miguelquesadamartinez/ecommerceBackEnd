<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    const ACTIVE                = 'Active';
    const BLOCKED               = 'Blocked';
    const INACTIVE_NEW_SAP      = 'Inactive New SAP';

    protected $table = 'pharmacies';
    protected $fillable = [
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
        'pharmacy_siret',
        'pharmacy_new_data',
        'pharmacy_new_pharmacy'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'order_pharmacy_id', 'id')->orderBy('created_at', 'DESC');
    }

    public function ordersConfirmed()
    {
        return $this->hasMany(Order::class, 'order_pharmacy_id', 'id')->where('order_status', '=', 'Confirmed')->orderBy('created_at', 'DESC');
    }

    public function newPharmacies()
    {
        $date = now()->subDays(1);
        $query = Pharmacy::query();
        $query->where('created_at', '>=', $date)
              ->orWhere('updated_at', '>=', $date);
        return $query->get();
    }

    public static function getCount(?string $country) {
        return ($country) ? Pharmacy::where('pharmacy_country', $country)->count() : Pharmacy::count();
    }

    public function search(string $search)
    {
        $pharmacies = Pharmacy::orderBy('pharmacy_name', 'ASC');
        if(!empty($search)) {
            $pharmacies->where('pharmacy_cip', 'LIKE', "%$search%")->orWhere('pharmacy_name', 'LIKE', "%$search%");
        }
        return $pharmacies->paginate(20);
    }

    public function getLastOrder()
    {
        return $this->hasMany(Order::class, 'order_pharmacy_id', 'id')
            ->latest()
            ->first();
    }
    public function getLastOrderOnHold()
    {
        return $this->hasMany(Order::class, 'order_pharmacy_id', 'id')
            ->where('order_status', Order::ONHOLD)
            ->latest()
            ->get();
    }
}
