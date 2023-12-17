<?php

namespace App\Models;

use App\Http\Controllers\FileController;
use GuzzleHttp\Cookie\FileCookieJar;
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
        'id_location',
        'highlight_owner',
        'hide_owner',
        'event_image'
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
        return $this->belongsToMany(User::class, 'joined', 'id_event', 'id_user')
        ->withPivot('date', 'ticket', 'highlighted', 'hidden');
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

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    
    public function getEventImage(int $id){
        return FileController::get('event', $id);
    }
}
