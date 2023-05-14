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

class SyncStoreProductsJob implements ShouldQueue
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
         sleep(1);
        $store = $this->store;
        if($store['allproducts']<=250 ){

            updatesales($store['url'],1);

    }else if($store['allproducts']<=500){

        for ($i = 1; $i <= 2; $i++) {
            updatesales($store['url'],$i);

        }

    }else if($store['allproducts']<=750){
        for ($i = 1; $i <= 3; $i++) {
            updatesales($store['url'],$i);

        }
    }else if($store['allproducts']<=1000){
            for ($i = 1; $i <= 4; $i++) {
                updatesales($store['url'],$i);
            }
    }

    }       

}
    function updatesales($store , $i){
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
    
    
                $productbd = DB::table('products')->where('id', $product->id)->where('timesparam', '!=', strtotime($product->updated_at))->first();
                if($productbd) {
        
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
            });//shoudl be updated now //ok wait
}