<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('event_type', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by event type
        if ($request->has('event_type') && $request->event_type != '') {
            $query->where('event_type', $request->event_type);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(25)->withQueryString();
        
        // Get unique event types for filter
        $eventTypes = ActivityLog::distinct()->orderBy('event_type')->pluck('event_type');

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Activity Logs']
        ];

        return view('activity-logs.index', compact('logs', 'eventTypes', 'breadcrumb'));
    }
}