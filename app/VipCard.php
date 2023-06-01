<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VipCard extends Model
{
    protected $fillable = [
    	'card_number',
    	'loyalty_number',
    	'discount',
    	'customer_id',
    	'customer_name',
    	'consume',
    ];
}
