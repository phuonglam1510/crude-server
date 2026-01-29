<?php

namespace App\Models\Bussiness;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use App\Models\BaseModel;
use App\Models\User\User;
use App\Models\Customer\Customer;
use App\Models\House\House;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasApiTokens, Notifiable, BaseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'brokerageFee', 'brokerageReceiveDate', 'date', 'house_id', 'notarizedDate', 'price', 'reservationDate', 
        'brokerageFeeReceived', 'users_group', 'user_id', 'created_at', 'updated_at', 'status',
        'staff_id', 'customer_id' ,'transactionCode', 'createBy', 'transaction_type', 'brokerageFeeUser', 'brokerage_rate', 'note'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password', 'remember_token',
    // ];

    protected $casts = [
        'users_group' => 'array'
    ];

    public function getCreateAtAttribute($value)
    {
        return strtotime($value);
    }

    // public function getUsersGroupAttribute($value)
    // {
    //     return unserialize($value);
    // }
    // public function getDateAttribute($value){
    //    $date = date_create($value, timezone_open('Asia/Ho_Chi_Minh'));
    //    return $date->$date;
    // }
    public function getStatusAttribute($value)
    {   $result = '';
        if ($value == 1) {
            $result = 'Giữ chổ';
        };
        if ($value == 2) {
            $result = 'Đặt cọc';
        };
        if ($value == 3) {
            $result = 'Công chứng hợp đồng mua bán';
        };
        if ($value == 4) {
            $result = 'Đăng bộ sang tên';
        };

        return $result;
    }

    public function setStatusAttribute($value)
    {  
        if(is_numeric($value)) {
            $this->attributes['status'] = $value;
        }
        if ($value == 'Giữ chỗ') {
            $this->attributes['status'] = 1;
        };
        if ($value == 'Đặt cọc') {
            $this->attributes['status'] = 2;
        };
        if ($value == 'Công chứng hợp đồng mua bán') {
            $this->attributes['status'] = 3;
        };
        if ($value == 'Đăng bộ sang tên') {
            $this->attributes['status'] = 4;

        };
    }

    // public function getBrokerageFeeStatusAttribute($value)
    // {
    //     if ($value == 1) {
    //         $result = 'Chưa thu tiền';
    //     };
    //     if ($value == 2) {
    //         $result = 'Nhận 1 phần';
    //     };
    //     if ($value == 3) {
    //         $result = 'Thu tiền ngay';
    //     };
    //     return $result;
    // }
    public function getTransactionTypeAttribute($value)
    {   
        $result = '';
        if ($value == 1) {
            $result = 'Khách hàng';
        };
        if ($value == 2) {
            $result = 'Chủ';
        };
        if ($value == 3) {
            $result = 'Trung gian';
        };
        if ($value == 4) {
            $result = 'Chính khách - chính chủ';
        };

        return $result;
    }


    public function setTransactionTypeAttribute($value)
    {   
        if(is_numeric($value)) {
            $this->attributes['transaction_type'] = $value;
        }
        if ($value == 'Khách hàng') {
            $this->attributes['transaction_type'] = 1;
        };
        if ($value == 'Chủ') {
            $this->attributes['transaction_type'] = 2;
        };
        if ($value == 'Trung gian') {
            $this->attributes['transaction_type'] = 3;
        };
        if ($value == 'Chính khách chính chủ') {
            $this->attributes['transaction_type'] = 4;
        };
    }

    public function House(){
        return $this->hasOne(House::class, 'id', 'house_id');
    }
    public function User()
    {
        return $this->hasOne(User::class, 'id', 'createBy');
    }
    public function Staff()
    {
        return $this->hasOne(User::class, 'id', 'staff_id');
    }
    public function Customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
    public function UsersGroup()
    {
        return $this->hasMany(User::class, 'id', $this->users_group);
    }
}

