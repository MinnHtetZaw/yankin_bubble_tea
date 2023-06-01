<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomDiscount extends Model
{
    protected $fillable = [
    	'name',
    	'discount_type_flag',
    	'discount_percent',
    	'discount_fixed_amount',
    	'discount_product_id',
    	'discount_applied_flag',
    	'applied_type_id',
    	'condition_type_flag',
    	'condition_amount',
    	'condition_range_from',
    	'condition_range_to',
    	'condition_product_id',
    	'condition_product_qty',
    	'discount_period_from',
    	'discount_period_to',
    	'unlimited_time_flag',
    	'announce_customer_flag',
    	'description',
    	'photo',
    ];

}
