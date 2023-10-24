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
use App\Jobs\ProcessProductResearch;
use App\Models\Sales;
use Illuminate\Support\Facades\DB;
use Laravel\Horizon\Http\Controllers\HorizonController;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;


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


Route::get('/removestore',function (){

    $id =979;

    Stores::where('id', $id)->delete();


});

Route::get('/bigdata',function (){

        $storesurl = [
            'bumpboxx.com',
        ];

        ProcessProductResearch::dispatch($storesurl);


});

Route::get('/product',function (){

        $store =823;

            // Configure Guzzle with proxy and timeout options
            $client = new Client([
                'timeout' => 10, // Set a reasonable timeout for requests
                'proxy' => '', // Initialize with an empty proxy
            ]);

                $urls = DB::table('products')
                ->where('stores_id', $store)
                ->take(30)
                ->pluck('url')
                ->toArray();
                
            $requestCount = 0; // Initialize the request count
            $proxies = [
                        'http://fkmroqdf:e7bqbxml5sfv@38.154.227.167:5868',
                        'http://fkmroqdf:e7bqbxml5sfv@185.199.229.156:7492',
                        'http://fkmroqdf:e7bqbxml5sfv@185.199.228.220:7300',
                        'http://fkmroqdf:e7bqbxml5sfv@185.199.231.45:8382',
                        'http://fkmroqdf:e7bqbxml5sfv@188.74.210.207:6286',
                        'http://fkmroqdf:e7bqbxml5sfv@188.74.183.10:8279',
                        'http://fkmroqdf:e7bqbxml5sfv@188.74.210.21:6100',
                        'http://fkmroqdf:e7bqbxml5sfv@45.155.68.129:8133',
                        'http://fkmroqdf:e7bqbxml5sfv@154.95.36.199:6893',
                        'http://fkmroqdf:e7bqbxml5sfv@45.94.47.66:8110'
        
                       // Add more proxy servers as needed
               ];

        foreach ($urls as $url) {
        // Implement rate limiting logic here to ensure you don't exceed the rate limit

        // Check if it's time to change the proxy (after every 6 requests)
        if ($requestCount % 3 === 0) {
            // Rotate to the next proxy server in your list
            $currentProxy = $proxies[$requestCount / 3 % count($proxies)];
            $client = new Client([
                'timeout' => 10,
                'proxy' => $currentProxy,
            ]);

            // Echo the current proxy URL
        }

        // Make an HTTP request using Guzzle
        try {
       $response = $client->get($url . '.json');
       $html = $response->getBody()->getContents();

       // Process the response as needed
       if ($response->getStatusCode() === 200) {
           $product = json_decode($html)->product;
           // Process and update the product data in the database

               $productbd = DB::table('products')->where('id', $product->id)->where('timesparam', '!=', strtotime($product->updated_at))->first();
               if($productbd) {
                // echo "Product Sale: " . $product->title . "<br>"."Update at : " . $product->updated_at."Using Proxy: $currentProxy\n";

                // Assuming $product and $currentProxy are defined elsewhere in your code
                $productTitle = $product->title;
                $updatedAt = $product->updated_at;

                $message = "Product Sale: $productTitle\nUpdate at: $updatedAt\nUsing Proxy: $currentProxy";

                Log::info($message);
                   $sales = $productbd->totalsales;
                   $todaysalesupdate = $productbd->todaysales;

                   $revenuenow = $productbd->revenue + $productbd->prix;
                   $sales ++ ;
                   $todaysalesupdate ++ ;
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
                       'todaysales' => $todaysalesupdate,
                       'totalsales' => $sales,
                       'updated_at' => Carbon::now()->format('Y-m-d'),
                   );

                   DB::table('products')->where('id', $productbd->id)->update($productreq);

                   DB::table('sales')->insert([
                       "product_id" => $productbd->id,
                       "stores_id" => $productbd->stores_id,
                       "prix" => $productbd->prix,
                       'created_at' => Carbon::now()->format('Y-m-d'),
                       'updated_at' => Carbon::now()->format('Y-m-d')
                   ]);

                   }
       }
   } catch (\Exception $e) {
       // Handle exceptions (e.g., connection errors, timeouts) and log errors
       Log::error($e->getMessage());
   }

   // Increment the request count
   $requestCount++;

   // Sleep for a brief moment between requests to avoid overwhelming the proxy and server
   sleep(1); // Adjust this as needed
}
   
});

