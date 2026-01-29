<?php

namespace App\Models\User;


use App\Models\BaseModel;
use App\Models\User\User;
use App\Models\User\PostManager;
use Illuminate\Database\Eloquent\Model;

class PostAddressStatus extends Model
{
    use BaseModel;
    protected $table = 'post_address_status';
    protected $fillable = ['id','user_id', 'channel' , 'status', 'created_at','updated_at'];
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function PostManager()
    {
        return $this->hasMany(PostManager::class,'status_id','id');
    }
    public function House()
    {
        return $this->hasOne(PostManager::class,'status_id','id');
    }
   
}
