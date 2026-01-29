<?php

namespace App\Models\FormLeave;

use App\Models\BaseModel;
use App\Models\User\User;
use App\Models\TypeForm\ReasonLeave;
use App\Models\TypeForm\Shift;


use Illuminate\Database\Eloquent\Model;

class FormLeave extends Model
{
    use BaseModel;
    protected $table = 'form_leave';
    protected $fillable = ['user_id','reason', 'labour', 'start_date', 'end_date', 'shift', 'user_approve', 'note', 'created_at', 'updated_at', 'status'];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ];


     public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function ReasonAbsence()
    {
        return $this->belongsTo(ReasonLeave::class, 'reason', 'id');
    }

    public function UserApprove()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function ShiftLeave()
    {
        return $this->belongsTo(Shift::class, 'shift', 'id');
    }

}

