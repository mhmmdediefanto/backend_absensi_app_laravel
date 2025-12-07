"""
Script untuk membantu menyiapkan data test
Script ini akan membuat struktur folder dan memberikan panduan
"""
import os
import shutil

BASE_STORAGE_PATH = os.path.join(os.path.dirname(__file__), "..", "storage", "app", "public")
DATASET_FOLDER = os.path.join(BASE_STORAGE_PATH, "dataset")
TEST_BASE = os.path.join(BASE_STORAGE_PATH, "test")

def create_test_structure_with_nim():
    """Buat struktur folder test dengan struktur berdasarkan NIM (seperti dataset)"""
    print("🔧 Membuat struktur folder test berdasarkan NIM...\n")
    
    # Ambil daftar NIM dari dataset
    nims = []
    if os.path.exists(DATASET_FOLDER):
        for item in os.listdir(DATASET_FOLDER):
            item_path = os.path.join(DATASET_FOLDER, item)
            if os.path.isdir(item_path) and item.isdigit():
                nims.append(item)
    
    if not nims:
        print("❌ Tidak ada folder NIM di dataset. Pastikan dataset sudah ada.")
        return
    
    print(f"📋 Ditemukan {len(nims)} NIM di dataset: {', '.join(nims[:5])}{'...' if len(nims) > 5 else ''}\n")
    
    # Kategori test
    categories = {
        "new_photo": "Foto Baru (Real-Test)",
        "bright_lighting": "Pencahayaan Terang",
        "normal_lighting": "Pencahayaan Normal",
        "dim_lighting": "Pencahayaan Redup"
    }
    
    # Buat struktur untuk setiap kategori
    for category, description in categories.items():
        category_path = os.path.join(TEST_BASE, category)
        print(f"📁 Membuat struktur untuk: {description}")
        
        for nim in nims:
            nim_folder = os.path.join(category_path, nim)
            os.makedirs(nim_folder, exist_ok=True)
        
        print(f"   ✅ Dibuat {len(nims)} folder NIM di {category}/")
    
    print("\n" + "="*60)
    print("📝 PANDUAN PENGISIAN DATA TEST")
    print("="*60)
    print("\n1. Struktur folder yang sudah dibuat:")
    print("   storage/app/public/test/")
    print("   ├── new_photo/")
    print("   │   ├── 211240001155/  ← Masukkan foto baru siswa ini")
    print("   │   ├── 211240001156/  ← Masukkan foto baru siswa ini")
    print("   │   └── ...")
    print("   ├── bright_lighting/")
    print("   │   ├── 211240001155/  ← Masukkan foto dengan pencahayaan terang")
    print("   │   └── ...")
    print("   ├── normal_lighting/")
    print("   │   ├── 211240001155/  ← Masukkan foto dengan pencahayaan normal")
    print("   │   └── ...")
    print("   └── dim_lighting/")
    print("       ├── 211240001155/  ← Masukkan foto dengan pencahayaan redup")
    print("       └── ...")
    
    print("\n2. Cara mengisi:")
    print("   - Ambil foto siswa dengan kondisi yang sesuai")
    print("   - Masukkan ke folder NIM yang sesuai")
    print("   - Contoh: foto siswa 211240001155 dengan pencahayaan terang")
    print("     → Masukkan ke: test/bright_lighting/211240001155/")
    
    print("\n3. Format file yang didukung:")
    print("   - .jpg, .jpeg, .png")
    
    print("\n4. Setelah data diisi, jalankan test:")
    print("   python face_recognition/test_all_categories.py")
    
    print("\n" + "="*60)

