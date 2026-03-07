<?php

class DeliveryQuoteService
{
    public const BASE_FARE = 20.0;
    public const PER_SUCCEEDING_KM = 10.0;

    public static function parseCoord($value)
    {
        if ($value === null || $value === '') return null;
        if (!is_numeric($value)) return null;
        return floatval($value);
    }

    public static function getStoreCoordsFromSettings($settings)
    {
        if (!is_array($settings)) return null;

        $lat = self::parseCoord($settings['store_latitude'] ?? null);
        $lng = self::parseCoord($settings['store_longitude'] ?? null);

        if ($lat === null || $lng === null) {
            return null;
        }

        return ['latitude' => $lat, 'longitude' => $lng];
    }

    public static function haversineKm($fromLat, $fromLng, $toLat, $toLng)
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($toLat - $fromLat);
        $dLon = deg2rad($toLng - $fromLng);
        $lat1 = deg2rad($fromLat);
        $lat2 = deg2rad($toLat);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             sin($dLon / 2) * sin($dLon / 2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    public static function calculateFee($distanceKm)
    {
        $safeDistance = max(0, floatval($distanceKm));
        $succeedingKm = max(0, ceil($safeDistance - 1));
        return self::BASE_FARE + ($succeedingKm * self::PER_SUCCEEDING_KM);
    }

    public static function estimateWindow($distanceKm)
    {
        $safeDistance = max(0, floatval($distanceKm));
        $min = max(15, round(15 + ($safeDistance * 4)));
        $max = $min + 10;
        return ['min' => intval($min), 'max' => intval($max)];
    }
}

