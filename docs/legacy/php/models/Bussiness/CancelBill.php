<?php

namespace App\Models\Bussiness;

use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\BaseModel;
use App\Models\User\User;
use App\Models\House\House;
use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;

class CancelBill extends Model
{
    use HasApiTokens, Notifiable, BaseModel;
    protected $table = 'cancel_bill';
    protected $guarded = ['id'];
    protected $fillable = [
        'transactionCode', 'feeCollected', 'house_id', 'transaction_id', 'cancelDate',
        'staff_id', 'customer_id','createBy', 'note', 'created_at', 'updated_at'
    ];
    
    public function getCreateAtAttribute($value)
    {
        return strtotime($value);
    }

    public function House(){
        return $this->hasOne(House::class, 'id', 'house_id');
    }
    public function Staff()
    {
        return $this->hasOne(User::class, 'id', 'staff_id');
    }
    public function Customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
    public function User()
    {
        return $this->hasOne(User::class, 'id', 'createBy');
    }
}

