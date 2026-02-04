<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use App\Helpers\NomaneHelper;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ProductUnitsSell;

class DataReturnController extends Controller
{
    public function pharmacySearch(Request $request)
     {
         if ($request->searchText != "") {
            $search_value = trim($request->searchText);

            $query = Pharmacy::query();
            $query = $query->where('pharmacy_cip13', 'like', '%' . $search_value . '%')
                            ->orWhere('pharmacy_phone', 'like', '%' . $search_value . '%')
                            //->orWhere('pharmacy_name', 'like', '%' . $search_value . '%')
                            ;

            $pharmacies = $query->limit(250)->get();

            return response()->json($pharmacies, ( count($pharmacies) ) ? 200 : 204);
         } else {
            return response()->json(['no_search_sended' => true], 204);
         }
     }

     public function productSearch(Request $request)
     {
         if ($request->searchText != "") {
                $search_value = trim($request->searchText);
                $query = Product::query();
                $query = $query->leftJoin('categories', 'products.product_category_id', '=', 'categories.id')
                                ->select('products.*', 'categories.category_name');

                $query = $query->where(function($q) use ($search_value) {
                    $q->where('products.product_name', 'like', '%' . $search_value . '%')
                      ->orWhere('products.product_presentation', 'like', '%' . $search_value . '%')
                      ->orWhere('products.product_cip13', 'like', '%' . $search_value . '%')
                      ->orWhere('products.product_sap_id', 'like', '%' . $search_value . '%')
                      ->orWhere('categories.category_name', 'like', '%' . $search_value . '%');
                });

                $query = $query->where(function($query) {
                    $query = $query->where('products.product_status', '=', Product::STATUS_DISPONIBLE);
                });

                $query = $query->where(function($query) {
                    $query = $query->where('products.product_sub_status', '=', Product::SUB_STATUS_ACTIVE);
                });

                $products = $query->limit(250)->get();

                $today = Carbon::now()->format('Y-m-d');

                foreach($products as $product) {
                    $unitsSold = ProductUnitsSell::where('product_units_sell_product_id', $product->id)
                        ->where('product_units_sell_date_start', '<=', $today)
                        ->where(function($query) use ($today) {
                            $query->where('product_units_sell_date_end', '>=', $today)
                                  ->orWhereNull('product_units_sell_date_end');
                        })
                        ->sum('product_units_sell_units_sell');

                    // Añadir el nombre de la categoría al objeto producto
                    $product->category_name = $product->category ? $product->category->category_name : '';
                }

            return response()->json($products, ( count($products) ) ? 200 : 204);
         } else {
            return response()->json(['no_search_sended' => true], 204);
         }
     }

     public function getPharmacy(Request $request)
     {
         $pharmacy = Pharmacy::where('pharmacy_cip13', $request->pharmacy_cip13)->first();
         return response()->json($pharmacy, ( $pharmacy ) ? 200 : 204);
     }

     public function getPharmacyId(Request $request)
     {
         $pharmacy = Pharmacy::find($request->pharmacy_id);
         return response()->json($pharmacy, ( $pharmacy ) ? 200 : 204);
     }

     public function getCreatePharmacy(Request $request)
     {
        $pharmacy = new Pharmacy();
        $pharmacy->pharmacy_cip13 = $request->searchText;
        $pharmacy->pharmacy_status = Pharmacy::BLOCKED;
        $pharmacy->pharmacy_type = 'Z031';
        //$pharmacy->pharmacy_account_status = 'Z2';
        $pharmacy->pharmacy_new_data = 1;
        $pharmacy->pharmacy_new_pharmacy = 1;
        $pharmacy->save();
        return response()->json($pharmacy, ( $pharmacy ) ? 200 : 204);
     }

     public function getProduct(Request $request)
     {
         $product = Product::where('product_cip13', $request->product_cip13)->first();
         return response()->json($product, ( $product ) ? 200 : 204);
     }

     public function getAllProducts(Request $request)
     {
         $products = Product::where('product_status', PRODUCT::STATUS_DISPONIBLE)
                            ->where('products.product_sub_status', '=', Product::SUB_STATUS_ACTIVE)
                            ->orderBy('product_name', 'asc')->get();

         foreach($products as $product) {
            $product->product_category_name = $product->category ? $product->category->category_name : '';
         }

         return response()->json($products, ( $products ) ? 200 : 204);
     }

     public function getProductId(Request $request)
     {
         $product = Product::find($request->product_id);
         return response()->json($product, ( $product ) ? 200 : 204);
     }

