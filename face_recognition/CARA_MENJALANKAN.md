# Cara Menjalankan Script Test Face Recognition

## 📋 Daftar Script yang Tersedia

1. **test_all_categories.py** - Script utama untuk menjalankan semua test
2. **test_dataset.py** - Script untuk test same-image saja
3. **prepare_test_data.py** - Script untuk setup dan mengelola data test

---

## 🚀 Cara Menjalankan

### 1. Test Semua Kategori (Rekomendasi)

**Jalankan semua test:**
```bash
python face_recognition/test_all_categories.py
```

**Atau dengan argumen:**
```bash
python face_recognition/test_all_categories.py all
```

**Output:** Akan menampilkan hasil semua 5 kategori test

---

### 2. Test Kategori Spesifik

**Test Same-Image:**
```bash
python face_recognition/test_all_categories.py same
# atau
python face_recognition/test_all_categories.py 1
```

**Test Foto Baru:**
```bash
python face_recognition/test_all_categories.py new
# atau
python face_recognition/test_all_categories.py 2
```

**Test Pencahayaan Terang:**
```bash
python face_recognition/test_all_categories.py bright
# atau
python face_recognition/test_all_categories.py 3
```

**Test Pencahayaan Normal:**
```bash
python face_recognition/test_all_categories.py normal
# atau
python face_recognition/test_all_categories.py 4
```

**Test Pencahayaan Redup:**
```bash
python face_recognition/test_all_categories.py dim
# atau
python face_recognition/test_all_categories.py 5
```

---

### 3. Test Same-Image (Script Lama)

**Jalankan test same-image saja:**
```bash
python face_recognition/test_dataset.py
```

---

### 4. Setup dan Kelola Data Test

**Cek status data test:**
```bash
python face_recognition/prepare_test_data.py
```

**Buat struktur folder:**
```bash
python face_recognition/prepare_test_data.py create
```

**Salin foto dari dataset (2 foto per NIM):**
```bash
python face_recognition/prepare_test_data.py copy
```

**Salin foto dari dataset (1 foto per NIM):**
```bash
python face_recognition/prepare_test_data.py copy 1
```

**Salin foto dari dataset (3 foto per NIM):**
```bash
python face_recognition/prepare_test_data.py copy 3
```

---

## 📝 Contoh Output

### Output Test Semua Kategori:
```
============================================================
🧪 SISTEM PENGUJIAN FACE RECOGNITION
============================================================

==================================================
🔍 SAME-IMAGE TEST
==================================================
📁 Test folder: E:\...\storage\app\public\dataset

==================================================
📊 HASIL SAME-IMAGE TEST
==================================================
Total uji: 49
Berhasil dikenali: 49
Tidak dikenali: 0
Akurasi: 100.0%
==================================================

... (kategori lain)

============================================================
📋 RINGKASAN HASIL SEMUA TEST
============================================================
No    Kategori Uji                   Jumlah Uji   Berhasil     Gagal        Akurasi
------------------------------------------------------------
1     Same-image test                49           49           0            100.0      %
2     Foto baru (real-test)          10           9            1            90.0       %
3     Pencahayaan terang             10           9            1            90.0       %
4     Pencahayaan normal             10           9            1            90.0       %
5     Pencahayaan redup              10           8            2            80.0       %
============================================================
```

---

## ⚠️ Troubleshooting

### Error: "Model tidak ditemukan"
**Solusi:** Pastikan sudah menjalankan training terlebih dahulu
```bash
python face_recognition/training.py
```

### Error: "Folder tidak ditemukan"
**Solusi:** Buat struktur folder terlebih dahulu
```bash
python face_recognition/prepare_test_data.py create
```

### Error: "Tidak ada foto di folder test"
**Solusi:** Salin foto dari dataset atau masukkan foto manual
```bash
python face_recognition/prepare_test_data.py copy
```

---

## 🎯 Workflow Lengkap

### Langkah 1: Setup (Hanya sekali)
```bash
# Buat struktur folder
python face_recognition/prepare_test_data.py create

# Salin foto dari dataset (opsional)
python face_recognition/prepare_test_data.py copy
```

### Langkah 2: Jalankan Test
```bash
# Test semua kategori
python face_recognition/test_all_categories.py
```

### Langkah 3: Cek Hasil
- Lihat output di terminal
- Atau cek file hasil (jika ada)

---

## 💡 Tips

1. **Jalankan dari root project:**
   ```bash
   cd E:\MUHAMMD EDIEFANTO\MATERI KULIAH\SKRIPSI\PROGRAM\backend_absensi_app
   python face_recognition/test_all_categories.py
   ```

2. **Gunakan PowerShell atau Command Prompt:**
   - Buka PowerShell/CMD di folder project
   - Jalankan command

3. **Pastikan Python sudah terinstall:**
   ```bash
   python --version
   ```

4. **Aktifkan virtual environment (jika ada):**
   ```bash
   venv\Scripts\activate  # Windows
   ```

---

## 📞 Quick Reference

| Perintah | Fungsi |
|----------|--------|
| `python face_recognition/test_all_categories.py` | Test semua kategori |
| `python face_recognition/test_all_categories.py new` | Test foto baru saja |
| `python face_recognition/prepare_test_data.py` | Cek status data |
| `python face_recognition/prepare_test_data.py copy` | Salin foto dari dataset |

