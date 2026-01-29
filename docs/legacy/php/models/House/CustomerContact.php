<?php

namespace App\Models\House;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerContact extends Model
{
    use BaseModel;
    protected $table = 'customer_contacts';
    protected $fillable = ['name', 'house_id', 'phone_number', 'name', 'email', 'note', 'status', 'processed_by'];

   
    public function House()
    {
        return $this->belongsTo('App\Models\House\House', 'house_id', 'id');
    }
    public function User()
    {
        return $this->belongsTo('App\Models\User\User', 'processed_by', 'id');
    }
}

