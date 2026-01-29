<?php

namespace App\Models\Label;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use BaseModel;
    protected $table = 'label';
    protected $fillable = ['name', 'color', 'user_id'];

    public function Houses()
    {
        return $this->hasMany(HouseLabel::class, 'label_id', 'id');
    }
}
