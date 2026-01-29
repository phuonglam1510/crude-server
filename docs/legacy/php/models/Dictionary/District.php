<?php

namespace App\Models\Dictionary;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use BaseModel;
    protected $table = 'district';
    protected $fillable = ['value', 'province_id'];

    public function Province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }
}
