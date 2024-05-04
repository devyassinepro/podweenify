<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Sales;
use Illuminate\Support\Facades\DB;
set_time_limit(0);
use App\Models\stores;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class TestController extends Controller
{
    //


    public function scrapeStore()
    {
         $url = "https://aquaticarts.com/";
        // $url = "https://styleombre.com/";


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
            
            // Extract keywords meta tag content
            $keywords_tag = $crawler->filterXPath('//meta[@name="keywords"]');
            if ($keywords_tag->count() > 0) {
                $keywords = $keywords_tag->attr('content');
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
        }
        
        // Check for TikTok pixel
        $tiktok_pixel = $this->checkTikTokPixel($html_content);
        
        // Check for Google Ads
        $google_ads = $this->checkGoogleAds($html_content);
        
        // Check for Facebook Pixel
        $facebook_pixel = $this->checkFacebookPixel($html_content);
        
        return response()->json([
            'site_name' => $site_name,
            'description' => $description,
            'keywords' => $keywords,
            'instagram_usernames' => $instagram_usernames,
            'facebook_usernames' => $facebook_usernames,
            'tiktok_usernames' => $tiktok_usernames,
            'tiktok_pixel' => $tiktok_pixel,
            'google_ads' => $google_ads,
            'facebook_pixel' => $facebook_pixel
        ]);
    }

    public function index()
    {
        //
        $products = Product::select("*")
        ->whereDate('updated_at', '=', Carbon::today()->format('Y-m-d'))
        ->where('id',8168403730725)
        ->get();
        echo $products; echo '<br />';

        foreach($products as $product){

            try {
                echo $product; echo '<br />';
        $countproductrevenue = Product::where('id', $product->id)->withCount(['todaysales', 'yesterdaysales'])->first();
        $productreqtoday = array(
                'todaysales' => $countproductrevenue->todaysales_count,
                'yesterdaysales' => $countproductrevenue->yesterdaysales_count,
            );
            DB::table('products')->where('id', $product->id)->update($productreqtoday);

            } catch(\Exception $exception) {

                Log::error($exception->getMessage());
                //echo "Error:".$exception->getMessage().'<br />';
            }
        }



    }


       //   //where timestap == Today
        //   $productCounter = Product::whereDate('updated_at', '=', Carbon::today());
        //   // ->withCount(['todaysales'])->get();
        //   foreach($productCounter as $producttoday){
        //       $countproducttoday=Sales::where('updated_at', '=', Carbon::today())->where('product_id','=',$producttoday->id)->withCount('product_id');
        //               $productreqtoday = array(
        //                       'todaysales' => $countproducttoday->product_count,
        //                   );
        //                   DB::table('products')->where('id', $producttoday->id)->update($productreqtoday);

        //                   echo $producttoday->title; echo '<br />';
        //                   echo $producttoday->todaysales_count; echo '<br />';

        //   //update

        //   $stores = stores::where('status','1')->where('id',546)->withSum('products', 'totalsales')
        //   ->withSum('products', 'revenue');
    public function updatesales(){


        $store = "https://printpocketgo.com/";
        $i = 1;
        $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
        $context = stream_context_create($opts);
        $html = file_get_contents($store.'products.json?page=1&limit=250',false,$context);


        DB::table('apistatuses')->insert([
            "store" => $store,
            "status" => $http_response_header[0],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // echo $responsecode;
        $products = json_decode($html)->products;
        collect($products)->map(function ($product) {

            $productbd = DB::table('products')->where('id', $product->id)->where('timesparam', '!=', strtotime($product->updated_at))->first();
            if($productbd) {

                //Ajouter La partie calcule Revenue chaque jours de la semaines


                $sales = $productbd->totalsales;
                $revenuenow = $productbd->revenue + $productbd->prix;
                $sales ++ ;
                //echo $sales;
                $timestt = strtotime($product->updated_at);

                $productreq = array(
                    'title' => $product->title,
                    'timesparam' => $timestt,
                    'prix' => $product->variants[0]->price,
                    'revenue' => $revenuenow,
                    'stores_id' => $productbd->stores_id,
                    'imageproduct' => $product->images[0]->src,
                    'favoris' => $productbd->favoris,
                    'totalsales' => $sales,
                    // 'todaysales' => $productCounter->todaysales_count,
                    // 'yesterdaysales' => $productCounter->yesterdaysales_count,
                    // 'day3sales' => 10,
                    // 'day4sales' => 10,
                    // 'day5sales' => 10,
                    // 'day6sales' => 10,
                    // 'day7sales' => 10,
                    // 'weeksales' => 10,
                    // 'monthsales' => 10,
                    'updated_at' => Carbon::now()->format('Y-m-d'),//pour comparer la journée
                );

                DB::table('products')->where('id', $productbd->id)->update($productreq);

                DB::table('sales')->insert([
                    "product_id" => $productbd->id,
                    "stores_id" => $productbd->stores_id,
                    "prix" => $productbd->prix,
                    'created_at' => Carbon::now()->format('Y-m-d'),
                    'updated_at' => Carbon::now()->format('Y-m-d')
                ]);

                echo $product->title; echo '<br />';
                }
        });//shoudl be updated now //ok wait


        // return view('index');
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
            }
        }
        return $usernames;
    }

    private function checkTikTokPixel($html_content)
    {
        if (!empty($html_content)) {
            return stripos($html_content, 'tiktok') !== false;
        }
        return false;
    }

    private function checkGoogleAds($html_content)
    {
        if (!empty($html_content)) {
            return stripos($html_content, 'googlesyndication') !== false;
        }
        return false;
    }

    private function checkFacebookPixel($html_content)
    {
        if (!empty($html_content)) {
            return stripos($html_content, 'facebook') !== false && stripos($html_content, 'pixel') !== false;
        }
        return false;
    }
}
