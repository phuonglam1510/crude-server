<?php

namespace App\Models\House;


use App\Models\BaseModel;
use App\Models\Bussiness\Accumulation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Awobaz\Compoships\Compoships;

class HouseStatistic extends Model
{
    use BaseModel;
    protected $table = 'houses_statistic';
    protected $fillable = [
        'id','date','user_id','sold_quantity','house_remain','created_at','updated_at'
    ];

    public function getDateAttribute($value){
        return explode(' ',$value)[0];
    }
     public function Accumulation()
    {
        return $this->belongsTo(Accumulation::class, 'user_id','user_id');
    }
}

