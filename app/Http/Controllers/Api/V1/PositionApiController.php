<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PositionApiController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'position_name' => 'required|unique:positions',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $position = Position::create($request->all());

        return response()->json($position, Response::HTTP_CREATED);
    }

    public function update(Request $request, $positionId)
    {
        $position = Position::findOrFail($positionId);

        $validator = Validator::make($request->all(), [
            'position_name' => 'required|unique:positions,position_name,' . $position->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $position->update($request->all());

        return response()->json($position, Response::HTTP_OK);
    }

    public function delete($positionId)
    {
        $position = Position::findOrFail($positionId);
        $position->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
