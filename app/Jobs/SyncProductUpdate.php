<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\stores;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Product;


class SyncProductUpdate implements ShouldQueue
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

        $store = $this->store;
        if($store['allproducts']<=250 ){

            updatedatabase($store['url'],1);

    }else if($store['allproducts']<=500){

        for ($i = 1; $i <= 2; $i++) {
            updatedatabase($store['url'],$i);

        }

    }else if($store['allproducts']<=750){
        for ($i = 1; $i <= 3; $i++) {
            updatedatabase($store['url'],$i);

        }
    }else if($store['allproducts']<=1000){
            for ($i = 1; $i <= 4; $i++) {
                updatedatabase($store['url'],$i);
            }
    }
    

    }
}

//check if we have new products in the store 

function updatedatabase($store , $i){
    $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
    $context = stream_context_create($opts);
    $html = file_get_contents($store.'products.json?page='.$i.'&limit=250',false,$context);
  
    // echo $responsecode;
    $products = json_decode($html)->products;
    collect($products)->map(function ($product) {

        $productbd = DB::table('products')->where('id', $product->id)->first();
        if($productbd) {

                    // Check if the images array has elements before accessing them
                    if (isset($product->images[1])) {
                        $image2 = $product->images[1]->src;
                    }else $image2 ='';
    
                    if (isset($product->images[2])) {
                        $image3 = $product->images[2]->src;
                    }else $image3 ='';
    
                    if (isset($product->images[3])) {
                        $image4 = $product->images[3]->src;
                    }else $image4 ='';
    
                    if (isset($product->images[4])) {
                        $image5 = $product->images[4]->src;
                    }else $image5 ='';
    
                    if (isset($product->images[5])) {
                        $image6 = $product->images[5]->src;
                    }else $image6 ='';
    
                   
                // title ,prix, url ,imageproduct, description , created_at_shopify , image2 , image3 , image4 , image5 , image6
                $productreq = array(
                    'title' => $product->title,
                    'prix' => $product->variants[0]->price,
                    'imageproduct' => $product->images[0]->src,
                    'description' => $product->body_html,
                    'created_at_shopify' => $product->published_at,
                    'image2' => $image2,
                    'image3' => $image3,
                    'image4' => $image4,
                    'image5' => $image5,
                    'image6' => $image6,
                );

            DB::table('products')->where('id', $productbd->id)->update($productreq);

            }
    });//shoudl be updated now //ok wait

    sleep(5);

}
