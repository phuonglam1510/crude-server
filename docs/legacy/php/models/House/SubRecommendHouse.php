<?php

namespace App\Models\House;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubRecommendHouse extends Model
{
    use BaseModel, SoftDeletes;
     protected $table = 'sub_recommend_houses';
    protected $fillable = [
        "id", 'house_id', 'request_id', 'customer_id','created_at', 'updated_at', 'deleted_at'
    ];

    
    public function Customer()
    {
        return $this->belongsTo('App\Models\Customer\Customer', 'customer_id', 'id');
    }

    public function House()
    {
        return $this->belongsTo('App\Models\House\House', 'house_id', 'id');
    }

}