     public function getOrders(Request $request)
      {
          $page = $request->page;
          $perPage = $request->per_page;
          $skip = ($page - 1) * $perPage;
          $orders = Order::orderBy('created_at', 'desc')
                    ->skip($skip)
                    ->take($perPage)
                    ->get();

          $total = Order::count();

          return response()->json([
                'data' => $orders,
                'total' => $total,
                'current_page'=> $request->page,
                'per_page' => $request->per_page
            ], ( count($orders) ) ? 200 : 204);
      }

        public function getOrdersFarm(Request $request)
      {
          $page = $request->page;
          $perPage = $request->per_page;
          $skip = ($page - 1) * $perPage;
          $orders = Order::orderBy('created_at', 'desc')
                    ->where('order_pharmacy_id', $request->pharmacy_id)
                    ->skip($skip)
                    ->take($perPage)
                    ->get();

          $total = Order::orderBy('created_at', 'desc')
                    ->where('order_pharmacy_id', $request->pharmacy_id)
                    ->count();

          return response()->json([
                'data' => $orders,
                'total' => $total,
                'current_page'=> $request->page,
                'per_page' => $request->per_page
            ], ( count($orders) ) ? 200 : 204);
      }

      public function getPharmacies(Request $request)
      {
          $page = $request->page;
          $perPage = $request->per_page;
          $skip = ($page - 1) * $perPage;

          if ( isset($request->searchText) && $request->searchText != "") {
            $pharmacies = Pharmacy::orderBy('created_at', 'desc')
                        ->where('pharmacy_cip13', 'like', '%' . $request->searchText . '%')
                        ->orWhere('pharmacy_phone', 'like', '%' . $request->searchText . '%')
                        ->orWhere('pharmacy_name', 'like', '%' . $request->searchText . '%')
                        ->skip($skip)
                        ->take($perPage)
                        ->get();
            $total = Pharmacy::orderBy('created_at', 'desc')
                        ->where('pharmacy_cip13', 'like', '%' . $request->searchText . '%')
                        ->orWhere('pharmacy_phone', 'like', '%' . $request->searchText . '%')
                        ->orWhere('pharmacy_name', 'like', '%' . $request->searchText . '%')
                        ->skip($skip)
                        ->take($perPage)
                        ->count();
          } else {
            $pharmacies = Pharmacy::orderBy('created_at', 'desc')
                        ->skip($skip)
                        ->take($perPage)
                        ->get();

            $total = Pharmacy::count();
          }

          return response()->json([
                'data' => $pharmacies,
                'total' => $total,
                'current_page'=> $request->page,
                'per_page' => $request->per_page
            ], ( count($pharmacies) ) ? 200 : 204);
      }

      public function getProducts(Request $request)
      {
        $currentMonth = Carbon::now()->format('Y-m');
        $currentMonthStart = $currentMonth . '-01';
        $currentMonthEnd = Carbon::now()->endOfMonth()->format('Y-m-d');

          if ( isset($request->searchText) && $request->searchText != "") {
            $page = $request->page;
            $perPage = $request->per_page;
            $skip = ($page - 1) * $perPage;
            $search_value = trim($request->searchText);

            $products = Product::leftJoin('categories', 'products.product_category_id', '=', 'categories.id')
                        ->select('products.*', 'categories.category_name')
                        ->where('products.product_sub_status', '=', Product::SUB_STATUS_ACTIVE)
                        ->where(function($q) use ($search_value) {
                            $q->where('products.product_presentation', 'like', '%' . $search_value . '%')
                              ->orWhere('products.product_name', 'like', '%' . $search_value . '%')
                              ->orWhere('products.product_cip13', 'like', '%' . $search_value . '%')
                              ->orWhere('products.product_sap_id', 'like', '%' . $search_value . '%')
                              ->orWhere('categories.category_name', 'like', '%' . $search_value . '%');
                        })
                        ->orderBy('products.product_presentation', 'asc')
                        ->skip($skip)
                        ->take($perPage)
                        ->get();

            foreach($products as $product){
                // ToDo: esto no esta al reves ?
                $sales = ProductUnitsSell::where('product_units_sell_product_id', $product->id)
                    ->where('product_units_sell_date_start', '<=', $currentMonthEnd)
                    ->where(function($query) use ($currentMonthStart) {
                        $query->where('product_units_sell_date_end', '>=', $currentMonthStart)
                              ->orWhereNull('product_units_sell_date_end');
                    })
                    ->first();
                $product->product_monthly_sales = $sales->product_units_sell_units_sell ?? 0;
                $product->category_name = $product->category ? $product->category->category_name : '';
            }

            $productsCount = Product::leftJoin('categories', 'products.product_category_id', '=', 'categories.id')
                        ->select('products.*', 'categories.category_name')
                        ->where('products.product_sub_status', '=', Product::SUB_STATUS_ACTIVE)
                        ->where(function($q) use ($search_value) {
                            $q->where('products.product_presentation', 'like', '%' . $search_value . '%')
                              ->orWhere('products.product_name', 'like', '%' . $search_value . '%')
                              ->orWhere('products.product_cip13', 'like', '%' . $search_value . '%')
                              ->orWhere('products.product_sap_id', 'like', '%' . $search_value . '%')
                              ->orWhere('categories.category_name', 'like', '%' . $search_value . '%');
                        })
                        ->get();
            $total = count($productsCount);
          } else {
            $page = $request->page;
            $perPage = $request->per_page;
            $skip = ($page - 1) * $perPage;
            $products = Product::orderBy('product_presentation', 'asc')
                        ->where('product_sub_status', '=', Product::SUB_STATUS_ACTIVE)
                        ->skip($skip)
                        ->take($perPage)
                        ->get();
            foreach($products as $product){
                // ToDo: esto no esta al reves ?
                $sales = ProductUnitsSell::where('product_units_sell_product_id', $product->id)
                    ->where('product_units_sell_date_start', '<=', $currentMonthEnd)
                    ->where(function($query) use ($currentMonthStart) {
                        $query->where('product_units_sell_date_end', '>=', $currentMonthStart)
                              ->orWhereNull('product_units_sell_date_end');
                    })
                    ->first();
                $product->product_monthly_sales = $sales->product_units_sell_units_sell ?? 0;
                $product->category_name = $product->category ? $product->category->category_name : '';
            }
            $total = Product::count();
          }

          return response()->json([
                'data' => $products,
                'total' => $total,
                'current_page'=> $request->page,
                'per_page' => $request->per_page
            ], ( count($products) ) ? 200 : 204);
      }


