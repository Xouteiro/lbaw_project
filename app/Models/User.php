<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Added to define Eloquent relationships.
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Don't add create and update timestamps in database.
    public $timestamps = false;
    protected $fillable = [
        'name',
        'username', 
        'email',
        'password',
    ];
    protected $hidden = [
        'password'
    ];
    protected $casts = [
        'password' => 'hashed'
    ];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'joined', 'id_user', 'id_event')
        ->withPivot('date', 'ticket', 'highlighted', 'hidden')
        ->orderByRaw('highlighted DESC, hidden ASC');;
    }

    public function ownedEvents()
    {
        return $this->hasMany(Event::class, 'id_owner')
        ->orderByRaw('highlight_owner DESC, hide_owner ASC');
    }
    
    public function pollOptions()
    {
        return $this->belongsToMany(Option::class, 'user_option', 'id_owner', 'id_option');
    }

    public function createdPolls()
    {
        return $this->hasMany(Poll::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function pendingInvites()
    {
        return $this->hasMany(Invite::class);
    }

    public function joinRequests()
    {
        return $this->hasMany(RequestToJoin::class);
    }
}
