<?php

namespace App\Models\Dictionary;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class HouseType extends Model
{
    use BaseModel;
    protected $table = 'house_type';
    protected $fillable = ['value'];
}
