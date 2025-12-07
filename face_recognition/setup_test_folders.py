"""
Script untuk setup struktur folder test
Jalankan script ini untuk membuat folder-folder test yang diperlukan
"""
import os

BASE_STORAGE_PATH = os.path.join(os.path.dirname(__file__), "..", "storage", "app", "public")
TEST_BASE = os.path.join(BASE_STORAGE_PATH, "test")

# Daftar folder test yang perlu dibuat
TEST_FOLDERS = [
    "new_photo",           # Test foto baru
    "bright_lighting",     # Test pencahayaan terang
    "normal_lighting",     # Test pencahayaan normal
    "dim_lighting"         # Test pencahayaan redup
]

def create_test_structure():
    """Buat struktur folder test"""
    print("🔧 Membuat struktur folder test...\n")
    
    # Buat folder base test
    os.makedirs(TEST_BASE, exist_ok=True)
    print(f"✅ Folder base: {os.path.abspath(TEST_BASE)}")
    
    # Buat setiap folder kategori test
    for folder in TEST_FOLDERS:
        folder_path = os.path.join(TEST_BASE, folder)
        os.makedirs(folder_path, exist_ok=True)
        print(f"✅ Folder: {folder}")
    
    print("\n📋 Struktur folder test:")
    print(f"   {TEST_BASE}/")
    for folder in TEST_FOLDERS:
        print(f"   ├── {folder}/")
        print(f"   │   └── (Masukkan foto test di sini)")
    
    print("\n💡 Cara menggunakan:")
    print("   1. Masukkan foto test ke folder yang sesuai:")
    print("      - new_photo/ : Foto baru yang berbeda dari dataset training")
    print("      - bright_lighting/ : Foto dengan pencahayaan terang")
    print("      - normal_lighting/ : Foto dengan pencahayaan normal")
    print("      - dim_lighting/ : Foto dengan pencahayaan redup")
    print("\n   2. Struktur folder bisa mengikuti struktur dataset:")
    print("      test/new_photo/211240001155/ (foto1.jpg, foto2.jpg, ...)")
    print("      atau langsung di folder:")
    print("      test/new_photo/ (foto1.jpg, foto2.jpg, ...)")
    print("\n   3. Jalankan test dengan:")
    print("      python test_all_categories.py all")
    print("      atau test spesifik:")
    print("      python test_all_categories.py new")

if __name__ == "__main__":
    create_test_structure()

