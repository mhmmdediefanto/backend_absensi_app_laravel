<?php

namespace Tests\Feature;

use App\Helpers\HaversineFormula;
use Tests\TestCase;

class GpsDistanceTest extends TestCase
{
    /**
     * Test perhitungan jarak GPS dengan berbagai titik koordinat
     * 
     * Lokasi acuan (Lokasi Industri):
     * - Latitude: -7.250000
     * - Longitude: 112.750000
     * - Radius: 100 meter
     */
    public function test_gps_distance_calculation()
    {
        // Koordinat lokasi acuan
        $lokasiLat = -7.250000;
        $lokasiLon = 112.750000;
        $radius = 100; // meter

        // Data titik uji sesuai Tabel 4.8
        $testPoints = [
            [
                'no' => 1,
                'nama' => 'Titik Dekat',
                'lat' => -7.250050,
                'lon' => 112.750020,
                'expected_status' => 'Valid'
            ],
            [
                'no' => 2,
                'nama' => 'Titik Sedang',
                'lat' => -7.250300,
                'lon' => 112.750200,
                'expected_status' => 'Valid'
            ],
            [
                'no' => 3,
                'nama' => 'Tepat Batas Radius',
                'lat' => -7.249200,
                'lon' => 112.749900,
                'expected_status' => 'Valid'
            ],
            [
                'no' => 4,
                'nama' => 'Di Luar Radius',
                'lat' => -7.249000,
                'lon' => 112.749800,
                'expected_status' => 'Tidak Valid'
            ],
            [
                'no' => 5,
                'nama' => 'Sangat Jauh',
                'lat' => -7.245000,
                'lon' => 112.745000,
                'expected_status' => 'Tidak Valid'
            ],
        ];

        echo "\n";
        echo str_repeat("=", 100) . "\n";
        echo "🧪 PENGUJIAN PERHITUNGAN JARAK GPS\n";
        echo str_repeat("=", 100) . "\n";
        echo "\n";
        echo "📍 Lokasi Acuan (Lokasi Industri):\n";
        echo "   Latitude:  {$lokasiLat}\n";
        echo "   Longitude: {$lokasiLon}\n";
        echo "   Radius:    {$radius} meter\n";
        echo "\n";
        echo str_repeat("-", 100) . "\n";
        echo sprintf(
            "%-5s %-25s %-30s %-20s %-15s %-15s\n",
            "No",
            "Titik Uji",
            "Koordinat Siswa",
            "Hasil Jarak (m)",
            "Status Sistem",
            "Hasil Uji"
        );
        echo str_repeat("-", 100) . "\n";

        $totalTest = 0;
        $totalBenar = 0;
        $results = [];

        foreach ($testPoints as $point) {
            $totalTest++;
            
            // Hitung jarak menggunakan Haversine Formula
            $distance = HaversineFormula::distance(
                $lokasiLat,
                $lokasiLon,
                $point['lat'],
                $point['lon']
            );

            // Tentukan status berdasarkan radius
            $status = ($distance <= $radius) ? 'Valid' : 'Tidak Valid';
            
            // Cek apakah hasil sesuai ekspektasi
            $isCorrect = ($status === $point['expected_status']);
            if ($isCorrect) {
                $totalBenar++;
            }

            $results[] = [
                'no' => $point['no'],
                'nama' => $point['nama'],
                'lat' => $point['lat'],
                'lon' => $point['lon'],
                'distance' => $distance,
                'status' => $status,
                'expected' => $point['expected_status'],
                'correct' => $isCorrect
            ];

            // Format koordinat untuk display
            $coords = sprintf("%.6f, %.6f", $point['lat'], $point['lon']);
            
            // Format jarak dengan 2 desimal
            $distanceFormatted = number_format($distance, 2) . " m";
            
            // Icon hasil uji
            $icon = $isCorrect ? "✅" : "❌";

            echo sprintf(
                "%-5s %-25s %-30s %-20s %-15s %-15s\n",
                $point['no'],
                $point['nama'],
                $coords,
                $distanceFormatted,
                $status,
                $icon . " " . ($isCorrect ? "Benar" : "Salah")
            );
        }

        echo str_repeat("-", 100) . "\n";
        echo "\n";

        // Hitung akurasi
        $akurasi = ($totalBenar / $totalTest) * 100;

        echo "📊 HASIL PENGUJIAN:\n";
        echo str_repeat("-", 100) . "\n";
        echo "Total Pengujian:     {$totalTest}\n";
        echo "Keputusan Benar:     {$totalBenar}\n";
        echo "Keputusan Salah:     " . ($totalTest - $totalBenar) . "\n";
        echo "Akurasi:             " . number_format($akurasi, 2) . "%\n";
        echo str_repeat("=", 100) . "\n";
        echo "\n";

        // Validasi tambahan
        echo "🔍 VALIDASI TAMBAHAN:\n";
        echo str_repeat("-", 100) . "\n";

        $allDistancesPositive = true;
        $noExtremeValues = true;
        $maxDistance = 0;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($results as $result) {
            if ($result['distance'] < 0) {
                $allDistancesPositive = false;
            }
            if ($result['distance'] > 10000) { // Lebih dari 10km dianggap ekstrem
                $noExtremeValues = false;
            }
            if ($result['distance'] > $maxDistance) {
                $maxDistance = $result['distance'];
            }
            if ($result['distance'] < $minDistance) {
                $minDistance = $result['distance'];
            }
        }

        echo "✅ Semua jarak bernilai positif: " . ($allDistancesPositive ? "Ya" : "Tidak") . "\n";
        echo "✅ Tidak ada nilai ekstrem:      " . ($noExtremeValues ? "Ya" : "Tidak") . "\n";
        echo "📏 Jarak minimum:                " . number_format($minDistance, 2) . " m\n";
        echo "📏 Jarak maksimum:               " . number_format($maxDistance, 2) . " m\n";
        echo str_repeat("=", 100) . "\n";
        echo "\n";

        // Assertions untuk PHPUnit
        $this->assertEquals($totalTest, $totalBenar, "Semua pengujian harus menghasilkan keputusan yang benar");
        $this->assertTrue($allDistancesPositive, "Semua jarak harus bernilai positif");
        $this->assertTrue($noExtremeValues, "Tidak boleh ada nilai ekstrem");
    }

