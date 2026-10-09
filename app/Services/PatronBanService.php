<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Faculty;
use App\Models\PatronBan;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class PatronBanService
{
    /**
     * Add a warning to a patron, increment count, and auto-ban if threshold is reached.
     * Warning count NEVER resets.
     * Auto-ban triggers on warning #3, warning #4, warning #5, etc.
     */
    public static function addWarning($patron, string $userType, ?string $reason = null, $adminUser = null, ?int $usageId = null, ?int $bookId = null): array
    {
        $adminId = $adminUser?->id ?? Auth::id() ?? 1;
        $adminName = $adminUser?->name ?? $adminUser?->email ?? (Auth::user()?->name ?? 'Admin');
        $patronName = $patron->first_name . ' ' . $patron->last_name;

        // Increment warning count (warnings accumulate and never reset)
        $patron->increment('damage_warning_count');
        $patron->refresh();
        $warningCount = (int) $patron->damage_warning_count;

        $threshold = (int) Setting::getValue('warning_ban_threshold', 3);
        $banDays = (int) Setting::getValue('ban_duration_days', 0); // 0 = indefinite/permanent

        $autoBanned = false;

        // Auto-ban triggers whenever a new warning is added and count >= threshold
        if ($warningCount >= $threshold) {
            $patron->is_banned = true;
            if ($banDays > 0) {
                $patron->borrowing_suspended_until = now()->addDays($banDays);
            }
            $patron->save();

            $banReason = $reason
                ? "Auto-banned: Reached warning #{$warningCount} (Threshold: {$threshold}) — {$reason}"
                : "Auto-banned: Reached warning #{$warningCount} (Warning threshold: {$threshold} reached).";

            $banRecord = PatronBan::create([
                'user_type'            => $userType,
                ($userType === 'student' ? 'student_id' : 'faculty_id') => $patron->id,
                'warning_count_at_ban' => $warningCount,
                'banned_by'            => $adminId,
                'banned_at'            => now(),
            ]);

            AuditLogger::log(
                'patron_banned',
                "{$patronName} (" . ucfirst($userType) . ") auto-banned upon receiving warning #{$warningCount}. {$banReason}",
                [
                    'performer_name' => $adminName,
                    'affected_user'  => "{$patronName} (" . ucfirst($userType) . ")",
                    'user_type'      => $userType,
                    'user_id'        => $patron->id,
                    'warning_count'  => $warningCount,
                    'reason'         => $banReason,
                    'patron_ban_id'  => $banRecord->id,
                    'ban_duration'   => $banDays > 0 ? "{$banDays} days" : 'Indefinite',
                    'auto_ban'       => true,
                ],
                'operations',
                'admin',
                $adminId
            );

            $autoBanned = true;
        } else {
            AuditLogger::log(
                'damage_warning_recorded',
                "Warning #{$warningCount} issued to {$patronName} (" . ucfirst($userType) . ")" . ($reason ? ": {$reason}" : "."),
                [
                    'performer_name' => $adminName,
                    'affected_user'  => "{$patronName} (" . ucfirst($userType) . ")",
                    'user_type'      => $userType,
                    'user_id'        => $patron->id,
                    'warning_count'  => $warningCount,
                    'remarks'        => $reason,
                ],
                'operations',
                'admin',
                $adminId
            );
        }

        return [
            'patron'        => $patron,
            'warning_count' => $warningCount,
            'auto_banned'   => $autoBanned,
        ];
    }

    /**
     * Unban a patron (requires reason from librarian).
     * Warning count stays unchanged.
     */
    public static function unbanPatron(string $userType, int $userId, string $unbanReason, $adminUser = null): bool
    {
        $adminId = $adminUser?->id ?? Auth::id() ?? 1;
        $adminName = $adminUser?->name ?? $adminUser?->email ?? (Auth::user()?->name ?? 'Admin');

        $patron = $userType === 'student' ? Student::findOrFail($userId) : Faculty::findOrFail($userId);
        $patronName = $patron->first_name . ' ' . $patron->last_name;

        $patron->is_banned = false;
        $patron->borrowing_suspended_until = null;
        $patron->save();

        // Update all active ban records
        PatronBan::where('user_type', $userType)
            ->where($userType === 'student' ? 'student_id' : 'faculty_id', $userId)
            ->whereNull('unbanned_at')
            ->update([
                'unbanned_by'  => $adminId,
                'unbanned_at'  => now(),
                'unban_reason' => $unbanReason,
            ]);

        AuditLogger::log(
            'patron_unbanned',
            "{$patronName} (" . ucfirst($userType) . ") unbanned by {$adminName}. Reason: {$unbanReason} (Warning count: {$patron->damage_warning_count} retained).",
            [
                'performer_name' => $adminName,
                'affected_user'  => "{$patronName} (" . ucfirst($userType) . ")",
                'user_type'      => $userType,
                'user_id'        => $userId,
                'warning_count'  => $patron->damage_warning_count,
                'remarks'        => $unbanReason,
            ],
            'operations',
            'admin',
            $adminId
        );

        return true;
    }

    /**
     * Manually ban a patron.
     */
    public static function banPatronManually(string $userType, int $userId, string $reason, $adminUser = null): bool
    {
        $adminId = $adminUser?->id ?? Auth::id() ?? 1;
        $adminName = $adminUser?->name ?? $adminUser?->email ?? (Auth::user()?->name ?? 'Admin');

        $patron = $userType === 'student' ? Student::findOrFail($userId) : Faculty::findOrFail($userId);
        $patronName = $patron->first_name . ' ' . $patron->last_name;

        $banDays = (int) Setting::getValue('ban_duration_days', 0);

        $patron->is_banned = true;
        if ($banDays > 0) {
            $patron->borrowing_suspended_until = now()->addDays($banDays);
        }
        $patron->save();

        $banRecord = PatronBan::create([
            'user_type'            => $userType,
            ($userType === 'student' ? 'student_id' : 'faculty_id') => $patron->id,
            'warning_count_at_ban' => $patron->damage_warning_count ?? 0,
            'banned_by'            => $adminId,
            'banned_at'            => now(),
        ]);

        AuditLogger::log(
            'patron_banned',
            "{$patronName} (" . ucfirst($userType) . ") manually banned by {$adminName}. Reason: {$reason}",
            [
                'performer_name' => $adminName,
                'affected_user'  => "{$patronName} (" . ucfirst($userType) . ")",
                'user_type'      => $userType,
                'user_id'        => $userId,
                'warning_count'  => $patron->damage_warning_count ?? 0,
                'reason'         => $reason,
                'patron_ban_id'  => $banRecord->id,
                'ban_duration'   => $banDays > 0 ? "{$banDays} days" : 'Indefinite',
                'auto_ban'       => false,
            ],
            'operations',
            'admin',
            $adminId
        );

        return true;
    }
}
