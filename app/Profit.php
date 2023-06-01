<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profit extends Model
{
    protected $fillable = [
    	'product_id',
    	'price_id',
    	'voucher_date',
    	'total_profits',
    	'qty',
    ];
}
