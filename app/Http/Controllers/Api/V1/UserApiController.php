<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Repositories\UserRepository;
use App\Http\Requests\Users\UserUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserApiController extends Controller
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function getUser($id){
        return apiResponse('Query successfully!',$this->userRepo->findOne($id));
    }

    public function updateUser(UserUpdateRequest $request, $id)
    {
        try {
            $user = $this->userRepo->findOne($id);

            if (!$user) {
                return apiResponse(__('User not found'), null, ResponseAlias::HTTP_NOT_FOUND);
            }

            $user->fill($request->validated());
            $this->userRepo->save($user);

            return apiResponse('User updated successfully', $user);
        } catch (\Exception $e) {
            Log::error('User update failed: ' . $e->getMessage());
            return apiResponse(__('Failed to update user'), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function search(Request $request)
    {
        return apiResponse('Query successfully!',$this->userRepo->findBySearchText($request->only('search_text')));
    }

    public function changePassword(Request $request,$id)
    {
        $request->validate([
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|different:current_password',
            'confirm_password' => 'required|string|min:6|same:new_password',
        ]);

        try {
            $user = $this->userRepo->findOne($id);

            if (!$user) {
                return apiResponse(__('User not found'), null, ResponseAlias::HTTP_NOT_FOUND);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return apiResponse('Current password is incorrect', null, ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user->password = Hash::make($request->new_password);
            $this->userRepo->save($user);
            return apiResponse('Password updated successfully');
        } catch (\Exception $e) {
            Log::error('Registration failed: '. $e->getMessage());
            return apiResponse(__($e->getMessage()), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    public function avatarUpload(Request $request,$id)
    {
        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            ]);

            $user = $this->userRepo->findOne($id);

            if (!$user) {
                return apiResponse(__('User not found'), null, ResponseAlias::HTTP_NOT_FOUND);
            }

            $profilePicture = $request->file('avatar');
            $fileName = $this->userRepo->avatarUpload($user->id, $profilePicture);
            return apiResponse('Profile picture updated successfully!', ['avatar' => $fileName]);
        }
        catch (\Exception $e) {
            return apiResponse(__("Something went wrong on our end"), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
