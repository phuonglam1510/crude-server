<?php

namespace App\Models\Bussiness;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use App\Models\BaseModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class TransactionSuccess extends Model
{
    use HasApiTokens, Notifiable, BaseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','date','address', 'price', 'transactionType', 'brokerageFee','brokerageFeeStatus','brokerageFeeReceived','brokerageFeeReceivable', 'brokerageReceiveDay', 'notarizedDay', 'note', 'user_id', 'created_at', 'updated_at', 'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password', 'remember_token',
    // ];

    public function getCreateAtAttribute($value){
        return strtotime($value);
    }
    // public function getDateAttribute($value){
    //    $date = date_create($value, timezone_open('Asia/Ho_Chi_Minh'));
    //    return $date->$date;
    // }
    public function getStatusAttribute($value){
        if($value ==1){
            $result = 'Giữ chổ';
        };
        if($value == 2){
            $result = 'Đặt cọc';
        };
        if($value == 3){
            $result = 'Công chứng hợp đồng mua bán';
        };
        if($value == 4){
            $result ='Đăng bộ sang tên';
        };

        return $result;
    }
    public function getBrokerageFeeStatusAttribute($value){
        if($value ==1){
            $result = 'Chưa thu tiền';
        };
        if($value == 2){
            $result = 'Nhận 1 phần';
        };
        if($value == 3){
            $result = 'Thu tiền ngay';
        };
        return $result;
    }
    public function User()
    {
        return $this->hasOne(User::class,'id','user_id');
    }
 }
