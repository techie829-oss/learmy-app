<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceGoogleToken extends Model
{
    protected $fillable = [
        'workspace_id',
        'email',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'expires_at' => 'datetime',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
