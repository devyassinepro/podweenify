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

        $proxies = [
    'http://fkmroqdf:e7bqbxml5sfv@103.37.181.20:6676',
    'http://fkmroqdf:e7bqbxml5sfv@45.61.98.111:5795',
    'http://fkmroqdf:e7bqbxml5sfv@45.192.148.61:6395',
    'http://fkmroqdf:e7bqbxml5sfv@38.170.175.251:5920',
    'http://fkmroqdf:e7bqbxml5sfv@84.33.200.193:6770',
    'http://fkmroqdf:e7bqbxml5sfv@43.229.9.128:6397',
    'http://fkmroqdf:e7bqbxml5sfv@119.42.39.196:5824',
    'http://fkmroqdf:e7bqbxml5sfv@38.170.188.129:5702',
    'http://fkmroqdf:e7bqbxml5sfv@45.249.104.117:6412',
    'http://fkmroqdf:e7bqbxml5sfv@216.173.80.124:6381',
    'http://fkmroqdf:e7bqbxml5sfv@38.170.159.174:6765',
    'http://fkmroqdf:e7bqbxml5sfv@193.42.224.24:6225',
    'http://fkmroqdf:e7bqbxml5sfv@184.174.27.124:6347',
    'http://fkmroqdf:e7bqbxml5sfv@45.43.189.161:5832',
    'http://fkmroqdf:e7bqbxml5sfv@104.239.53.136:7554',
    'http://fkmroqdf:e7bqbxml5sfv@138.128.145.232:6151',
    'http://fkmroqdf:e7bqbxml5sfv@154.92.123.194:5532',
    'http://fkmroqdf:e7bqbxml5sfv@194.116.250.85:6543',
    'http://fkmroqdf:e7bqbxml5sfv@134.73.2.36:6377',
    'http://fkmroqdf:e7bqbxml5sfv@86.38.154.234:5877',
    'http://fkmroqdf:e7bqbxml5sfv@142.147.129.70:5679',
    'http://fkmroqdf:e7bqbxml5sfv@104.233.12.154:6705',
    'http://fkmroqdf:e7bqbxml5sfv@193.161.2.242:6665',
    'http://fkmroqdf:e7bqbxml5sfv@104.249.55.39:6407',
    'http://fkmroqdf:e7bqbxml5sfv@107.181.148.163:6023',
    'http://fkmroqdf:e7bqbxml5sfv@107.181.143.130:6261',
    'http://fkmroqdf:e7bqbxml5sfv@185.72.241.89:7381',
    'http://fkmroqdf:e7bqbxml5sfv@156.238.5.81:5422',
    'http://fkmroqdf:e7bqbxml5sfv@137.59.4.81:5950',
    'http://fkmroqdf:e7bqbxml5sfv@142.147.240.141:6663',
    'http://fkmroqdf:e7bqbxml5sfv@209.99.129.208:6196',
    'http://fkmroqdf:e7bqbxml5sfv@45.138.119.190:5939',
    'http://fkmroqdf:e7bqbxml5sfv@84.33.241.223:6580',
    'http://fkmroqdf:e7bqbxml5sfv@107.150.21.46:5662',
    'http://fkmroqdf:e7bqbxml5sfv@45.131.95.189:5853',
    'http://fkmroqdf:e7bqbxml5sfv@104.238.4.181:5744',
    'http://fkmroqdf:e7bqbxml5sfv@193.160.237.208:5887',
    'http://fkmroqdf:e7bqbxml5sfv@45.43.71.59:6657',
    'http://fkmroqdf:e7bqbxml5sfv@216.173.72.194:6813',
    'http://fkmroqdf:e7bqbxml5sfv@216.19.206.40:6018',
    'http://fkmroqdf:e7bqbxml5sfv@119.42.38.249:6431',
    'http://fkmroqdf:e7bqbxml5sfv@45.41.179.89:6624',
    'http://fkmroqdf:e7bqbxml5sfv@104.143.245.243:6483',
    'http://fkmroqdf:e7bqbxml5sfv@156.238.5.174:5515',
    'http://fkmroqdf:e7bqbxml5sfv@185.72.240.111:7147',
    'http://fkmroqdf:e7bqbxml5sfv@43.228.237.128:6074',
    'http://fkmroqdf:e7bqbxml5sfv@104.239.35.214:5896',
    'http://fkmroqdf:e7bqbxml5sfv@217.69.126.185:6055',
    'http://fkmroqdf:e7bqbxml5sfv@31.223.188.33:5710',
    'http://fkmroqdf:e7bqbxml5sfv@38.154.195.244:9332',
    'http://fkmroqdf:e7bqbxml5sfv@38.154.217.50:7241',
    'http://fkmroqdf:e7bqbxml5sfv@81.21.234.21:6410','http://fkmroqdf:e7bqbxml5sfv@84.33.26.108:5779',
    'http://fkmroqdf:e7bqbxml5sfv@102.212.88.147:6144',
    'http://fkmroqdf:e7bqbxml5sfv@104.249.29.168:5861',
    'http://fkmroqdf:e7bqbxml5sfv@38.154.217.145:7336',
    'http://fkmroqdf:e7bqbxml5sfv@38.154.195.225:9313',
    'http://fkmroqdf:e7bqbxml5sfv@45.131.103.117:6103',
    'http://fkmroqdf:e7bqbxml5sfv@45.135.139.21:6324',
    'http://fkmroqdf:e7bqbxml5sfv@64.64.118.90:6673',
    'http://fkmroqdf:e7bqbxml5sfv@104.239.53.228:7646',
    'http://fkmroqdf:e7bqbxml5sfv@216.173.104.14:6151',
    'http://fkmroqdf:e7bqbxml5sfv@104.245.244.230:6670',
    'http://fkmroqdf:e7bqbxml5sfv@198.23.239.182:6588',
    'http://fkmroqdf:e7bqbxml5sfv@89.116.77.235:6230',
    'http://fkmroqdf:e7bqbxml5sfv@107.172.156.81:5729',
    'http://fkmroqdf:e7bqbxml5sfv@38.153.140.247:9125',
    'http://fkmroqdf:e7bqbxml5sfv@45.61.125.249:6260',
    'http://fkmroqdf:e7bqbxml5sfv@23.109.225.81:5712',
    'http://fkmroqdf:e7bqbxml5sfv@5.154.253.138:8396',
    'http://fkmroqdf:e7bqbxml5sfv@104.249.29.90:5783',
    'http://fkmroqdf:e7bqbxml5sfv@154.92.124.79:5107',
    'http://fkmroqdf:e7bqbxml5sfv@45.135.139.99:6402',
    'http://fkmroqdf:e7bqbxml5sfv@104.143.251.218:6480',
    'http://fkmroqdf:e7bqbxml5sfv@45.43.68.94:5734',
    'http://fkmroqdf:e7bqbxml5sfv@64.137.70.119:5670',
    'http://fkmroqdf:e7bqbxml5sfv@45.151.161.200:6291',
    'http://fkmroqdf:e7bqbxml5sfv@137.59.4.114:5983',
    'http://fkmroqdf:e7bqbxml5sfv@142.147.242.58:6037',
    'http://fkmroqdf:e7bqbxml5sfv@142.147.131.82:5982',
    'http://fkmroqdf:e7bqbxml5sfv@45.61.97.5:6531',
    'http://fkmroqdf:e7bqbxml5sfv@103.37.181.154:6810',
    'http://fkmroqdf:e7bqbxml5sfv@104.233.26.114:5952',
    'http://fkmroqdf:e7bqbxml5sfv@45.43.82.103:6097',
    'http://fkmroqdf:e7bqbxml5sfv@104.239.2.196:6499',
    'http://fkmroqdf:e7bqbxml5sfv@104.250.203.43:5733',
    'http://fkmroqdf:e7bqbxml5sfv@193.160.237.183:5862',
    'http://fkmroqdf:e7bqbxml5sfv@91.223.126.90:6702',
    'http://fkmroqdf:e7bqbxml5sfv@45.131.101.48:6315',
    'http://fkmroqdf:e7bqbxml5sfv@84.33.241.63:6420',
    'http://fkmroqdf:e7bqbxml5sfv@84.33.243.198:5889',
    'http://fkmroqdf:e7bqbxml5sfv@216.19.205.211:6532',
    'http://fkmroqdf:e7bqbxml5sfv@45.192.155.145:7156',
    'http://fkmroqdf:e7bqbxml5sfv@104.143.250.129:5761',
    'http://fkmroqdf:e7bqbxml5sfv@104.238.8.244:6102',
    'http://fkmroqdf:e7bqbxml5sfv@209.99.165.78:5983',
    'http://fkmroqdf:e7bqbxml5sfv@104.239.52.64:7226',
    'http://fkmroqdf:e7bqbxml5sfv@156.238.10.231:5313',
    'http://fkmroqdf:e7bqbxml5sfv@45.43.70.238:6525',
    'http://fkmroqdf:e7bqbxml5sfv@45.43.179.220:6227',
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
