<?php

namespace App\Models\Knowledge;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use App\Models\BaseModel;
use App\Models\User\User;
use App\Models\Customer\Customer;
use App\Models\House\House;
use Illuminate\Database\Eloquent\Model;
use App\Models\Type\Type;

class Knowledge extends Model
{
    use HasApiTokens, Notifiable, BaseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'title',
        'type_id',
        'cover_image',
        'images',
        'content',
        'created_at',
        'updated_at',
        'approval',
        'reason_reject',
    ];
    public function Type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password', 'remember_token',
    // ];
    public function setImagesAttribute($value)
    {
        if ($value && is_array($value)) {
            $this->attributes['images'] = json_encode($value);
        } elseif (is_string($value)) {
            $this->attributes['images'] = $value;
        } else {
            $this->attributes['images'] = json_encode([]);
        }
    }
    public function setCoverImageAttribute($value)
    {
        if ($value && is_array($value)) {
            $this->attributes['cover_image'] = json_encode($value);
        } elseif (is_string($value)) {
            $this->attributes['cover_image'] = $value;
        } else {
            $this->attributes['cover_image'] = json_encode([]);
        }
    }
}

