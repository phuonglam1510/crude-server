<?php

namespace App\Models\Customer;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BaseModel, SoftDeletes;
    protected $table = 'customer';
    protected $fillable = ['user_id', 'name', 'address', 'phone_number','phone_number2','phone_number3','phone_number4', 'email', 'fax', 'social_id', 'date_of_birth',
        'sell_need', 'rent_need', 'public', 'description', 'image','character'];
 
    public function User()
    {
        return $this->belongsTo('App\Models\User\User', 'user_id', 'id');
    }
}
