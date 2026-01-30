<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class OrderCreateController extends Controller
{
     public function begin(Request $request)
     {
         $data["search_cip"] = "";
         $data["operator_id"] = "";
         return view('order.begin', $data);
     }
     public function beginResult(Request $request)
     {
         $data["pharmacies"] = Pharmacy::where('cip', 'like', '%' . $request->search_cip . '%')
                         ->orWhere('name', 'like', '%' . $request->search_cip . '%')
                         ->limit(250)
                         ->get();
 
         $teleoperator = $request->session()->get('tele_operator');
         $data["operator_id"] = $teleoperator->operator_id;
         $data["search_cip"] = $request->search_cip;
 
         return view('order.begin', $data);
     }

    public function start(Request $request)
    {
        $laboratory = new Laboratory();
        $pharmacy = new Pharmacy();
        $cip = $request->query('cip');

        $incoming = $request->query('incoming');

        $operator = $request->query('operator'); // Hermes ID
        if (isset($operator)) {
            $tele_operator = TeleOperator::where('operator_id', $operator)->first();
            if ( ! isset($tele_operator->country) ){
                return redirect('/')->with(['status' => 'error', 'message' => __('You must select an operator')]);
            }
            $request->session()->put('tele_operator', $tele_operator);
            app()->setLocale($tele_operator->country);
            session()->put('locale', $tele_operator->country);
            session()->put('tele_operator_name', $tele_operator->first_name . ' ' . $tele_operator->last_name);
        }
        if ( ! empty($phone) || ! empty($cip) ) // modification ici
        {
            $pharmacy = Pharmacy::where(function ($query) use ($cip) { // modification ici
                $query->where('cip', $cip);
            })->first();
        }
        if (empty($tele_operator)) {
            return redirect('/')->with(['status' => 'error', 'message' => __('You must select an operator')]);
        }
        if ( ! isset($pharmacy->id)) {
            return redirect('/')->with(['status' => 'error', 'message' => __('No pharmacy selected')]);
        }
        session()->put('shared_pharmacy_id', $pharmacy->id);
        session()->put('shared_tele_operator_id', $tele_operator->id);
        Session::put('shared_tele_operator', $tele_operator);
        $data['pharmacy'] = $pharmacy;

        $data['laboratories'] = $laboratory->getActiveTo($pharmacy->cip);

        $data['restricted_laboratories'] = PharmacyLaboratoryRestricted::where('cip', '=', $pharmacy->cip)->get();
        $data['creating_order'] = true;
        $data['tele_operator_vble'] = $tele_operator;
        if($incoming == '1')
            $data['incoming'] = '1';
        return view('order.order_edit', $data);
    }
}
