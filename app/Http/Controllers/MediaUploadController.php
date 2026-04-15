<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MediaUploadController extends Controller
{
    /**
     * Handle Quill editor media uploads (Images and Videos)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        // Require authentication (already handled by web route middleware, but extra validation here is safe)
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        try {
            // Validate incoming file
            $request->validate([
                'file' => 'required|file|mimes:jpeg,png,jpg,gif,svg,webp,mp4,webm,ogg,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,7z,txt,csv|max:51200', // max 50MB
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Determine sub-folder based on mime type
                $mime = $file->getMimeType();
                if (str_starts_with($mime, 'image')) {
                    $typeFolder = 'images';
                } elseif (str_starts_with($mime, 'video')) {
                    $typeFolder = 'videos';
                } else {
                    $typeFolder = 'files';
                }
                
                // Store file in public disk (accessible via storage symlink)
                $path = $file->store('uploads/tinymce/' . $typeFolder, 'public');
                
                // Generate the absolute public URL to the stored file
                $url = asset('storage/' . $path);

                Log::info('Media uploaded successfully via Quill: ' . $path);

                return response()->json([
                    'success' => true,
                    'url' => $url
                ], 200);
            }

            return response()->json(['error' => 'No file was uploaded.'], 400);

        } catch (\Exception $e) {
            Log::error('MediaUploadController Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server failed to process the upload.'], 500);
        }
    }
}
