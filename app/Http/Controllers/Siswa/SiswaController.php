<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class SiswaController extends Controller
{
    public function registeredFace(Request $request)
    {
        $request->validate([
            'image' => 'mimes:jpg',
            'nis' => 'required'
        ]);

        $nisSiswa = $request->input('nis');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store("dataset/{$nisSiswa}", 'public');
            return response()->json(['success' => true, 'path' => $path]);
        }
        return response()->json(['error' => 'Tidak ada file dikirim'], 400);
    }
}
