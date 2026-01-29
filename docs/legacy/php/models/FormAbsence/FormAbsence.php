<?php

namespace App\Models\FormAbsence;

use App\Models\BaseModel;
use App\Models\User\User;
use App\Models\TypeForm\ReasonLeave;

use Illuminate\Database\Eloquent\Model;

class FormAbsence extends Model
{
    use BaseModel;
    protected $table = 'form_absence';
    protected $fillable = ['user_id','reason', 'labour', 'time_start', 'date', 'late', 'time_end', 'user_approve', 'note', 'created_at', 'updated_at'];
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

}

