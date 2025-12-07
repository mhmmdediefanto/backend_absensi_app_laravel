<?php

/**
 * Script Standalone untuk Pengujian Perhitungan Jarak GPS
 * 
 * Cara menjalankan:
 * 1. Via command line: php test_gps_distance.php
 * 2. Via browser: akses http://localhost/backend_absensi_app/test_gps_distance.php
 * 
 * Lokasi acuan (Lokasi Industri):
 * - Latitude: -7.250000
 * - Longitude: 112.750000
 * - Radius: 100 meter
 */

// Fungsi untuk menghitung jarak menggunakan Haversine Formula
// (sama seperti App\Helpers\HaversineFormula)
function calculateDistance($lat1, $lon1, $lat2, $lon2)
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

    // Rumus inti Haversine
    $a = sin($dLat / 2) ** 2 +
        cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;

    // Menghitung sudut pusat (central angle)
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    // hasil dalam meter
    return $earthRadius * $c;
}

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

// Output HTML jika diakses via browser
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pengujian Perhitungan Jarak GPS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .valid { color: #4CAF50; font-weight: bold; }
        .invalid { color: #f44336; font-weight: bold; }
        .success { color: #4CAF50; }
        .error { color: #f44336; }
        .info-box { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196F3; margin: 20px 0; }
        .summary { background: #f1f8e9; padding: 15px; border-left: 4px solid #8BC34A; margin: 20px 0; }
        .validation { background: #fff3e0; padding: 15px; border-left: 4px solid #FF9800; margin: 20px 0; }
    </style>
</head>
<body>
<div class="container">';
}

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

if (!$isCli) {
    echo '<div class="info-box">
    <strong>📍 Lokasi Acuan (Lokasi Industri):</strong><br>
    Latitude: ' . $lokasiLat . '<br>
    Longitude: ' . $lokasiLon . '<br>
    Radius: ' . $radius . ' meter
</div>';
}

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

if (!$isCli) {
    echo '<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Titik Uji</th>
            <th>Koordinat Siswa</th>
            <th>Hasil Jarak (meter)</th>
            <th>Status Sistem</th>
            <th>Hasil Uji</th>
        </tr>
    </thead>
    <tbody>';
}

$totalTest = 0;
$totalBenar = 0;
$results = [];

foreach ($testPoints as $point) {
    $totalTest++;
    
    // Hitung jarak menggunakan Haversine Formula
    $distance = calculateDistance(
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
    $statusClass = $status === 'Valid' ? 'valid' : 'invalid';
    $resultClass = $isCorrect ? 'success' : 'error';

    echo sprintf(
        "%-5s %-25s %-30s %-20s %-15s %-15s\n",
        $point['no'],
        $point['nama'],
        $coords,
        $distanceFormatted,
        $status,
        $icon . " " . ($isCorrect ? "Benar" : "Salah")
    );

    if (!$isCli) {
        echo '<tr>
            <td>' . $point['no'] . '</td>
            <td>' . htmlspecialchars($point['nama']) . '</td>
            <td>' . htmlspecialchars($coords) . '</td>
            <td>' . number_format($distance, 2) . ' m</td>
            <td class="' . $statusClass . '">' . $status . '</td>
            <td class="' . $resultClass . '">' . ($isCorrect ? '✅ Benar' : '❌ Salah') . '</td>
        </tr>';
    }
}

if (!$isCli) {
    echo '</tbody></table>';
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

if (!$isCli) {
    echo '<div class="summary">
    <h2>📊 Hasil Pengujian</h2>
    <p><strong>Total Pengujian:</strong> ' . $totalTest . '</p>
    <p><strong>Keputusan Benar:</strong> ' . $totalBenar . '</p>
    <p><strong>Keputusan Salah:</strong> ' . ($totalTest - $totalBenar) . '</p>
    <p><strong>Akurasi:</strong> ' . number_format($akurasi, 2) . '%</p>
</div>';
}

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

if (!$isCli) {
    echo '<div class="validation">
    <h2>🔍 Validasi Tambahan</h2>
    <p><strong>✅ Semua jarak bernilai positif:</strong> ' . ($allDistancesPositive ? 'Ya' : 'Tidak') . '</p>
    <p><strong>✅ Tidak ada nilai ekstrem:</strong> ' . ($noExtremeValues ? 'Ya' : 'Tidak') . '</p>
    <p><strong>📏 Jarak minimum:</strong> ' . number_format($minDistance, 2) . ' m</p>
    <p><strong>📏 Jarak maksimum:</strong> ' . number_format($maxDistance, 2) . ' m</p>
</div>';

    // Edge Cases Test
    echo '<h2>🧪 Pengujian Edge Cases</h2>';
    echo '<table>
    <thead>
        <tr>
            <th>Test Case</th>
            <th>Jarak (meter)</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>';

    // Test 1: Koordinat sama persis
    $distance1 = calculateDistance($lokasiLat, $lokasiLon, $lokasiLat, $lokasiLon);
    $test1Pass = $distance1 < 0.01;
    echo '<tr>
        <td>Koordinat sama persis</td>
        <td>' . number_format($distance1, 2) . '</td>
        <td class="' . ($test1Pass ? 'success' : 'error') . '">' . ($test1Pass ? '✅ Pass' : '❌ Fail') . '</td>
    </tr>';

    // Test 2: Koordinat sangat dekat
    $distance2 = calculateDistance(
        $lokasiLat,
        $lokasiLon,
        $lokasiLat + 0.000001,
        $lokasiLon + 0.000001
    );
    $test2Pass = $distance2 < 1;
    echo '<tr>
        <td>Koordinat sangat dekat</td>
        <td>' . number_format($distance2, 2) . '</td>
        <td class="' . ($test2Pass ? 'success' : 'error') . '">' . ($test2Pass ? '✅ Pass' : '❌ Fail') . '</td>
    </tr>';

    // Test 3: Koordinat sangat jauh
    $distance3 = calculateDistance(
        $lokasiLat,
        $lokasiLon,
        $lokasiLat + 0.1,
        $lokasiLon + 0.1
    );
    $test3Pass = $distance3 > 1000;
    echo '<tr>
        <td>Koordinat sangat jauh</td>
        <td>' . number_format($distance3, 2) . '</td>
        <td class="' . ($test3Pass ? 'success' : 'error') . '">' . ($test3Pass ? '✅ Pass' : '❌ Fail') . '</td>
    </tr>';

    echo '</tbody></table>';
    echo '</div></body></html>';
} else {
    // Edge Cases Test untuk CLI
    echo "🧪 PENGUJIAN EDGE CASES:\n";
    echo str_repeat("-", 100) . "\n";
    
    // Test 1: Koordinat sama persis
    $distance1 = calculateDistance($lokasiLat, $lokasiLon, $lokasiLat, $lokasiLon);
    echo "Test 1 - Koordinat sama persis:\n";
    echo "   Jarak: " . number_format($distance1, 2) . " m\n";
    echo "   Status: " . ($distance1 < 0.01 ? "✅ Pass" : "❌ Fail") . "\n\n";

    // Test 2: Koordinat sangat dekat
    $distance2 = calculateDistance(
        $lokasiLat,
        $lokasiLon,
        $lokasiLat + 0.000001,
        $lokasiLon + 0.000001
    );
    echo "Test 2 - Koordinat sangat dekat:\n";
    echo "   Jarak: " . number_format($distance2, 2) . " m\n";
    echo "   Status: " . ($distance2 < 1 ? "✅ Pass" : "❌ Fail") . "\n\n";

    // Test 3: Koordinat sangat jauh
    $distance3 = calculateDistance(
        $lokasiLat,
        $lokasiLon,
        $lokasiLat + 0.1,
        $lokasiLon + 0.1
    );
    echo "Test 3 - Koordinat sangat jauh:\n";
    echo "   Jarak: " . number_format($distance3, 2) . " m\n";
    echo "   Status: " . ($distance3 > 1000 ? "✅ Pass" : "❌ Fail") . "\n\n";

    echo str_repeat("=", 100) . "\n";
}

