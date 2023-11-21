<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_eventnotification';
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'invite';
    

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'id_eventnotification');
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
