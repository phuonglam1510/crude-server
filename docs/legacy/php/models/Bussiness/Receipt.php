<?php

namespace App\Models\Bussiness;

use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\BaseModel;
use App\Models\User\User;
use App\Models\House\House;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasApiTokens, Notifiable, BaseModel;

    protected $guarded = ['id'];
    
    public function getCreateAtAttribute($value)
    {
        return strtotime($value);
    }

    public function House(){
        return $this->hasOne(House::class, 'id', 'house_id');
    }
    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}

