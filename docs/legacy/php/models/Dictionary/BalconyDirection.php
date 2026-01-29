<?php

namespace App\Models\Dictionary;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class BalconyDirection extends Model
{
    use BaseModel;
    protected $table = 'balcony_direction';
    protected $fillable = ['value'];
}
