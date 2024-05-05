<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('status');
            $table->float('revenue');
            $table->string('city');
            $table->string('country');
            $table->string('currency');
            $table->string('shopifydomain');
            $table->integer('dropshipping');
            $table->integer('tshirt');
            $table->integer('digital');
            $table->integer('sales');
            $table->integer('allproducts');
            $table->integer('user_id');
            $table->integer('todaysales');
            $table->integer('yesterdaysales');
            $table->integer('day3sales');
            $table->integer('day4sales');
            $table->integer('day5sales');
            $table->integer('day6sales');
            $table->integer('day7sales');
            $table->integer('weeksales');
            $table->integer('monthsales');
            $table->string('title');
            $table->string('description');
            $table->string('theme');
            $table->string('facebookusername');
            $table->string('pinterestusername');
            $table->string('instagramusername');
            $table->string('youtubeusername');
            $table->string('tiktokusername');
            $table->string('snapchatusername');
            $table->integer('facebookpixel');
            $table->integer('googlepixel');
            $table->integer('snapchatpixel');
            $table->integer('pinterestpixel');
            $table->integer('tiktokpixel');
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
        Schema::dropIfExists('stores');
    }
}
