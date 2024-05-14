<?php

namespace App\Http\Repositories\Implements;

use App\Models\Attendance;
use App\Models\User;
use App\Http\Repositories\UserRepository;
use InvalidArgumentException;


class UserRepositoryImpl implements UserRepository
{
    public function findAll()
    {
        return User::all();
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
            return $e->getMessage();
        }
    }
    public function deleteById($id):bool
    {
        try {
            User::destroy($id);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
