<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps = false;
    protected $table = 'event_notification';
    protected $fillables = [
        'name'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'id_event');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // verificar se é assim que é suposto referenciar a classe abstrata
    public function invite()
    {
        return $this->belongsTo(Invite::class, 'id_eventnotification');
    }

    // verificar se é assim que é suposto referenciar a classe abstrata
    public function eventUpdate()
    {
        return $this->belongsTo(EventUpdate::class, 'id_eventnotification');
    }

    // verificar se é assim que é suposto referenciar a classe abstrata
    public function requestToJoin()
    {
        return $this->belongsTo(RequestToJoin::class, 'id_eventnotification');
    }
}
