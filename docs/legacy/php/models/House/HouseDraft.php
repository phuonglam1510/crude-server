<?php

namespace App\Models\House;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class HouseDraft extends Model
{
    use BaseModel;
    protected $table = 'house_drafts';
    protected $fillable = [
        'house_id', 'status', 'attributes', 'approved_at', 'approved_by'
    ];


    public function House()
    {
        return $this->belongsTo('App\Models\House\House', 'house_id', 'id');
    }
}
