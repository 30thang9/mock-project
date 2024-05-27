<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class RoleApiController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_name' => 'required|unique:roles',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $role = Role::create($request->all());

        return response()->json($role, Response::HTTP_CREATED);
    }

    public function update(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);

        $validator = Validator::make($request->all(), [
            'role_name' => 'required|unique:roles,role_name,' . $role->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $role->update($request->all());

        return response()->json($role, Response::HTTP_OK);
    }

    public function delete($roleId)
    {
        $role = Role::findOrFail($roleId);
        $role->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
