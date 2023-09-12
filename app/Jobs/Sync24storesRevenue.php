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



class Sync24storesRevenue implements ShouldQueue
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
        sleep(2);
        $store = $this->store;
        $storescounter = stores::where('id',$store->id)->first();
        $storeCountStoresRevenue = array(
            'todaysales'=> 0,
            'yesterdaysales'=> $storescounter->todaysales,
            'day3sales'=> $storescounter->yesterdaysales,
            'day4sales'=> $storescounter->day3sales,
            'day5sales'=> $storescounter->day4sales,
            'day6sales'=> $storescounter->day5sales,
            'day7sales'=> $storescounter->day6sales,
        );
        $storeStopUpdate = array(
            'status' => 0,
        );
        // if no movement in 7 days Stop Update store
        if( $storescounter->products_sum_totalsales == 0 &&  $storescounter->created_at > Carbon::now()->subDays(6)->format('Y-m-d')){
            DB::table('stores')->where('id', $storescounter->id)->update($storeStopUpdate);

        }
        DB::table('stores')->where('id', $storescounter->id)->update($storeCountStoresRevenue);

        //After 24 hours update also products  
        $productcounters = Product::where('stores_id',$store->id)
                        ->where('todaysales', '>=', 1)
                        ->get();
  
            foreach($productcounters as $product){
            
                $productcounter = Product::where('id',$product->id)->first();
                $productCountStoresRevenue = array(
                    'todaysales'=> 0,
                    'yesterdaysales'=> $productcounter->todaysales,
                    'day3sales'=> $productcounter->yesterdaysales,
                    'day4sales'=> $productcounter->day3sales,
                    'day5sales'=> $productcounter->day4sales,
                    'day6sales'=> $productcounter->day5sales,
                    'day7sales'=> $productcounter->day6sales,
                );
        
                DB::table('products')->where('id', $productcounter->id)->update($productCountStoresRevenue);
    
            }

    }
}
