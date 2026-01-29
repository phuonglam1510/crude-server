<?php

namespace App\Models\Dictionary;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class CustomerType extends Model
{
    use BaseModel;
    protected $table = 'customer_type';
    protected $fillable = ['value'];
}
