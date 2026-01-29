<?php

namespace App\Models\House;

use App\Models\BaseModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class HouseComment extends Model
{
    use BaseModel;
    protected $table = 'house_comment';
    protected $fillable = ['user_id', 'content', 'house_id', 'parent_comment_id', 'status', 'image'];


    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function House()
    {
        return $this->belongsTo(House::class, 'house_id', 'id');
    }

    public function Image()
    {
        return $this->belongsTo(Image::class, 'image', 'id');
    }

}
