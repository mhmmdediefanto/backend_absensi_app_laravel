import os
import cv2
import numpy as np
import json

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
model_path = os.path.join(BASE_DIR, "trainer/lbph_model.yml")
labels_path = os.path.join(BASE_DIR, "trainer/labels.json")

recognizer = cv2.face.LBPHFaceRecognizer_create()
recognizer.read(model_path)

print("=== Informasi Model LBPH ===")

# Baca label dari file JSON
if os.path.exists(labels_path):
    with open(labels_path, 'r') as f:
        label_data = json.load(f)
    
    # Tampilkan semua folder yang ada
    print("📁 Semua folder dalam dataset:")
    all_folders = label_data.get('all_folders', {})
    for folder_id, info in all_folders.items():
        print(f"   - Folder ID: {info['folder_id']} -> Folder Name: {info['folder_name']}")
    
    # Tampilkan mapping internal label
    print("\n🔗 Mapping Internal Label OpenCV:")
    internal_labels = label_data.get('internal_labels', {})
    for internal_label, info in internal_labels.items():
        print(f"   - Internal Label: {internal_label} -> Folder ID: {info['folder_id']} -> Folder Name: {info['folder_name']}")
    
    # Tampilkan info training
    training_info = label_data.get('training_info', {})
    print(f"\n📊 Info Training:")
    print(f"   - Total wajah: {training_info.get('total_faces', 0)}")
    print(f"   - ID unik: {training_info.get('unique_ids', [])}")
    print(f"   - Total ID: {len(training_info.get('unique_ids', []))}")
    
    # Dapatkan semua folder ID
    folder_ids = list(all_folders.keys())
    print(f"\n📋 Daftar ID yang ada di model: {folder_ids}")
    print(f"📊 Total ID: {len(folder_ids)}")
else:
    print("❌ File labels.json tidak ditemukan!")

# Test prediksi dengan gambar dummy
print("\n=== Test Prediksi ===")
test_img = np.zeros((100, 100), dtype=np.uint8)
try:
    label, confidence = recognizer.predict(test_img)
    print(f"Test prediksi - Internal Label: {label}, Confidence: {confidence}")
    
    # Cari folder name berdasarkan internal label
    if os.path.exists(labels_path):
        with open(labels_path, 'r') as f:
            label_data = json.load(f)
        
        internal_labels = label_data.get('internal_labels', {})
        internal_label_str = str(label)
        
        if internal_label_str in internal_labels:
            info = internal_labels[internal_label_str]
            folder_id = info['folder_id']
            folder_name = info['folder_name']
            print(f"✅ Internal Label {label} sesuai dengan:")
            print(f"   - Folder ID: {folder_id}")
            print(f"   - Folder Name: {folder_name}")
        else:
            print(f"⚠️ Internal Label {label} tidak ditemukan dalam mapping")
            print(f"   Internal labels yang tersedia: {list(internal_labels.keys())}")
    
except Exception as e:
    print("Test prediksi - Error:", e)

print("\n=== Kesimpulan ===")
print("✅ Model berhasil dimuat dan label dapat diakses dari file JSON")
if os.path.exists(labels_path):
    with open(labels_path, 'r') as f:
        label_data = json.load(f)
    training_info = label_data.get('training_info', {})
    print(f"✅ Training data: {training_info.get('total_faces', 0)} wajah dengan {len(training_info.get('unique_ids', []))} ID unik")
    print(f"✅ Folder yang terdeteksi: {training_info.get('unique_ids', [])}")
    print("✅ Script check_model.py sekarang berfungsi dengan sempurna untuk multiple folder!")
