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
use App\Jobs\ProcessProductUpdate;
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


Route::get('/uploadcsv', function () {

    return view('upload-file');
});

// Import Csv Stores to Weenify
Route::post('/uploadcsv', function () {


    if (request()->hasFile('mycsv')) {
        $csvFile = request()->file('mycsv');
        $category = request()->input('category');
    
        if ($csvFile->isValid() && $csvFile->isReadable()) {
            $data = array_map('str_getcsv', file($csvFile));
    
            // Check if the file has any data
            if (count($data) > 0) {
                $header = $data[0];
                unset($data[0]);
    
                $urls = [];
    
                foreach ($data as $row) {
                    foreach ($row as $value) {
                        // Collect all URLs in an array
                        $urls[] = $value;
                    }
                }
    
                // Dispatch the job with the entire array of URLs

             ProcessProductResearch::dispatch($urls,$category);

                echo 'Job dispatched for processing all URLs.';
            } else {
                echo 'The CSV file is empty.';
            }
        } else {
            echo 'The uploaded file is not a valid CSV file.';
        }
    } else {
        echo 'No CSV file uploaded.';
    }
    return 'Please Upload File';
});


Route::get('/horizon', [HorizonController::class, 'index']);


// Start Queue every 2 min Tracking Sales
Route::get('/start',function (){
    $stores = Stores::select("*")
        ->where('status','1')
        ->get();

     ProcessApiStoreJob::dispatch($stores);
      echo $stores; echo '<br />';
});

// Update product database after existing in database 
Route::get('/updateproducts',function (){

    $stores = Stores::all();

    ProcessProductUpdate::dispatch($stores);

     echo $stores; echo '<br />';
});

// Start Queue every 24 Hours stores and products //  Sync24storesRevenue
Route::get('/countstoresdaily',function (){
    $stores = Stores::select("*")
            ->where('status','1')
            ->get();

    Process24storesRevenue::dispatch($stores);
  echo $stores; echo '<br />';
});

// Start Queue every 2 Hours Calculate Revenue Stores // SyncCountStoresRevenue
Route::get('/countstores',function (){
    $stores = Stores::select("*")
        ->where('status','1')
        ->get();

     ProcessCountStoresRevenue::dispatch($stores);
      echo $stores; echo '<br />';
});

// Start Queue every 4 Hours Count Products Revenue //SyncCountProductsRevenue
Route::get('/countProducts',function (){
    $products = Product::select("*")
        ->whereDate('updated_at', '=', Carbon::today()->format('Y-m-d'))
        ->get();
        ProcessCountproductsRevenue::dispatch($products);
      echo $products; echo '<br />';
});


// Start Queue every 4 Hours Count Products Revenue
Route::get('/deletestore',function (){
   

    // DB::table('products')->where('stores_id', 11800)->delete();

    DB::beginTransaction();

    try {
        // Delete products where id_store is equal to 20
        Product::where('stores_id', 11800)->delete();

        // Commit the transaction
        DB::commit();
    } catch (Exception $e) {
        // Handle any exceptions and rollback the transaction if needed
        DB::rollback();
    }

    // Stores::where('id', 11808)->delete();
   
    // Product::where('stores_id', 11808)->delete();

      echo "Done"; echo '<br />';
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

