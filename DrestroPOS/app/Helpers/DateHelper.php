<?php

namespace App\Helpers;

use App\Models\Restaurant;
use Carbon\Carbon;

class DateHelper {
    public static function format($date) {
        if (!$date) return '';

        if (!($date instanceof Carbon)) {
            try {
                $date = Carbon::parse($date);
            } catch (\Exception $e) {
                return $date; // Fallback
            }
        }

        $restaurant = current_restaurant();
        $type = $restaurant->date_calendar_type ?? 'AD';

        if (strtoupper($type) === 'BS') {
            $calendar = new NepaliCalendar();
            $nep = $calendar->eng_to_nep($date->year, $date->month, $date->day);
            if ($nep) {
                return $nep['nmonth'] . ' ' . $nep['date'] . ', ' . $nep['year'];
            }
        }

        return $date->format('M d, Y');
    }
}
