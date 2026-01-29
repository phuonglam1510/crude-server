<?php

namespace App\Models\Checkin;

use App\Models\BaseModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class Checkin extends Model
{
    use BaseModel;
    protected $table = 'checkin';
    protected $fillable = ['user_id', 'location', 'device_type', 'ip_address','time', 'shift', 'created_at', 'updated_at', 'status'];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ];
    public function setLocationAttribute($value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $this->attributes['location'] = $value;
    }

    public function getLocationAttribute($value)
    {
        if (is_object(json_decode($value))) {
            return json_decode($value);
        }

        return $value;
    }

     public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // public function UserCheckIn()
    // {
    //     return $this->belongsTo(User::class, 'user_id', 'id')->where('status', 0);
    // }

    // public function UserCheckOut()
    // {
    //     return $this->belongsTo(User::class, 'user_id', 'id')->where('status', 1);
    // }
}

