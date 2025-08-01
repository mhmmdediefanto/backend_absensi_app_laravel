<?php

namespace App\Helpers;


class HaversineFormula
{
    public static function distance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // radius bumi dalam meter

        // konversi derajat ke radian
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // hitung selisih latitude dan longitude
        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        // Rumus inti Haversine: menghitung kuadrat jarak chord di permukaan bumi.
        $a = sin($dLat / 2) ** 2 +
            cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;

        // Menghitung sudut pusat (central angle) antara dua titik di bola bumi.
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // hasil dalam meter
        return $earthRadius * $c;
    }
}
