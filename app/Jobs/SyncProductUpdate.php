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

        if($store['dropshipping'] == 1){

            $storeIndex = 1;
            $productsPerPage = 250;
            $totalProductsRemaining = $store['allproducts'];
            
            while ($totalProductsRemaining > 0) {
                updatedatabase($store['url'],$store['id'],$storeIndex,$store['dropshipping'],$store['tshirt'],$store['digital']);
                $storeIndex++;
                $totalProductsRemaining -= $productsPerPage;
            }

        }else{
            //only 1000 products if else
            $storeIndex = 1;
            $productsPerPage = 250;
            $totalProductsRemaining = min($totalproducts, 1000); // Limit to 1000 products

            while ($totalProductsRemaining > 0) {
                updatedatabase($store['url'],$store['id'],$storeIndex,$store['dropshipping'],$store['tshirt'],$store['digital']);
                $storeIndex++;
                $totalProductsRemaining -= $productsPerPage;
            }
       
        }
    

    }
}

//check if we have new products in the store 

function updatedatabase($store,$store_id , $storeIndex , $dropshipping, $tshirt, $digital){
    $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
    $context = stream_context_create($opts);
    $html = file_get_contents($store.'products.json?page='.$storeIndex.'&limit=250',false,$context);

    $urlstore = $store;

  
    // echo $responsecode;
    $products = json_decode($html)->products;
    // collect($products)->map(function ($product) {
        collect($products)->map(function ($product) use ($urlstore,$store_id) {
            
            $urlproduct = $urlstore.'products/'.$product->handle;


        $productbd = DB::table('products')->where('id', $product->id)->first();
        if($productbd) {

                    // Check if the images array has elements before accessing them
                    if(isset($product->variants[0]->price)){
                        $price= $product->variants[0]->price;
                    }else{
                        $price=0;
                    }
                    if(isset($product->images[0]->src)){
                        $image= $product->images[0]->src;
                    }else{
                        $image ='';
                    }
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
                    'prix' => $price,
                    'imageproduct' => $image,
                    'description' => $product->body_html,
                    'created_at_shopify' => $product->published_at,
                    'image2' => $image2,
                    'image3' => $image3,
                    'image4' => $image4,
                    'image5' => $image5,
                    'image6' => $image6,
                );

            DB::table('products')->where('id', $productbd->id)->update($productreq);

        }else{
            if(isset($product->variants[0]->price)){
                $price= $product->variants[0]->price;
            }else{
                $price=0;
            }
            if(isset($product->images[0]->src)){
                $image= $product->images[0]->src;
            }else{
                $image ='';
            }
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

            $timeconvert = strtotime($product->updated_at);
            $totalsales = 0;
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
                "monthsales" => 0,
                'dropshipping' => $dropshipping,
                'tshirt' => $tshirt,
                'digital' => $digital,
                'price_aliexpress'=>0,
                'description' => $product->body_html,
                'created_at_shopify' => $product->published_at,
                'created_at_favorite' => $product->published_at,
                'image2' => $image2,
                'image3' => $image3,
                'image4' => $image4,
                'image5' => $image5,
                'image6' => $image6,
            ]);
            
        }
    });//shoudl be updated now //ok wait

    sleep(3);

}
