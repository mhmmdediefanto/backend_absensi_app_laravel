import cv2
import os
import sys
from datetime import datetime

# Pastikan argumen lengkap
if len(sys.argv) < 3:
    print('{"success": false, "message": "Argumen tidak lengkap"}')
    exit()

image_path = sys.argv[1]   # path file dari Laravel
folder_id = sys.argv[2]    # NIS siswa

# Baca gambar asli (BGR)
img = cv2.imread(image_path)
if img is None:
    print('{"success": false, "message": "Gagal membaca gambar"}')
    exit()

# Deteksi wajah pakai grayscale (lebih akurat untuk Haar Cascade)
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
faces = face_cascade.detectMultiScale(gray, scaleFactor=1.3, minNeighbors=5)

# Buat path dataset sesuai folder_id
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
dataset_dir = os.path.join(BASE_DIR, "storage", "app", "public", "dataset", folder_id)

print(f"Dataset dir: {dataset_dir}")
os.makedirs(dataset_dir, exist_ok=True)

saved_files = []

if len(faces) > 0:
    for (x, y, w, h) in faces:
        # ✅ Crop dari gambar asli (BGR), bukan dari gray
        face = img[y:y+h, x:x+w]
        face_resized = cv2.resize(face, (100, 100))  # Resize ke 100x100

        filename = datetime.now().strftime("%Y%m%d_%H%M%S_%f") + ".jpg"
        file_path = os.path.join(dataset_dir, filename)

        cv2.imwrite(file_path, face_resized)  # ✅ Simpan dalam warna (BGR)
        saved_files.append(filename)

    print('{"success": true, "message": "Wajah berhasil disimpan", "saved_files": ' + str(saved_files) + '}')
else:
    print('{"success": false, "message": "Tidak ada wajah terdeteksi"}')
