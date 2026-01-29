<?php

namespace App\Models\House;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use BaseModel;
    protected $table = 'files';
    protected $fillable = ['url', 'name', 'size', 'type'];
}
