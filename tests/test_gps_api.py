#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script Real Test untuk Validasi Lokasi GPS via API Laravel
Mengirim request ke API untuk test validasi lokasi secara real-time
"""

import sys
import os

# Import dari gps_distance_test.py
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from gps_distance_test import ApiTester, GpsDistanceTester


def main():
    """Main function untuk real test API"""
    # Konfigurasi
    API_BASE_URL = os.getenv('API_BASE_URL', 'http://localhost:8000/api')
    API_TOKEN = os.getenv('API_TOKEN', '')  # Set via environment variable
    LOKASI_PRAKERIN_ID = int(os.getenv('LOKASI_PRAKERIN_ID', '1'))
    
    # Lokasi acuan untuk validasi
    LOKASI_LAT = -7.250000
    LOKASI_LON = 112.750000
    RADIUS = 100
    
    # Data titik uji
    TEST_POINTS = [
        {
            'no': 1,
            'nama': 'Titik Dekat',
            'lat': -7.250050,
            'lon': 112.750020,
            'expected_status': 'Valid'
        },
        {
            'no': 2,
            'nama': 'Titik Sedang',
            'lat': -7.250300,
            'lon': 112.750200,
            'expected_status': 'Valid'
        },
        {
            'no': 3,
            'nama': 'Tepat Batas Radius',
            'lat': -7.249200,
            'lon': 112.749900,
            'expected_status': 'Valid'
        },
        {
            'no': 4,
            'nama': 'Di Luar Radius',
            'lat': -7.249000,
            'lon': 112.749800,
            'expected_status': 'Tidak Valid'
        },
        {
            'no': 5,
            'nama': 'Sangat Jauh',
            'lat': -7.245000,
            'lon': 112.745000,
            'expected_status': 'Tidak Valid'
        },
    ]
    
    print("\n" + "=" * 100)
    print("🌐 REAL TEST - VALIDASI LOKASI GPS VIA API LARAVEL")
    print("=" * 100)
    
    if not API_TOKEN:
        print("\n⚠️  PERINGATAN: API_TOKEN tidak diset!")
        print("   Set environment variable: export API_TOKEN='your_token'")
        print("   Atau edit file ini dan set API_TOKEN secara manual")
        print("\n   Test akan tetap dijalankan, tapi mungkin akan gagal karena auth")
        print()
    
    # Inisialisasi API tester
    api_tester = ApiTester(API_BASE_URL, API_TOKEN if API_TOKEN else None)
    
    # Jalankan test
    result = api_tester.test_location_validation(LOKASI_PRAKERIN_ID, TEST_POINTS)
    
    # Bandingkan dengan perhitungan manual
    print("\n" + "=" * 100)
    print("📊 PERBANDINGAN: API vs PERHITUNGAN MANUAL")
    print("=" * 100)
    
    tester = GpsDistanceTester(LOKASI_LAT, LOKASI_LON, RADIUS)
    
    print(f"\n{'No':<5} {'Titik Uji':<25} {'Jarak Manual':<20} {'Status Manual':<15} {'Status API':<15} {'Match':<10}")
    print("-" * 100)
    
    matches = 0
    for point in TEST_POINTS:
        status_manual, distance = tester.validate_location(point['lat'], point['lon'])
        
        # Cari hasil API
        api_result = next((r for r in result['results'] if r['no'] == point['no']), None)
        status_api = api_result['api_status'] if api_result else 'N/A'
        
        match = "✅" if status_manual == status_api else "❌"
        if status_manual == status_api:
            matches += 1
        
        print(f"{point['no']:<5} {point['nama']:<25} {distance:.2f} m{'':<10} {status_manual:<15} {status_api:<15} {match:<10}")
    
    print("-" * 100)
    match_percentage = (matches / len(TEST_POINTS) * 100) if TEST_POINTS else 0
    print(f"\nKecocokan: {matches}/{len(TEST_POINTS)} ({match_percentage:.2f}%)")
    print("=" * 100)
    print()


if __name__ == "__main__":
    main()


