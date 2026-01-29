<?php

namespace App\Models\House;

use App\Models\BaseModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class HouseLike extends Model
{
    use BaseModel;
    protected $table = 'house_like';
    protected $fillable = ['house_id', 'user_id'];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
