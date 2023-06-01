<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = [
    	'product_id',
    	'size',
    	'sell_price',
        'deli_price'
    ];

    public function ingredients(){
    	return $this->belongsToMany('App\Ingredient');
    }

    public function assignIngredient($ingredient) {
        return $this->ingredients()->attach($ingredient);
    }

    public function removeIngredient($ingredient) {
        return $this->ingredients()->detach($ingredient);
    }
}
