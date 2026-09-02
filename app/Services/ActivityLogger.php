<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model|null  $subject  The model the event is directly about.
     * @param  int|null  $obligationId  The owning Obligation's PK, for anything in the obligation family
     *                                  (the Obligation itself, or one of its PurchaseOrder/Disbursement/
     *                                  ObligationAdjustment children), so history stays lookup-able by a
     *                                  permanent numeric ID even after the subject row itself is deleted.
     */
    public static function log($description, $eventType, $details = null, $subject = null, $obligationId = null)
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
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'obligation_id' => $obligationId,
        ]);
    }
}