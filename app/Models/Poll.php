<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps = false;
    protected $table = 'poll';
    protected $fillable = [
        'title',
        'id_event',
        'id_user',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'id_event');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function options()
    {
        return $this->hasMany(Option::class, 'id_poll');
    }
}
