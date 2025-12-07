import os
import argparse
from recognize import FaceRecognizer
from visualization import visualize_recognition

# Base path untuk storage
BASE_STORAGE_PATH = os.path.join(os.path.dirname(__file__), "..", "storage", "app", "public")
DEFAULT_VISUAL_DIR = os.path.join(BASE_STORAGE_PATH, "test_results")
DATASET_FOLDER = os.path.join(BASE_STORAGE_PATH, "dataset")

def _slugify(text):
    return text.lower().replace(" ", "_")


def _build_visual_path(category_name, test_folder, current_root, file_name, visualize_root):
    slug_category = _slugify(category_name)
    rel_folder = os.path.relpath(current_root, test_folder)
    rel_folder = "" if rel_folder == "." else rel_folder
    output_dir = os.path.join(visualize_root, slug_category, rel_folder)
    base_name, ext = os.path.splitext(file_name)
    return os.path.join(output_dir, f"{base_name}_viz{ext}")


def run_test_category(
    recognizer,
    test_folder,
    category_name,
    expected_folder_id=None,
    validate_folder_id=False,
    visualize=False,
    visualize_root=DEFAULT_VISUAL_DIR,
):
    """
    Fungsi umum untuk menjalankan test kategori
    
    Args:
        recognizer: FaceRecognizer instance
        test_folder: Path folder yang berisi gambar test
        category_name: Nama kategori test
        expected_folder_id: ID folder yang diharapkan (untuk validasi). 
                           Jika None dan validate_folder_id=True, akan menggunakan nama folder sebagai expected
        validate_folder_id: Jika True, akan memvalidasi folder_id. Jika False, hanya cek apakah recognized
    """
    total_test = 0
    success = 0
    failed = 0
    confidence_scores = []  # Kumpulkan confidence scores untuk analisis
    
    print(f"\n{'='*50}")
    print(f"🔍 {category_name.upper()}")
    print(f"{'='*50}")
    print(f"📁 Test folder: {os.path.abspath(test_folder)}\n")
    
    # Pastikan folder ada
    if not os.path.exists(test_folder):
        print(f"❌ ERROR: Folder tidak ditemukan di path: {os.path.abspath(test_folder)}")
        print("Pastikan path sudah benar sesuai struktur proyek.")
        return {
            'total': 0,
            'success': 0,
            'failed': 0,
            'akurasi': 0.0
        }
    
    # Walk melalui folder test
    for root, dirs, files in os.walk(test_folder):
        for file in files:
            if file.lower().endswith((".jpg", ".jpeg", ".png")):
                img_path = os.path.join(root, file)
                
                # Tentukan expected_folder_id jika perlu validasi
                current_expected = None
                if validate_folder_id:
                    if expected_folder_id is not None:
                        current_expected = expected_folder_id
                    else:
                        # Ambil nama folder dari path (folder terakhir sebelum file)
                        folder_name = os.path.basename(root)
                        # Jika folder_name adalah angka (NIM), gunakan sebagai expected
                        if folder_name.isdigit():
                            current_expected = folder_name
                
                result = recognizer.recognize_face_from_path(img_path)
                total_test += 1
                
                if result['success'] and result['data']:
                    # Cek apakah recognized
                    recognition_result = result['data'][0]
                    is_recognized = recognition_result['status'] == "recognized"
                    confidence = recognition_result.get('confidence', 0)
                    confidence_scores.append(confidence)  # Simpan confidence score
                    
                    # Jika perlu validasi folder_id
                    if validate_folder_id and current_expected:
                        folder_id_match = recognition_result.get('folder_id') == current_expected
                        if is_recognized and folder_id_match:
                            success += 1
                            # Tampilkan detail jika confidence tinggi (lebih sulit dikenali)
                            if confidence > 60:
                                print(f"⚠️  {file}: Dikenali (confidence: {confidence:.2f} - agak sulit)")
                        else:
                            failed += 1
                            if not is_recognized:
                                # Tampilkan detail kenapa tidak dikenali
                                status = recognition_result.get('status', 'unknown')
                                got_folder_id = recognition_result.get('folder_id', 'None')
                                print(f"❌ {file}: Status={status}, Confidence={confidence:.2f}, Expected={current_expected}, Got={got_folder_id}")
                            elif not folder_id_match:
                                print(f"⚠️  {file}: Dikenali tapi folder_id salah (expected: {current_expected}, got: {recognition_result.get('folder_id')}, confidence: {confidence:.2f})")
                    else:
                        # Hanya cek apakah recognized (untuk same-image test)
                        if is_recognized:
                            success += 1
                            # Tampilkan detail jika confidence tinggi
                            if confidence > 60:
                                print(f"⚠️  {file}: Dikenali (confidence: {confidence:.2f} - agak sulit)")
                        else:
                            failed += 1
                            print(f"❌ {file}: Tidak dikenali (confidence: {confidence:.2f})")
                else:
                    failed += 1
                    error_msg = result.get('message', 'Unknown error')
                    # Tampilkan detail error
                    if 'wajah' in error_msg.lower() or 'face' in error_msg.lower():
                        print(f"❌ {file}: {error_msg} (Tidak ada wajah terdeteksi)")
                    else:
                        print(f"❌ {file}: {error_msg}")

                if visualize and result.get('data'):
                    output_path = _build_visual_path(
                        category_name,
                        test_folder,
                        root,
                        file,
                        visualize_root or DEFAULT_VISUAL_DIR,
                    )
                    saved = visualize_recognition(
                        img_path,
                        result['data'],
                        output_path,
                        metadata={'category': category_name}
                    )
                    if saved:
                        print(f"🖼️  Visualisasi disimpan: {output_path}")
    
    # Hitung akurasi dan statistik confidence
    akurasi = round((success / total_test) * 100, 2) if total_test > 0 else 0.0
    
    # Hitung statistik confidence
    avg_confidence = round(sum(confidence_scores) / len(confidence_scores), 2) if confidence_scores else 0
    min_confidence = min(confidence_scores) if confidence_scores else 0
    max_confidence = max(confidence_scores) if confidence_scores else 0
    
    print(f"\n{'='*50}")
    print(f"📊 HASIL {category_name.upper()}")
    print(f"{'='*50}")
    print(f"Total uji: {total_test}")
    print(f"Berhasil dikenali: {success}")
    print(f"Tidak dikenali: {failed}")
    print(f"Akurasi: {akurasi}%")
    if confidence_scores:
        print(f"\n📈 Statistik Confidence Score:")
        print(f"   Rata-rata: {avg_confidence}")
        print(f"   Minimum: {min_confidence} (terbaik)")
        print(f"   Maksimum: {max_confidence} (terburuk)")
        print(f"   Catatan: Semakin rendah confidence, semakin baik")
    print(f"{'='*50}\n")
    
    return {
        'total': total_test,
        'success': success,
        'failed': failed,
        'akurasi': akurasi
    }

