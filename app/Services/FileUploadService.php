<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    private const PROFILE_PICTURE_PATH = 'profile_pictures';

    /**
     * @throws \Exception
     */
    public function uploadProfilePicture($profilePicture)
    {
        try {
            $fileName = time() . '_' . Str::random(10) . '.' . $profilePicture->getClientOriginalExtension();

            $path = $profilePicture->storeAs(self::PROFILE_PICTURE_PATH, $fileName, 'public');

            if (!$path) {
                throw new \Exception('Failed to store profile picture.');
            }

            return $fileName;
        } catch (\Exception $e) {
            Log::error('Error uploading profile picture: ' . $e->getMessage());
            throw new \Exception('Failed to upload profile picture.');
        }
    }

    public function deleteProfilePicture($fileName)
    {
        Storage::disk('public')->delete(self::PROFILE_PICTURE_PATH . '/' . $fileName);
    }

    public function getProfilePictureUrl($fileName)
    {
        return Storage::url(self::PROFILE_PICTURE_PATH . '/' . $fileName);
    }
}
