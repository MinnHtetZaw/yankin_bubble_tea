<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Foc extends Model
{
    protected $fillable = [
    	'promotion_id',
    	'product_id',
    	'count',
    	'status',
    ];

    public function loyalty_cards() {
        return $this->belongsToMany(LoyaltyCard::class);
    }
}
