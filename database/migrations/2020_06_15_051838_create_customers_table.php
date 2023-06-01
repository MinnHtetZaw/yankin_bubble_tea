<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->integer('vipcard_number')->nullable();
            $table->string('customer_code')->nullable();
            $table->string('name');
            $table->string('phone');
            $table->text('frequent_item')->nullable();
            $table->integer('discount_percent')->default(0);
            $table->integer('tax_flag')->default(0);
            $table->integer('tax_percent')->default(0);
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
        Schema::dropIfExists('customers');
    }
}
