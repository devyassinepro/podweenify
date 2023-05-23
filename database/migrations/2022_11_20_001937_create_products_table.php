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
            $table->string('timesparam',500);
            $table->float('prix');
            $table->float('revenue');
            // $table->integer('stores_id');
            $table->foreignId('stores_id');
            $table->string('url',500);
            $table->string('imageproduct',500);
            $table->integer('totalsales');
            $table->integer('favoris');
            $table->integer('todaysales');
            $table->integer('yesterdaysales');
            $table->integer('day3sales');
            $table->integer('day4sales');
            $table->integer('day5sales');
            $table->integer('day6sales');
            $table->integer('day7sales');
            $table->integer('weeksales');
            $table->integer('monthsales');
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
