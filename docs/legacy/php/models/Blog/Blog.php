<?php

namespace App\Models\Blog;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use BaseModel, SoftDeletes;

    protected $table = 'blog';
    protected $dateFormat = 'U';
    protected $fillable = ['title', 'content', 'status', 'tag', 'created_by', 'updated_by', 'cover', 'type'];

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = htmlspecialchars($value);
    }

    public function getContentAttribute($value)
    {
        return html_entity_decode($value);
    }

    public function setTagAttribute($value)
    {
        if ($value) {
            $this->attributes['tag'] = json_encode($value);
        } else {
            $this->attributes['tag'] = json_encode([]);
        }
    }

    public function getTagAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        }

        return $value;
    }

    public function User()
    {
        return $this->belongsTo('App\Models\User\User', 'created_by', 'id');
    }

    public function Cover()
    {
        return $this->belongsTo('App\Models\House\Image', 'cover', 'id');
    }
}
