<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    private UserRepository $userRepo;
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }
    public function getUsers()
    {
        return apiResponse('Query successfully!',$this->userRepo->findAll());
    }

    public function getUser($id){
        return apiResponse('Query successfully!',$this->userRepo->findOne($id));
    }

    public function updateUser(Request $request, $id){

    }
}
