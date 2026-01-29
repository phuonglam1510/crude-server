<?php

namespace App\Models\User;


use App\Models\BaseModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use BaseModel;
    protected $table = 'user_activity';
    protected $fillable = ['id', 'user_id', 'entity_type', 'entity_id', 'action_type', 'created_at', 'updated_at'];
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
