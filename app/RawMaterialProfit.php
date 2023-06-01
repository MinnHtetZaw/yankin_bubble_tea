<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RawMaterialProfit extends Model
{
    protected $fillable = [
    	'raw_material_id',
    	'total_profits',
    	'voucher_date',
    ];
}
