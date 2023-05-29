<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCountProductsRevenue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $Products;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($Products)
    {
        //

        $this->Products = $Products;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        foreach($this->Products as $product){
            
            try {
                
                SyncCountProductsRevenue::dispatch($product);
            } catch(\Exception $exception) {

                Log::error($exception->getMessage());
                //echo "Error:".$exception->getMessage().'<br />';
            }
        }
    }
}
