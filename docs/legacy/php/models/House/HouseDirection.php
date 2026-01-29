<?php

namespace App\Models\House;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class HouseDirection extends Model
{
    use BaseModel;
    protected $table = 'house_direction';
    protected $fillable = ['house_id', 'direction'];
}