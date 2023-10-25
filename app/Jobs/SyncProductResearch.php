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
use App\Models\stores;
use App\Models\Product;

set_time_limit(0);


class SyncProductResearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $store;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store)
    {
        $this->store = $store;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        $domain = $this->store;
        // Use try-catch for error handling
        try {

            // $stores = stores::where('url', $domain)->first();
            $stores =  DB::table('stores')->where('url', $domain)->first();
            // DB::table('products')
            if($stores){

            }else{
                $opts = array('http' => array('header' => "User-Agent: MyAgent/1.0\r\n"));
                $context = stream_context_create($opts);
                $meta = file_get_contents($domain.'meta.json', false, $context);
        
                // Check if the JSON content is valid
                if ($meta === false) {
                    echo "Failed to retrieve data from $modifiedUrl";
                } else {
                    $metas = json_decode($meta);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $totalproducts = $metas->published_products_count;
                        
                        // echo "Total products for $site: $totalproducts<br>";
                        
                        $store_id = DB::table('stores')->insertGetId(
                            ['url' => $domain,
                            'name' => $metas->name,
                            'status' => 0,
                            'sales' => 0,
                            'revenue' => 0,
                            'city' => $metas->city,
                            'country' => $metas->country,
                            'currency' => $metas->currency,
                            'shopifydomain' => $metas->myshopify_domain,
                            'allproducts' => $metas->published_products_count,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'user_id' => 0
                            ]
                        );
                        
                        if($totalproducts<=250){
                            $this->createstore($domain,$store_id,1);


                        }else if($totalproducts<=500){
                            for ($i = 1; $i <= 2; $i++) {
                                $this->createstore($domain,$store_id,$i);

                            }
                         }else if($totalproducts<=750){
                            for ($i = 1; $i <= 3; $i++) {
                                $this->createstore($domain,$store_id,$i);

                            }
                        }
                        else if($totalproducts<=1000 || $totalproducts>1000){
                            for ($i = 1; $i <= 4; $i++) {
                                $this->createstore($domain,$store_id,$i);
                            }
                        }

                       
                    } else {
                        echo "Failed to decode JSON from $modifiedUrl: " . json_last_error_msg();
                    }
                }
            }
            } catch (Exception $e) {
                echo "An error occurred: " . $e->getMessage();
            }

        sleep(7);
        
    }


    public function createstore ($store ,$store_id, $i){


        try {
                    $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
                    $context = stream_context_create($opts);
                    $html = file_get_contents($store.'products.json?page='.$i.'&limit=250',false,$context);
                    $products = json_decode($html)->products;
                    foreach ($products as $product) {

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
            }

        
        } catch (Exception $e) {
                    echo "An error occurred: " . $e->getMessage();
        }    
    }


}

