<?php

namespace App\Http\Repositories\Implements;

use App\Http\Repositories\AttendanceRepository;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AttendanceRepositoryImpl implements AttendanceRepository
{
    function findAll()
    {
        return Attendance::all();
    }

    function findOne($id)
    {
        try {
            return Attendance::findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return null;
        }
    }

    function findByUser($user)
    {
        if (!($user instanceof User)) {
            return collect();
        }

        return Attendance::where('user_id', $user->id)->get();
    }

    function findByUserPosition($position)
    {
        if (!($position instanceof Position)) {
            return collect();
        }

        return Attendance::whereHas('user', function ($query) use ($position) {
            $query->where('position_id', $position->id);
        })->get();
    }

    function findByUserDepartment($department)
    {
        if (!($department instanceof Department)) {
            return collect();
        }

        return Attendance::whereHas('user', function ($query) use ($department) {
            $query->where('department_id', $department->id);
        })->get();
    }

    function save($attendance)
    {
        if (!($attendance instanceof Attendance)) {
            return null;
        }
        try {
            DB::beginTransaction();

            $attendance->save();

            DB::commit();

            return $attendance;
        }
        catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
    }

    function deleteById($id):bool
    {
        try {
            DB::beginTransaction();

            Attendance::destroy($id);

            DB::commit();

            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }

    function deleteByUser($user):bool
    {
        if (!($user instanceof User)) {
            return false;
        }

        try {
            DB::beginTransaction();

            Attendance::where('user_id', $user->id)->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

}
