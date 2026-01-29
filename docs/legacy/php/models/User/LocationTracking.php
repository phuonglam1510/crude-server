<?php

namespace App\Models\User;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class LocationTracking extends Model
{
    use BaseModel;
    protected $table = 'location_tracking';
    protected $fillable = ['user_id', 'location', 'device_type', 'ip_address'];

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
}
