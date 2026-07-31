<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingTarget extends Model
{
    protected $fillable = [
        'meeting_id',
        'target_type',
        'target_id',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function target()
    {
        return $this->morphTo();
    }
}
