<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VoucherTest extends Model
{   
    //John Edit for Cashback_flag and Cashback
    protected $fillable = [
    	'voucher_number',
    	'voucher_data',
    	'voucher_grand_total',
    	'total',
    	'promotion_id',
    	'customer_id',
    	'sold_by',
    	'date',
    	'cashback_flag',
    	'cashback',
        'employee_name',
    ];

    public function getVoucherDataAttribute($value) {
		return json_decode($value);
	}
}