      public function getProductsAll(Request $request)
      {
            $currentMonth = Carbon::now()->format('Y-m');
            $currentMonthStart = $currentMonth . '-01';
            $currentMonthEnd = Carbon::now()->endOfMonth()->format('Y-m-d');

            $search_value = trim($request->searchText);

          if ( isset($request->searchText) && $request->searchText != "") {
            $products = Product::leftJoin('categories', 'products.product_category_id', '=', 'categories.id')
                ->select('products.*', 'categories.category_name')
                ->where('products.product_sub_status', '=', Product::SUB_STATUS_ACTIVE)
                ->where(function($q) use ($search_value) {
                    $q->where('products.product_presentation', 'like', '%' . $search_value . '%')
                    ->orWhere('products.product_name', 'like', '%' . $search_value . '%')
                    ->orWhere('products.product_cip13', 'like', '%' . $search_value . '%')
                    ->orWhere('products.product_sap_id', 'like', '%' . $search_value . '%')
                    ->orWhere('categories.category_name', 'like', '%' . $search_value . '%');
                })
                ->get();
            foreach($products as $product){
                $sales = ProductUnitsSell::where('product_units_sell_product_id', $product->id)
                    ->where('product_units_sell_date_start', '<=', $currentMonthEnd)
                    ->where(function($query) use ($currentMonthStart) {
                        $query->where('product_units_sell_date_end', '>=', $currentMonthStart)
                              ->orWhereNull('product_units_sell_date_end');
                    })
                    ->first();

                $product->product_monthly_sales = $sales->product_units_sell_units_sell ?? 0;
            }
            $total = count($products);
          } else {
            $products = Product::orderBy('product_presentation', 'asc')
                        ->where('products.product_sub_status', '=', Product::SUB_STATUS_ACTIVE)
                        ->get();
            foreach($products as $product){
                $sales = ProductUnitsSell::where('product_units_sell_product_id', $product->id)
                    ->where('product_units_sell_date_start', '<=', $currentMonthEnd)
                    ->where(function($query) use ($currentMonthStart) {
                        $query->where('product_units_sell_date_end', '>=', $currentMonthStart)
                              ->orWhereNull('product_units_sell_date_end');
                    })
                    ->first();
                $product->product_monthly_sales = $sales->product_units_sell_units_sell ?? 0;
            }
            $total = Product::count();
          }

          return response()->json([
                'data' => $products,
                'total' => $total,
                'current_page'=> $request->page,
                'per_page' => $request->per_page
            ], ( count($products) ) ? 200 : 204);
      }


      public function getCategories(Request $request)
      {
          $categories = Category::all();
          return response()->json($categories, ( $categories ) ? 200 : 204);
      }
}








