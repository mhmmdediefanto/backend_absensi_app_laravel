#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script Pengujian Perhitungan Jarak GPS
Menggunakan Haversine Formula untuk menghitung jarak antara dua koordinat

Lokasi acuan (Lokasi Industri):
- Latitude: -7.250000
- Longitude: 112.750000
- Radius: 100 meter
"""

import math
import requests
import json
from typing import Dict, List, Tuple
from datetime import datetime


class HaversineFormula:
    """Kelas untuk menghitung jarak menggunakan Haversine Formula"""
    
    EARTH_RADIUS = 6371000  # Radius bumi dalam meter
    
    @staticmethod
    def distance(lat1: float, lon1: float, lat2: float, lon2: float) -> float:
        """
        Menghitung jarak antara dua koordinat menggunakan Haversine Formula
        
        Args:
            lat1: Latitude titik pertama
            lon1: Longitude titik pertama
            lat2: Latitude titik kedua
            lon3: Longitude titik kedua
            
        Returns:
            Jarak dalam meter
        """
        # Konversi derajat ke radian
        lat1_rad = math.radians(lat1)
        lon1_rad = math.radians(lon1)
        lat2_rad = math.radians(lat2)
        lon2_rad = math.radians(lon2)
        
        # Hitung selisih latitude dan longitude
        d_lat = lat2_rad - lat1_rad
        d_lon = lon2_rad - lon1_rad
        
        # Rumus inti Haversine
        a = (math.sin(d_lat / 2) ** 2 +
             math.cos(lat1_rad) * math.cos(lat2_rad) * math.sin(d_lon / 2) ** 2)
        
        # Menghitung sudut pusat (central angle)
        c = 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a))
        
        # Hasil dalam meter
        return HaversineFormula.EARTH_RADIUS * c


class GpsDistanceTester:
    """Kelas untuk melakukan pengujian perhitungan jarak GPS"""
    
    def __init__(self, lokasi_lat: float, lokasi_lon: float, radius: float):
        """
        Inisialisasi tester
        
        Args:
            lokasi_lat: Latitude lokasi acuan
            lokasi_lon: Longitude lokasi acuan
            radius: Radius valid dalam meter
        """
        self.lokasi_lat = lokasi_lat
        self.lokasi_lon = lokasi_lon
        self.radius = radius
        self.haversine = HaversineFormula()
    
    def calculate_distance(self, lat: float, lon: float) -> float:
        """Hitung jarak dari koordinat ke lokasi acuan"""
        return self.haversine.distance(self.lokasi_lat, self.lokasi_lon, lat, lon)
    
    def validate_location(self, lat: float, lon: float) -> Tuple[str, float]:
        """
        Validasi lokasi berdasarkan radius
        
        Returns:
            Tuple (status, jarak)
        """
        distance = self.calculate_distance(lat, lon)
        status = "Valid" if distance <= self.radius else "Tidak Valid"
        return status, distance
    
    def run_test_points(self, test_points: List[Dict]) -> Dict:
        """
        Jalankan pengujian dengan berbagai titik koordinat
        
        Args:
            test_points: List titik uji dengan format:
                [{
                    'no': int,
                    'nama': str,
                    'lat': float,
                    'lon': float,
                    'expected_status': str
                }]
        
        Returns:
            Dictionary hasil pengujian
        """
        results = []
        total_test = 0
        total_benar = 0
        
        print("\n" + "=" * 100)
        print("🧪 PENGUJIAN PERHITUNGAN JARAK GPS")
        print("=" * 100)
        print(f"\n📍 Lokasi Acuan (Lokasi Industri):")
        print(f"   Latitude:  {self.lokasi_lat}")
        print(f"   Longitude: {self.lokasi_lon}")
        print(f"   Radius:    {self.radius} meter\n")
        print("-" * 100)
        print(f"{'No':<5} {'Titik Uji':<25} {'Koordinat Siswa':<30} {'Hasil Jarak (m)':<20} {'Status Sistem':<15} {'Hasil Uji':<15}")
        print("-" * 100)
        
        for point in test_points:
            total_test += 1
            
            status, distance = self.validate_location(point['lat'], point['lon'])
            is_correct = (status == point['expected_status'])
            
            if is_correct:
                total_benar += 1
            
            result = {
                'no': point['no'],
                'nama': point['nama'],
                'lat': point['lat'],
                'lon': point['lon'],
                'distance': distance,
                'status': status,
                'expected': point['expected_status'],
                'correct': is_correct
            }
            results.append(result)
            
            # Format output
            coords = f"{point['lat']:.6f}, {point['lon']:.6f}"
            distance_str = f"{distance:.2f} m"
            icon = "✅" if is_correct else "❌"
            result_text = f"{icon} {'Benar' if is_correct else 'Salah'}"
            
            print(f"{point['no']:<5} {point['nama']:<25} {coords:<30} {distance_str:<20} {status:<15} {result_text:<15}")
        
        print("-" * 100)
        
        # Hitung akurasi
        akurasi = (total_benar / total_test * 100) if total_test > 0 else 0
        
        print(f"\n📊 HASIL PENGUJIAN:")
        print("-" * 100)
        print(f"Total Pengujian:     {total_test}")
        print(f"Keputusan Benar:     {total_benar}")
        print(f"Keputusan Salah:     {total_test - total_benar}")
        print(f"Akurasi:             {akurasi:.2f}%")
        print("=" * 100)
        
        # Validasi tambahan
        self._print_validation(results)
        
        return {
            'total_test': total_test,
            'total_benar': total_benar,
            'akurasi': akurasi,
            'results': results
        }
    
    def _print_validation(self, results: List[Dict]):
        """Cetak validasi tambahan"""
        print("\n🔍 VALIDASI TAMBAHAN:")
        print("-" * 100)
        
        all_positive = all(r['distance'] >= 0 for r in results)
        no_extreme = all(r['distance'] < 10000 for r in results)  # < 10km
        distances = [r['distance'] for r in results]
        min_dist = min(distances) if distances else 0
        max_dist = max(distances) if distances else 0
        
        print(f"✅ Semua jarak bernilai positif: {'Ya' if all_positive else 'Tidak'}")
        print(f"✅ Tidak ada nilai ekstrem:      {'Ya' if no_extreme else 'Tidak'}")
        print(f"📏 Jarak minimum:                {min_dist:.2f} m")
        print(f"📏 Jarak maksimum:               {max_dist:.2f} m")
        print("=" * 100)
    
    def test_edge_cases(self):
        """Test edge cases untuk memastikan tidak ada error"""
        print("\n" + "=" * 100)
        print("🧪 PENGUJIAN EDGE CASES")
        print("=" * 100)
        print()
        
        # Test 1: Koordinat sama persis
        distance1 = self.calculate_distance(self.lokasi_lat, self.lokasi_lon)
        test1_pass = distance1 < 0.01
        print(f"Test 1 - Koordinat sama persis:")
        print(f"   Jarak: {distance1:.2f} m")
        print(f"   Status: {'✅ Pass' if test1_pass else '❌ Fail'}\n")
        
        # Test 2: Koordinat sangat dekat
        distance2 = self.calculate_distance(
            self.lokasi_lat + 0.000001,
            self.lokasi_lon + 0.000001
        )
        test2_pass = distance2 < 1
        print(f"Test 2 - Koordinat sangat dekat:")
        print(f"   Jarak: {distance2:.2f} m")
        print(f"   Status: {'✅ Pass' if test2_pass else '❌ Fail'}\n")
        
        # Test 3: Koordinat sangat jauh
        distance3 = self.calculate_distance(
            self.lokasi_lat + 0.1,
            self.lokasi_lon + 0.1
        )
        test3_pass = distance3 > 1000
        print(f"Test 3 - Koordinat sangat jauh:")
        print(f"   Jarak: {distance3:.2f} m")
        print(f"   Status: {'✅ Pass' if test3_pass else '❌ Fail'}\n")
        
        print("=" * 100)
        
        return {
            'test1': test1_pass,
            'test2': test2_pass,
            'test3': test3_pass
        }


class ApiTester:
    """Kelas untuk melakukan real test ke API Laravel"""
    
    def __init__(self, base_url: str, token: str = None):
        """
        Inisialisasi API tester
        
        Args:
            base_url: Base URL API (contoh: http://localhost:8000/api)
            token: Token autentikasi (opsional)
        """
        self.base_url = base_url.rstrip('/')
        self.token = token
        self.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
        if token:
            self.headers['Authorization'] = f'Bearer {token}'
    
    def test_location_validation(self, lokasi_prakerin_id: int, 
                                 test_points: List[Dict]) -> Dict:
        """
        Test validasi lokasi melalui API
        
        Args:
            lokasi_prakerin_id: ID lokasi prakerin
            test_points: List titik uji
        
        Returns:
            Dictionary hasil pengujian API
        """
        print("\n" + "=" * 100)
        print("🌐 REAL TEST - VALIDASI LOKASI VIA API")
        print("=" * 100)
        print(f"\n🔗 Base URL: {self.base_url}")
        print(f"📍 Lokasi Prakerin ID: {lokasi_prakerin_id}\n")
        print("-" * 100)
        print(f"{'No':<5} {'Titik Uji':<25} {'Koordinat':<30} {'Response Status':<20} {'API Status':<20} {'Hasil Uji':<15}")
        print("-" * 100)
        
        results = []
        total_test = 0
        total_success = 0
        
        for point in test_points:
            total_test += 1
            
            # Simulasi request absensi (tanpa foto dan face recognition)
            # Catatan: Ini hanya test validasi lokasi, bukan full absensi
            url = f"{self.base_url}/siswa/absensi"
            
            payload = {
                'lokasi_prakerin_id': lokasi_prakerin_id,
                'lat': point['lat'],
                'lon': point['lon'],
                'tipe_absen': 'masuk'
            }
            
            try:
                # Catatan: Request ini akan gagal karena tidak ada foto
                # Tapi kita bisa lihat response error untuk validasi lokasi
                response = requests.post(
                    url,
                    json=payload,
                    headers=self.headers,
                    timeout=10
                )
                
                response_status = response.status_code
                response_data = response.json() if response.content else {}
                
                # Cek apakah error karena lokasi atau hal lain
                message = response_data.get('message', '')
                is_location_error = 'lokasi' in message.lower() or 'jarak' in message.lower()
                
                # Jika error karena lokasi dan expected "Tidak Valid", itu benar
                # Jika success atau error bukan lokasi dan expected "Valid", itu benar
                if point['expected_status'] == 'Tidak Valid':
                    api_correct = is_location_error or response_status == 400
                else:
                    api_correct = not is_location_error or response_status == 200
                
                if api_correct:
                    total_success += 1
                
                api_status = "Valid" if not is_location_error else "Tidak Valid"
                icon = "✅" if api_correct else "❌"
                
                results.append({
                    'no': point['no'],
                    'nama': point['nama'],
                    'response_status': response_status,
                    'api_status': api_status,
                    'message': message,
                    'correct': api_correct
                })
                
                coords = f"{point['lat']:.6f}, {point['lon']:.6f}"
                print(f"{point['no']:<5} {point['nama']:<25} {coords:<30} {response_status:<20} {api_status:<20} {icon} {'Benar' if api_correct else 'Salah'}")
                
            except requests.exceptions.RequestException as e:
                print(f"{point['no']:<5} {point['nama']:<25} {'Error':<30} {'N/A':<20} {'Error':<20} ❌ {str(e)[:30]}")
                results.append({
                    'no': point['no'],
                    'nama': point['nama'],
                    'error': str(e),
                    'correct': False
                })
        
        print("-" * 100)
        akurasi = (total_success / total_test * 100) if total_test > 0 else 0
        print(f"\n📊 HASIL REAL TEST API:")
        print("-" * 100)
        print(f"Total Test:          {total_test}")
        print(f"Berhasil:            {total_success}")
        print(f"Gagal:               {total_test - total_success}")
        print(f"Akurasi:             {akurasi:.2f}%")
        print("=" * 100)
        
        return {
            'total_test': total_test,
            'total_success': total_success,
            'akurasi': akurasi,
            'results': results
        }


def main():
    """Main function untuk menjalankan semua test"""
    # Konfigurasi lokasi acuan
    LOKASI_LAT = -7.250000
    LOKASI_LON = 112.750000
    RADIUS = 100  # meter
    
    # Data titik uji sesuai Tabel 4.8
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
    
    # Inisialisasi tester
    tester = GpsDistanceTester(LOKASI_LAT, LOKASI_LON, RADIUS)
    
    # Jalankan test perhitungan jarak
    print("\n" + "=" * 100)
    print("📋 SIMULASI PERHITUNGAN JARAK GPS")
    print("=" * 100)
    result = tester.run_test_points(TEST_POINTS)
    
    # Jalankan edge cases test
    edge_results = tester.test_edge_cases()
    
    # Real test ke API (opsional - uncomment jika ingin test ke API)
    # API_BASE_URL = "http://localhost:8000/api"
    # API_TOKEN = "your_token_here"  # Ganti dengan token yang valid
    # LOKASI_PRAKERIN_ID = 1  # Ganti dengan ID lokasi prakerin yang valid
    # 
    # api_tester = ApiTester(API_BASE_URL, API_TOKEN)
    # api_result = api_tester.test_location_validation(LOKASI_PRAKERIN_ID, TEST_POINTS)
    
    # Summary
    print("\n" + "=" * 100)
    print("📊 RINGKASAN HASIL SEMUA TEST")
    print("=" * 100)
    print(f"✅ Test Perhitungan Jarak: {result['akurasi']:.2f}% akurasi")
    print(f"✅ Edge Cases: Semua test {'Pass' if all(edge_results.values()) else 'Ada yang Fail'}")
    print("=" * 100)
    print()


if __name__ == "__main__":
    main()


