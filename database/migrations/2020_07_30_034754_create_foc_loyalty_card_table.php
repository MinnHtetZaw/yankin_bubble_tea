<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFocLoyaltyCardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('foc_loyalty_card', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loyalty_card_id');
            $table->unsignedBigInteger('foc_id');
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
        Schema::dropIfExists('foc_loyalty_card');
    }
}
