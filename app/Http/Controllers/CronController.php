<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PfizerHelper;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{
    public function pharmacies (Request $request) {

        $return = PfizerHelper::getInFiles("pharmacies");
        //Log::info('Pharmacy test2 :'.response()->json(['pharmacies' => $return], ( count($return) ) ? 200 : 204));
        return response()->json(['pharmacies' => $return], ( count($return) ) ? 200 : 204);
    }

    public function products (Request $request) {
        $return = PfizerHelper::getInFiles("products");
        return response()->json(['products' => $return], ( count($return) ) ? 200 : 204);
    }

    public function tradePolicy (Request $request) {
        $return = PfizerHelper::getInFiles("tradePolicy");
        return response()->json(['tradePolicy' => $return], ( count($return) ) ? 200 : 204);
    }

    public function productControlFile(Request $request) {
        $return = PfizerHelper::getInFiles("productControlFile");
        return response()->json(['productControlFile' => $return], ( count($return) ) ? 200 : 204);
    }

    public function priceControlFile(Request $request) {
        $return = PfizerHelper::getInFiles("priceControlFile");
        return response()->json(['priceControlFile' => $return], ( count($return) ) ? 200 : 204);
    }

    public function procesUnavailableProducts(Request $request) {
        $return = PfizerHelper::getInFiles("procesUnavailableProducts");
        return response()->json(['procesUnavailableProducts' => $return], ( count($return) ) ? 200 : 204);
    }

    public function productsOutOfStock(Request $request) {
        $return = PfizerHelper::getInFiles("productsOutOfStock");
        return response()->json(['productsOutOfStock' => $return], ( count($return) ) ? 200 : 204);
    }

    public function shortTermProducts(Request $request) {
        $return = PfizerHelper::getInFiles("shortTermProducts");
        return response()->json(['shortTermProducts' => $return], ( count($return) ) ? 200 : 204);
    }

    public function productQuotes(Request $request) {
        $return = PfizerHelper::getInFiles("productQuotes");
        return response()->json(['productQuotes' => $return], ( count($return) ) ? 200 : 204);
    }

    public function blockedOrdersIn(Request $request) {
        $return = PfizerHelper::getInFiles("blockedOrdersIn");
        return response()->json(['blockedOrdersIn' => $return], ( count($return) ) ? 200 : 204);
    }

    public function cagedimOrders(Request $request) {
        $return = PfizerHelper::getInFiles("cagedimOrders");
        return response()->json(['cagedimOrders' => $return], 200);
    }

    public function customerSanitation(Request $request) {
        $return = PfizerHelper::getInFiles("customerSanitation");
        return response()->json(['customerSanitation' => $return], ( count($return) ) ? 200 : 204);
    }

    public function cancelledOrders(Request $request) {
        $return = PfizerHelper::getInFiles("cancelledOrders");
        return response()->json(['cancelledOrders' => $return], ( count($return) ) ? 200 : 204);
    }
/*
    public function productsUnderAllocation(Request $request) {
        $return = PfizerHelper::getInFiles("productsUnderAllocation");
        return response()->json(['productsUnderAllocation' => $return], ( count($return) ) ? 200 : 204);
    }
*/
    // Out folders

    public function productIntegrationCheck(Request $request) {
        $return = PfizerHelper::doOutFiles("productIntegrationCheck");
        return response()->json(['productIntegrationCheck' => $return], ( count($return) ) ? 200 : 204);
    }

    public function ordersSentToPfizer(Request $request) {
        $return = PfizerHelper::doOutFiles("ordersSentToPfizer");
        return response()->json(['ordersSentToPfizer' => $return], ( count($return) ) ? 200 : 204);
    }

    public function newCustomerOrChange(Request $request) {
        $return = PfizerHelper::doOutFiles("newCustomerOrChange");
        return response()->json(['newCustomerOrChange' => $return], ( count($return) ) ? 200 : 204);
    }
/*
    public function ordersExportedToPfizer(Request $request)
    {
        $return = PfizerHelper::doOutFiles("ordersExportedToPfizer");
        return response()->json(['ordersExportedToPfizer' => $return], ( count($return) ) ? 200 : 204);
    }
*/
    public function rollingOrderHistory(Request $request)
    {
        $return = PfizerHelper::doOutFiles("rollingOrderHistory");
        return response()->json(['rollingOrderHistory' => $return], ( count($return) ) ? 200 : 204);
    }

    public function weeklyOrderConfirmations(Request $request) {
        $return = PfizerHelper::doOutFiles("weeklyOrderConfirmations");
        return response()->json(['weeklyOrderConfirmations' => $return], ( count($return) ) ? 200 : 204);
    }

    public function monthlyActivityReporting(Request $request) {
        $return = PfizerHelper::doOutFiles("monthlyActivityReporting");
        return response()->json(['monthlyActivityReporting' => $return], ( count($return) ) ? 200 : 204);
    }

    public function quarterlyActivityReporting(Request $request) {
        $return = PfizerHelper::doOutFiles("quarterlyActivityReporting");
        return response()->json(['quarterlyActivityReporting' => $return], ( count($return) ) ? 200 : 204);
    }

    public function blockedOrdersOut(Request $request) {
        $return = PfizerHelper::doOutFiles("blockedOrdersOut");
        return response()->json(['blockedOrdersOut' => $return], ( count($return) ) ? 200 : 204);
    }

    public function productAudit(Request $request) {
        $return = PfizerHelper::doOutFiles("productAudit");
        return response()->json(['productAudit' => $return], ( count($return) ) ? 200 : 204);
    }
}
