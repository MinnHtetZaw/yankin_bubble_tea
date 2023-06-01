<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
    	'name',
    	'email',
    	'phone',
    	'address',
    	'photo',
    	'nrc',
    	'salary',
    	'user_id'
    ];
}
