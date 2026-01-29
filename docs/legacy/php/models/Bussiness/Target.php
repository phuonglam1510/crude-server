<?php

namespace App\Models\Bussiness;

use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\BaseModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;


class Target extends Model
{
    use HasApiTokens, Notifiable, BaseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','user_id','quarter', 'startAt', 'endAt', 'purchase', 'connection',
        'created_at', 'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password', 'remember_token',
    // ];

    // public function setPasswordAttribute($value)
    // {
    //     $hash = config('API.Constant.App.Secret');
    //     $this->attributes['password'] = Hash::make($value . $hash);
    // }

    public function setStartAtAttribute($value)
    {
        $this->attributes['startAt'] = strtotime($value);
    }

    public function setEndAtAttribute($value)
    {
        $this->attributes['endAt'] = strtotime($value);
    }
    public function User()
    {
        return $this->hasOne(User::class,'id','user_id');
    }
    


}
