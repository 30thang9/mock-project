<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ScheduledNotification;
use Illuminate\Http\Request;

class ScheduledNotificationApiController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('date')) {
            $date = $request->input('date');

            $notifications = ScheduledNotification::whereDate('scheduled_at', $date)->get();

            return apiResponse('Query successfully!', ['notifications' => $notifications]);
        }

        $notifications = ScheduledNotification::all();
        return apiResponse('Query successfully!', ['notifications' => $notifications]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'scheduled_at' => 'required|date',
        ]);

        $notification = ScheduledNotification::create($request->all());

        return apiResponse('Notification created successfully!', ['notification' => $notification], 201);
    }

    public function show($id)
    {
        $notification = ScheduledNotification::findOrFail($id);
        return apiResponse('Query successfully!', ['notification' => $notification]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'scheduled_at' => 'required|date',
        ]);

        $notification = ScheduledNotification::findOrFail($id);
        $notification->update($request->all());

        return apiResponse('Notification updated successfully!', ['notification' => $notification]);
    }

    public function destroy($id)
    {
        $notification = ScheduledNotification::findOrFail($id);
        $notification->delete();

        return apiResponse('Notification deleted successfully!', null, 204);
    }
}