//test 1 store

Route::get('/updateproducts',function (){

    // $storeid = 467;

    // $urls = DB::table('products')
    //     ->where('stores_id', $storeid)
    //     ->take(10)
    //     ->pluck('url')
    //     ->toArray();
    
    // // Loop through the URLs and fetch product information
    // foreach ($urls as $url) {
    //     $opts = array('http' => array('header' => "User-Agent:MyAgent/1.0\r\n"));
    //     $context = stream_context_create($opts);
    //     $html = file_get_contents($url.'.json', false, $context);
    
    //     if ($html !== false) {
    //         $product = json_decode($html)->product;

    //         $productbd = DB::table('products')->where('id', $product->id)->where('timesparam', '!=', strtotime($product->updated_at))->first();
    //         if($productbd) {

    //             $sales = $productbd->totalsales;
    //             $todaysalesupdate = $productbd->todaysales;

    //             $revenuenow = $productbd->revenue + $productbd->prix;
    //             $sales ++ ;
    //             $todaysalesupdate ++ ;
    //             //echo $sales;
    //             $timestt = strtotime($product->updated_at);
    //             $productreq = array(
    //                 'title' => $product->title,
    //                 'timesparam' => $timestt,
    //                 'prix' => $product->variants[0]->price,
    //                 'revenue' => $revenuenow,
    //                 'stores_id' => $productbd->stores_id,
    //                 'imageproduct' => $product->images[0]->src,
    //                 'favoris' => $productbd->favoris,
    //                 'todaysales' => $todaysalesupdate,
    //                 'totalsales' => $sales,
    //                 'updated_at' => Carbon::now()->format('Y-m-d'),
    //             );

    //             DB::table('products')->where('id', $productbd->id)->update($productreq);

    //             DB::table('sales')->insert([
    //                 "product_id" => $productbd->id,
    //                 "stores_id" => $productbd->stores_id,
    //                 "prix" => $productbd->prix,
    //                 'created_at' => Carbon::now()->format('Y-m-d'),
    //                 'updated_at' => Carbon::now()->format('Y-m-d')
    //             ]);

    //             }
    //     } else {
    //         echo "Failed to fetch data from URL: " . $url . "<br>";
    //     }
    // }
            
             // Replace these values with your proxy server information


            // get 10 product from store by id and make in array
            // ping with proxy for this products  


        // $storeurl = "https://leospaw.com/products/ferris-wheel-cat-scratching-board.json";
        // $proxyHost = '138.219.75.64'; // e.g., 'proxy.example.com':
        // $proxyPort = 9124; // e.g., 8080
        // $proxyUsername = 'YYoYwm'; // if your proxy requires authentication
        // $proxyPassword = 'mPJVXX';

        // $opts = array(
        //     'http' => array(
        //         'header' => "User-Agent: MyAgent/1.0\r\n",
        //         'proxy' => "tcp://{$proxyHost}:{$proxyPort}",
        //         'request_fulluri' => true,
        //     ),
        // );

        // // If your proxy requires authentication, add the following line:
        // if (!empty($proxyUsername) && !empty($proxyPassword)) {
        //     $opts['http']['header'] .= "Proxy-Authorization: Basic " . base64_encode("$proxyUsername:$proxyPassword") . "\r\n";
        // }

        // $context = stream_context_create($opts);
        // $html = file_get_contents($storeurl, false, $context);
        // $product = json_decode($html)->product;

        // echo "Product id: " . $product->id . "<br>";
        // echo "Product Title: " . $product->title . "<br>";
        // echo "Product updated_at: " . $product->updated_at . "<br>";


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
