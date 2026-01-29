<?php

namespace App\Models\House;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use BaseModel, SoftDeletes;
    protected $table = 'tag';
    protected $fillable = [
        'id', 'name','color', 'user_id', 'created_at', 'updated_at'
    ];
    public function House()
    {
        return $this->belongsTo('App\Models\House\House', 'id', 'house_id');
    }
    public function HouseTag()
    {
        return $this->belongsTo('App\Models\House\HouseTag', 'id', 'tag_id');
    }
}
