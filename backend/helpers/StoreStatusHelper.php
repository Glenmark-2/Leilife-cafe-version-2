<?php

class StoreStatusHelper
{
    /**
     * Checks if the store is open based on manual toggle and opening hours.
     * 
     * @param array $settings The site settings from database.
     * @return bool True if open, False if closed.
     */
    public static function isStoreOpen($settings)
    {
        if (!$settings) {
            return true; // Default to open if settings not found
        }

        // 1. Check Manual Toggle (Admin Override)
        if (isset($settings['is_store_open']) && !$settings['is_store_open']) {
            return false;
        }

        // 2. Check Opening Hours
        if (!empty($settings['opening_hours'])) {
            date_default_timezone_set('Asia/Manila');

            $openingHours = is_string($settings['opening_hours'])
                ? json_decode($settings['opening_hours'], true)
                : $settings['opening_hours'];

            if (!$openingHours) {
                return true; // Default if JSON is corrupt
            }

            $currentDay = date('D'); // Mon, Tue, etc.
            $currentTime = date('H:i');

            if (isset($openingHours[$currentDay])) {
                $dayData = $openingHours[$currentDay];
                $start = '';
                $end = '';

                if (is_array($dayData) && isset($dayData['open']) && isset($dayData['close'])) {
                    $start = date('H:i', strtotime($dayData['open']));
                    $end = date('H:i', strtotime($dayData['close']));
                } else if (is_string($dayData)) {
                    $range = explode('-', $dayData);
                    if (count($range) === 2) {
                        $start = trim($range[0]);
                        $end = trim($range[1]);
                    }
                }

                if ($start && $end) {
                    // Handle Midnight
                    if ($end === '00:00' || $end === '24:00') {
                        $end = '23:59';
                    }

                    if ($currentTime < $start || $currentTime > $end) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
