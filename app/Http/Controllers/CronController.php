<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\NomaneHelper;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{
    public function pharmacies (Request $request) {

        $return = NomaneHelper::getInFiles("pharmacies");
        //Log::info('Pharmacy test2 :'.response()->json(['pharmacies' => $return], ( count($return) ) ? 200 : 204));
        return response()->json(['pharmacies' => $return], ( count($return) ) ? 200 : 204);
    }

    public function products (Request $request) {
        $return = NomaneHelper::getInFiles("products");
        return response()->json(['products' => $return], ( count($return) ) ? 200 : 204);
    }

    public function tradePolicy (Request $request) {
        $return = NomaneHelper::getInFiles("tradePolicy");
        return response()->json(['tradePolicy' => $return], ( count($return) ) ? 200 : 204);
    }

    public function productControlFile(Request $request) {
        $return = NomaneHelper::getInFiles("productControlFile");
        return response()->json(['productControlFile' => $return], ( count($return) ) ? 200 : 204);
    }

    public function priceControlFile(Request $request) {
        $return = NomaneHelper::getInFiles("priceControlFile");
        return response()->json(['priceControlFile' => $return], ( count($return) ) ? 200 : 204);
    }

    public function procesUnavailableProducts(Request $request) {
        $return = NomaneHelper::getInFiles("procesUnavailableProducts");
        return response()->json(['procesUnavailableProducts' => $return], ( count($return) ) ? 200 : 204);
    }

    public function productsOutOfStock(Request $request) {
        $return = NomaneHelper::getInFiles("productsOutOfStock");
        return response()->json(['productsOutOfStock' => $return], ( count($return) ) ? 200 : 204);
    }

    public function shortTermProducts(Request $request) {
        $return = NomaneHelper::getInFiles("shortTermProducts");
        return response()->json(['shortTermProducts' => $return], ( count($return) ) ? 200 : 204);
    }

    public function productQuotes(Request $request) {
        $return = NomaneHelper::getInFiles("productQuotes");
        return response()->json(['productQuotes' => $return], ( count($return) ) ? 200 : 204);
    }

    public function blockedOrdersIn(Request $request) {
        $return = NomaneHelper::getInFiles("blockedOrdersIn");
        return response()->json(['blockedOrdersIn' => $return], ( count($return) ) ? 200 : 204);
    }

    public function cagedimOrders(Request $request) {
        $return = NomaneHelper::getInFiles("cagedimOrders");
        return response()->json(['cagedimOrders' => $return], 200);
    }

    public function customerSanitation(Request $request) {
        $return = NomaneHelper::getInFiles("customerSanitation");
        return response()->json(['customerSanitation' => $return], ( count($return) ) ? 200 : 204);
    }

    public function cancelledOrders(Request $request) {
        $return = NomaneHelper::getInFiles("cancelledOrders");
        return response()->json(['cancelledOrders' => $return], ( count($return) ) ? 200 : 204);
    }
/*
    public function productsUnderAllocation(Request $request) {
        $return = NomaneHelper::getInFiles("productsUnderAllocation");
        return response()->json(['productsUnderAllocation' => $return], ( count($return) ) ? 200 : 204);
    }
*/
    // Out folders

    public function productIntegrationCheck(Request $request) {
        $return = NomaneHelper::doOutFiles("productIntegrationCheck");
        return response()->json(['productIntegrationCheck' => $return], ( count($return) ) ? 200 : 204);
    }

    public function ordersSentToNomane(Request $request) {
        $return = NomaneHelper::doOutFiles("ordersSentToNomane");
        return response()->json(['ordersSentToNomane' => $return], ( count($return) ) ? 200 : 204);
    }

    public function newCustomerOrChange(Request $request) {
        $return = NomaneHelper::doOutFiles("newCustomerOrChange");
        return response()->json(['newCustomerOrChange' => $return], ( count($return) ) ? 200 : 204);
    }
/*
    public function ordersExportedToNomane(Request $request)
    {
        $return = NomaneHelper::doOutFiles("ordersExportedToNomane");
        return response()->json(['ordersExportedToNomane' => $return], ( count($return) ) ? 200 : 204);
    }
*/
    public function rollingOrderHistory(Request $request)
    {
        $return = NomaneHelper::doOutFiles("rollingOrderHistory");
        return response()->json(['rollingOrderHistory' => $return], ( count($return) ) ? 200 : 204);
    }

    public function weeklyOrderConfirmations(Request $request) {
        $return = NomaneHelper::doOutFiles("weeklyOrderConfirmations");
        return response()->json(['weeklyOrderConfirmations' => $return], ( count($return) ) ? 200 : 204);
    }

    public function monthlyActivityReporting(Request $request) {
        $return = NomaneHelper::doOutFiles("monthlyActivityReporting");
        return response()->json(['monthlyActivityReporting' => $return], ( count($return) ) ? 200 : 204);
    }

    public function quarterlyActivityReporting(Request $request) {
        $return = NomaneHelper::doOutFiles("quarterlyActivityReporting");
        return response()->json(['quarterlyActivityReporting' => $return], ( count($return) ) ? 200 : 204);
    }

    public function blockedOrdersOut(Request $request) {
        $return = NomaneHelper::doOutFiles("blockedOrdersOut");
        return response()->json(['blockedOrdersOut' => $return], ( count($return) ) ? 200 : 204);
    }

    public function productAudit(Request $request) {
        $return = NomaneHelper::doOutFiles("productAudit");
        return response()->json(['productAudit' => $return], ( count($return) ) ? 200 : 204);
    }
}