def copy_dataset_to_test(num_files_per_nim=2, categories=None):
    """
    Salin foto dari dataset ke folder test
    
    Args:
        num_files_per_nim: Jumlah foto yang akan disalin per NIM per kategori (default: 2)
        categories: List kategori yang akan diisi. Jika None, akan mengisi semua kategori
    """
    print("\n📋 Menyalin foto dari dataset ke folder test...\n")
    
    # Kategori test
    all_categories = {
        "new_photo": "Foto Baru",
        "bright_lighting": "Pencahayaan Terang",
        "normal_lighting": "Pencahayaan Normal",
        "dim_lighting": "Pencahayaan Redup"
    }
    
    if categories is None:
        categories = list(all_categories.keys())
    
    # Ambil daftar NIM dari dataset
    nims = []
    if os.path.exists(DATASET_FOLDER):
        for item in os.listdir(DATASET_FOLDER):
            item_path = os.path.join(DATASET_FOLDER, item)
            if os.path.isdir(item_path) and item.isdigit():
                nims.append(item)
    
    if not nims:
        print("❌ Tidak ada folder NIM di dataset.")
        return
    
    total_copied = 0
    
    for category in categories:
        if category not in all_categories:
            print(f"⚠️  Kategori '{category}' tidak dikenal, dilewati")
            continue
        
        category_path = os.path.join(TEST_BASE, category)
        category_name = all_categories[category]
        
        print(f"📁 {category_name}:")
        
        for nim in nims:
            source_folder = os.path.join(DATASET_FOLDER, nim)
            target_folder = os.path.join(category_path, nim)
            
            if not os.path.exists(source_folder):
                continue
            
            # Buat folder target jika belum ada
            os.makedirs(target_folder, exist_ok=True)
            
            # Ambil file gambar dari source
            image_files = []
            for file in os.listdir(source_folder):
                if file.lower().endswith((".jpg", ".jpeg", ".png")):
                    image_files.append(file)
            
            # Salin beberapa file (maksimal num_files_per_nim)
            copied = 0
            for file in image_files[:num_files_per_nim]:
                source_path = os.path.join(source_folder, file)
                target_path = os.path.join(target_folder, file)
                
                # Hanya salin jika belum ada
                if not os.path.exists(target_path):
                    shutil.copy2(source_path, target_path)
                    copied += 1
                    total_copied += 1
            
            if copied > 0:
                print(f"   ✅ {nim}: {copied} foto disalin")
        
        print()
    
    print("="*60)
    print(f"✅ Total {total_copied} foto berhasil disalin")
    print("="*60)
    print("\n⚠️  CATATAN:")
    print("   - Foto yang disalin adalah foto dari dataset training")
    print("   - Untuk pengujian yang valid, sebaiknya gunakan foto yang BERBEDA dari dataset")
    print("   - Ini hanya untuk testing struktur sistem")
    print()

def show_test_status():
    """Tampilkan status pengisian data test"""
    print("\n📊 STATUS PENGISIAN DATA TEST")
    print("="*60)
    
    categories = {
        "new_photo": "Foto Baru",
        "bright_lighting": "Pencahayaan Terang",
        "normal_lighting": "Pencahayaan Normal",
        "dim_lighting": "Pencahayaan Redup"
    }
    
    total_files = 0
    
    for category, name in categories.items():
        category_path = os.path.join(TEST_BASE, category)
        count = 0
        
        if os.path.exists(category_path):
            for root, dirs, files in os.walk(category_path):
                for file in files:
                    if file.lower().endswith((".jpg", ".jpeg", ".png")):
                        count += 1
            total_files += count
        
        status = "✅" if count > 0 else "❌"
        print(f"{status} {name:25} : {count:3} foto")
    
    print("-"*60)
    print(f"Total foto test: {total_files}")
    print("="*60)
    
    if total_files == 0:
        print("\n💡 Tips:")
        print("   - Jalankan 'python prepare_test_data.py create' untuk membuat struktur folder")
        print("   - Jalankan 'python prepare_test_data.py copy' untuk menyalin foto dari dataset")

if __name__ == "__main__":
    import sys
    
    if len(sys.argv) > 1:
        command = sys.argv[1]
        
        if command == "create":
            create_test_structure_with_nim()
        elif command == "copy":
            # Ambil jumlah file per NIM dari argumen (default: 2)
            num_files = 2
            if len(sys.argv) > 2:
                try:
                    num_files = int(sys.argv[2])
                except ValueError:
                    print("⚠️  Jumlah file tidak valid, menggunakan default: 2")
            
            # Cek apakah struktur sudah ada
            if not os.path.exists(TEST_BASE):
                print("❌ Struktur folder test belum ada!")
                print("💡 Jalankan dulu: python prepare_test_data.py create")
            else:
                copy_dataset_to_test(num_files_per_nim=num_files)
        else:
            print("❌ Perintah tidak dikenal!")
            print("\n💡 Perintah yang tersedia:")
            print("   python prepare_test_data.py create  - Buat struktur folder")
            print("   python prepare_test_data.py copy   - Salin foto dari dataset (2 foto per NIM)")
            print("   python prepare_test_data.py copy 3  - Salin foto dari dataset (3 foto per NIM)")
    else:
        show_test_status()
        print("\n💡 Perintah yang tersedia:")
        print("   python prepare_test_data.py create  - Buat struktur folder")
        print("   python prepare_test_data.py copy    - Salin foto dari dataset ke folder test")

