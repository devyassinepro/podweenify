<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Sales;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class SyncCountProductsRevenue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $sale;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sale)
    {
        //
        $this->$sale = $sale;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        $sale = $this->sale;

        $countproducttoday=Sales::where('product_id','=',$sale->product_id)->withCount('product_id')->first();
        $productreqtoday = array(
                'todaysales' => $countproducttoday->product_count,
            );
            DB::table('products')->where('id', $sale->product_id)->update($productreqtoday);


    }
}
