<?php

namespace App\Models\Customer;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HouseRecommendStatus extends Model
{
    use BaseModel, SoftDeletes;
    protected $table = 'house_recommend_status';
    protected $fillable = ['id', 'customer_id', 'house_id','status','priority'];

}
