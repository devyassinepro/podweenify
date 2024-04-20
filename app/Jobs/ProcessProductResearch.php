<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

set_time_limit(0);


class ProcessProductResearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $stores;
    public $storetype;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($stores,$storetype)
    {
        $this->stores = $stores;
        $this->storetype = $storetype;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        foreach($this->stores as $store){

            try {
                // $modifiedUrl = "https://$store/";
                     // Check if the URL starts with "https://"
                if (strpos($store, 'https://') !== 0) {
                    $modifiedUrl = "https://$store/";
                } else {
                    $modifiedUrl = $store;
                }

                SyncProductResearch::dispatch($modifiedUrl,$this->storetype)->onQueue('daycounter');
            } catch(\Exception $exception) {

                Log::error($exception->getMessage());
                //echo "Error:".$exception->getMessage().'<br />';
            }
        }
    }
}