    /**
     * Test edge cases untuk memastikan tidak ada error
     */
    public function test_edge_cases()
    {
        $lokasiLat = -7.250000;
        $lokasiLon = 112.750000;

        echo "\n";
        echo str_repeat("=", 100) . "\n";
        echo "🧪 PENGUJIAN EDGE CASES\n";
        echo str_repeat("=", 100) . "\n";
        echo "\n";

        // Test 1: Koordinat sama persis (jarak harus 0)
        $distance1 = HaversineFormula::distance($lokasiLat, $lokasiLon, $lokasiLat, $lokasiLon);
        echo "Test 1 - Koordinat sama persis:\n";
        echo "   Jarak: " . number_format($distance1, 2) . " m\n";
        echo "   Status: " . ($distance1 < 0.01 ? "✅ Pass" : "❌ Fail") . "\n\n";

        $this->assertLessThan(0.01, $distance1, "Jarak koordinat yang sama harus mendekati 0");

        // Test 2: Koordinat sangat dekat (harus < 1 meter)
        $distance2 = HaversineFormula::distance(
            $lokasiLat,
            $lokasiLon,
            $lokasiLat + 0.000001,
            $lokasiLon + 0.000001
        );
        echo "Test 2 - Koordinat sangat dekat:\n";
        echo "   Jarak: " . number_format($distance2, 2) . " m\n";
        echo "   Status: " . ($distance2 < 1 ? "✅ Pass" : "❌ Fail") . "\n\n";

        $this->assertLessThan(1, $distance2, "Jarak koordinat sangat dekat harus < 1 meter");

        // Test 3: Koordinat sangat jauh (harus > 1000 meter)
        $distance3 = HaversineFormula::distance(
            $lokasiLat,
            $lokasiLon,
            $lokasiLat + 0.1,
            $lokasiLon + 0.1
        );
        echo "Test 3 - Koordinat sangat jauh:\n";
        echo "   Jarak: " . number_format($distance3, 2) . " m\n";
        echo "   Status: " . ($distance3 > 1000 ? "✅ Pass" : "❌ Fail") . "\n\n";

        $this->assertGreaterThan(1000, $distance3, "Jarak koordinat sangat jauh harus > 1000 meter");

        echo str_repeat("=", 100) . "\n";
        echo "\n";
    }
}

