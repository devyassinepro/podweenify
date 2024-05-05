<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Models\stores;
use App\Models\Product;
use Symfony\Component\DomCrawler\Crawler;

set_time_limit(0);


class SyncProductResearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $store;
    public $storetype;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store,$storetype)
    {
        $this->store = $store;
        $this->storetype = $storetype;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    
        $domain = $this->store;
        $storetype = $this->storetype;


        // Scrapping DATA 
        $storedata =$this->scrapeStore($domain);

        //Dropshipping

        $dropshipping = 0;
        $digital = 0;
        $tshirt = 0;
        if ($storetype == 1) {
            $dropshipping = 1;
        } elseif ($storetype == 3) {
            $tshirt = 1;
        } else {
            $digital = 1;
        }

        // Use try-catch for error handling
        try {

            // $stores = stores::where('url', $domain)->first();
            $stores =  DB::table('stores')->where('url', $domain)->first();
            // DB::table('products')
            if($stores){

            }else{
                $opts = array('http' => array('header' => "User-Agent: MyAgent/1.0\r\n"));
                $context = stream_context_create($opts);
                $meta = file_get_contents($domain.'meta.json', false, $context);
        
                // Check if the JSON content is valid
                if ($meta === false) {
                    echo "Failed to retrieve data from $modifiedUrl";
                } else {
                    $metas = json_decode($meta);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $totalproducts = $metas->published_products_count;
                        
                        // echo "Total products for $site: $totalproducts<br>";
                        
                        $store_id = DB::table('stores')->insertGetId(
                            ['url' => $domain,
                            'name' => $metas->name,
                            'status' => 0,
                            'sales' => 0,
                            'tag' => '',
                            'revenue' => 0,
                            'city' => $metas->city,
                            'country' => $metas->country,
                            'currency' => $metas->currency,
                            'shopifydomain' => $metas->myshopify_domain,
                            'allproducts' => $metas->published_products_count,
                            'todaysales' => 0,
                            'yesterdaysales' => 0,
                            'day3sales' => 0,
                            'day4sales' => 0,
                            'day5sales' => 0,
                            'day6sales' => 0,
                            'day7sales' => 0,
                            'weeksales' => 0,
                            'monthsales' => 0,
                            'dropshipping' => $dropshipping,
                            'tshirt' => $tshirt,
                            'digital' => $digital,
                            'title'=> $storedata['site_name'],
                            'description'=> $storedata['description'],
                            'theme'=> $storedata['theme_name'],
                            'facebookusername'=> implode(', ', $storedata['facebook_usernames']),
                            'instagramusername'=> implode(', ', $storedata['instagram_usernames']),
                            'pinterestusername'=> implode(', ', $storedata['pinterest_usernames']),
                            'youtubeusername'=> implode(', ', $storedata['youtube_usernames']),
                            'tiktokusername'=> implode(', ', $storedata['tiktok_usernames']),
                            'snapchatusername'=> implode(', ', $storedata['snapchat_usernames']),
                            'facebookpixel'=> $storedata['facebook_pixel'],
                            'googlepixel'=> $storedata['google_ads'],
                            'snapchatpixel'=> $storedata['snapchat_pixel'],
                            'pinterestpixel'=> $storedata['pinterest_pixel'],
                            'tiktokpixel'=> $storedata['tiktok_pixel'],
                            'created_at' => now(),
                            'updated_at' => now(),
                            'user_id' => 0
                            ]
                        );
                      //add all products if is dropshipping
                        if($dropshipping){
                            $storeIndex = 1;
                            $productsPerPage = 250;
                            $totalProductsRemaining = $totalproducts;
                            
                            while ($totalProductsRemaining > 0) {
                                $this->createstore($domain, $store_id, $storeIndex, $dropshipping, $digital, $tshirt);
                                $storeIndex++;
                                $totalProductsRemaining -= $productsPerPage;
                            }
                        }else{
                            //only 1000 products if else
                            $storeIndex = 1;
                            $productsPerPage = 250;
                            $totalProductsRemaining = min($totalproducts, 1000); // Limit to 1000 products
    
                            while ($totalProductsRemaining > 0) {
                                $this->createstore($domain, $store_id, $storeIndex, $dropshipping, $digital, $tshirt);
                                $storeIndex++;
                                $totalProductsRemaining -= $productsPerPage;
                            }
                        }
                       

                       
                    } else {
                        echo "Failed to decode JSON from $modifiedUrl: " . json_last_error_msg();
                    }
                }
            }
            } catch (Exception $e) {
                echo "An error occurred: " . $e->getMessage();
            }

        sleep(7);
        
    }


    public function createstore ($store ,$store_id, $i,$dropshipping,$digital,$tshirt){


        try {
                    $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
                    $context = stream_context_create($opts);
                    $html = file_get_contents($store.'products.json?page='.$i.'&limit=250',false,$context);
                    $products = json_decode($html)->products;
                    foreach ($products as $product) {

                        if(isset($product->variants[0]->price)){
                            $price= $product->variants[0]->price;
                        }else{
                            $price=0;
                        }
                        if(isset($product->images[0]->src)){
                            $image= $product->images[0]->src;
                        }else{
                            $image ='';
                        }
                        if (isset($product->images[1])) {
                            $image2 = $product->images[1]->src;
                        }else $image2 ='';
                
                        if (isset($product->images[2])) {
                            $image3 = $product->images[2]->src;
                        }else $image3 ='';
                
                        if (isset($product->images[3])) {
                            $image4 = $product->images[3]->src;
                        }else $image4 ='';
                
                        if (isset($product->images[4])) {
                            $image5 = $product->images[4]->src;
                        }else $image5 ='';
                
                        if (isset($product->images[5])) {
                            $image6 = $product->images[5]->src;
                        }else $image6 ='';

                        $timeconvert = strtotime($product->updated_at);
                        $totalsales = 0;
                        $urlproduct = $store.'products/'.$product->handle;
                        Product::firstOrCreate([
                            "id" => $product->id,
                            "title" => $product->title,
                            "timesparam" => $timeconvert,
                            "prix" => $price,
                            "revenue" => 0,
                            "stores_id" => $store_id,
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
                            "monthsales" => 0,
                            'dropshipping' => $dropshipping,
                            'tshirt' => $tshirt,
                            'digital' => $digital,
                            'price_aliexpress'=>0,
                            'description' => $product->body_html,
                            'created_at_shopify' => $product->published_at,
                            'created_at_favorite' => $product->published_at,
                            'image2' => $image2,
                            'image3' => $image3,
                            'image4' => $image4,
                            'image5' => $image5,
                            'image6' => $image6,
                        ]);
            }

        
        } catch (Exception $e) {
                    echo "An error occurred: " . $e->getMessage();
        }    
    }



    public function scrapeStore($url)
    {

        // Fetch HTML content from the URL
        try {
            $client = new Client();
            $response = $client->get($url);
            $html_content = $response->getBody()->getContents();
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Failed to fetch URL: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch URL'], 500);
        }
        
        // Extract description, keywords, site name, and social media usernames
        $description = null;
        $keywords = null;
        $site_name = null;
        $instagram_usernames = [];
        $facebook_usernames = [];
        $tiktok_usernames = [];
        
        if (!empty($html_content)) {
            $crawler = new Crawler($html_content);
            
            // Extract description meta tag content
            $description_tag = $crawler->filterXPath('//meta[@name="description"]');
            if ($description_tag->count() > 0) {
                $description = $description_tag->attr('content');
            }
            
            
            // Extract site name from title tag
            $title_tag = $crawler->filter('title');
            if ($title_tag->count() > 0) {
                $site_name = $title_tag->text();
            }
            
            // Extract social media usernames
            $instagram_usernames = $this->extractSocialMediaUsernames($html_content, 'instagram');
            $facebook_usernames = $this->extractSocialMediaUsernames($html_content, 'facebook');
            $tiktok_usernames = $this->extractSocialMediaUsernames($html_content, 'tiktok');
            $pinterest_usernames = $this->extractSocialMediaUsernames($html_content, 'pinterest');
            $youtube_usernames = $this->extractSocialMediaUsernames($html_content, 'youtube');
            $snapchat_usernames = $this->extractSocialMediaUsernames($html_content, 'snapchat');

        }
        
        // Check for TikTok pixel
        $tiktok_pixel = $this->checkTikTokPixel($html_content);
        
        // Check for Google Ads
        $google_ads = $this->checkGoogleAds($html_content);
        
        // Check for Facebook Pixel
        $facebook_pixel = $this->checkFacebookPixel($html_content);
        
        return [
            'site_name' => $site_name,
            'description' => $description,
            'instagram_usernames' => $instagram_usernames,
            'facebook_usernames' => $facebook_usernames,
            'tiktok_usernames' => $tiktok_usernames,
            'snapchat_usernames' => $snapchat_usernames,
            'pinterest_usernames' => $pinterest_usernames,
            'youtube_usernames' => $youtube_usernames,
            'theme_name' => $this->extractThemeName($html_content),
            'tiktok_pixel' => $tiktok_pixel,
            'google_ads' => $google_ads,
            'facebook_pixel' => $facebook_pixel,
            'snapchat_pixel' => $this->checkSnapchatPixel($html_content),
            'pinterest_pixel' => $this->checkPinterestPixel($html_content),
        ];

    }

    private function extractSocialMediaUsernames($html_content, $platform)
    {
        $usernames = [];
        if (!empty($html_content)) {
            // Extract usernames based on the platform
            switch ($platform) {
                case 'instagram':
                    preg_match_all('/(?:https?:\/\/)?(?:www\.)?instagram\.com\/([^\s\/]+)/i', $html_content, $matches);
                    $usernames = $matches[1];
                    break;
                case 'facebook':
                    preg_match_all('/(?:https?:\/\/)?(?:www\.)?facebook\.com\/([^\s\/]+)/i', $html_content, $matches);
                    $usernames = $matches[1];
                    break;
                case 'tiktok':
                    preg_match_all('/(?:https?:\/\/)?(?:www\.)?tiktok\.com\/(@[^\s\/]+)/i', $html_content, $matches);
                    $usernames = $matches[1];
                    break;
                case 'snapchat':
                    preg_match_all('/(?:https?:\/\/)?(?:www\.)?snapchat\.com\/(@[^\s\/]+)/i', $html_content, $matches);
                    $usernames = $matches[1];
                    break;
                case 'pinterest':
                    preg_match_all('/(?:https?:\/\/)?(?:www\.)?pinterest\.com\/(@[^\s\/]+)/i', $html_content, $matches);
                    $usernames = $matches[1];
                    break;
                case 'youtube':
                    preg_match_all('/(?:https?:\/\/)?(?:www\.)?youtube\.com\/(@[^\s\/]+)/i', $html_content, $matches);
                    $usernames = $matches[1];
                    break;
            }
        }
        return $usernames;
    }

    private function checkTikTokPixel($html_content)
    {
        if (!empty($html_content)) {
            return stripos($html_content, 'tiktok') !== false ? 1 : 0;
        }
        return 0;
    }
    
    private function checkGoogleAds($html_content)
    {
        if (!empty($html_content)) {
            // Look for specific patterns indicating Google Tag Manager
            // Check for URLs containing "googletagmanager.com"
            $pattern = '/googletagmanager\.com/i';
            return preg_match($pattern, $html_content) ? 1 : 0;
        }
        return 0;
    }
    
    private function checkFacebookPixel($html_content)
    {
        if (!empty($html_content)) {
            return (stripos($html_content, 'facebook') !== false && stripos($html_content, 'pixel') !== false) ? 1 : 0;
        }
        return 0;
    }
    
    private function extractThemeName($html_content)
    {
        $theme_name = null;
        if (!empty($html_content)) {
            // Use regular expressions to extract the theme name from the script tag
            $pattern = '/Shopify\.theme\s*=\s*{"name":"([^"]+)"/i';
            if (preg_match($pattern, $html_content, $matches)) {
                // The theme name will be captured in the first captured group ($matches[1])
                $theme_name = $matches[1];
            }
        }
        return $theme_name ? 1 : 0;
    }
    
    private function checkSnapchatPixel($html_content)
    {
        if (!empty($html_content)) {
            // Look for specific patterns indicating Snap Pixel code
            $pattern = '/<!-- Snap Pixel Code -->.*?snaptr\(\'init\'.*?\'PAGE_VIEW\'\);\s*<\/script>/is';
            return preg_match($pattern, $html_content) ? 1 : 0;
        }
        return 0;
    }
    
    private function checkPinterestPixel($html_content)
    {
        if (!empty($html_content)) {
            // Look for the occurrence of the pintrk function
            $pattern = '/pintrk\s*\(/i';
            return preg_match($pattern, $html_content) ? 1 : 0;
        }
        return 0;
    }


}

