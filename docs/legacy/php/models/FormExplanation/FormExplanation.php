<?php

namespace App\Models\FormExplanation;

use App\Models\BaseModel;
use App\Models\User\User;
use App\Models\TypeForm\ReasonLeave;
use App\Models\TypeForm\Shift;
use App\Models\TypeForm\ReasonExplanation;
use App\Models\TypeForm\Activity;


use Illuminate\Database\Eloquent\Model;

class FormExplanation extends Model
{
    use BaseModel;
    protected $table = 'form_explanation';
    protected $fillable = ['user_id','reason','days', 'shift','activity_explanation','reason_explanation','user_approve', 'note', 'created_at', 'updated_at'];
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
    public function ReasonExplanation()
    {
        return $this->belongsTo(ReasonExplanation::class, 'reason_explanation', 'id');
    }
    public function ActivityExplanation()
    {
        return $this->belongsTo(Activity::class, 'activity_explanation', 'id');
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

