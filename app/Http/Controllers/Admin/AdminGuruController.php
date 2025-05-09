<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminGuruController extends Controller
{
    public function getGuru()
    {
        $guru = Guru::select('nama', 'nip', 'no_hp')->get();
        return response()->json([
            'guru' => $guru,
        ]);
    }

    public function delete($id)
    {
        $guru = Guru::find($id);
        if ($guru) {
            if ($guru->foto) {
                Storage::delete($guru->foto);
            }
            
            $guru->delete();
            return response()->json([
                'message' => 'Guru deleted successfully',
            ]);
        } else {
            return response()->json([
                'message' => 'Guru not found',
            ], 404);
        }
    }
}
