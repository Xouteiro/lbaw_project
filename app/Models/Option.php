<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps = false;
    protected $table = 'option';
    protected $fillable = [
        'name'
    ];

    public function poll()
    {
        return $this->belongsTo(Poll::class, 'id_poll');
    }

    public function voters()
    {
        return $this->belongsToMany(User::class, 'user_option', 'id_option', 'id_user');
    }
}
