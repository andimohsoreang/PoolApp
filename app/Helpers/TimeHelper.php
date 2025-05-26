<?php

namespace App\Helpers;

class TimeHelper
{
    /**
     * Format duration in hours to a human-readable string
     *
     * @param float $hours
     * @return string
     */
    public static function formatDuration($hours)
    {
        $wholeHours = floor($hours);
        $minutes = round(($hours - $wholeHours) * 60);

        if ($wholeHours === 0) {
            return "{$minutes} menit";
        } else if ($minutes === 0) {
            return "{$wholeHours} jam";
        } else {
            return "{$wholeHours} jam {$minutes} menit";
        }
    }
}
