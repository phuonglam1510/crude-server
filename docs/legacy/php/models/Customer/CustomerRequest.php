<?php

namespace App\Models\Customer;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerRequest extends Model
{
    use BaseModel, SoftDeletes;
    protected $table = 'customer_request';
    protected $fillable = [
        'user_id', 'customer_id', 'property_type', 'min_price', 'max_price', 'purpose',
        'house_type', 'description', 'province', 'area', 'floors', 'number_bedroom', 'public', 'status', 'districts', 'min_width', 'road_area','staff','manager',
        'staff_role','manager_role', 'require_column'
    ];

    public function setRequireColumnAttribute($value)
    {
        if ($value) {
            $this->attributes['require_column'] = json_encode($value);
        } 
    }
    public function setPropertyTypeAttribute($value)
    {
        if ($value) {
            $this->attributes['property_type'] = json_encode($value);
        } 
    }
    public function setManagerAttribute($value)
    {
        if ($value && $value!=0) {
            $this->attributes['manager'] = json_encode($value);
        }else{
            $this->attributes['manager'] = json_encode([]);
        }
    }

    public function setHouseTypeAttribute($value)
    {
        if ($value && is_array($value)) {
            $this->attributes['house_type'] = json_encode($value);
        } else {
            $this->attributes['house_type'] = json_encode([]);
        }
    }
     
    public function setDistrictsAttribute($value)
    {
        if ($value && is_array($value)) {
            $this->attributes['districts'] = json_encode($value);
        } else {
            $this->attributes['districts'] = json_encode([]);
        }
    }
    public function setProvinceAttribute($value)
    {
        if ($value && is_array($value)) {
            $this->attributes['province'] = json_encode($value);
        } else {
            $this->attributes['province'] = json_encode([]);
        }
    }
    public function setStaffAttribute($value)
    {
        if ($value && is_array($value)) {
            $this->attributes['staff'] = json_encode($value);
        } else {
            $this->attributes['staff'] = json_encode([]);
        }
    }

    public function getRequireColumnAttribute($value)
    {
        return json_decode($value);
    }
    public function getManagerAttribute($value)
    {
        return json_decode($value);
    }
    public function getStaffAttribute($value)
    {
        return json_decode($value);
    }
    public function getDistrictsAttribute($value)
    {
        return json_decode($value);
    }
    public function getPropertyTypeAttribute($value)
    {
        return json_decode($value);
    }
    public function getHouseTypeAttribute($value)
    {
        return json_decode($value);
    }
    public function getProvinceAttribute($value)
    {
        $result = json_decode($value);
        if (json_last_error()) {
            return $value;
        } else {
            return json_decode($value);
        }
    }


    public function Customer()
    {
        return $this->belongsTo('App\Models\Customer\Customer', 'customer_id', 'id');
    }

    public function RequestDirection()
    {
        return $this->hasMany(RequestDirection::class, 'request_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo('App\Models\User\User', 'user_id', 'id');
    }
}
