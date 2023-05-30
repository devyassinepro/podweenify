<?php

namespace App\Jobs;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;


class SyncCountProductsRevenue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        sleep(5);
        $product = $this->product;
        $countproductrevenue = Product::where('id', $product->id)->withCount(['todaysales', 'yesterdaysales'])->first();
        $productreqtoday = array(
                'todaysales' => $countproductrevenue->todaysales_count,
                'yesterdaysales' => $countproductrevenue->yesterdaysales_count,
            );
            DB::table('products')->where('id', $product->id)->update($productreqtoday);

    }
}
