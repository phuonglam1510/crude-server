<?php

namespace App\Models\User;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class PostAddress extends Model
{
  use BaseModel;
  protected $table = 'post_address';
  protected $fillable = ['id','user_id', 'channel' ,'created_at','updated_at'];
  public function User()
    {
      return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
   
}
