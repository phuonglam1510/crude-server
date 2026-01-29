<?php

namespace App\Models\TypeForm;

use App\Models\BaseModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class ReasonLeave extends Model
{
    use BaseModel;
    protected $table = 'reason_leave';
    protected $fillable = ['user_id','name', 'created_at', 'updated_at'];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ];


     public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
}

