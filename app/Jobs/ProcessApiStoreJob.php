<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ProcessApiStoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $stores;
    public $proxies;
    public $currentProxyIndex = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($stores)
    {
        $this->stores = $stores;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $requestCount = 0; // Initialize the request count
        $proxies = [
                    'http://fkmroqdf:e7bqbxml5sfv@38.154.227.167:5868',
                    'http://fkmroqdf:e7bqbxml5sfv@185.199.229.156:7492',
                    'http://fkmroqdf:e7bqbxml5sfv@185.199.228.220:7300',
                    'http://fkmroqdf:e7bqbxml5sfv@185.199.231.45:8382',
                    'http://fkmroqdf:e7bqbxml5sfv@188.74.210.207:6286',
                    'http://fkmroqdf:e7bqbxml5sfv@188.74.183.10:8279',
                    'http://fkmroqdf:e7bqbxml5sfv@188.74.210.21:6100',
                    'http://fkmroqdf:e7bqbxml5sfv@45.155.68.129:8133',
                    'http://fkmroqdf:e7bqbxml5sfv@154.95.36.199:6893',
                    'http://fkmroqdf:e7bqbxml5sfv@45.94.47.66:8110'
    
                   // Add more proxy servers as needed
           ];


           $currentProxyIndex = 0; // Initialize the proxy index

           foreach ($this->stores as $store) {
               try {
                   // Dispatch the job with the current store and proxy
                   SyncStoreProductsJob::dispatch($store, $proxies[$currentProxyIndex])
                       ->onQueue('tracksales');
       
                   $requestCount++;
       
                   // Check if we've reached 2 requests and rotate to the next proxy
                   if ($requestCount % 2 === 0) {
                       $currentProxyIndex++; // Move to the next proxy
                   }
               } catch (\Exception $exception) {
                   Log::error($exception->getMessage());
                   // Handle exceptions as needed
               }
       
               // Ensure that the proxy index stays within the bounds of the array
               if ($currentProxyIndex >= count($proxies)) {
                   $currentProxyIndex = 0; // Reset to the first proxy if we've reached the end
               }
           }
    }
}
