<?php

namespace App\Models\House;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HouseTag extends Model
{
    use BaseModel, SoftDeletes;
    protected $table = 'house_tag';
    protected $fillable = [
        'id', 'house_id', 'user_id', 'tag_id', 'created_at', 'updated_at', 'deleted_at'
    ];
    
    public function House()
    {
        return $this->belongsTo('App\Models\House\House', 'id', 'house_id');
    }
    public function Tag()
    {
        return $this->hasOne('App\Models\House\Tag', 'id', 'tag_id');
    }
    public function User()
    {
        return $this->belongsTo('App\Models\User\User', 'user_id', 'id');
    }
}
