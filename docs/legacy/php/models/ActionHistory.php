<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class ActionHistory extends Model
{
    use BaseModel;
    protected $table = 'action_history';
    protected $fillable = ['column', 'model', 'model_id', 'old_value', 'new_value', 'user_id'];

    public function User()
    {
        return $this->belongsTo('App\Models\User\User', 'user_id', 'id');
    }
}
