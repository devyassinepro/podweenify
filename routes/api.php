<?php

use App\Jobs\ProcessApiStoreJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// iwill test again ?

// Route::resource('products', ProductController::class);

// Route::post('data', function(Request $request) {
//     Log::info($request->all());//this is the receiver??yes
//     $post = '';

//     ProcessApiStoreJob::dispatch($request->post('stores'));
   
//     return response()->json([
//         'status' => true,
//         'message' => "Processing stores in backgroudn....",
//         'post' => $post
//     ], 200);
// });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
