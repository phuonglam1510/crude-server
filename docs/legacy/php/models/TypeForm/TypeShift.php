<?php

namespace App\Models\TypeForm;

use App\Models\BaseModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class TypeShift extends Model
{
    use BaseModel;
    protected $table = 'type_shift';
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

