<?php

namespace App\Models\House;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use BaseModel, SoftDeletes;
    protected $table = 'projects';
    protected $fillable = ['name', 'content', 'images', 'slug', 'descriptions','key_word', 'address'];

    public function setImagesAttribute($value)
    {
        if ($value && is_array($value)) {
            $this->attributes['images'] = json_encode($value);
        } else {
            $this->attributes['images'] = json_encode([]);
        }
    }

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }
    public function Houses()
    {
        return $this->hasMany(House::class, 'project_id', 'id');
    }
}

