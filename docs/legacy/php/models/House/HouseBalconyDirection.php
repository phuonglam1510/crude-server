<?php

namespace App\Models\House;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class HouseBalconyDirection extends Model
{
    use BaseModel;
    protected $table = 'house_balcony_direction';
    protected $fillable = ['house_id', 'balcony'];
}
