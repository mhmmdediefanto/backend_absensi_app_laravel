# Dokumentasi Sistem Pengujian Face Recognition

## Kategori Pengujian

Sistem pengujian terdiri dari 5 kategori:

1. **Same-image test** - Test dengan gambar yang sama persis dari dataset training
2. **Foto baru (real-test)** - Test dengan foto baru yang berbeda dari dataset training
3. **Pencahayaan terang** - Test dengan foto yang diambil dalam kondisi pencahayaan terang
4. **Pencahayaan normal** - Test dengan foto yang diambil dalam kondisi pencahayaan normal
5. **Pencahayaan redup** - Test dengan foto yang diambil dalam kondisi pencahayaan redup

## Struktur Folder

```
storage/app/public/
├── dataset/              # Dataset training (sudah ada)
│   ├── 211240001155/
│   ├── 211240001156/
│   └── ...
└── test/                 # Folder untuk test (akan dibuat)
    ├── new_photo/        # Foto baru untuk test
    ├── bright_lighting/  # Foto dengan pencahayaan terang
    ├── normal_lighting/ # Foto dengan pencahayaan normal
    └── dim_lighting/    # Foto dengan pencahayaan redup
```

## Setup

1. **Buat struktur folder test (dengan struktur NIM):**
   ```bash
   python face_recognition/prepare_test_data.py create
   ```
   
   Script ini akan membuat struktur folder berdasarkan NIM yang ada di dataset:
   ```
   test/new_photo/
   ├── 211240001155/  ← Masukkan foto baru siswa ini
   ├── 211240001156/  ← Masukkan foto baru siswa ini
   └── ...
   
   test/bright_lighting/
   ├── 211240001155/  ← Masukkan foto dengan pencahayaan terang
   └── ...
   
   test/normal_lighting/
   ├── 211240001155/  ← Masukkan foto dengan pencahayaan normal
   └── ...
   
   test/dim_lighting/
   ├── 211240001155/  ← Masukkan foto dengan pencahayaan redup
   └── ...
   ```

2. **Cek status pengisian data:**
   ```bash
   python face_recognition/prepare_test_data.py
   ```
   
   Akan menampilkan jumlah foto yang sudah ada di setiap kategori.

3. **Siapkan foto test:**
   - Ambil foto siswa dengan kondisi yang sesuai untuk setiap kategori
   - Masukkan foto ke folder NIM yang sesuai
   - Contoh: 
     - Foto siswa 211240001155 dengan pencahayaan terang → `test/bright_lighting/211240001155/foto.jpg`
     - Foto baru siswa 211240001156 → `test/new_photo/211240001156/foto.jpg`
   
   **Catatan:** 
   - Foto harus berbeda dari foto di dataset training
   - Untuk pencahayaan terang/normal/redup, gunakan foto yang diambil dalam kondisi pencahayaan yang sesuai
   - Format yang didukung: .jpg, .jpeg, .png

## Cara Menjalankan Test

### 1. Test Semua Kategori
```bash
python face_recognition/test_all_categories.py
```
atau
```bash
python face_recognition/test_all_categories.py all
```

### 2. Test Kategori Spesifik

**Same-image test:**
```bash
python face_recognition/test_all_categories.py same
# atau
python face_recognition/test_all_categories.py 1
```

**Foto baru:**
```bash
python face_recognition/test_all_categories.py new
# atau
python face_recognition/test_all_categories.py 2
```

**Pencahayaan terang:**
```bash
python face_recognition/test_all_categories.py bright
# atau
python face_recognition/test_all_categories.py 3
```

**Pencahayaan normal:**
```bash
python face_recognition/test_all_categories.py normal
# atau
python face_recognition/test_all_categories.py 4
```

**Pencahayaan redup:**
```bash
python face_recognition/test_all_categories.py dim
# atau
python face_recognition/test_all_categories.py 5
```

### 3. Test Same-Image (File Lama)
```bash
python face_recognition/test_dataset.py
```

## Output

Setiap test akan menampilkan:
- Total uji
- Berhasil dikenali
- Tidak dikenali
- Akurasi (%)

Contoh output:
```
==================================================
🔍 FOTO BARU (REAL-TEST)
==================================================
📁 Test folder: E:\...\storage\app\public\test\new_photo

==================================================
📊 HASIL FOTO BARU (REAL-TEST)
==================================================
Total uji: 30
Berhasil dikenali: 27
Tidak dikenali: 3
Akurasi: 90.0%
==================================================
```

## Catatan

- Pastikan model sudah di-train sebelum menjalankan test
- Foto test harus berisi wajah yang jelas
- Format foto yang didukung: .jpg, .jpeg, .png
- Jika folder test kosong, test akan menampilkan error tapi tidak akan crash

