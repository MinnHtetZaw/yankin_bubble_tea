<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
    	'name',
    	'phone',
    	'address',
    	'brand_id',
    	'credit_amount',
    	'repayment_period',
    	'repayment_date',
    ];
    
    public function assignBrand($brand) {
        return $this->brands()->attach($brand);
    }

    public function removeBrand($brand) {
        return $this->brands->detach($brand);
    }

    public function brands(){
    	return $this->belongsToMany('App\Brand');
    }
}
