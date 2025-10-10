<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log($description, $eventType, $details = null)
    {
        if (!Auth::check()) {
            return;
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'description' => $description,
            'event_type' => $eventType,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}