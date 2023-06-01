<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = [
    	'name',
    	'product_id',
    	'raw_material_id',
    	'amount',
    	'size',
    ];
}
