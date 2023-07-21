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



class SyncCountStoresRevenue implements ShouldQueue
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
        sleep(5);
        $store = $this->store;
        //Let Check if we have new products  add imediately

        try {
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
                if($store->allproducts<=250 ){

                    addNewproduct($store,1);
        
                }else if($store->allproducts<=500){
            
                    for ($i = 1; $i <= 2; $i++) {
                        addNewproduct($store,$i);
            
                    }
            
                }else if($store->allproducts<=750){
                    for ($i = 1; $i <= 3; $i++) {
                        addNewproduct($store,$i);
            
                    }
                }else if($store->allproducts<=1000){
                        for ($i = 1; $i <= 4; $i++) {
                            addNewproduct($store,$i);
                        }
                }
            }


        } catch(\Exception $exception) {

            Log::error($exception->getMessage());
        }
        //count stores revenue

        $storescounter = stores::where('id',$store->id)->withSum('products', 'totalsales')
        ->withSum('products', 'revenue')->withCount(['todaysales'])->first();

        $storeCountStoresRevenue = array(
            'revenue' => $storescounter->products_sum_revenue,
            'sales' => $storescounter->products_sum_totalsales,
            'todaysales'=> $storescounter->todaysales_count
        );
        $storeStopUpdate = array(
            'status' => 0,
        );
        // if no movement in 7 days Stop Update store
        if( $storescounter->products_sum_totalsales == 0 &&  $storescounter->created_at > Carbon::now()->subDays(6)->format('Y-m-d')){
            DB::table('stores')->where('id', $storescounter->id)->update($storeStopUpdate);

        }
        DB::table('stores')->where('id', $storescounter->id)->update($storeCountStoresRevenue);

    }

    function addNewproduct ($store, $i){
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
