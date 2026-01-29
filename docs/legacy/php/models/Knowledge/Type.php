<?php

namespace App\Models\Knowledge;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use App\Models\BaseModel;
use App\Models\User\User;
use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use App\Models\House\House;

class Type extends Model
{
    use HasApiTokens, Notifiable, BaseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type_name', 'created_at', 'updated_at'];

   
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password', 'remember_token',
    // ];
}

