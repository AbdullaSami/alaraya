<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    /**
     * Display a paginated listing of system activity logs with filters.
     *
     * Supported query parameters:
     * - log_name: Filter by log category (e.g. clients, users, operating_orders)
     * - event: Filter by action type (created, updated, deleted)
     * - causer_id: Filter by user ID who performed the action
     * - subject_type: Filter by target model class name or short name
     * - subject_id: Filter by target model ID
     * - search: Search in description, log_name, or properties
     * - from_date: Start date (YYYY-MM-DD)
     * - to_date: End date (YYYY-MM-DD)
     * - per_page: Items per page (default 15, max 100)
     * - sort_by: Sort column (default 'id')
     * - sort_dir: Sort direction ('asc' or 'desc', default 'desc')
     */
    public function index(Request $request)
    {
        try {
            $query = Activity::with([
                'causer' => function ($q) {
                    $q->select('id', 'full_name', 'user_name', 'email');
                },
                'subject'
            ]);

            // Filter by log_name
            if ($request->filled('log_name')) {
                $query->where('log_name', $request->input('log_name'));
            }

            // Filter by event (created, updated, deleted)
            if ($request->filled('event')) {
                $query->where('event', $request->input('event'));
            }

            // Filter by causer_id
            if ($request->filled('causer_id')) {
                $query->where('causer_id', $request->input('causer_id'));
            }

            // Filter by subject_type (supports full namespace or basename e.g. "Client" or "App\Models\Client")
            if ($request->filled('subject_type')) {
                $type = $request->input('subject_type');
                if (!str_contains($type, '\\')) {
                    $type = 'App\\Models\\' . ucfirst($type);
                }
                $query->where('subject_type', $type);
            }

            // Filter by subject_id
            if ($request->filled('subject_id')) {
                $query->where('subject_id', $request->input('subject_id'));
            }

            // Filter by date range
            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->input('from_date'));
            }
            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->input('to_date'));
            }

            // Search term (description, log_name, or properties)
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('log_name', 'like', "%{$search}%")
                        ->orWhere('properties', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'id');
            $sortDir = strtolower($request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

            $allowedSorts = ['id', 'log_name', 'event', 'created_at', 'causer_id'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'id';
            }

            $query->orderBy($sortBy, $sortDir);

            // Pagination
            $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
            $logs = $query->paginate($perPage);

            return response()->json($logs, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve activity logs',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified activity log detail.
     */
    public function show(string $id)
    {
        try {
            $activity = Activity::with([
                'causer' => function ($q) {
                    $q->select('id', 'full_name', 'user_name', 'email');
                },
                'subject'
            ])->findOrFail($id);

            return response()->json($activity, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Activity log entry not found'], 404);
        }
    }

    /**
     * Get distinct log names available in the system (for frontend dropdown filters).
     */
    public function logNames()
    {
        try {
            $logNames = Activity::distinct()
                ->pluck('log_name')
                ->filter()
                ->values();

            return response()->json([
                'log_names' => $logNames
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve log names'], 500);
        }
    }

    /**
     * Get activity statistics for dashboard display.
     */
    public function stats()
    {
        try {
            $totalActivities = Activity::count();

            $eventsCount = Activity::select('event', DB::raw('count(*) as count'))
                ->groupBy('event')
                ->pluck('count', 'event');

            $logNamesCount = Activity::select('log_name', DB::raw('count(*) as count'))
                ->groupBy('log_name')
                ->pluck('count', 'log_name');

            $recentActivities = Activity::with([
                'causer' => function ($q) {
                    $q->select('id', 'full_name', 'user_name', 'email');
                }
            ])
                ->latest('id')
                ->limit(5)
                ->get();

            return response()->json([
                'total_activities' => $totalActivities,
                'events_breakdown' => $eventsCount,
                'categories_breakdown' => $logNamesCount,
                'recent_activities' => $recentActivities
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve activity stats'], 500);
        }
    }

    public function getLogs()
    {
        $logs = Activity::latest()->with('causer')->get();
        return response()->json($logs);
    }
}
