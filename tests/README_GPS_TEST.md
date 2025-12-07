# Quick Start - GPS Distance Test

## Install Dependencies

```bash
pip install requests
```

## Jalankan Simulasi Test

```bash
python tests/gps_distance_test.py
```

## Jalankan Real Test ke API

1. Set environment variables:
```bash
export API_BASE_URL="http://localhost:8000/api"
export API_TOKEN="your_token_here"
export LOKASI_PRAKERIN_ID=1
```

2. Jalankan test:
```bash
python tests/test_gps_api.py
```

## Output

Script akan menampilkan:
- ✅ Tabel hasil pengujian 5 titik koordinat
- ✅ Statistik akurasi (harusnya 100%)
- ✅ Validasi tambahan (jarak positif, tidak ada nilai ekstrem)
- ✅ Edge cases testing

## Dokumentasi Lengkap

Lihat: `docs/TESTING_GPS_PYTHON.md`


