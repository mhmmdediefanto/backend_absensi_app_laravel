<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FaceRecognitionController extends Controller
{
    /**
     * Recognize face from uploaded image
     */
    public function recognizeFace(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg|max:4096'
            ]);

            // Get the uploaded image
            $image = $request->file('image');
            
            // Save image temporarily
            $tempPath = $image->store('temp', 'public');
            $fullPath = storage_path('app/public/' . $tempPath);
            Log::info('File saved at: '.$fullPath);

            $result = $this->callPythonScript($fullPath);

            // Hanya hapus jika sukses dan file selesai diproses
            if ($result && $result['success'] === true) {
                Storage::disk('public')->delete($tempPath);
            }
            
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Face recognition error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing face recognition: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Recognize face from base64 image (for Vue.js)
     */
    public function recognizeFaceBase64(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'image_base64' => 'required|string'
            ]);

            $base64String = $request->input('image_base64');
            
            // Remove data:image/jpeg;base64, prefix if present
            $base64String = preg_replace('/^data:image\/[a-z]+;base64,/', '', $base64String);

            // Call Python script with base64
            $result = $this->callPythonScriptBase64($base64String);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Face recognition base64 error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing face recognition: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Call Python script with image path
     */
    private function callPythonScript($imagePath)
    {
        $pythonScript = base_path('face_recognition/recognize.py');
        
        // Use Python from virtual environment
        $venvPython = base_path('venv/Scripts/python.exe');
        
        // Execute Python script
        $command = "\"$venvPython\" \"$pythonScript\" \"$imagePath\" 2>&1";
        $output = shell_exec($command);
        
        // Parse JSON output
        $result = json_decode($output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON output from Python script: ' . $output);
        }
        
        return $result;
    }

    /**
     * Call Python script with base64 string
     */
    private function callPythonScriptBase64($base64String)
    {
        $pythonScript = base_path('face_recognition/recognize.py');
        
        // Use Python from virtual environment
        $venvPython = base_path('venv/Scripts/python.exe');
        
        // Escape base64 string for command line
        $escapedBase64 = escapeshellarg($base64String);
        
        // Execute Python script with base64
        $command = "\"$venvPython\" \"$pythonScript\" --base64 $escapedBase64 2>&1";
        $output = shell_exec($command);
        
        // Parse JSON output
        $result = json_decode($output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON output from Python script: ' . $output);
        }
        
        return $result;
    }

    /**
     * Get available faces in the model
     */
    public function getAvailableFaces()
    {
        try {
            $labelsPath = base_path('face_recognition/trainer/labels.json');
            
            if (!file_exists($labelsPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Labels file not found. Please run training first.',
                    'data' => null
                ]);
            }
            
            $labelsData = json_decode(file_get_contents($labelsPath), true);
            
            $availableFaces = [];
            if (isset($labelsData['all_folders'])) {
                foreach ($labelsData['all_folders'] as $folderId => $info) {
                    $availableFaces[] = [
                        'folder_id' => $info['folder_id'],
                        'folder_name' => $info['folder_name']
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Available faces retrieved successfully',
                'data' => [
                    'faces' => $availableFaces,
                    'total_faces' => count($availableFaces),
                    'training_info' => $labelsData['training_info'] ?? null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get available faces error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving available faces: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Test face recognition with sample image
     */
    public function testRecognition()
    {
        try {
            // Use a sample image from dataset
            $sampleImagePath = storage_path('app/public/dataset/211240001155/09kG3IsllDtAd8llzJuKo1b0xwh9PsDBE5c6ee2F.jpg');
            
            if (!file_exists($sampleImagePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sample image not found',
                    'data' => null
                ]);
            }
            
            $result = $this->callPythonScript($sampleImagePath);
            
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Test recognition error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error testing recognition: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
} 