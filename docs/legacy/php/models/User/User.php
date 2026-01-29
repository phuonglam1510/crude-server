<?php

namespace App\Models\User;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use App\Models\House\Image;
use App\Models\Bussiness\Accumulation;
use App\Models\Checkin\Checkin;
use App\Models\House\File;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, BaseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'profile_picture', 'role', 'phone_number', 'date_of_birth', 'address', 'social_id',
        'provided_date', 'reset', 'status', 'achievement', 'introduction', 'position', 'job_position', 'super_admin_id',
        'admin_id', 'sale_admin_id', 'senior_manager_id', 'alonhadat_name', 'alonhadat_password', 'batdongsan_name', 'batdongsan_password',
        'manager_id', 'team_leader_id', 'create_product_permission', 'update_product_permission',
        'chat_permission', 'gender', 'issued_place', 'hometown_address', 'id_address', 'marriage', 'religion', 'literacy', 'contact_name',
        'contact_phone', 'employee_type', 'employee_status', 'department', 'office', 'note', 'tax_number', 'bank_account', 'bank_name',
        'front_id_file', 'back_id_file', 'certificate', 'onboarding_date'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function setPasswordAttribute($value)
    {
        $hash = config('API.Constant.App.Secret');
        $this->attributes['password'] = Hash::make($value . $hash);
    }
    public function setDateOfBirthAttribute($value)
    {
        $this->attributes['date_of_birth'] = date('Y-m-d', strtotime($value));
    }

    public function setProvidedDateAttribute($value)
    {
        $this->attributes['provided_date'] = date('Y-m-d', strtotime($value));
    }
    public function setOnboardingDateAttribute($value)
    {
        $this->attributes['onboarding_date'] = date('Y-m-d', strtotime($value));
    }
    public function getProjectAttribute($value)
    {
        return unserialize($value);
    }
    public function getProvinceAttribute($value)
    {
        return unserialize($value);
    }
    public function Avatar()
    {
        return $this->belongsTo(Image::class, 'profile_picture', 'id');
    }
    public function BackId()
    {
        return $this->belongsTo(Image::class, 'back_id_file', 'id');
    }
    public function FrontId()
    {
        return $this->belongsTo(Image::class, 'front_id_file', 'id');
    }
    public function CertificateFile()
    {
        return $this->belongsTo(File::class, 'certificate', 'id');
    }
    public function Accumulation()
    {
        return $this->hasMany(Accumulation::class, 'user_id', 'id');
    }
    public function UserCheckIn()
    {
        return $this->hasMany(Checkin::class, 'user_id', 'id')->where('status', 0);
    }

    public function UserCheckOut()
    {
        return $this->hasMany(Checkin::class, 'user_id', 'id')->where('status', 1);
    }
}
