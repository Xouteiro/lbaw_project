<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps = false;
    protected $table = 'event';
    protected $fillable = [
        'name',
        'eventdate',
        'description',
        'creationdate',
        'price',
        'public',
        'opentojoin',
        'capacity',
        'id_owner',
        'id_location'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            $event->creationdate = now();
        });
    }


    public function participants()
    {
        return $this->belongsToMany(User::class, 'joined', 'id_event', 'id_owner')
        ->withPivot('date', 'ticket');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'id_owner');
    }

    public function polls()
    {
        return $this->hasMany(Poll::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'id_event');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'id_location');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'events_tags', 'id_event', 'id_tag');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    
}
