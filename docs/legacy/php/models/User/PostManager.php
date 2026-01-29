<?php

namespace App\Models\User;


use App\Models\BaseModel;
use App\Models\House\House;
use App\Models\House\BatdongsanHouse;
use App\Models\House\AlonhadatHouse;
use App\Models\User\User;
use App\Models\User\PostAddressStatus;
use Illuminate\Database\Eloquent\Model;

class PostManager extends Model
{
    use BaseModel;
    protected $table = 'post_manager';
    protected $fillable = ['id', 'house_id', 'user_id', 'status_id', 'channel', 'link', 'created_at', 'updated_at'];
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function BatdongsanHouse()
    {
        return $this->belongsTo(BatdongsanHouse::class, 'house_id', 'house_id');
    }

    public function House()
    {
        return $this->belongsTo(House::class, 'house_id', 'id');
    }

    public function AlonhadatHouse()
    {
        return $this->belongsTo('App\Models\House\AlonhadatHouse', 'house_id', 'house_id');
    }

    // public function available_AloNhaDatHouse()
    // {
    //     return $this->AloNhaDatHouse()->where('post_address', '=', 'alonhadat.vn');
    // }
    public function PostAddressStatus()
    {
        return $this->hasOne(PostAddressStatus::class, 'id', 'status_id');
    }


    // public function setLink($value){

    //     if($value&& is_array($value)){
    //         $this->attributes['link'] = json_encode($value);
    //     }else{
    //          $this->attributes['link'] = json_encode([]);
    //     }

    // }
    // public function getLink($value){

    //     return json_decode($value);

    // }
}

