<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'action',
        'description',
        'performed_by_type',
        'performed_by_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'performed_by_id');
    }

    public function getModuleAttribute()
    {
        if (!empty($this->metadata['module'])) {
            return $this->metadata['module'];
        }
        return \App\Services\AuditLogger::detectModule($this->action);
    }

    public function getModuleLabelAttribute()
    {
        $labels = [
            'users'            => 'Users Management',
            'books'            => 'Books Management',
            'circulation'      => 'Circulation',
            'kiosk'            => 'Library Kiosk',
            'auth'             => 'Authentication',
            'operations'       => 'Operations & Penalties',
            'programs'         => 'Programs & Departments',
            'collection_types' => 'Collection Types',
            'settings'         => 'System Settings',
            'general'          => 'General',
        ];
        return $labels[$this->module] ?? ucfirst(str_replace('_', ' ', $this->module));
    }

    public function getPerformerNameAttribute()
    {
        if (!empty($this->metadata['performer_name'])) {
            return $this->metadata['performer_name'];
        }
        if ($this->performed_by_type === 'admin') {
            return $this->adminUser?->name ?? $this->adminUser?->email ?? 'Admin';
        }
        return ucfirst($this->performed_by_type ?? 'System');
    }

    public function getActionLabelAttribute()
    {
        $labels = [
            // User Management
            'user_created'             => 'User Created',
            'user_updated'             => 'User Updated',
            'user_archived'            => 'User Archived',
            'user_restored'            => 'User Restored',
            'user_deleted'             => 'User Deleted',
            'student_approved'         => 'Student Approved',
            'student_rejected'         => 'Student Rejected',
            'faculty_approved'         => 'Faculty Approved',
            'faculty_rejected'         => 'Faculty Rejected',
            'patron_banned'            => 'Patron Banned (Manual)',
            'patron_unbanned'          => 'Patron Unbanned',
            'patron_suspended'         => 'Patron Suspended',
            'patron_unsuspended'       => 'Patron Unsuspended',

            // Book Management
            'book_created'             => 'Book Added',
            'book_updated'             => 'Book Updated',
            'book_deleted'             => 'Book Deleted',
            'book_archived'            => 'Book Archived',
            'book_restored'            => 'Book Restored',
            'book_unarchived'          => 'Book Restored',
            'book_condemned'           => 'Book Condemned',
            'book_uncondemned'         => 'Book Uncondemned',
            'book_label_printed'       => 'Label Printed',

            // Circulation & Kiosk
            'borrow_created'           => 'Book Borrowed (Desk)',
            'borrow_returned'          => 'Book Returned (Desk)',
            'borrow_renewed'           => 'Borrow Renewed',
            'borrow_acknowledged'      => 'Borrow Acknowledged',
            'kiosk_borrow_created'     => 'Kiosk Borrow',
            'kiosk_return_completed'   => 'Kiosk Return',
            'kiosk_return_reviewed'    => 'Kiosk Condition Checked',
            'reservation_created'      => 'Reservation Created',
            'reservation_cancelled'    => 'Reservation Cancelled',
            'reservation_ready_email'  => 'Reservation Notified',

            // Operations & Penalties
            'damage_report_resolved'   => 'Damage Resolved',
            'damage_report_dismissed'  => 'Damage Dismissed',
            'penalty_resolved'         => 'Penalty Resolved',
            'penalty_paid'             => 'Penalty Paid',
            'penalty_waived'           => 'Penalty Waived',
            'penalty_status_updated'   => 'Penalty Updated',
            'damage_penalty_created'   => 'Damage Penalty Added',
            'manual_penalty_created'   => 'Manual Penalty Added',
            'damage_warning_recorded'  => 'Damage Warning Recorded',
            'overdue_fine_applied'     => 'Overdue Fine Charged',
            'overdue_flag_cleared'     => 'Overdue Flag Cleared',
            'requisition_status_updated' => 'Requisition Updated',

            // Authentication
            'auth_login_admin'         => 'Admin Login',
            'auth_logout_admin'        => 'Admin Logout',
            'auth_failed_admin'        => 'Failed Admin Login',
            'auth_login_student'       => 'Student Login',
            'auth_logout_student'      => 'Student Logout',
            'auth_login_faculty'       => 'Faculty Login',
            'auth_logout_faculty'      => 'Faculty Logout',
            'auth_registered_student'  => 'Student Registered',

            // Programs, Collection Types & Settings
            'course_created'           => 'Course Added',
            'course_updated'           => 'Course Updated',
            'course_deleted'           => 'Course Deleted',
            'specialty_created'        => 'Specialty Added',
            'specialty_updated'        => 'Specialty Updated',
            'specialty_deleted'        => 'Specialty Deleted',
            'collection_type_created'  => 'Collection Type Added',
            'collection_type_updated'  => 'Collection Type Updated',
            'collection_type_archived' => 'Collection Type Archived',
            'collection_type_restored' => 'Collection Type Restored',
            'collection_type_deleted'  => 'Collection Type Deleted',
            'library_config_updated'   => 'Configuration Updated',
        ];

        return $labels[$this->action] ?? ucwords(str_replace('_', ' ', $this->action));
    }
}
