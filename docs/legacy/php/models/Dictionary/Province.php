<?php

namespace App\Models\Dictionary;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use BaseModel;
    protected $table = 'province';
    protected $fillable = ['value', 'city_id'];

    // TODO: City = Province, Province = District, District = Ward 
    public function City()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }
}
