# Penjelasan Sistem Test Face Recognition

## Cara Kerja Sistem Test

Sistem test bekerja dengan cara:

1. **Scan semua foto** di folder test
2. **Setiap foto = 1 test case**
3. **Uji setiap foto** apakah bisa dikenali oleh sistem
4. **Hitung hasil**: Berhasil vs Gagal
5. **Hitung akurasi**: (Berhasil / Total) × 100%

## Jumlah Foto yang Dibutuhkan

### ✅ **Bisa 1 foto per NIM** (Minimal)
- Sistem akan tetap berjalan
- Setiap foto dihitung sebagai 1 test case
- Contoh: 5 NIM × 1 foto = 5 test cases

### ✅ **Lebih banyak foto = Lebih baik** (Rekomendasi)
- Lebih representatif untuk pengujian
- Bisa melihat konsistensi sistem
- Contoh: 5 NIM × 3 foto = 15 test cases

## Contoh Perhitungan

### Skenario 1: Minimal (1 foto per NIM)
```
test/new_photo/
├── 211240001155/foto1.jpg  ← 1 test case
├── 211240001156/foto1.jpg  ← 1 test case
├── 211240001157/foto1.jpg  ← 1 test case
├── 211240001158/foto1.jpg  ← 1 test case
└── 211240001159/foto1.jpg  ← 1 test case

Total: 5 test cases
```

### Skenario 2: Ideal (3 foto per NIM)
```
test/new_photo/
├── 211240001155/
│   ├── foto1.jpg  ← 1 test case
│   ├── foto2.jpg  ← 1 test case
│   └── foto3.jpg  ← 1 test case
├── 211240001156/
│   ├── foto1.jpg  ← 1 test case
│   ├── foto2.jpg  ← 1 test case
│   └── foto3.jpg  ← 1 test case
└── ...

Total: 15 test cases (5 NIM × 3 foto)
```

## Struktur Folder

Sistem mendukung 2 struktur:

### Struktur 1: Dengan NIM (Direkomendasikan)
```
test/new_photo/
├── 211240001155/
│   └── foto.jpg
└── 211240001156/
    └── foto.jpg
```
✅ **Keuntungan**: Sistem bisa validasi apakah foto dikenali dengan NIM yang benar

### Struktur 2: Tanpa NIM (Sederhana)
```
test/new_photo/
├── foto1.jpg
└── foto2.jpg
```
⚠️ **Catatan**: Sistem hanya cek apakah dikenali, tidak validasi NIM

## Rekomendasi Jumlah Foto

Berdasarkan tabel pengujian Anda:

| Kategori | Jumlah Uji | Rekomendasi Foto |
|----------|------------|------------------|
| Same-image test | 30 | Semua foto di dataset (otomatis) |
| Foto baru | 30 | 5-6 NIM × 5-6 foto |
| Pencahayaan terang | 20 | 5 NIM × 4 foto |
| Pencahayaan normal | 20 | 5 NIM × 4 foto |
| Pencahayaan redup | 20 | 5 NIM × 4 foto |

## Cara Mengatur Jumlah Foto

### Opsi 1: Salin dari Dataset (Cepat)
```bash
# Salin 1 foto per NIM
python face_recognition/prepare_test_data.py copy 1

# Salin 3 foto per NIM
python face_recognition/prepare_test_data.py copy 3

# Salin 5 foto per NIM
python face_recognition/prepare_test_data.py copy 5
```

### Opsi 2: Manual (Lebih Valid)
- Ambil foto yang berbeda dari dataset
- Masukkan ke folder sesuai kategori
- Pastikan foto sesuai kondisi (terang/normal/redup)

## Contoh Output Test

```
==================================================
📊 HASIL FOTO BARU (REAL-TEST)
==================================================
Total uji: 15          ← Jumlah foto yang diuji
Berhasil dikenali: 13  ← Foto yang berhasil dikenali
Tidak dikenali: 2      ← Foto yang gagal dikenali
Akurasi: 86.67%        ← (13/15) × 100%
==================================================
```

## Kesimpulan

- ✅ **Minimal**: 1 foto per NIM sudah cukup untuk test
- ✅ **Ideal**: 3-5 foto per NIM untuk hasil yang lebih representatif
- ✅ **Fleksibel**: Bisa menambah/mengurangi foto kapan saja
- ✅ **Otomatis**: Sistem akan menghitung semua foto yang ada

**Tips**: Mulai dengan 1-2 foto per NIM untuk testing cepat, lalu tambah jika perlu hasil yang lebih detail.

