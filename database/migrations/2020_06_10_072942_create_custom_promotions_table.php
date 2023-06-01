<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomPromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('condition')->comment('0-amount,1-product');
            $table->integer('condition_amount')->nullable()->comment('condition for 0');
            $table->integer('condition_product_id')->nullable()->comment('condition for 1');
            $table->integer('condition_product_qty')->nullable()->comment('condition for 1');
            $table->integer('reward_flag')->comment('0-cashback,1-discount,2-product');
            $table->integer('cashback_amount')->nullable()->comment('reward for 0');
            $table->integer('discount_flag')->nullable()->comment('reward for 1');
            $table->integer('custom_discount_id')->nullable()->comment('discount flag for 0');
            $table->integer('discount_percent')->nullable()->comment('discount flag for 1');
            $table->integer('reward_product_id')->nullable()->comment('reward for 2');
            $table->date('promotion_period_from');
            $table->date('promotion_period_to');
            $table->integer('link_customer_flag');
            $table->integer('announce_customer_flag');
            $table->string('description');
            $table->string('photo')->nullable();
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
        Schema::dropIfExists('custom_promotions');
    }
}
