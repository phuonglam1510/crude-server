<?php

namespace App\Models\Knowledge;

use App\Models\BaseModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class KnowledgeComment extends Model
{
    use BaseModel;
    protected $table = 'knowledge_comment';
    protected $fillable = ['user_id', 'content', 'knowledge_id', 'status', 'image', 'created_At', 'updated_At'];


    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function Knowledge()
    {
        return $this->belongsTo(Knowledge::class, 'knowledge_id', 'id');
    }

    public function Image()
    {
        return $this->belongsTo(Image::class, 'image', 'id');
    }
}

