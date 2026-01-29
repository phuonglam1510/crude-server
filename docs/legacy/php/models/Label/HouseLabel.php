<?php

namespace App\Models\Label;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class HouseLabel extends Model
{
    use BaseModel;
    protected $table = 'house_label';
    protected $fillable = ['house_id', 'user_id', 'label_id'];

    public function Label()
    {
        return $this->belongsTo(Label::class, 'label_id', 'id');
    }
}
