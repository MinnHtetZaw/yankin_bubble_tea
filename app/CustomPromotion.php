<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomPromotion extends Model
{
    protected $fillable = [
    	'name',
    	'condition',
    	'condition_amount',
    	'condition_product_id',
    	'condition_product_qty',
    	'reward_flag',
    	'cashback_amount',
    	'discount_flag',
    	'custom_discount_id',
    	'discount_percent',
    	'reward_product_id',
    	'promotion_period_from',
    	'promotion_period_to',
    	'link_customer_flag',
    	'announce_customer_flag',
    	'description',
    	'photo',
    ];
}