def test_same_image(**kwargs):
    """Test 1: Same-image test - Test dengan gambar yang sama persis dari dataset training"""
    recognizer = FaceRecognizer()
    recognizer.load_model()
    
    return run_test_category(
        recognizer=recognizer,
        test_folder=DATASET_FOLDER,
        category_name="Same-Image Test",
        validate_folder_id=False,  # Untuk same-image test, tidak perlu validasi folder_id
        **kwargs
    )

def test_new_photo(**kwargs):
    """Test 2: Foto baru (real-test) - Test dengan foto baru yang berbeda dari dataset training"""
    recognizer = FaceRecognizer()
    recognizer.load_model()
    
    # Path untuk foto baru (bisa disesuaikan)
    # Asumsi: foto baru disimpan di storage/app/public/test/new_photo
    test_folder = os.path.join(BASE_STORAGE_PATH, "test", "new_photo")
    
    return run_test_category(
        recognizer=recognizer,
        test_folder=test_folder,
        category_name="Foto Baru (Real-Test)",
        validate_folder_id=False,  # Nonaktifkan validasi folder_id untuk melihat apakah foto dikenali (meski label salah)
        **kwargs
    )

def test_bright_lighting(**kwargs):
    """Test 3: Pencahayaan terang - Test dengan foto yang diambil dalam kondisi pencahayaan terang"""
    recognizer = FaceRecognizer()
    recognizer.load_model()
    
    # Path untuk foto dengan pencahayaan terang
    test_folder = os.path.join(BASE_STORAGE_PATH, "test", "bright_lighting")
    
    return run_test_category(
        recognizer=recognizer,
        test_folder=test_folder,
        category_name="Pencahayaan Terang",
        validate_folder_id=False,  # Nonaktifkan validasi untuk melihat apakah dikenali
        **kwargs
    )

