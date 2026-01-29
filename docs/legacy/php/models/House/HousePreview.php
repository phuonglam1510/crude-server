<?php

namespace App\Models\House;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class HousePreview extends Model
{
    use BaseModel;
    protected $table = 'house_preview';
    protected $fillable = ['house_id', 'user_id'];

    public function User()
    {
        return $this->belongsTo('App\Models\User\User', 'user_id', 'id');
    }

    public function House()
    {
        return $this->belongsTo('App\Models\House\House', 'house_id', 'id');
    }
}