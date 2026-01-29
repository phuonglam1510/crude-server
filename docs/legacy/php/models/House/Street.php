<?php

namespace App\Models\House;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Street extends Model
{
    use BaseModel;
    protected $table = 'street';
    protected $fillable = ['street_name'];
}