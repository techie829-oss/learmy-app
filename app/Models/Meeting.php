<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'workspace_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'timezone',
        'google_event_id',
        'meet_link',
        'status',
        'whatsapp_template',
        'send_whatsapp_notification',
    ];

    protected $casts = [
        'start_time'                 => 'datetime',
        'end_time'                   => 'datetime',
        'send_whatsapp_notification' => 'boolean',
    ];


    public function targets()
    {
        return $this->hasMany(MeetingTarget::class);
    }
}
