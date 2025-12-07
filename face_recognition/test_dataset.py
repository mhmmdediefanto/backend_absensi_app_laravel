import os
from recognize import FaceRecognizer

# Path dataset sesuai struktur kamu
# Path dataset sesuai struktur kamu
DATASET_FOLDER = os.path.join(os.path.dirname(__file__), "storage/dataset")
def test_same_image():
    recognizer = FaceRecognizer()
    recognizer.load_model()

    total_test = 0
    success = 0
    failed = 0

    # ==========================================================
    # PERBAIKAN 1: Tambahkan ../ untuk naik satu folder
    # ==========================================================
    global DATASET_FOLDER
    DATASET_FOLDER = os.path.join(os.path.dirname(__file__), "..", "storage", "app", "public", "dataset")    # ==========================================================

    print("📁 Dataset folder:", os.path.abspath(DATASET_FOLDER))
    print("🔍 Mulai scanning...\n")

    # Pastikan folder ada sebelum di-walk
    if not os.path.exists(DATASET_FOLDER):
        print(f"❌ ERROR: Folder tidak ditemukan di path: {os.path.abspath(DATASET_FOLDER)}")
        print("Pastikan path sudah benar sesuai struktur proyek.")
        # Hentikan eksekusi lebih awal jika folder tidak ada
        return 

    for root, dirs, files in os.walk(DATASET_FOLDER):
        for file in files:
            if file.lower().endswith(".jpg") or file.lower().endswith(".png"):
                img_path = os.path.join(root, file)
                
                # Tambahkan print ini untuk debugging (opsional)
                # print(f"Menguji gambar: {img_path}") 
                
                result = recognizer.recognize_face_from_path(img_path)
                total_test += 1

                if result['success'] and result['data'] and result['data'][0]['status'] == "recognized":
                    success += 1
                else:
                    failed += 1

    print("===== SAME-IMAGE TEST =====")
    print("Total uji:", total_test)
    print("Berhasil:", success)
    print("Gagal:", failed)

    # ==========================================================
    # PERBAIKAN 2: Tambahkan cek "if total_test > 0"
    # ==========================================================
    if total_test > 0:
        akurasi = round((success / total_test) * 100, 2)
        print("Akurasi:", akurasi, "%")
    else:
        print("Akurasi: 0.0 % (Tidak ada gambar yang diuji)")
    # ==========================================================
if __name__ == "__main__":
    test_same_image()
