<?php

namespace App\Http\Controllers;

use App\Factories\FileFactory;
use App\Http\Requests\Assets\FileAttachmentRequest;
use App\Models\File\File;
use App\Models\Hr\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function index()
    {
        $files = File::where('uploaded_by', User::auth()->id)
            ->where('active', true)
            ->orderBy('created_at', 'desc')
        ;

        return response()->json($files->get()->toArray(), 200);
    }

    public function recent()
    {
        $files = File::where('uploaded_by', User::auth()->id)
            ->where('active', true)
            ->where('attached', false)
            ->orderBy('created_at', 'desc')
        ;

        return response()->json($files->get()->toArray(), 200);
    }

    public function store(FileAttachmentRequest $request)
    {
        $validated = $request->validated();
        $files = [];

        if (isset($validated['file'])) {
            $file = $validated['file'];
            $fileRecord = FileFactory::create(
                $file,
                'attachments',
            );
            $files[] = $fileRecord;
        } else {
            foreach ($validated['files'] as $file) {
                $fileRecord = FileFactory::create(
                    $file,
                    'attachments',
                );
                $files[] = $fileRecord;
            }
        }

        return response()->json($files, 201);
    }

    public function show(string $uuid)
    {
        $path = "uploads/attachments/{$uuid}";
        $file = Storage::get($path);
        if (empty($file)) {
            return response()->json(['message' => 'No images found'], 404);
        }

        $type = Storage::mimeType($path);

        return response($file, 200)->header('Content-Type', $type);
    }

    public function miniature(string $uuid)
    {
        $path = "uploads/attachments/{$uuid}";
        $file = Storage::get($path);
        if (empty($file)) {
            return response()->json(['message' => 'No images found'], 404);
        }

        $mime = Storage::mimeType($path) ?: ($file->mime_type ?? null);
        if (!$mime || explode('/', $mime)[0] !== 'image') {
            return response()->json(['message' => 'Cannot get file miniature as it is not an image'], 400);
        }

        $thumbPath = "uploads/thumbnails/{$uuid}.jpg";
        if (Storage::exists($thumbPath)) {
            $thumb = Storage::get($thumbPath);

            return response($thumb, 200)->header('Content-Type', 'image/jpeg');
        }

        $contents = Storage::get($path);

        try {
            if (function_exists('imagecreatefromstring')) {
                $src = imagecreatefromstring($contents);
                if ($src === false) {
                    return response()->json(['message' => 'Cannot create image from source'], 500);
                }
                $origW = imagesx($src);
                $origH = imagesy($src);
                $max = 150;
                $ratio = min($max / $origW, $max / $origH, 1);
                $newW = (int) floor($origW * $ratio);
                $newH = (int) floor($origH * $ratio);
                $dst = imagecreatetruecolor($newW, $newH);

                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

                ob_start();
                imagejpeg($dst, null, 80);
                $thumb = ob_get_clean();

                imagedestroy($src);
                imagedestroy($dst);
            } else {
                return response()->json(['message' => 'No supported image library (Imagick or GD) available'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error generating thumbnail', 'error' => $e->getMessage()], 500);
        }

        Storage::put($thumbPath, $thumb);

        return response($thumb, 200)->header('Content-Type', 'image/jpeg');
    }
}
