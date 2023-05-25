<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Support\Facades\DB;


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
        $html = file_get_contents($store.'products.json?page='.$i.'&limit=250',false,$context);

        
        DB::table('apistatuses')->insert([
            "store" => $store,
            "status" => $http_response_header[0],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // echo $responsecode;
        $products = json_decode($html)->products;
        collect($products)->map(function ($product) {

            $productbd = Product::where('id', $product->id)->where('timesparam', '!=', strtotime($product->updated_at))->first();
            if($productbd) {

                //Ajouter La partie calcule Revenue chaque jours de la semaines 

                $productbd = $productbd->withCount(['todaysales', 'yesterdaysales' , 'day3sales' , 'day4sales' , 'day5sales' , 'day6sales', 'day7sales', 'weeklysales', 'montlysales'])->get();

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
                    'todaysales' => $productbd->todaysales_count,
                    'yesterdaysales' => $productbd->yesterdaysales_count,
                    'day3sales' => $productbd->day3sales_count,
                    'day4sales' => $productbd->day4sales_count,
                    'day5sales' => $productbd->day5sales_count,
                    'day6sales' => $productbd->day6sales_count,
                    'day7sales' => $productbd->day6sales_count,
                    'weeksales' => $productbd->weeklysales_count,
                    'monthsales' => $productbd->montlysales_count,
                );
    
                DB::table('products')->where('id', $productbd->id)->update($productreq);
    
                DB::table('sales')->insert([
                    "product_id" => $productbd->id,
                    "stores_id" => $productbd->stores_id,
                    "prix" => $productbd->prix,
                    'created_at' => Carbon::now()->format('Y-m-d'),
                    'updated_at' => Carbon::now()->format('Y-m-d')
                ]);
                echo $productbd->first()->todaysales_count; echo '<br />';
                echo $productbd->todaysales_count; echo '<br />';
                echo $product->title; echo '<br />';
                } 
        });//shoudl be updated now //ok wait


        // return view('index');
    }
}
