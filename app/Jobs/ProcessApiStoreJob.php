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

        // $proxies = [
        //     'http://fkmroqdf:e7bqbxml5sfv@45.43.65.21:6535',
        //     'http://fkmroqdf:e7bqbxml5sfv@138.128.145.78:5997',
        //     'http://fkmroqdf:e7bqbxml5sfv@157.52.174.190:6399',
        //     'http://fkmroqdf:e7bqbxml5sfv@45.61.118.28:5725',
        //     'http://fkmroqdf:e7bqbxml5sfv@185.72.242.236:5919',
        //     'http://fkmroqdf:e7bqbxml5sfv@209.99.135.178:6809',
        //     'http://fkmroqdf:e7bqbxml5sfv@216.74.118.227:6382',
        //     'http://fkmroqdf:e7bqbxml5sfv@81.161.8.214:5891',
        //     'http://fkmroqdf:e7bqbxml5sfv@184.174.46.202:5831',
        //     'http://fkmroqdf:e7bqbxml5sfv@64.137.71.141:5959',
        //     'http://fkmroqdf:e7bqbxml5sfv@104.143.224.239:6100',
        //     'http://fkmroqdf:e7bqbxml5sfv@104.232.211.108:5721',
        //     'http://fkmroqdf:e7bqbxml5sfv@206.41.179.12:5688',
        //     'http://fkmroqdf:e7bqbxml5sfv@64.137.79.33:5947',
        //     'http://fkmroqdf:e7bqbxml5sfv@161.123.151.42:6026',
        //     'http://fkmroqdf:e7bqbxml5sfv@23.129.254.64:6046',
        //     'http://fkmroqdf:e7bqbxml5sfv@104.238.49.185:5839',
        //     'http://fkmroqdf:e7bqbxml5sfv@154.30.251.77:5218',
        //     'http://fkmroqdf:e7bqbxml5sfv@154.194.10.91:6104',
        //     'http://fkmroqdf:e7bqbxml5sfv@104.239.38.189:6722',
        //     'http://fkmroqdf:e7bqbxml5sfv@154.92.126.19:5357',
        //     'http://fkmroqdf:e7bqbxml5sfv@136.0.109.184:5778',
        //     'http://fkmroqdf:e7bqbxml5sfv@206.41.168.135:6800',
        //     'http://fkmroqdf:e7bqbxml5sfv@104.239.33.49:6404',
        //     'http://fkmroqdf:e7bqbxml5sfv@64.137.31.108:6722',
        //     'http://fkmroqdf:e7bqbxml5sfv@38.153.152.65:9415',
        //     'http://fkmroqdf:e7bqbxml5sfv@104.239.3.108:6068',
        //     'http://fkmroqdf:e7bqbxml5sfv@38.153.152.138:9488',
        //     'http://fkmroqdf:e7bqbxml5sfv@45.43.190.191:6709',
        //     'http://fkmroqdf:e7bqbxml5sfv@206.41.174.186:6141',
        //     'http://fkmroqdf:e7bqbxml5sfv@192.210.132.80:6050',
        //     'http://fkmroqdf:e7bqbxml5sfv@198.23.128.254:5882',
        //     'http://fkmroqdf:e7bqbxml5sfv@104.168.25.148:5830',
        //     'http://fkmroqdf:e7bqbxml5sfv@91.246.195.82:6851',
        //     'http://fkmroqdf:e7bqbxml5sfv@93.120.32.102:9286',
        //     'http://fkmroqdf:e7bqbxml5sfv@178.159.34.45:5992',
        //     'http://fkmroqdf:e7bqbxml5sfv@45.61.123.247:5926',
        //     'http://fkmroqdf:e7bqbxml5sfv@38.154.233.186:5596',
        //     'http://fkmroqdf:e7bqbxml5sfv@64.137.31.52:6666',
        //     'http://fkmroqdf:e7bqbxml5sfv@134.73.109.73:5838',
        //     'http://fkmroqdf:e7bqbxml5sfv@209.99.165.180:6085',
        //     'http://fkmroqdf:e7bqbxml5sfv@198.105.101.17:5646',
        //     'http://fkmroqdf:e7bqbxml5sfv@198.105.108.78:6100',
        //     'http://fkmroqdf:e7bqbxml5sfv@38.154.217.98:7289',
        //     'http://fkmroqdf:e7bqbxml5sfv@188.74.169.162:5459',
        //     'http://fkmroqdf:e7bqbxml5sfv@193.42.224.98:6299',
        //     'http://fkmroqdf:e7bqbxml5sfv@38.154.233.175:5585',
        //     'http://fkmroqdf:e7bqbxml5sfv@45.252.58.176:6805',
        //     'http://fkmroqdf:e7bqbxml5sfv@171.22.248.123:6015',
        //     'http://fkmroqdf:e7bqbxml5sfv@94.46.206.197:6970',
        //     'http://fkmroqdf:e7bqbxml5sfv@154.85.101.218:5649',
        //     'http://fkmroqdf:e7bqbxml5sfv@154.92.116.147:6459',
        //     'http://fkmroqdf:e7bqbxml5sfv@209.127.143.48:8147',
        //     'http://fkmroqdf:e7bqbxml5sfv@216.173.98.62:6064',
        //     'http://fkmroqdf:e7bqbxml5sfv@109.196.161.166:6614',
        //     'http://fkmroqdf:e7bqbxml5sfv@64.137.90.195:5815',
        //     'http://fkmroqdf:e7bqbxml5sfv@134.73.64.188:6473',
        //     'http://fkmroqdf:e7bqbxml5sfv@136.0.88.103:6396',
        //     'http://fkmroqdf:e7bqbxml5sfv@216.173.120.136:6428',
        //     'http://fkmroqdf:e7bqbxml5sfv@161.123.154.158:6688',
        //     'http://fkmroqdf:e7bqbxml5sfv@5.154.254.199:5210',
        //     'http://fkmroqdf:e7bqbxml5sfv@103.47.53.149:8447',
        //     'http://fkmroqdf:e7bqbxml5sfv@134.73.103.214:5898',
        //     'http://fkmroqdf:e7bqbxml5sfv@166.88.224.160:6789',
        //     'http://fkmroqdf:e7bqbxml5sfv@66.78.32.231:5281',
        //     'http://fkmroqdf:e7bqbxml5sfv@94.46.206.109:6882',
        //     'http://fkmroqdf:e7bqbxml5sfv@103.99.33.181:6176',
        //     'http://fkmroqdf:e7bqbxml5sfv@119.42.36.164:6064',
        //     'http://fkmroqdf:e7bqbxml5sfv@134.73.64.8:6293',
        //     'http://fkmroqdf:e7bqbxml5sfv@154.194.10.196:6209',
        //     'http://fkmroqdf:e7bqbxml5sfv@45.192.134.170:6491',
        //     'http://fkmroqdf:e7bqbxml5sfv@23.228.83.25:5721',
        //     'http://fkmroqdf:e7bqbxml5sfv@104.239.33.147:6502',
        //     'http://fkmroqdf:e7bqbxml5sfv@192.177.103.17:5958',
        //     'http://fkmroqdf:e7bqbxml5sfv@23.247.112.227:6883',
        //     'http://fkmroqdf:e7bqbxml5sfv@64.137.71.164:5982',
        //     'http://fkmroqdf:e7bqbxml5sfv@150.107.225.82:6347',
        //     'http://fkmroqdf:e7bqbxml5sfv@104.250.205.254:6001',
        //     'http://fkmroqdf:e7bqbxml5sfv@161.123.131.100:5705',
        //     'http://fkmroqdf:e7bqbxml5sfv@194.39.34.252:6264',
        //     'http://fkmroqdf:e7bqbxml5sfv@45.43.81.53:5700',
        //     'http://fkmroqdf:e7bqbxml5sfv@64.137.31.33:6647',
        //     'http://fkmroqdf:e7bqbxml5sfv@198.154.89.17:6108',
        //     'http://fkmroqdf:e7bqbxml5sfv@104.239.107.13:5665',
        //     'http://fkmroqdf:e7bqbxml5sfv@103.47.53.144:8442',
        //     'http://fkmroqdf:e7bqbxml5sfv@166.88.83.105:5740',
        //     'http://fkmroqdf:e7bqbxml5sfv@45.131.103.204:6190',
        //     'http://fkmroqdf:e7bqbxml5sfv@206.41.164.85:6384',
        //     'http://fkmroqdf:e7bqbxml5sfv@107.173.137.179:6433',
        //     'http://fkmroqdf:e7bqbxml5sfv@154.85.100.201:5242',
        //     'http://fkmroqdf:e7bqbxml5sfv@45.127.248.131:5132',
        //     'http://fkmroqdf:e7bqbxml5sfv@156.238.9.52:6943',
        //     'http://fkmroqdf:e7bqbxml5sfv@216.173.120.128:6420',
        //     'http://fkmroqdf:e7bqbxml5sfv@154.30.242.190:9584',
        //     'http://fkmroqdf:e7bqbxml5sfv@45.252.58.198:6827',
        //     'http://fkmroqdf:e7bqbxml5sfv@209.242.203.230:6945',
        //     'http://fkmroqdf:e7bqbxml5sfv@38.153.140.73:8951',
        //     'http://fkmroqdf:e7bqbxml5sfv@192.210.191.148:6134',
        //     'http://fkmroqdf:e7bqbxml5sfv@45.43.183.224:6536',
        //     'http://fkmroqdf:e7bqbxml5sfv@45.43.64.54:6312',
        //     // Add more proxy servers as needed
        // ];

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


        $requestCount = 0; // Initialize the request count
        

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
