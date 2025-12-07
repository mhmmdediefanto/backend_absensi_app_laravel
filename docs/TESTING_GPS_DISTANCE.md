# Dokumentasi Pengujian Perhitungan Jarak GPS

## Deskripsi

Script pengujian ini digunakan untuk mensimulasikan perhitungan jarak GPS menggunakan formula Haversine. Pengujian dilakukan dengan berbagai titik koordinat untuk memastikan:

1. Jarak yang dihitung logis
2. Sistem memberikan status "Valid" jika ≤ 100 meter
3. Sistem memberikan status "Tidak Valid" jika > 100 meter
4. Tidak ada error seperti jarak negatif atau nilai ekstrem

## Lokasi Acuan

- **Latitude:** -7.250000
- **Longitude:** 112.750000
- **Radius:** 100 meter

## Titik Uji

| No | Titik Uji | Koordinat Siswa | Expected Status |
|----|-----------|-----------------|-----------------|
| 1 | Titik Dekat | -7.250050, 112.750020 | Valid |
| 2 | Titik Sedang | -7.250300, 112.750200 | Valid |
| 3 | Tepat Batas Radius | -7.249200, 112.749900 | Valid |
| 4 | Di Luar Radius | -7.249000, 112.749800 | Tidak Valid |
| 5 | Sangat Jauh | -7.245000, 112.745000 | Tidak Valid |

## Cara Menjalankan

### 1. Menggunakan PHPUnit (Recommended)

```bash
# Jalankan semua test GPS
php artisan test --filter GpsDistanceTest

# Atau menggunakan vendor/bin/phpunit
vendor/bin/phpunit tests/Feature/GpsDistanceTest.php
```

### 2. Menggunakan Script Standalone

#### Via Command Line:
```bash
php test_gps_distance.php
```

#### Via Browser:
Akses URL berikut di browser:
```
http://localhost/backend_absensi_app/test_gps_distance.php
```

## Output yang Diharapkan

### Hasil Pengujian:
- **Total Pengujian:** 5
- **Keputusan Benar:** 5
- **Keputusan Salah:** 0
- **Akurasi:** 100%

### Validasi Tambahan:
- ✅ Semua jarak bernilai positif
- ✅ Tidak ada nilai ekstrem
- 📏 Jarak minimum: ~6.22 m
- 📏 Jarak maksimum: ~142.3 m

## Edge Cases

Script juga menguji edge cases:
1. **Koordinat sama persis** - jarak harus mendekati 0
2. **Koordinat sangat dekat** - jarak harus < 1 meter
3. **Koordinat sangat jauh** - jarak harus > 1000 meter

## Catatan

- Formula Haversine menggunakan radius bumi 6,371,000 meter
- Semua koordinat dikonversi ke radian sebelum perhitungan
- Hasil jarak dalam satuan meter
- Status "Valid" diberikan jika jarak ≤ radius (100 meter)
- Status "Tidak Valid" diberikan jika jarak > radius (100 meter)


