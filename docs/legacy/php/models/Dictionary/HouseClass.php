<?php

namespace App\Models\Dictionary;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class HouseClass extends Model
{
    use BaseModel;
    protected $table = 'house_class';
    protected $fillable = ['value'];
}
