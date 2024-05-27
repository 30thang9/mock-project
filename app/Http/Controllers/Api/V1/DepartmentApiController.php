<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class DepartmentApiController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_name' => 'required|unique:departments',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $department = Department::create($request->all());

        return response()->json($department, Response::HTTP_CREATED);
    }

    public function update(Request $request, $departmentId)
    {
        $department = Department::findOrFail($departmentId);

        $validator = Validator::make($request->all(), [
            'department_name' => 'required|unique:departments,department_name,' . $department->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $department->update($request->all());

        return response()->json($department, Response::HTTP_OK);
    }

    public function delete($departmentId)
    {
        $department = Department::findOrFail($departmentId);
        $department->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
