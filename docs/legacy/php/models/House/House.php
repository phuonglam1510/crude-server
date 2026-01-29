<?php

namespace App\Models\House;


use App\Models\BaseModel;
use App\Models\Customer\HouseRecommendStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class House extends Model
{
    use BaseModel, SoftDeletes;
    protected $table = 'houses';
    protected $fillable = [
        'sign', 'house_number', 'house_address', 'property_type', 'house_type', 'floors', 'width', 'length',
        'end_open', 'area', 'into_money', 'user_id', 'internal_image', 'public_image', 'customer_id', 'project_id',
        'village', 'district', 'province', 'city_id', 'brokerage_rate', 'brokerage_fee', 'number_bedroom', 'number_wc', 'status',
        'street_type', 'wide_street', 'title', 'floor_area', 'description', 'suitable_customer', 'offered_customer',
        'seen_customer', 'require_info_customer', 'deposit_customer', 'approve', 'public', 'web', 'purpose', 'ownership',
        'total_view', 'public_approval', 'slug', 'floor_lot', 'block_section', 'descriptions', 'key_word', 'recommnend_quantity', 'seen_quantity', 'postQuantity',
        'internalDescription', 'type_news', 'type_news_value', 'commission', 'type_news_day', 'dinning_room', 'kitchen', 'terrace', 'car_parking',
        'initialDescription', 'reject_public_condition', 'reject_web_condition', 'reason_stop_selling', 'file_ids'
    ];

    public function getNonHistory()
    {
        return [
            'updated_at', 'created_at', 'total_view', 'slug'
        ];
    }

    public function setInternalImageAttribute($value)
    {
        if ($value && is_array($value)) {
            $this->attributes['internal_image'] = json_encode($value);
        } elseif (is_string($value)) {
            $this->attributes['internal_image'] = $value;
        } else {
            $this->attributes['internal_image'] = json_encode([]);
        }
    }

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

    public function setFileIdsAttribute($value)
    {
        if ($value && is_array($value)) {
            $this->attributes['file_ids'] = json_encode($value);
        } elseif (is_string($value)) {
            $this->attributes['file_ids'] = $value;
        } else {
            $this->attributes['file_ids'] = json_encode([]);
        }
    }

    public function getInternalImageAttribute($value)
    {
        return json_decode($value);
    }

    public function getPublicImageAttribute($value)
    {
        return json_decode($value);
    }

    public function getFileIdsAttribute($value)
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

    public function City()
    {
        return $this->belongsTo('App\Models\Dictionary\City', 'city_id', 'id');
    }

    public function Project()
    {
        return $this->belongsTo('App\Models\House\Project', 'project_id', 'id');
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
        return $this->belongsTo('App\Models\User\User', 'user_id', 'id');
    }
    public function HouseId()
    {
        return $this->hasMany('App\Models\TransactionSuccess\TransactionSuccess', 'house_id', 'id');
    }
    public function HouseTag()
    {
        return $this->hasMany('App\Models\House\HouseTag', 'house_id', 'id');
    }
    public function Search()
    {
        return $this->belongsTo(HouseSearch::class, 'id', 'house_id');
    }
    public function Drafts()
    {
        return $this->hasMany(HouseDraft::class, 'house_id', 'id');
    }

    public function Comments()
    {
        return $this->hasMany(HouseComment::class, 'house_id', 'id');
    }
    public function Likes()
    {
        return $this->hasMany(HouseLike::class, 'house_id', 'id');
    }
    public function HouseRecommendStatus()
    {
        return $this->hasMany(HouseRecommendStatus::class, 'house_id', 'id');
    }
}
