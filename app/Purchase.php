<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
    	'supplier_id',
    	'raw_material_id',
    	'purchase_qty',
    	'purchase_by',
        'purchase_price',
    	'purchase_date',
    	'timetick',
    	'total_amount',
    ];
}
