<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FocLog extends Model
{
    protected $fillable = [
    	'loyalty_card_number',
    	'product_id',
    	'pay_date',
    ];
}
