import cv2
import os
import numpy as np
import json
import sys
import base64
from datetime import datetime

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, "trainer/lbph_model.yml")
LABELS_PATH = os.path.join(BASE_DIR, "trainer/labels.json")

class FaceRecognizer:
    def __init__(self):
        self.recognizer = cv2.face.LBPHFaceRecognizer_create()
        self.face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
        self.label_mapping = {}
        self.load_model()
    
    def load_model(self):
        """Load model dan mapping label"""
        try:
            # Load model
            if not os.path.exists(MODEL_PATH):
                print("❌ Model tidak ditemukan! Jalankan training terlebih dahulu.")
                return False
            
            self.recognizer.read(MODEL_PATH)
            
            # Load label mapping
            if os.path.exists(LABELS_PATH):
                with open(LABELS_PATH, 'r') as f:
                    label_data = json.load(f)
                
                self.label_mapping = label_data.get('internal_labels', {})
                return True
            else:
                print("❌ File labels.json tidak ditemukan!")
                return False
                
        except Exception as e:
            print(f"❌ Error loading model: {e}")
            return False
    
    def get_folder_name(self, internal_label):
        """Dapatkan folder name berdasarkan internal label"""
        internal_label_str = str(internal_label)
        if internal_label_str in self.label_mapping:
            return self.label_mapping[internal_label_str]['folder_name']
        return "Unknown"
    
    def get_folder_id(self, internal_label):
        """Dapatkan folder ID berdasarkan internal label"""
        internal_label_str = str(internal_label)
        if internal_label_str in self.label_mapping:
            return self.label_mapping[internal_label_str]['folder_id']
        return "Unknown"
    
    def determine_status(self, confidence, folder_name):
        """Tentukan status berdasarkan confidence dan folder name"""
        # Threshold yang lebih ketat untuk LBPH
        if confidence < 80:  # Threshold lebih ketat untuk recognition yang baik
            return "recognized"
        elif confidence < 120:  # Range yang lebih sempit untuk "maybe"
            return "maybe"
        else:
            return "unknown"
    
    def process_recognition_result(self, label, confidence):
        """Proses hasil recognition dengan validasi tambahan"""
        # Dapatkan informasi folder
        folder_name = self.get_folder_name(label)
        folder_id = self.get_folder_id(label)
        
        # Tentukan status dengan threshold yang lebih ketat
        status = self.determine_status(confidence, folder_name)
        
        # Jika status unknown, kosongkan folder info
        if status == "unknown":
            folder_name = None
            folder_id = None
        
        return folder_name, folder_id, status
    
    def recognize_face_from_path(self, image_path):
        """Recognize wajah dari path file gambar"""
        try:
            # Baca gambar
            image = cv2.imread(image_path)
            if image is None:
                return {
                    'success': False,
                    'message': f'Gagal membaca gambar: {image_path}',
                    'data': None
                }
            
            # Convert ke grayscale
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
            
            # Deteksi wajah
            faces = self.face_cascade.detectMultiScale(
                gray, 
                scaleFactor=1.1, 
                minNeighbors=5, 
                minSize=(30, 30)
            )
            
            if len(faces) == 0:
                return {
                    'success': False,
                    'message': 'Tidak ada wajah terdeteksi dalam gambar',
                    'data': None
                }
            
            results = []
            for (x, y, w, h) in faces:
                # Crop wajah
                face_roi = gray[y:y+h, x:x+w]
                
                # Resize ke ukuran standar
                face_roi = cv2.resize(face_roi, (100, 100))
                
                # Predict
                label, confidence = self.recognizer.predict(face_roi)
                
                # Proses hasil recognition dengan validasi
                folder_name, folder_id, status = self.process_recognition_result(label, confidence)
                
                result = {
                    'internal_label': int(label),
                    'folder_id': folder_id,
                    'folder_name': folder_name,
                    'confidence': float(confidence),
                    'status': status,
                    'position': {
                        'x': int(x),
                        'y': int(y),
                        'width': int(w),
                        'height': int(h)
                    },
                    'timestamp': datetime.now().isoformat()
                }
                
                results.append(result)
            
            return {
                'success': True,
                'message': f'Berhasil mendeteksi {len(results)} wajah',
                'data': results
            }
            
        except Exception as e:
            return {
                'success': False,
                'message': f'Error dalam recognize_face: {str(e)}',
                'data': None
            }
    
    def recognize_face_from_base64(self, base64_string):
        """Recognize wajah dari base64 string (untuk foto dari Vue)"""
        try:
            # Decode base64
            image_data = base64.b64decode(base64_string)
            nparr = np.frombuffer(image_data, np.uint8)
            image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            
            if image is None:
                return {
                    'success': False,
                    'message': 'Gagal decode base64 image',
                    'data': None
                }
            
            # Convert ke grayscale
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
            
            # Deteksi wajah
            faces = self.face_cascade.detectMultiScale(
                gray, 
                scaleFactor=1.1, 
                minNeighbors=5, 
                minSize=(30, 30)
            )
            
            if len(faces) == 0:
                return {
                    'success': False,
                    'message': 'Tidak ada wajah terdeteksi dalam gambar',
                    'data': None
                }
            
            results = []
            for (x, y, w, h) in faces:
                # Crop wajah
                face_roi = gray[y:y+h, x:x+w]
                
                # Resize ke ukuran standar
                face_roi = cv2.resize(face_roi, (100, 100))
                
                # Predict
                label, confidence = self.recognizer.predict(face_roi)
                
                # Proses hasil recognition dengan validasi
                folder_name, folder_id, status = self.process_recognition_result(label, confidence)
                
                result = {
                    'internal_label': int(label),
                    'folder_id': folder_id,
                    'folder_name': folder_name,
                    'confidence': float(confidence),
                    'status': status,
                    'position': {
                        'x': int(x),
                        'y': int(y),
                        'width': int(w),
                        'height': int(h)
                    },
                    'timestamp': datetime.now().isoformat()
                }
                
                results.append(result)
            
            return {
                'success': True,
                'message': f'Berhasil mendeteksi {len(results)} wajah',
                'data': results
            }
            
        except Exception as e:
            return {
                'success': False,
                'message': f'Error dalam recognize_face_from_base64: {str(e)}',
                'data': None
            }

def main():
    """Main function untuk testing atau dipanggil dari Laravel"""
    if len(sys.argv) < 2:
        print("❌ Usage: python recognize.py <image_path>")
        print("   atau: python recognize.py --base64 <base64_string>")
        return
    
    # Inisialisasi recognizer
    recognizer = FaceRecognizer()
    
    if not recognizer.load_model():
        print("❌ Gagal memuat model. Pastikan training sudah dijalankan.")
        return
    
    # Check mode
    if sys.argv[1] == "--base64" and len(sys.argv) >= 3:
        # Mode base64 (untuk foto dari Vue)
        base64_string = sys.argv[2]
        result = recognizer.recognize_face_from_base64(base64_string)
    else:
        # Mode file path
        image_path = sys.argv[1]
        result = recognizer.recognize_face_from_path(image_path)
    
    # Output JSON untuk Laravel
    print(json.dumps(result, indent=2))

if __name__ == "__main__":
    main()
