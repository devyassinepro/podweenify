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
        $storescounter = stores::where('id',$store->id)->withSum('products', 'totalsales')
        ->withSum('products', 'revenue')->first();

        $storeCountStoresRevenue = array(
            'revenue' => $storescounter->products_sum_revenue,
            'sales' => $storescounter->products_sum_totalsales,
            
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
}
