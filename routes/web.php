<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Models\Apistatus;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoresController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\TestController;
use App\Jobs\ProcessApiStoreJob;
use App\Jobs\ProcessCountProductsRevenue;
use App\Jobs\ProcessCountStoresRevenue;
use App\Models\Sales;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\stores;
use Carbon\Carbon;

set_time_limit(0);

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*///

Route::get('/', function () {
    return view('index');
});


Route::resource('affiche', TestController::class);



// Start Queue every 2 min
Route::get('/start',function (){
    $stores = Stores::select("*")
        ->where('status','1')
        ->get();

     ProcessApiStoreJob::dispatch($stores);
      echo $stores; echo '<br />';
});

// Start Queue every 5 Hours
Route::get('/countstores',function (){
    $stores = Stores::select("*")
        ->where('status','1')
        ->get();

     ProcessCountStoresRevenue::dispatch($stores);
      echo $stores; echo '<br />';
});

// Start Queue every 4 Hours
Route::get('/countProducts',function (){
    $Products = Product::select("*")
        ->whereDate('updated_at', '=', Carbon::today()->format('Y-m-d'))
        ->get();
     ProcessCountProductsRevenue::dispatch($Products);
      echo $Products; echo '<br />';
});


//test 1 store

Route::get('/starttest',function (){

        $store = "https://printpocketgo.com/";
        $i = 1;
        $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
        $context = stream_context_create($opts);
        $html = file_get_contents($store.'products.json?page='.$i.'&limit=250',false,$context);


        DB::table('apistatuses')->insert([
            "store" => $store,
            "status" => $http_response_header[0],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // echo $responsecode;
        $products = json_decode($html)->products;
        collect($products)->map(function ($product) {

            $productbd = Product::where('id', $product->id)->where('timesparam', '!=', strtotime($product->updated_at))->first();
            if($productbd) {

                //Ajouter La partie calcule Revenue chaque jours de la semaines

                $productbd = $productbd->withCount(['todaysales', 'yesterdaysales' , 'day3sales' , 'day4sales' , 'day5sales' , 'day6sales', 'day7sales', 'weeklysales', 'montlysales'])->get();

                $sales = $productbd->totalsales;
                $revenuenow = $productbd->revenue + $productbd->prix;
                $sales ++ ;
                //echo $sales;
                $timestt = strtotime($product->updated_at);
                $productreq = array(
                    'title' => $product->title,
                    'timesparam' => $timestt,
                    'prix' => $product->variants[0]->price,
                    'revenue' => $revenuenow,
                    'stores_id' => $productbd->stores_id,
                    'imageproduct' => $product->images[0]->src,
                    'favoris' => $productbd->favoris,
                    'totalsales' => $sales,
                    'todaysales' => $productbd->todaysales_count,
                    'yesterdaysales' => $productbd->yesterdaysales_count,
                    'day3sales' => $productbd->day3sales_count,
                    'day4sales' => $productbd->day4sales_count,
                    'day5sales' => $productbd->day5sales_count,
                    'day6sales' => $productbd->day6sales_count,
                    'day7sales' => $productbd->day6sales_count,
                    'weeksales' => $productbd->weeklysales_count,
                    'monthsales' => $productbd->montlysales_count,
                );

                DB::table('products')->where('id', $productbd->id)->update($productreq);

                //Count

                DB::table('sales')->insert([
                    "product_id" => $productbd->id,
                    "stores_id" => $productbd->stores_id,
                    "prix" => $productbd->prix,
                    'created_at' => Carbon::now()->format('Y-m-d'),
                    'updated_at' => Carbon::now()->format('Y-m-d')
                ]);
                echo $productbd->first()->todaysales_count; echo '<br />';
                echo $productbd->todaysales_count; echo '<br />';
                echo $product->title; echo '<br />';
                }
        });//shoudl be updated now //ok wait


});
