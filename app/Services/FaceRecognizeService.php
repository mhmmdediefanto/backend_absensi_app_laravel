<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FaceRecognizeService
{
    public function recognize($image)
    {
        $tempPath = $image->store('temp', 'public');
        $fullPath = storage_path('app/public/' . $tempPath);

        $pythonScript = base_path('face_recognition/recognize.py');
        $venvPython   = base_path('venv/Scripts/python.exe');
        $command = "\"$venvPython\" \"$pythonScript\" \"$fullPath\" 2>&1";

        $output = shell_exec($command);
        $result = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Invalid JSON from Python: $output");
            throw new \Exception("Face recognition failed.");
        }

        // Hapus file temp setelah dipakai
        if ($result && $result['success'] === true) {
            Storage::disk('public')->delete($tempPath);
        }

        return $result;
    }
}
