<?php

namespace App\Models\User;


use App\Models\BaseModel;
use App\Models\House\House;
use App\Models\House\HouseComment;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use BaseModel;
    protected $table = 'notification';
    protected $fillable = ['user_id', 'target_id', 'is_admin', 'house_id', 'type', 'status'];

    public function House()
    {
        return $this->belongsTo(House::class, 'house_id', 'id');
    }
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function Target()
    {
        return $this->belongsTo(User::class, 'target_id', 'id');
    }
    public function Comments()
    {
        return $this->hasMany(HouseComment::class, 'house_id', 'id');
    }
}
