<?php

namespace App\Models\House;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class HouseSearch extends Model
{
    use BaseModel;
    protected $table = 'house_search';
    protected $fillable = ['search', 'house_id', 'user_id', 'customer_id', 'request_id', 'project_id'];
}
