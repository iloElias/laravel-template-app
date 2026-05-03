<?php

namespace App\Http\Controllers;

use App\Factories\FileFactory;
use App\Http\Requests\Assets\FileAttachmentRequest;
use App\Models\File\File;
use App\Models\Hr\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function recent(): JsonResponse
    {
        $files = File::where('uploaded_by', User::auth()->id)
            ->where('active', true)
            ->where('attached', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($files);
    }

    public function store(FileAttachmentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $files = [];

        if (isset($validated['file'])) {
            $files[] = FileFactory::create($validated['file'], 'attachments');
        } else {
            foreach ($validated['files'] as $file) {
                $files[] = FileFactory::create($file, 'attachments');
            }
        }

        return response()->json($files, 201);
    }

    public function show(string $uuid, Request $request): Response|JsonResponse
    {
        $path = "uploads/attachments/{$uuid}";
        $file = Storage::get($path);

        if (empty($file)) {
            return response()->json(['message' => 'No images found'], 404);
        }

        $etag = md5($uuid);

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304);
        }

        return response($file, 200)
            ->header('Content-Type', Storage::mimeType($path))
            ->header('ETag', $etag)
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function miniature(string $uuid, Request $request): Response|JsonResponse
    {
        $path = "uploads/attachments/{$uuid}";
        $file = Storage::get($path);

        if (empty($file)) {
            return response()->json(['message' => 'No images found'], 404);
        }

        $mime = Storage::mimeType($path);

        if (!$mime || explode('/', $mime)[0] !== 'image') {
            return response()->json(['message' => 'Cannot get file miniature as it is not an image'], 400);
        }

        $etag = md5("{$uuid}_miniature");

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304);
        }

        $thumbPath = "uploads/thumbnails/{$uuid}.jpg";

        if (Storage::exists($thumbPath)) {
            return response(Storage::get($thumbPath), 200)
                ->header('Content-Type', 'image/jpeg')
                ->header('ETag', $etag)
                ->header('Cache-Control', 'public, max-age=86400');
        }

        try {
            if (!function_exists('imagecreatefromstring')) {
                return response()->json(['message' => 'No supported image library (Imagick or GD) available'], 500);
            }

            $src = imagecreatefromstring($file);

            if ($src === false) {
                return response()->json(['message' => 'Cannot create image from source'], 500);
            }

            $origW = imagesx($src);
            $origH = imagesy($src);
            $max = 150;
            $ratio = min($max / $origW, $max / $origH, 1);
            $dst = imagecreatetruecolor((int) floor($origW * $ratio), (int) floor($origH * $ratio));

            imagecopyresampled($dst, $src, 0, 0, 0, 0, imagesx($dst), imagesy($dst), $origW, $origH);

            ob_start();
            imagejpeg($dst, null, 80);
            $thumb = ob_get_clean();

            imagedestroy($src);
            imagedestroy($dst);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error generating thumbnail', 'error' => $e->getMessage()], 500);
        }

        Storage::put($thumbPath, $thumb);

        return response($thumb, 200)
            ->header('Content-Type', 'image/jpeg')
            ->header('ETag', $etag)
            ->header('Cache-Control', 'public, max-age=86400');
    }
}

