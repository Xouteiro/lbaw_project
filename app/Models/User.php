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
        ->withPivot('date', 'ticket');
    }

    public function ownedEvents()
    {
        return $this->hasMany(Event::class);
    }
    
    public function pollOptions()
    {
        return $this->belongsToMany(Option::class, 'user_option', 'id_user', 'id_option');
    }
}
