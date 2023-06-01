<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
    	'name',
    	'category_id',
    	'description',
    	'photo',
        'discount_id',
    	'custom_discount_id',
    	'custom_promotion_id',
        'option_flag',
    ];

    public function getSizeOfIngredientAttribute($value) {
		return json_decode($value);
	}

    public function customers(){
        return $this->belongsToMany('App\Customer');
    }
    public function ingredients(){
        return $this->hasMany('App\Ingredient');
    }
    

}
