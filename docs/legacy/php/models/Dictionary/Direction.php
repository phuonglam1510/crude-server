<?php

namespace App\Models\Dictionary;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    use BaseModel;
    protected $table = 'direction';
    protected $fillable = ['value'];
}
