# Dokumentasi Pengujian GPS Distance dengan Python

## Deskripsi

Script Python untuk melakukan pengujian perhitungan jarak GPS menggunakan Haversine Formula. Script ini dapat melakukan:

1. **Simulasi Perhitungan Jarak** - Menghitung jarak dan validasi lokasi secara offline
2. **Real Test API** - Mengirim request ke API Laravel untuk test validasi lokasi secara real-time

## Instalasi

### 1. Install Dependencies

```bash
pip install -r requirements_test.txt
```

Atau install manual:
```bash
pip install requests
```

### 2. Pastikan Python 3.6+ terinstall

```bash
python --version
# atau
python3 --version
```

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

### 1. Simulasi Perhitungan Jarak (Offline)

Jalankan script utama untuk simulasi perhitungan jarak:

```bash
python tests/gps_distance_test.py
```

Atau:

```bash
python3 tests/gps_distance_test.py
```

**Output yang dihasilkan:**
- Tabel hasil pengujian dengan 5 titik uji
- Statistik akurasi
- Validasi tambahan (jarak positif, tidak ada nilai ekstrem)
- Edge cases testing

### 2. Real Test ke API Laravel

Untuk melakukan real test ke API, jalankan:

```bash
# Set environment variables
export API_BASE_URL="http://localhost:8000/api"
export API_TOKEN="your_auth_token_here"
export LOKASI_PRAKERIN_ID=1

# Jalankan test
python tests/test_gps_api.py
```

Atau edit file `test_gps_api.py` dan set variabel secara manual:

```python
API_BASE_URL = 'http://localhost:8000/api'
API_TOKEN = 'your_token_here'
LOKASI_PRAKERIN_ID = 1
```

**Catatan:** 
- Pastikan server Laravel berjalan
- Dapatkan token autentikasi dari login API
- Ganti `LOKASI_PRAKERIN_ID` dengan ID lokasi yang valid di database

## Output yang Diharapkan

### Simulasi Perhitungan Jarak:

```
🧪 PENGUJIAN PERHITUNGAN JARAK GPS
====================================================================================================
📍 Lokasi Acuan (Lokasi Industri):
   Latitude:  -7.25
   Longitude: 112.75
   Radius:    100 meter

No    Titik Uji                  Koordinat Siswa              Hasil Jarak (m)      Status Sistem   Hasil Uji      
----------------------------------------------------------------------------------------------------
1     Titik Dekat                -7.250050, 112.750020        6.22 m               Valid           ✅ Benar      
2     Titik Sedang               -7.250300, 112.750200         36.66 m              Valid           ✅ Benar      
3     Tepat Batas Radius         -7.249200, 112.749900        97.11 m              Valid           ✅ Benar      
4     Di Luar Radius             -7.249000, 112.749800         142.30 m             Tidak Valid    ✅ Benar      
5     Sangat Jauh                -7.245000, 112.745000         142.30 m             Tidak Valid    ✅ Benar      

📊 HASIL PENGUJIAN:
----------------------------------------------------------------------------------------------------
Total Pengujian:     5
Keputusan Benar:     5
Keputusan Salah:     0
Akurasi:             100.00%
====================================================================================================
```

### Real Test API:

```
🌐 REAL TEST - VALIDASI LOKASI VIA API
====================================================================================================
🔗 Base URL: http://localhost:8000/api
📍 Lokasi Prakerin ID: 1

No    Titik Uji                  Koordinat                     Response Status     API Status          Hasil Uji      
----------------------------------------------------------------------------------------------------
1     Titik Dekat                -7.250050, 112.750020        200                 Valid               ✅ Benar      
...
```

## Struktur File

```
tests/
├── gps_distance_test.py    # Script utama untuk simulasi perhitungan jarak
├── test_gps_api.py         # Script untuk real test ke API
└── ...

requirements_test.txt        # Dependencies Python
docs/
└── TESTING_GPS_PYTHON.md   # Dokumentasi ini
```

## Fitur

### 1. Simulasi Perhitungan Jarak (`gps_distance_test.py`)

- ✅ Menghitung jarak menggunakan Haversine Formula
- ✅ Validasi status berdasarkan radius
- ✅ Test 5 titik koordinat sesuai Tabel 4.8
- ✅ Edge cases testing (koordinat sama, sangat dekat, sangat jauh)
- ✅ Validasi tambahan (jarak positif, tidak ada nilai ekstrem)
- ✅ Menampilkan statistik akurasi

### 2. Real Test API (`test_gps_api.py`)

- ✅ Mengirim request ke API Laravel
- ✅ Test validasi lokasi secara real-time
- ✅ Membandingkan hasil API dengan perhitungan manual
- ✅ Menampilkan response status dan message dari API
- ✅ Statistik kecocokan antara API dan perhitungan manual

## Troubleshooting

### Error: ModuleNotFoundError: No module named 'requests'

**Solusi:**
```bash
pip install requests
```

### Error: Connection refused saat test API

**Solusi:**
1. Pastikan server Laravel berjalan
2. Cek `API_BASE_URL` sudah benar
3. Pastikan tidak ada firewall yang memblokir koneksi

### Error: 401 Unauthorized saat test API

**Solusi:**
1. Pastikan `API_TOKEN` sudah diset dengan benar
2. Token mungkin sudah expired, login ulang untuk mendapatkan token baru
3. Pastikan format token: `Bearer {token}` (script sudah handle ini)

### Error: 404 Not Found saat test API

**Solusi:**
1. Cek route API sudah benar: `/api/siswa/absensi`
2. Pastikan middleware auth sudah dikonfigurasi dengan benar
3. Cek `LOKASI_PRAKERIN_ID` sudah valid di database

## Customization

### Menambah Titik Uji

Edit variabel `TEST_POINTS` di file `gps_distance_test.py`:

```python
TEST_POINTS = [
    {
        'no': 6,
        'nama': 'Titik Baru',
        'lat': -7.250100,
        'lon': 112.750100,
        'expected_status': 'Valid'
    },
    # ... tambahkan lebih banyak
]
```

### Mengubah Lokasi Acuan

Edit variabel di awal fungsi `main()`:

```python
LOKASI_LAT = -7.250000  # Ganti dengan latitude lokasi
LOKASI_LON = 112.750000  # Ganti dengan longitude lokasi
RADIUS = 100  # Ganti dengan radius dalam meter
```

## Catatan

- Formula Haversine menggunakan radius bumi 6,371,000 meter
- Semua koordinat dikonversi ke radian sebelum perhitungan
- Hasil jarak dalam satuan meter
- Status "Valid" diberikan jika jarak ≤ radius
- Status "Tidak Valid" diberikan jika jarak > radius


