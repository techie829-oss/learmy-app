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
        'reminder_settings',
        'reminded_morning_at',
        'reminded_15m_at',
        'reminded_start_at',
    ];

    protected $casts = [
        'start_time'                 => 'datetime',
        'end_time'                   => 'datetime',
        'send_whatsapp_notification' => 'boolean',
        'reminder_settings'          => 'array',
        'reminded_morning_at'        => 'datetime',
        'reminded_15m_at'            => 'datetime',
        'reminded_start_at'          => 'datetime',
    ];

    public function targets()
    {
        return $this->hasMany(MeetingTarget::class);
    }

    /**
     * Check if a specific reminder trigger is enabled for this meeting.
     * Trigger types: 'on_create', 'morning', 'before_15m', 'on_start'
     */
    public function isReminderEnabled(string $trigger): bool
    {
        if ($this->send_whatsapp_notification === false) {
            return false;
        }

        $settings = $this->reminder_settings;
        if (empty($settings)) {
            // Default: all enabled if master toggle is on
            return true;
        }

        return !empty($settings[$trigger]['enabled']);
    }

    /**
     * Get configured template name for a specific trigger.
     */
    public function getReminderTemplate(string $trigger): string
    {
        $settings = $this->reminder_settings;
        if (!empty($settings[$trigger]['template'])) {
            return $settings[$trigger]['template'];
        }

        // Fallback to legacy single template field or default
        return $this->whatsapp_template ?: "class_{$trigger}_reminder";
    }
}
