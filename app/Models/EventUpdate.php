<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventUpdate extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_eventnotification';
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'event_update';

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'id_eventnotification');
    }
}
