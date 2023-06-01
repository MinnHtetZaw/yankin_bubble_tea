<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoyaltyCard extends Model
{
    protected $fillable = [
    	'card_number',
    	'promotion_id',
    	'customer_id',
    	'customer_name',
    	'count',
    	'product_id',
    	'status',
    ];

    public function focs() {
        return $this->belongsToMany(Foc::class);
    }

    public function assignFoc($foc) {
        return $this->focs()->attach($foc);
    }

    public function removeFoc($focs) {
        return $this->focs()->detach($foc);
    }
}
