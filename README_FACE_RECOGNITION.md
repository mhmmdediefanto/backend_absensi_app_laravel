# Face Recognition Setup & Troubleshooting

## Error yang Terjadi
```
ModuleNotFoundError: No module named 'cv2'
```

## Penyebab Error
1. Laravel menggunakan Python system default (bukan virtual environment)
2. OpenCV tidak terinstall di Python system default
3. Virtual environment ada di folder `venv/` tapi tidak digunakan

## Solusi yang Diterapkan

### 1. Install Dependencies di Virtual Environment
```bash
# Aktifkan virtual environment
.\venv\Scripts\Activate.ps1

# Install dependencies
pip install -r requirements.txt
```

### 2. Update Requirements.txt
File `requirements.txt` sudah diupdate dengan package yang benar:
```
opencv-contrib-python>=4.8.0
numpy>=1.24.0
```

**Catatan:** Gunakan `opencv-contrib-python` bukan `opencv-python` karena modul face recognition hanya tersedia di contrib version.

### 3. Update Laravel Controller
File `app/Http/Controllers/FaceRecognitionController.php` sudah diupdate untuk menggunakan Python dari virtual environment:

```php
// Sebelum
$command = "python \"$pythonScript\" \"$imagePath\" 2>&1";

// Sesudah
$venvPython = base_path('venv/Scripts/python.exe');
$command = "\"$venvPython\" \"$pythonScript\" \"$imagePath\" 2>&1";
```

## Testing

### Test Python Script Langsung
```bash
# Test dengan file gambar
.\venv\Scripts\python.exe face_recognition\recognize.py "public\uploads\foto_siswa\1746713628_WIN_20231117_02_54_03_Pro.jpg"
```

### Test via Laravel API
Setelah perubahan, face recognition API Laravel seharusnya sudah berfungsi normal.

## Troubleshooting Lainnya

### Jika Error "module 'cv2' has no attribute 'face'"
- Pastikan menggunakan `opencv-contrib-python` bukan `opencv-python`
- Uninstall opencv-python dan install opencv-contrib-python

### Jika Virtual Environment Tidak Aktif
- Pastikan menggunakan path lengkap ke Python di virtual environment
- Path: `venv/Scripts/python.exe` (Windows)

### Jika Dependencies Tidak Terinstall
- Aktifkan virtual environment terlebih dahulu
- Jalankan `pip install -r requirements.txt`

## Struktur File Penting
```
face_recognition/
├── recognize.py          # Script utama face recognition
├── trainer/
│   ├── lbph_model.yml   # Model yang sudah ditraining
│   └── labels.json      # Mapping label
└── requirements.txt     # Dependencies Python
```

## Catatan Penting
- Selalu gunakan virtual environment untuk isolasi dependencies
- Pastikan model training sudah dijalankan sebelum menggunakan face recognition
- File gambar temporary akan otomatis dihapus setelah proses selesai 