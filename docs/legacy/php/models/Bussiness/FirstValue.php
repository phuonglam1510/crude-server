<?php

namespace App\Models\Bussiness;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class FirstValue extends Model
{
    use HasApiTokens, Notifiable, BaseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product', 'customerInteraction','customerSeen','customerSeen2nd','productConnection','productTransaction','customerTransaction','transactionQuantity','user_id','created_at', 'updated_at'
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

    // public function Avatar()
    // {
    //     return $this->belongsTo(Image::class, 'profile_picture', 'id');
    // }
}
