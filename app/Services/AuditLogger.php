<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Determine module from action if not explicitly provided.
     */
    public static function detectModule(string $action): string
    {
        if (str_starts_with($action, 'user_') || str_starts_with($action, 'student_') || str_starts_with($action, 'faculty_') || str_starts_with($action, 'patron_')) {
            return 'users';
        }
        if (str_starts_with($action, 'book_') || str_starts_with($action, 'archive_')) {
            return 'books';
        }
        if (str_starts_with($action, 'borrow_') || str_starts_with($action, 'return_') || str_starts_with($action, 'renew_') || str_starts_with($action, 'reservation_')) {
            return 'circulation';
        }
        if (str_starts_with($action, 'kiosk_')) {
            return 'kiosk';
        }
        if (str_starts_with($action, 'auth_') || str_starts_with($action, 'login_') || str_starts_with($action, 'logout_') || str_starts_with($action, 'otp_')) {
            return 'auth';
        }
        if (str_starts_with($action, 'damage_') || str_starts_with($action, 'penalty_') || str_starts_with($action, 'overdue_') || str_starts_with($action, 'requisition_')) {
            return 'operations';
        }
        if (str_starts_with($action, 'program_') || str_starts_with($action, 'course_') || str_starts_with($action, 'department_') || str_starts_with($action, 'specialty_')) {
            return 'programs';
        }
        if (str_starts_with($action, 'collection_type_')) {
            return 'collection_types';
        }
        if (str_starts_with($action, 'config_') || str_starts_with($action, 'settings_') || str_starts_with($action, 'library_config_')) {
            return 'settings';
        }
        return 'general';
    }

    /**
     * Create an activity log record with rich metadata.
     */
    public static function log(
        string $action,
        string $description,
        array $metadata = [],
        ?string $module = null,
        ?string $performerType = null,
        $performerId = null
    ): ActivityLog {
        $module = $module ?? self::detectModule($action);
        $metadata['module'] = $module;

        // Auto-resolve performer if not supplied
        if ($performerType === null) {
            if (Auth::guard('web')->check()) {
                $user = Auth::guard('web')->user();
                $performerType = 'admin';
                $performerId = $user->id;
                $metadata['performer_name'] = $metadata['performer_name'] ?? ($user->name ?? $user->email ?? 'Admin');
            } elseif (Auth::guard('student')->check()) {
                $student = Auth::guard('student')->user();
                $performerType = 'student';
                $performerId = $student->id;
                $metadata['performer_name'] = $metadata['performer_name'] ?? ($student->first_name . ' ' . $student->last_name);
            } elseif (Auth::guard('faculty')->check()) {
                $faculty = Auth::guard('faculty')->user();
                $performerType = 'faculty';
                $performerId = $faculty->id;
                $metadata['performer_name'] = $metadata['performer_name'] ?? ($faculty->first_name . ' ' . $faculty->last_name);
            } else {
                $performerType = 'system';
                $metadata['performer_name'] = $metadata['performer_name'] ?? 'System';
            }
        }

        if (Request::hasSession() || Request::ip()) {
            $metadata['ip_address'] = Request::ip();
        }

        return ActivityLog::create([
            'action'            => $action,
            'description'       => $description,
            'performed_by_type' => $performerType,
            'performed_by_id'   => $performerId,
            'metadata'          => $metadata,
        ]);
    }
}
