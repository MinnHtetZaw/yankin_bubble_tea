<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    protected $fillable = [
    	'name',
    	'category_id',
    	'brand_id',
    	'supplier_id',
    	'instock_qty',
    	'reorder_qty',
    	'unit',
    	'purchase_price',
    	'currency',
    	'topping_flag',
    	'topping_sales_amount',
    	'topping_sales_price',
    	'topping_photo_path',
    	'special_flag',
    ];
}