def test_normal_lighting(**kwargs):
    """Test 4: Pencahayaan normal - Test dengan foto yang diambil dalam kondisi pencahayaan normal"""
    recognizer = FaceRecognizer()
    recognizer.load_model()
    
    # Path untuk foto dengan pencahayaan normal
    test_folder = os.path.join(BASE_STORAGE_PATH, "test", "normal_lighting")
    
    return run_test_category(
        recognizer=recognizer,
        test_folder=test_folder,
        category_name="Pencahayaan Normal",
        validate_folder_id=False,  # Nonaktifkan validasi untuk melihat apakah dikenali
        **kwargs
    )

def test_dim_lighting(**kwargs):
    """Test 5: Pencahayaan redup - Test dengan foto yang diambil dalam kondisi pencahayaan redup"""
    recognizer = FaceRecognizer()
    recognizer.load_model()
    
    # Path untuk foto dengan pencahayaan redup
    test_folder = os.path.join(BASE_STORAGE_PATH, "test", "dim_lighting")
    
    return run_test_category(
        recognizer=recognizer,
        test_folder=test_folder,
        category_name="Pencahayaan Redup",
        validate_folder_id=False,  # Nonaktifkan validasi untuk melihat apakah dikenali
        **kwargs
    )

def run_all_tests(**kwargs):
    """Jalankan semua test kategori"""
    print("\n" + "="*60)
    print("🧪 SISTEM PENGUJIAN FACE RECOGNITION")
    print("="*60)
    
    results = []
    
    # Test 1: Same-image test
    result1 = test_same_image(**kwargs)
    results.append(("Same-image test", result1))
    
    # Test 2: Foto baru
    result2 = test_new_photo(**kwargs)
    results.append(("Foto baru (real-test)", result2))
    
    # Test 3: Pencahayaan terang
    result3 = test_bright_lighting(**kwargs)
    results.append(("Pencahayaan terang", result3))
    
    # Test 4: Pencahayaan normal
    result4 = test_normal_lighting(**kwargs)
    results.append(("Pencahayaan normal", result4))
    
    # Test 5: Pencahayaan redup
    result5 = test_dim_lighting(**kwargs)
    results.append(("Pencahayaan redup", result5))
    
    # Tampilkan ringkasan
    print("\n" + "="*60)
    print("📋 RINGKASAN HASIL SEMUA TEST")
    print("="*60)
    print(f"{'No':<5} {'Kategori Uji':<30} {'Jumlah Uji':<12} {'Berhasil':<12} {'Gagal':<12} {'Akurasi':<12}")
    print("-"*60)
    
    for idx, (category, result) in enumerate(results, 1):
        print(f"{idx:<5} {category:<30} {result['total']:<12} {result['success']:<12} {result['failed']:<12} {result['akurasi']:<12}%")
    
    print("="*60)
    
    return results

def parse_args():
    parser = argparse.ArgumentParser(
        description="Jalankan berbagai kategori pengujian face recognition.",
    )
    parser.add_argument(
        "test",
        nargs="?",
        default="all",
        help="Pilihan: same|new|bright|normal|dim|all atau angka 1-5",
    )
    parser.add_argument(
        "--viz",
        action="store_true",
        help="Simpan visualisasi (gambar dengan bounding box + label)",
    )
    parser.add_argument(
        "--viz-dir",
        default=None,
        help=f"Folder output visualisasi (default: {DEFAULT_VISUAL_DIR})",
    )
    return parser.parse_args()


def main():
    args = parse_args()
    visualize = args.viz
    visualize_root = args.viz_dir or DEFAULT_VISUAL_DIR

    test_name = args.test.lower()
    kwargs = {"visualize": visualize, "visualize_root": visualize_root}

    if test_name in ("same", "1"):
        test_same_image(**kwargs)
    elif test_name in ("new", "2"):
        test_new_photo(**kwargs)
    elif test_name in ("bright", "3"):
        test_bright_lighting(**kwargs)
    elif test_name in ("normal", "4"):
        test_normal_lighting(**kwargs)
    elif test_name in ("dim", "5"):
        test_dim_lighting(**kwargs)
    elif test_name == "all":
        run_all_tests(**kwargs)
    else:
        print("❌ Test tidak dikenal!")
        print("Usage: python test_all_categories.py [same|new|bright|normal|dim|all]")
        print("   atau: python test_all_categories.py [1|2|3|4|5|all]")


if __name__ == "__main__":
    main()

