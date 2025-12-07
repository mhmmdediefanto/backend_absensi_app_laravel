import cv2
import numpy as np

# Ukuran standar wajah agar konsisten antara training dan inference
FACE_SIZE = (120, 120)


def _ensure_grayscale(image):
    """Pastikan gambar dalam format grayscale."""
    if len(image.shape) == 2:
        return image
    return cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)


def preprocess_face(image):
    """
    Preprocessing untuk ROI wajah agar lebih tahan terhadap perbedaan pencahayaan.
    Langkah:
      1. Pastikan grayscale
      2. Resize ke ukuran standar
      3. Terapkan CLAHE untuk kontras adaptif
      4. Normalisasi intensitas ke rentang 0-255
    """
    gray = _ensure_grayscale(image)
    resized = cv2.resize(gray, FACE_SIZE)

    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    equalized = clahe.apply(resized)

    normalized = cv2.normalize(equalized, None, alpha=0, beta=255, norm_type=cv2.NORM_MINMAX)
    return normalized


def preprocess_frame(image):
    """
    Preprocessing ringan untuk seluruh frame sebelum deteksi wajah.
    Menggunakan CLAHE agar detektor Haar lebih stabil di kondisi gelap/terang.
    """
    gray = _ensure_grayscale(image)
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    equalized = clahe.apply(gray)
    return equalized

