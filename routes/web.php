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
use App\Jobs\ProcessCountproductsRevenue;
use App\Jobs\ProcessCountStoresRevenue;
use App\Jobs\Process24storesRevenue;
use App\Models\Sales;
use Illuminate\Support\Facades\DB;
use Laravel\Horizon\Http\Controllers\HorizonController;


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

Route::get('/horizon', [HorizonController::class, 'index']);


// Start Queue every 2 min
Route::get('/start',function (){
    $stores = Stores::select("*")
        ->where('status','1')
        ->get();

     ProcessApiStoreJob::dispatch($stores);
      echo $stores; echo '<br />';
});

// Start Queue every 2 Hours
Route::get('/countstores',function (){
    $stores = Stores::select("*")
        ->where('status','1')
        ->get();

     ProcessCountStoresRevenue::dispatch($stores);
      echo $stores; echo '<br />';
});

// Start Queue every 24 Hours stores and products
Route::get('/countstoresdaily',function (){
        $stores = Stores::select("*")
                ->where('status','1')
                ->get();

        Process24storesRevenue::dispatch($stores);
      echo $stores; echo '<br />';
});

// Start Queue every 4 Hours
Route::get('/countProducts',function (){
    $products = Product::select("*")
        ->whereDate('updated_at', '=', Carbon::today()->format('Y-m-d'))
        ->get();
        ProcessCountproductsRevenue::dispatch($products);
      echo $products; echo '<br />';
});


// Start Queue every 4 Hours
Route::get('/testcountProducts',function (){
    // $products = Product::select("*")
    //     ->whereDate('updated_at', '=', Carbon::today()->format('Y-m-d'))
    //     ->get();
        // ProcessCountproductsRevenue::dispatch($products);


        $product = 7714800402643;
        $countproductrevenue = Product::where('id', $product)->withCount(['todaysales', 'yesterdaysales'])->first();
        $productreqtoday = array(
                'todaysales' => $countproductrevenue->todaysales_count,
                'yesterdaysales' => $countproductrevenue->yesterdaysales_count,
            );
            DB::table('products')->where('id', $product)->update($productreqtoday);
      echo $product; echo '<br />';
});

// Start Queue every 4 Hours
Route::get('/testaddnewproduct',function (){


    try {
        $store = Stores::select("*")
        ->where('id','4')
        ->first();
        echo $store->url; echo '<br />';

        $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
        $context = stream_context_create($opts);
        $meta = file_get_contents($store->url.'meta.json',false,$context);
        $metas = json_decode($meta);
        $totalproductslive = $metas->published_products_count;

        echo $metas->published_products_count; echo '<br />';


        //to compare with database 
        $storeproductsDB = DB::table('stores')->where('id', $store->id)->first();

        echo $storeproductsDB->allproducts; echo '<br />';

        if($totalproductslive != $storeproductsDB->allproducts){

                for ($i = 1; $i <= 3; $i++) {
                 
                    $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
                    $context = stream_context_create($opts);
                    $html = file_get_contents($store->url.'products.json?page='.$i.'&limit=250',false,$context);
                    $products = json_decode($html)->products;
  

                    collect($products)->map(function ($product) use ($store){

                        $productbd = DB::table('products')->where('id', '=', $product->id)->first();


                        if (!$productbd) {

                            if(isset($product->variants[0]->price)){
                                $price= $product->variants[0]->price;
                            }else{
                                $price=0;
                            }
                            if(isset($product->images[0]->src)){
                                $image= $product->images[0]->src;
                            }else{
                                $image="default";
                            }

                            $timeconvert = strtotime($product->updated_at);
                            $totalsales = 0;

                            $urlproduct = $store->url.'products/'.$product->handle;

                            Product::firstOrCreate([
                                "id" => $product->id,
                                "title" => $product->title,
                                "timesparam" => $timeconvert,
                                "prix" => $price,
                                "revenue" => 0,
                                "stores_id" => $store->id,
                                "url" => $urlproduct,
                                "imageproduct" => $image,
                                "favoris" => 0,
                                "totalsales" => $totalsales,
                                "todaysales" => 0,
                                "yesterdaysales" => 0,
                                "day3sales" => 0,
                                "day4sales" => 0,
                                "day5sales" => 0,
                                "day6sales" => 0,
                                "day7sales" => 0,
                                "weeksales" => 0,
                                "monthsales" => 0
                            ]);
                                        }
                    });
                        //update number of products
                        $updatenumberofnewproduct = array(
                            'allproducts' => $totalproductslive,
                        );
                        DB::table('stores')->where('id', $store->id)->update($updatenumberofnewproduct);
                
                        echo $totalproductslive; echo '<br />';
        
                }

        }

    } catch(\Exception $exception) {

        Log::error($exception->getMessage());
    }

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




function addNewproduct ($store, $i){
    $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
    $context = stream_context_create($opts);
    $html = file_get_contents($store->url.'products.json?page='.$i.'&limit=250',false,$context);
    $products = json_decode($html)->products;


    collect($products)->map(function ($product) {
        
        $productbd = DB::table('products')->where('id', '!=', $product->id)->first();

        if($productbd) {

            if(isset($product->variants[0]->price)){
                $price= $product->variants[0]->price;
            }else{
                $price=0;
            }
            if(isset($product->images[0]->src)){
                $image= $product->images[0]->src;
            }else{
                $image="default";
            }

            $timeconvert = strtotime($product->updated_at);
            $totalsales = 0;
            $urlproduct = $store.'products/'.$product->handle;
            Product::firstOrCreate([
                "id" => $product->id,
                "title" => $product->title,
                "timesparam" => $timeconvert,
                "prix" => $price,
                "revenue" => 0,
                "stores_id" => $store_id,
                "url" => $urlproduct,
                "imageproduct" => $image,
                "favoris" => 0,
                "totalsales" => $totalsales,
                "todaysales" => 0,
                "yesterdaysales" => 0,
                "day3sales" => 0,
                "day4sales" => 0,
                "day5sales" => 0,
                "day6sales" => 0,
                "day7sales" => 0,
                "weeksales" => 0,
                "monthsales" => 0
            ]);

            echo $product->title; echo '<br />';
                }
    });
        //update number of products
        $updatenumberofnewproduct = array(
            'allproducts' => $totalproductslive,
        );
        DB::table('stores')->where('id', $store->id)->update($updatenumberofnewproduct);

        echo $totalproductslive; echo '<br />';

}
