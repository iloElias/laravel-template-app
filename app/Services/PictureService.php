<?php

namespace App\Services;

use App\Factories\FileFactory;
use App\Http\Responses\User\UserDataResponse;
use App\Models\Error;
use App\Models\File\File;
use App\Models\Hr\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PictureService
{
    /**
     * Returns the user's image from Storage.
     */
    public function getPicture(string $userUuid, ?string $pictureUuid = null)
    {
        $filePath = $pictureUuid
            ? "uploads/pictures/{$userUuid}/{$pictureUuid}"
            : $this->getLastUploadedPicturePath($userUuid);

        if (!$filePath || !Storage::exists($filePath)) {
            return false;
        }

        return [
            'file' => Storage::get($filePath),
            'mime' => Storage::mimeType($filePath),
        ];
    }

    /**
     * Uploads an image and updates the user's record.
     *
     * @return array Result with the file record or error
     */
    public function uploadPicture(Request $request, User $user)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $file = $validated['image'];
        $disk = env('FILESYSTEM_DISK', 's3');

        $fileRecord = FileFactory::create($file, "pictures/{$user->uuid}");
        if (!$fileRecord) {
            return throw new \Exception('failed_to_store_image');
        }

        $fileRecord->update(['attached' => true]);
        $user->update([
            'profile_picture' => $fileRecord->path,
        ]);

        if (!$fileRecord) {
            Storage::disk($disk)->delete($fileRecord->path);

            throw new \Exception('failed_to_save_image_record');
        }

        return ['user' => UserDataResponse::format($user)];
    }

    /**
     * Retrieves the path of the last uploaded picture for a user.
     */
    private function getLastUploadedPicturePath(string $userUuid): ?string
    {
        $files = Storage::files("uploads/pictures/{$userUuid}");

        return empty($files) ? null : end($files);
    }
}
