<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('discount_type_flag')->comment('0-percent,1-fixed,2-foc');
            $table->integer('discount_percent')->nullable();
            $table->integer('discount_fixed_amount')->nullable();
            $table->integer('discount_product_id')->nullable();
            $table->integer('discount_applied_flag');
            $table->integer('applied_type_id')->nullable();
            $table->integer('condition_type_flag')->comment('0-amount,1-range,2-item_qty');
            $table->integer('condition_amount')->nullable()->comment('condition for 0');
            $table->integer('condition_range_from')->nullable()->comment('condition for 1');
            $table->integer('condition_range_to')->nullable()->comment('condition for 1');
            $table->integer('condition_product_id')->nullable()->comment('condition for 2');
            $table->integer('condition_product_qty')->nullable()->comment('condition for 2');
            $table->date('discount_period_from');
            $table->date('discount_period_to');
            $table->integer('unlimited_time_flag');
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
        Schema::dropIfExists('custom_discounts');
    }
}
