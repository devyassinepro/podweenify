<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Models\Apistatus;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoresController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataController;
use App\Jobs\ProcessApiStoreJob;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\stores;

set_time_limit(0);

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*///

Route::get('/', function () {
    return view('index');
});

// Start Queue every 2 min 
Route::get('/start',function (){
    $stores = Stores::select("*")
        ->where('status','1')
        ->get();
      
     ProcessApiStoreJob::dispatch($stores); 
      echo $stores; echo '<br />';
});