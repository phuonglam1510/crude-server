<?php

namespace App\Models\Bussiness;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use App\Models\BaseModel;
use App\Models\User\User;
use App\Models\House\HouseStatistic;
use Illuminate\Database\Eloquent\Model;
use Compoships;


class Accumulation extends Model
{
    use HasApiTokens, Notifiable, BaseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','date','marketingQuantity', 'saleQuantity', 'saleHouseSeenQuantity', 'saleCustomerSeenQuantity',
        'newSaleNegotiationQuantity', 'purchaseQuantity', 'purchaseCustomerSeenQuantity', 'purchaseToTakeCustomerSeenHouse',
        'purchaseProductSeenQuantity', 'purchaseCustomerSeen2ndQuantity','newPurchaseNegotiationQuantity','connectionQuantity',
        'connectionCustomerSeenQuantity','suitableConnectionProductQuantity','connectionProductSeenQuantity','newConnectionNegotiationQuantity',
        'user_id','created_at','updated_at','status'
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

    // public function setDateOfBirthAttribute($value)
    // {
    //     $this->attributes['date_of_birth'] = date('Y-m-d', strtotime($value));
    // }

    // public function setProvidedDateAttribute($value)
    // {
    //     $this->attributes['provided_date'] = date('Y-m-d', strtotime($value));
    // }
    public function User()
    {
        return $this->hasOne(User::class,'id','user_id');
    }
    public function HouseStatistic()
    {
       return $this->hasMany(HouseStatistic::class, 'user_id','user_id');
    }
    


}

