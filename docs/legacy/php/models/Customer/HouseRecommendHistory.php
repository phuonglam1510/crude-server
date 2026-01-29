<?php

namespace App\Models\Customer;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HouseRecommendHistory extends Model
{
    use BaseModel, SoftDeletes;
    protected $table = 'house_recommend_history';
    protected $fillable = ['id','request_id', 'user_id','house_id','customer_id','target','created_at'];

    public function House()
    {
        return $this->belongsTo('App\Models\House\House', 'house_id', 'id');
    }
    public function User()
    {
        return $this->belongsTo('App\Models\User\User', 'user_id', 'id');
    }

}
