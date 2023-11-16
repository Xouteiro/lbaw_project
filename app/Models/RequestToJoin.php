<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestToJoin extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps = false;
    protected $table = 'request_to_join';

    // verificar se é assim que é suposto referenciar a classe abstrata
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'id_eventnotification');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
