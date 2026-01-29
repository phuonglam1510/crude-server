<?php

namespace App\Models\Dictionary;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use BaseModel;
    protected $table = 'city';
    protected $fillable = ['value'];

   
}
