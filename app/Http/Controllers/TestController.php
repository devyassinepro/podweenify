<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
set_time_limit(0);
use App\Models\stores;
use Illuminate\Support\Facades\Route;


class TestController extends Controller
{
    //

    public function index()
    {
        //


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

                $productCounter = Product::where('id', $product->id)
                ->withCount(['todaysales', 'yesterdaysales'])->get();
        
                $productreq = array(
                    'title' => $product->title,
                    'timesparam' => $timestt,
                    'prix' => $product->variants[0]->price,
                    'revenue' => $revenuenow,
                    'stores_id' => $productbd->stores_id,
                    'imageproduct' => $product->images[0]->src,
                    'favoris' => $productbd->favoris,
                    'totalsales' => $sales,
                    'todaysales' => $productCounter->todaysales_count,
                    'yesterdaysales' => $productCounter->yesterdaysales_count,
                    'day3sales' => 10,
                    'day4sales' => 10,
                    'day5sales' => 10,
                    'day6sales' => 10,
                    'day7sales' => 10,
                    'weeksales' => 10,
                    'monthsales' => 10,
                );
    
                DB::table('products')->where('id', $productbd->id)->update($productreq);
    
                DB::table('sales')->insert([
                    "product_id" => $productbd->id,
                    "stores_id" => $productbd->stores_id,
                    "prix" => $productbd->prix,
                    'created_at' => Carbon::now()->format('Y-m-d'),
                    'updated_at' => Carbon::now()->format('Y-m-d')
                ]);
                echo $productCounter->todaysales_count; echo '<br />';
                echo $productCounter->yesterdaysales_count; echo '<br />';
                echo $product->title; echo '<br />';
                } 
        });//shoudl be updated now //ok wait


        // return view('index');
    }
}
