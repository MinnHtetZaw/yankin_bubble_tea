<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->nullable();
            $table->longText('voucher_data');
            $table->integer('voucher_grand_total');
            $table->integer('total_discount')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('sold_by');
            $table->string('employee_name');
            $table->date('date');
            $table->integer('cashback_flag')->default(0);
            $table->bigInteger('cashback')->default(0)->after('date');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
}
