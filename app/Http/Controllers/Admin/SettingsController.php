<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'damage_penalty_amount'        => 'sometimes|numeric|min:0',
            'lost_penalty_amount'          => 'sometimes|numeric|min:0',
            'max_borrow_days_student'      => 'sometimes|integer|min:1',
            'overdue_fine_per_day_student' => 'sometimes|numeric|min:0',
            'overdue_fine_per_day_faculty' => 'sometimes|numeric|min:0',
            'ban_duration_days'            => 'sometimes|integer|min:0|max:365',
            'warning_ban_threshold'        => 'sometimes|integer|min:1|max:10',
            'book_requests_disabled_reason'=> 'nullable|string|max:500',
        ]);

        $oldConfig = [
            'damage_penalty_amount'        => Setting::getValue('damage_penalty_amount', 500),
            'lost_penalty_amount'          => Setting::getValue('lost_penalty_amount', 1000),
            'max_borrow_days_student'      => Setting::getValue('max_borrow_days_student', 7),
            'overdue_fines_enabled'        => Setting::getValue('overdue_fines_enabled', '0'),
            'overdue_fine_per_day_student' => Setting::getValue('overdue_fine_per_day_student', 10),
            'overdue_fine_per_day_faculty' => Setting::getValue('overdue_fine_per_day_faculty', 20),
            'student_take_home_allowed'    => Setting::getValue('student_take_home_allowed', '1'),
            'ban_duration_days'            => Setting::getValue('ban_duration_days', 0),
            'warning_ban_threshold'        => Setting::getValue('warning_ban_threshold', 3),
            'book_requests_enabled'        => Setting::getValue('book_requests_enabled', '1'),
            'book_requests_disabled_reason'=> Setting::getValue('book_requests_disabled_reason', ''),
        ];

        Setting::setValue('overdue_fines_enabled', $request->has('overdue_fines_enabled') ? '1' : '0', 'penalties');
        Setting::setValue('student_take_home_allowed', $request->has('student_take_home_allowed') ? '1' : '0', 'borrowing');
        Setting::setValue('book_requests_enabled', $request->has('book_requests_enabled') ? '1' : '0', 'general');
        if ($request->filled('book_requests_disabled_reason')) {
            Setting::setValue('book_requests_disabled_reason', $request->input('book_requests_disabled_reason'), 'general');
        }
        // Faculty indefinite borrowing removed — always enforce due dates
        Setting::setValue('faculty_borrow_indefinite', '0', 'borrowing');

        foreach ($validated as $key => $value) {
            if ($key !== 'book_requests_disabled_reason') {
                Setting::setValue($key, $value, 'penalties');
            }
        }

        $newConfig = [
            'damage_penalty_amount'        => Setting::getValue('damage_penalty_amount', 500),
            'lost_penalty_amount'          => Setting::getValue('lost_penalty_amount', 1000),
            'max_borrow_days_student'      => Setting::getValue('max_borrow_days_student', 7),
            'overdue_fines_enabled'        => Setting::getValue('overdue_fines_enabled', '0'),
            'overdue_fine_per_day_student' => Setting::getValue('overdue_fine_per_day_student', 10),
            'overdue_fine_per_day_faculty' => Setting::getValue('overdue_fine_per_day_faculty', 20),
            'student_take_home_allowed'    => Setting::getValue('student_take_home_allowed', '1'),
            'ban_duration_days'            => Setting::getValue('ban_duration_days', 0),
            'warning_ban_threshold'        => Setting::getValue('warning_ban_threshold', 3),
        ];

        $adminName = Auth::user()?->name ?? Auth::user()?->email ?? 'Admin';

        ActivityLog::create([
            'action'            => 'library_config_updated',
            'description'       => "Library operations configuration updated by {$adminName}.",
            'performed_by_type' => 'admin',
            'performed_by_id'   => Auth::id(),
            'metadata'          => [
                'performer_name' => $adminName,
                'before'         => $oldConfig,
                'after'          => $newConfig,
            ],
        ]);

        return redirect()->back()->with('success', 'Configuration saved.');
    }
}
