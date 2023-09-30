<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;



class SyncStoreProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $store;
    public $currentProxy;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store,$currentProxy)
    {
        $this->store = $store;
        $this->currentProxy = $currentProxy;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $store = $this->store;
        $currentProxy = $this->currentProxy;

        $storeid = $store['id'];

        $urls = DB::table('products')
        ->where('stores_id', $storeid)
        ->take(10)
        ->pluck('url')
        ->toArray();
                
        // $requestCount = 0; // Initialize the request count


        foreach ($urls as $url) {
  
            $client = new Client([
                            'timeout' => 10,
                            'proxy' => $currentProxy,
                        ]);
        // Make an HTTP request using Guzzle
        try {
       $response = $client->get($url . '.json');
       $html = $response->getBody()->getContents();

       // Process the response as needed
       if ($response->getStatusCode() === 200) {
           $product = json_decode($html)->product;
           // Process and update the product data in the database
            // Assuming $product and $currentProxy are defined elsewhere in your code
            // $productTitle = $product->title;
            // $updatedAt = $product->updated_at;
            // $message = "Product update: $productTitle  Update at: $updatedAt \n Using Proxy: $currentProxy";
            // Log::info($message);

               $productbd = DB::table('products')->where('id', $product->id)->where('timesparam', '!=', strtotime($product->updated_at))->first();
               if($productbd) {

                    // $message = "Product Sale: $productTitle\nUpdate at: $updatedAt\nUsing Proxy: $currentProxy";

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
//    $requestCount++;

   // Sleep for a brief moment between requests to avoid overwhelming the proxy and server
//    sleep(1); // Adjust this as needed
}
   
    }

}
