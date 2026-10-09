<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\LibraryDamageReport;
use App\Models\LibraryPenalty;
use App\Models\PatronBan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('adminUser');

        // 1. Date Filtering
        $preset = $request->input('date_preset', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if ($preset === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($preset === 'yesterday') {
            $query->whereDate('created_at', Carbon::yesterday());
        } elseif ($preset === 'this_week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($preset === 'this_month') {
            $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
        } elseif ($preset === 'last_month') {
            $query->whereMonth('created_at', Carbon::now()->subMonth()->month)->whereYear('created_at', Carbon::now()->subMonth()->year);
        } elseif ($preset === 'custom' || ($dateFrom || $dateTo)) {
            if ($dateFrom) {
                $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
            }
            if ($dateTo) {
                $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
            }
        }

        // 2. Module Filtering
        $module = $request->input('module', 'all');
        if ($module !== 'all' && !empty($module)) {
            $query->where(function ($q) use ($module) {
                $q->whereJsonContains('metadata->module', $module)
                  ->orWhere('action', 'like', $module . '_%');
            });
        }

        // 3. Action Filtering
        $action = $request->input('action', 'all');
        if ($action !== 'all' && !empty($action)) {
            $query->where('action', $action);
        }

        // 4. Performer Type Filtering
        $performerType = $request->input('performer_type', 'all');
        if ($performerType !== 'all' && !empty($performerType)) {
            $query->where('performed_by_type', $performerType);
        }

        // 5. Search Filter
        $search = trim($request->input('search', ''));
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $query->where(function ($q) use ($searchLower) {
                $q->where('description', 'like', "%{$searchLower}%")
                  ->orWhere('action', 'like', "%{$searchLower}%")
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.performer_name'))) LIKE ?", ["%{$searchLower}%"])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.affected_user'))) LIKE ?", ["%{$searchLower}%"])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.affected_book'))) LIKE ?", ["%{$searchLower}%"])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.remarks'))) LIKE ?", ["%{$searchLower}%"])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.reason'))) LIKE ?", ["%{$searchLower}%"])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.notes'))) LIKE ?", ["%{$searchLower}%"])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.ip_address'))) LIKE ?", ["%{$searchLower}%"]);
            });
        }

        // 6. Sorting
        $sort = $request->input('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $logs = $query->paginate(30)->withQueryString();

        // Distinct action types for filter dropdown
        $allActions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        // Historical collections for specialized subtabs
        $damageHistory = LibraryDamageReport::with(['book', 'student', 'faculty', 'adminUser'])
            ->orderBy('updated_at', 'desc')
            ->limit(200)
            ->get();

        $penaltyHistory = LibraryPenalty::with(['book', 'student', 'faculty'])
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        $banHistory = PatronBan::with(['student', 'faculty', 'bannedByAdmin', 'unbannedByAdmin'])
            ->orderByDesc('banned_at')
            ->limit(200)
            ->get();

        // Overall stats
        $stats = [
            'total_logs'    => ActivityLog::count(),
            'today_logs'    => ActivityLog::whereDate('created_at', Carbon::today())->count(),
            'user_events'   => ActivityLog::where('action', 'like', 'user_%')->orWhere('action', 'like', 'student_%')->orWhere('action', 'like', 'faculty_%')->orWhere('action', 'like', 'patron_%')->count(),
            'circ_events'   => ActivityLog::where('action', 'like', 'borrow_%')->orWhere('action', 'like', 'kiosk_%')->orWhere('action', 'like', 'return_%')->count(),
        ];

        return view('admin.audit-logs.index', compact(
            'logs',
            'allActions',
            'damageHistory',
            'penaltyHistory',
            'banHistory',
            'stats',
            'preset',
            'module',
            'action',
            'performerType',
            'search',
            'sort',
            'dateFrom',
            'dateTo'
        ));
    }
}
