<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryService extends Model
{
    protected $fillable = [
    	'name',
    	'address',
    	'phone',
    	'email',
    	'delivery_capacity',
    	'delivery_unit',
    	'delivery_vehicle',
    	'delivery_time_from',
    	'delivery_time_to',
    	'delivery_township',
    	'product_id',
    	'deliverey_charge',
    ];
}
