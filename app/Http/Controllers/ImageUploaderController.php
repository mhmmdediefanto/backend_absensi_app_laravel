<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploaderController extends Controller
{
    public function uploadImage(Request $request)
    {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/kegiatan', 'public');

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'url' => asset('storage/' . $path), // kasih balik URL biar dipakai Quill
                'path' => $path, // kalau butuh path asli untuk delete nanti
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
    }

    public function destroy(Request $request)
    {
        $url = $request->input('url');

        // Buat relative path dari URL
        $path = str_replace(asset('storage') . '/', '', $url);


        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return response()->json(['success' => true, 'message' => 'File deleted successfully']);
        }

        return response()->json(['success' => false, 'message' => 'File not found'], 404);
    }
}
