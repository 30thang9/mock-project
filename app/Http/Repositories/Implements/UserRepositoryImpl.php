<?php

namespace App\Http\Repositories\Implements;

use App\Models\User;
use App\Http\Repositories\UserRepository;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;


class UserRepositoryImpl implements UserRepository
{
    private $fileUploadService;
    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    public function findAll($currentPage = 1, $perPage = null)
    {
        if ($perPage === null) {
            return User::all();
        }
        $currentPage = max(1, $currentPage);
        return User::paginate($perPage, ['*'], 'page', $currentPage);
    }
    public function findOne($id)
    {
        return User::find($id);
    }
    public function findByEmail($email){
        return User::where('email', $email)->first();
    }
    public function save($user)
    {
        if (!($user instanceof User)) {
            return null;
        }
        try {
            $user->save();
            return $user;
        }
        catch (\Exception $e) {
            Log::error("Error save: " . $e->getMessage());
            return null;
        }
    }
    public function deleteById($id):bool
    {
        try {
            User::destroy($id);
            return true;
        } catch (\Exception $exception) {
            Log::error("Error delete: " . $exception->getMessage());
            return false;
        }
    }

    function findBySearchText($searchText)
    {
        $query = User::query();
        $searchText = trim($searchText);

        if ($searchText) {
            $query->where(function ($q) use ($searchText) {
                $q->where('name', 'like', '%' . $searchText . '%')
                    ->orWhere('email', 'like', '%' . $searchText . '%');
            });
        }
        $users = $query->get();
        return $users;
    }

    /**
     * @throws \Exception
     */
    public function avatarUpload($id, $profileAvatar)
    {
        $user = $this->findOne($id);

        if (!$user) {
            throw new InvalidArgumentException("User with ID $id not found.");
        }

        $fileName = $this->fileUploadService->uploadProfilePicture($profileAvatar);

        if ($user->avatar) {
            $this->fileUploadService->deleteProfilePicture($user->avatar);
        }

        $user->avatar = $fileName;
        if (!$this->save($user)) {
            throw new \Exception("Failed to update user's avatar.");
        }

        return $fileName;
    }

}
