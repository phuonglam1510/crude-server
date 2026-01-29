<?php


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

trait BaseModel
{
    public function getDateFormat()
    {
        return 'U';
    }

//    public function setCreatedAtAttribute($value)
//    {
//        if (is_object($value)) {
//            $this->attributes['created_at'] = $value->getTimestamp();
//        } else {
//            $this->attributes['created_at'] = strtotime($value);
//        }
//    }
//
//    public function setUpdatedAtAttribute($value)
//    {
//        if (is_object($value)) {
//            $this->attributes['updated_at'] = $value->getTimestamp();
//        } else {
//            $this->attributes['updated_at'] = strtotime($value);
//        }
//
//    }
//
//    public function setDeletedAtAttribute($value)
//    {
//        if (is_object($value)) {
//            $this->attributes['deleted_at'] = $value->getTimestamp();
//        } else {
//            $this->attributes['deleted_at'] = strtotime($value);
//        }
//    }
//
//    public function getCreatedAtAttribute($value)
//    {
//        return strtotime($value);
//    }
//
//    public function getUpdatedAtAttribute($value)
//    {
//        return strtotime($value);
//    }
}
