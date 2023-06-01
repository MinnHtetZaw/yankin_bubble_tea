<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
    	'name',
    	'phone',
        'customer_code',
    	'frequent_item',
        'vipcard_number',
        'discount_percent',
        'tax_flag',
        'tax_percent',
    ];

    public function products(){
        return $this->belongsToMany('App\Product');
    }
    public function assignProduct($product) {
        return $this->products()->attach($product);
    }

    public function removeProduct($product) {
        return $this->products()->detach($product);
    }
}
