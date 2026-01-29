<?php

namespace App\Models\Customer;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class RequestDirection extends Model
{
    use BaseModel;
    protected $table = 'customer_request_direction';
    protected $fillable = ['request_id', 'direction'];

}