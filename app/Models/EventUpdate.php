<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventUpdate extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps = false;
    protected $table = 'event_update';

    // verificar se é assim que é suposto referenciar a classe abstrata
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'id_eventnotification');
    }
}
