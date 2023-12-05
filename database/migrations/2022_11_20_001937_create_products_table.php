<?php

use App\Models\Sales;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title',500);
            $table->text('description');
            $table->string('timesparam',500);
            $table->float('prix');
            $table->float('revenue');
            // $table->integer('stores_id');
            $table->foreignId('stores_id');
            $table->string('url',500);
            $table->string('imageproduct',500);
            $table->integer('totalsales');
            $table->integer('favoris');
            $table->integer('dropshipping');
            $table->integer('todaysales');
            $table->integer('yesterdaysales');
            $table->integer('day3sales');
            $table->integer('day4sales');
            $table->integer('day5sales');
            $table->integer('day6sales');
            $table->integer('day7sales');
            $table->integer('weeksales');
            $table->integer('monthsales');
            $table->date('created_at_shopify')->nullable();
            $table->date('created_at_favorite')->nullable();
            $table->float('price_aliexpress');
            $table->string('image2',500);
            $table->string('image3',500);
            $table->string('image4',500);
            $table->string('image5',500);
            $table->string('image6',500);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
