<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\UploadedFile;

class FileController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:pdf,docx,csv,xml|max:10240',
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('uploads', 'public');

                $uploadedFile = UploadedFile::create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);

                return response()->json([
                    'message' => 'Файл успешно загружен',
                    'file_id' => $uploadedFile->id,
                    'path' => $path,
                ]);
            }

            return response()->json(['error' => 'Файл не был загружен'], 400);
        } catch (\Exception $e) {
            \Log::error('File upload error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
