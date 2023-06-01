<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
    	'product_id',
    	'raw_material_id',
    	'unit_name',
    	'amount',
    ];

    public function prices(){
    	return $this->belongsToMany('App\Price');
    }

    public function product(){
    	return $this->belongsTo('App\Product');
    }

    public function rawmaterial(){
        return $this->belongsTo(RawMaterial::class,'raw_material_id');
    }
}
