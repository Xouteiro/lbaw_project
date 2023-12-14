<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikesDislikes extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'likes_dislikes';
    protected $fillable = [
        'liked'
    ];

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'id_comment');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
