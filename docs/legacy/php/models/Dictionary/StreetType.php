<?php

namespace App\Models\Dictionary;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class StreetType extends Model
{
    use BaseModel;
    protected $table = 'street_type';
    protected $fillable = ['value'];
}
