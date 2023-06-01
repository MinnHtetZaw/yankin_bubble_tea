<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplierRepaymentHistory extends Model
{
    protected $fillable = [
    	'supplier_id',
    	'supplier_payment_history_id',
    	'paid_amount',
    	'paid_timetick',
    ];
}
