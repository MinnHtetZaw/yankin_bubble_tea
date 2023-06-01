<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplierPaymentHistory extends Model
{
    protected $fillable = [
    	'supplier_id',
    	'purchase_id',
    	'total_credit_amount',
    	'total_paid_amount',
    	'remaining_amount',
    	'payment_due_date',
    ];
}
