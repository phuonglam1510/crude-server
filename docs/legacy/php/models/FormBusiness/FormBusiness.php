<?php

namespace App\Models\FormBusiness;

use App\Models\BaseModel;
use App\Models\User\User;
use App\Models\TypeForm\Shift;


use Illuminate\Database\Eloquent\Model;

class FormBusiness extends Model
{
    use BaseModel;
    protected $table = 'form_business';
    protected $fillable = ['user_id','time_start','time_end', 'address', 'start_date', 'end_date', 'shift', 'user_approve', 'note', 'created_at', 'updated_at', 'status'];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ];


     public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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

