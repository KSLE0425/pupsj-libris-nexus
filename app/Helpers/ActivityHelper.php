<?php

namespace App\Helpers;

use Carbon\Carbon;

class ActivityHelper
{
    /**
     * Get the user's activity status with relative time formatting
     *
     * @param Carbon|null $lastActivityAt
     * @return array['status' => string, 'label' => string]
     */
    public static function getActivityStatus($lastActivityAt)
    {
        if (!$lastActivityAt) {
            return [
                'status' => 'offline',
                'label' => 'Never active',
                'class' => 'status-badge offline'
            ];
        }

        $now = now();
        $diffInMinutes = $now->diffInMinutes($lastActivityAt);
        $diffInHours = $now->diffInHours($lastActivityAt);
        $diffInDays = $now->diffInDays($lastActivityAt);

        // Online if active within last 5 minutes
        if ($diffInMinutes < 5) {
            return [
                'status' => 'online',
                'label' => 'Online now',
                'class' => 'status-badge online'
            ];
        }

        // Show minutes
        if ($diffInMinutes < 60) {
            $mins = $diffInMinutes;
            return [
                'status' => 'away',
                'label' => $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago',
                'class' => 'status-badge away'
            ];
        }

        // Show hours
        if ($diffInHours < 24) {
            $hours = $diffInHours;
            return [
                'status' => 'offline',
                'label' => $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago',
                'class' => 'status-badge offline'
            ];
        }

        // Show days
        if ($diffInDays < 7) {
            $days = $diffInDays;
            return [
                'status' => 'offline',
                'label' => $days . ' day' . ($days > 1 ? 's' : '') . ' ago',
                'class' => 'status-badge offline'
            ];
        }

        // Show weeks
        $weeks = floor($diffInDays / 7);
        if ($weeks < 4) {
            return [
                'status' => 'offline',
                'label' => $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago',
                'class' => 'status-badge offline'
            ];
        }

        // Show months
        $months = floor($diffInDays / 30);
        return [
            'status' => 'offline',
            'label' => $months . ' month' . ($months > 1 ? 's' : '') . ' ago',
            'class' => 'status-badge offline'
        ];
    }

    /**
     * Get a simple status badge for display
     *
     * @param Carbon|null $lastActivityAt
     * @return string
     */
    public static function getActivityBadge($lastActivityAt)
    {
        $activity = self::getActivityStatus($lastActivityAt);
        return $activity['label'];
    }
}
