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

    public function productresearch(){

        $store = "https://angelcurves.com/";

        // echo $storedata['site_name'] ;echo '<br />';
        // echo $storedata['description'];echo '<br />';
        // echo implode(', ', $storedata['instagram_usernames']);echo '<br />';
        // echo implode(', ', $storedata['facebook_usernames']);echo '<br />';
        // echo implode(', ', $storedata['tiktok_usernames']);echo '<br />';
        // echo implode(', ', $storedata['pinterest_usernames']);echo '<br />';
        // echo implode(', ', $storedata['youtube_usernames']);echo '<br />';
        // echo $storedata['theme_name'];echo '<br />';
        // echo $storedata['tiktok_pixel'];echo '<br />';
        // echo $storedata['google_ads'];echo '<br />';
        // echo $storedata['facebook_pixel'];echo '<br />';
        // echo $storedata['snapchat_pixel'];echo '<br />';
        // echo $storedata['pinterest_pixel'];echo '<br />';


        $storedata =$this->scrapeStore($store);
        
        // Use try-catch for error handling
        try {
           $stores =  DB::table('stores')->where('url', $store)->first();
           // DB::table('products')
           if($stores){
                       $Updatescrappingstores = array(
                           'title'=> $storedata['site_name'],
                           'description'=> $storedata['description'],
                           'theme'=> $storedata['theme_name'],
                           'facebookusername'=> $storedata['facebook_usernames'],
                           'instagramusername'=> $storedata['instagram_usernames'],
                           'pinterestusername'=> $storedata['pinterest_usernames'],
                           'youtubeusername'=> $storedata['youtube_usernames'],
                           'tiktokusername'=> $storedata['tiktok_usernames'],
                           'snapchatusername'=> $storedata['snapchat_usernames'],
                           'facebookpixel'=> $storedata['facebook_pixel'],
                           'googlepixel'=> $storedata['google_ads'],
                           'snapchatpixel'=> $storedata['snapchat_pixel'],
                           'pinterestpixel'=> $storedata['pinterest_pixel'],
                           'tiktokpixel'=> $storedata['tiktok_pixel'],
                       );

                   DB::table('stores')->where('url', $store)->update($Updatescrappingstores);
                   
               } 
           
           } catch (Exception $e) {
               echo "An error occurred: " . $e->getMessage();
           }


    }

    public function scrapeStore($url)
    {
        //   $url = "https://aquaticarts.com/";
        // $url = "https://lespetitsimprimes.com/";


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
            $instagram_usernames = $this->extractSocialMediaURLs($html_content, 'instagram');
            $facebook_usernames = $this->extractSocialMediaURLs($html_content, 'facebook');
            $tiktok_usernames = $this->extractSocialMediaURLs($html_content, 'tiktok');
            $pinterest_usernames = $this->extractSocialMediaURLs($html_content, 'pinterest');
            $youtube_usernames = $this->extractSocialMediaURLs($html_content, 'youtube');
            $snapchat_usernames = $this->extractSocialMediaURLs($html_content, 'snapchat');


        }
        
        // Check for TikTok pixel
        $tiktok_pixel = $this->checkTikTokPixel($html_content);
        
        // Check for Google Ads
        $google_ads = $this->checkGoogleAds($html_content);
        
        // Check for Facebook Pixel
        $facebook_pixel = $this->checkFacebookPixel($html_content);
        
        // return response()->json([
        //     'site_name' => $site_name,
        //     'description' => $description,
        //     'keywords' => $keywords,
        //     'instagram_usernames' => $instagram_usernames,
        //     'facebook_usernames' => $facebook_usernames,
        //     'tiktok_usernames' => $tiktok_usernames,
        //     'tiktok_pixel' => $tiktok_pixel,
        //     'google_ads' => $google_ads,
        //     'facebook_pixel' => $facebook_pixel
        // ]);

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

    private function extractSocialMediaURLs($html_content, $platform)
    {
        $url = [];

        if (!empty($html_content)) {
            // Extract URLs based on the platform
            switch ($platform) {
                case 'instagram':
                    preg_match_all('/(?:https?:\/\/)?(?:www\.)?instagram\.com\/[^s\/]+/i', $html_content, $matches);
                    $url = isset($matches[0][0]) ? $matches[0][0] : null;
                    break;
                case 'facebook':
                    preg_match_all('/(?:https?:\/\/)?(?:www\.)?facebook\.com\/[^\s\/]+/i', $html_content, $matches);
                    $url = isset($matches[0][0]) ? $matches[0][0] : null;
                    break;
                case 'tiktok':
                    preg_match_all('/(?:https?:\/\/)?(?:www\.)?tiktok\.com\/@?[a-zA-Z0-9_.-]+/i', $html_content, $matches);
                    $url = isset($matches[0][0]) ? $matches[0][0] : null;
                    break;
                   
                case 'snapchat':
                    preg_match_all('/(?:https?:\/\/)?(?:www\.)?snapchat\.com\/@[^\s\/]+/i', $html_content, $matches);
                    $url = isset($matches[0][0]) ? $matches[0][0] : null;
                    break;
                case 'pinterest':
                    preg_match_all('/(?:https?:\/\/)?(?:www\.)?pinterest\.com\/[^\s\/]+/i', $html_content, $matches);
                    $url = isset($matches[0][0]) ? $matches[0][0] : null;
                    break;
                case 'youtube':
                    preg_match_all('/(?:https?:\/\/)?(?:www\.)?youtube\.com\/@[^\s\/]+/i', $html_content, $matches);
                    $url = isset($matches[0][0]) ? $matches[0][0] : null;
                    break;
            }
        }
        return $url;
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
            // Look for specific patterns indicating Google Tag Manager
            // Check for URLs containing "googletagmanager.com"
            $pattern = '/googletagmanager\.com/i';
            if (preg_match($pattern, $html_content)) {
                return true;
            }
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
        return $theme_name;
    }

    private function checkSnapchatPixel($html_content)
    {
        if (!empty($html_content)) {
            // Look for specific patterns indicating Snap Pixel code
            $pattern = '/<!-- Snap Pixel Code -->.*?snaptr\(\'init\'.*?\'PAGE_VIEW\'\);\s*<\/script>/is';
            if (preg_match($pattern, $html_content)) {
                return true;
            }
        }
        return false;
    }


    private function checkPinterestPixel($html_content)
    {
        if (!empty($html_content)) {
            // Look for the occurrence of the pintrk function
            $pattern = '/pintrk\s*\(/i';
            if (preg_match($pattern, $html_content)) {
                return true;
            }
        }
        return false;
    }

}
