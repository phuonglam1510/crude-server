<?php

namespace App\Models\House;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlonhadatHouse extends Model
{
    use BaseModel, SoftDeletes;
    protected $table = 'alonhadat_houses';
    protected $fillable = [
        'house_id','house_number', 'house_address', 'property_type', 'house_type', 'floors', 'width', 'length','post_address',
        'end_open', 'area', 'into_money', 'user_id', 'public_image', 'project_id','village', 'district', 'province', 'number_bedroom',
        'street_type', 'wide_street', 'title', 'floor_area', 'description', 'purpose', 'ownership','floor_lot', 'block_section','descriptions',
        'internalDescription','type_news','type_news_value','commission', 'type_news_day', 'dinning_room', 'kitchen', 'terrace', 'car_parking', 'post', 'failed_quantity',
        'initialDescription','alonhadat_name' , 'alonhadat_password', 'update_status'
    ];


    public function setPublicImageAttribute($value)
    {
        if ($value && is_array($value)) {
            $this->attributes['public_image'] = json_encode($value);
        } elseif (is_string($value)) {
            $this->attributes['public_image'] = $value;
        } else {
            $this->attributes['public_image'] = json_encode([]);
        }
    }


    public function getPublicImageAttribute($value)
    {
        return json_decode($value);
    }

    public function Customer()
    {
        return $this->belongsTo('App\Models\Customer\Customer', 'customer_id', 'id');
    }

    public function HouseType()
    {
        return $this->belongsTo('App\Models\Dictionary\HouseType', 'property_type', 'id');
    }
    public function ProjectInfo()
    {
        return $this->belongsTo('App\Models\House\Project', 'project_id', 'id')->select(['id', 'name']);
    }
    public function HouseDirection()
    {
        return $this->hasMany(HouseDirection::class, 'house_id', 'id');
    }

    public function HouseBalconyDirection()
    {
        return $this->hasMany(HouseBalconyDirection::class, 'house_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo('App\Models\User\User', 'user_id', 'id')->select(['id', 'name', 'phone_number']);
    }
    public function HouseId()
    {
        return $this->hasMany('App\Models\TransactionSuccess\TransactionSuccess', 'house_id', 'id');
    }
    public function House()
    {
        return $this->belongsTo('App\Models\House\House', 'house_id', 'id');
    }
}
