<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\RegistationEvent;
use App\Http\Controllers\Controller;
use App\Http\Repositories\UserRepository;
use App\Http\Requests\Users\AdminHandleUserUpdateRequest;
use App\Http\Requests\Users\UserCreateRequest;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AdminApiController extends Controller
{
    private $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function createUser(UserCreateRequest $request)
    {
        $validatedData = $request->validated();

        $user = new User([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'department_id' => $validatedData['department_id'],
            'role_id' => $validatedData['role_id'],
        ]);

        try {
            DB::beginTransaction();
            $user = $this->userRepo->save($user);
            DB::commit();
            if(!$user){
                return apiResponse(__('Registation error'), null, ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
            }
            event(new RegistationEvent($user));
            return apiResponse(__('Registation successfully!'), $user, ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Registration failed: '. $e->getMessage());
            return apiResponse(__("Something went wrong on our end"), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function updateUser(AdminHandleUserUpdateRequest $request, $id)
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

    public function deleteUser($id)
    {
        try {
            $user = $this->userRepo->findOne($id);

            if (!$user) {
                return apiResponse(__('User not found'), null, ResponseAlias::HTTP_NOT_FOUND);
            }

            $this->userRepo->deleteById($id);

            return apiResponse('User deleted successfully');
        } catch (\Exception $e) {
            Log::error('User delete failed: ' . $e->getMessage());
            return apiResponse(__('Failed to delete user'), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getUsers(Request $request)
    {
        $currentPage = $request->input('current_page', 1);
        $perPage = $request->input('per_page', null);

        $users = $this->userRepo->findOnlyRoleUser($currentPage, $perPage);
        foreach ($users as $user) {
            $user->positions = $user->positions;
            $user->role = $user->role;
            $user->department = $user->department;
        }

        return apiResponse('Query successfully!', $users);
    }

    public function getUser($id)
    {
        try {
            $user = $this->userRepo->findOne($id);

            if (!$user) {
                return apiResponse(__('User not found'), null, ResponseAlias::HTTP_NOT_FOUND);
            }

            $user->positions = $user->positions;

            $user->role = $user->role;

            $user->department = $user->department;

            return apiResponse('Query successfully!', ['user' => $user]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch user: ' . $e->getMessage());
            return apiResponse(__('Failed to fetch user'), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createUserPosition(Request $request, $userId)
    {
        $request->validate([
            'position_id' => 'required|exists:positions,id',
        ]);

        try {
            $user = $this->userRepo->findOne($userId);
            if (!$user) {
                return apiResponse(__('User not found'), null, ResponseAlias::HTTP_NOT_FOUND);
            }
            $positionId = $request->input('position_id');

            if ($user->positions()->where('id', $positionId)->exists()) {
                return apiResponse(__('User already has this position'), null, ResponseAlias::HTTP_BAD_REQUEST);
            }

            $user->positions()->attach($positionId);

            $position = Position::findOrFail($positionId);

            return apiResponse(__('Position assigned successfully'), ['position' => $position]);
        } catch (\Exception $e) {
            Log::error('Failed to assign position to user: ' . $e->getMessage());
            return apiResponse(__('Failed to assign position to user'), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateUserPosition(Request $request, $userId, $positionId)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        try {
            $user = $this->userRepo->findOne($userId);
            if (!$user) {
                return apiResponse(__('User not found'), null, ResponseAlias::HTTP_NOT_FOUND);
            }

            if (!$user->positions()->where('id', $positionId)->exists()) {
                return apiResponse(__('User does not have this position'), null, ResponseAlias::HTTP_BAD_REQUEST);
            }

            $position = $user->positions()->findOrFail($positionId);

            $position->pivot->status = $request->input('status');
            $position->pivot->save();

            return apiResponse(__('Position status updated successfully'), ['position' => $position]);
        } catch (\Exception $e) {
            Log::error('Failed to update position status for user: ' . $e->getMessage());
            return apiResponse(__('Failed to update position status for user'), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteUserPosition($userId, $positionId)
    {
        try {
            $user = $this->userRepo->findOne($userId);
            if (!$user) {
                return apiResponse(__('User not found'), null, ResponseAlias::HTTP_NOT_FOUND);
            }

            $user->positions()->detach($positionId);

            return apiResponse(__('Position removed successfully'));
        } catch (\Exception $e) {
            Log::error('Failed to remove position from user: ' . $e->getMessage());
            return apiResponse(__('Failed to remove position from user'), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
