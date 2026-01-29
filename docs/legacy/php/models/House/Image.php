<?php

namespace App\Models\House;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use BaseModel;
    protected $table = 'images';
    protected $fillable = ['main', 'thumbnail'];
}
