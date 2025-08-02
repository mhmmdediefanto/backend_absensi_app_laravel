import cv2
import os
import numpy as np
import json

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATASET_PATH = os.path.join(BASE_DIR, "../storage/app/public/dataset")
MODEL_PATH = os.path.join(BASE_DIR, "trainer/lbph_model.yml")
LABELS_PATH = os.path.join(BASE_DIR, "trainer/labels.json")

def get_images_and_labels(dataset_path):
    faces = []
    ids = []
    label_mapping = {}  # Mapping folder name ke label ID

    print(f"🔍 Mengecek dataset di: {dataset_path}")

    for folder in os.listdir(dataset_path):
        folder_path = os.path.join(dataset_path, folder)

        if not os.path.isdir(folder_path):
            continue

        try:
            label_id = int(folder)
        except:
            print(f"⚠️ Folder {folder} bukan angka, dilewati.")
            continue

        print(f"\n📂 Membaca folder ID: {label_id}")

        for file in os.listdir(folder_path):
            img_path = os.path.join(folder_path, file)

            img = cv2.imread(img_path, cv2.IMREAD_GRAYSCALE)

            if img is None:
                print(f"❌ Gagal membaca {img_path}")
                continue
            else:
                print(f"✅ Foto terbaca: {img_path}")

            faces.append(img)
            ids.append(label_id)
            label_mapping[label_id] = folder  # Simpan mapping

    return faces, np.array(ids), label_mapping

def train_lbph():
    print("🚀 Mulai training LBPH...\n")

    faces, ids, label_mapping = get_images_and_labels(DATASET_PATH)

    print(f"\n📊 Total wajah ditemukan: {len(faces)}")
    print(f"📊 Total ID unik: {len(set(ids))}")
    print(f"📊 Label mapping: {label_mapping}")

    if len(faces) == 0:
        print("❌ Tidak ada data wajah valid. Training dibatalkan.")
        return

    recognizer = cv2.face.LBPHFaceRecognizer_create()
    recognizer.train(faces, ids)

    # Dapatkan label internal yang digunakan OpenCV
    internal_labels = recognizer.getLabels()
    print(f"📊 Internal labels dari OpenCV: {internal_labels}")

    # Buat mapping yang benar antara internal label dan folder name
    correct_mapping = {}
    unique_ids = sorted(list(set(ids)))  # Urutkan ID untuk konsistensi
    
    print(f"📊 Unique IDs: {unique_ids}")
    
    # Simpan semua folder yang ada dalam dataset
    all_folders = {}
    for folder_id, folder_name in label_mapping.items():
        all_folders[str(folder_id)] = {
            "folder_id": str(folder_id),
            "folder_name": folder_name
        }
    
    # Simpan mapping internal label (jika ada yang berbeda)
    internal_unique = list(set([label[0] for label in internal_labels]))
    print(f"📊 Internal unique labels: {internal_unique}")
    
    for i, internal_label in enumerate(internal_unique):
        if i < len(unique_ids):
            folder_id = unique_ids[i]
            folder_name = label_mapping[folder_id]
            correct_mapping[str(internal_label)] = {
                "folder_id": str(folder_id),
                "folder_name": folder_name
            }
            print(f"📊 Mapping: Internal {internal_label} -> Folder {folder_id} ({folder_name})")

    # Gabungkan semua informasi
    final_mapping = {
        "internal_labels": correct_mapping,
        "all_folders": all_folders,
        "training_info": {
            "total_faces": len(faces),
            "unique_ids": [str(id) for id in unique_ids],
            "internal_labels": [str(label[0]) for label in internal_labels]
        }
    }

    os.makedirs(os.path.dirname(MODEL_PATH), exist_ok=True)
    recognizer.save(MODEL_PATH)

    # Simpan mapping yang benar ke file JSON
    with open(LABELS_PATH, 'w') as f:
        json.dump(final_mapping, f, indent=2)

    print(f"\n✅ Training selesai! Model disimpan di {MODEL_PATH}")
    print(f"✅ Label mapping disimpan di {LABELS_PATH}")
    print(f"✅ Final mapping: {final_mapping}")

if __name__ == "__main__":
    train_lbph()
