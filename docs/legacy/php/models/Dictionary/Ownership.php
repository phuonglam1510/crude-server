<?php

namespace App\Models\Dictionary;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Ownership extends Model
{
    use BaseModel;
    protected $table = 'ownership';
    protected $fillable = ['value'];
}
