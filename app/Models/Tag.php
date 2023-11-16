<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps = false;
    protected $fillables = [
        'name'
    ];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'events_tags', 'id_tag', 'id_event');
    }
}
