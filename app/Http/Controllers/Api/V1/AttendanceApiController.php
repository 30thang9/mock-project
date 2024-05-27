<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Repositories\AttendanceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AttendanceApiController extends Controller
{
    private $attendanceRepo;

    public function __construct(AttendanceRepository $attendanceRepo)
    {
        $this->attendanceRepo = $attendanceRepo;
    }

    public function checkIn(Request $request) {
        try {
            $user = JWTAuth::user();
            if (!$user) {
                return apiResponse(__('Unauthenticated.'), null, Response::HTTP_UNAUTHORIZED);
            }
            $timestamp = $request->input('check_in_time');

            $redisKey = "attendance:$user->id";

            Redis::hset($redisKey, $timestamp, json_encode([
                'user_id'=> $user->id,
                'check_in_time' => $timestamp,
            ]));

            return apiResponse(__('Check-in recorded'));
        } catch (\Exception $e) {
            return apiResponse(__("Something went wrong on our end"), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getCurrentAttendanceByUser($userId) {
        try {
            $authUser = JWTAuth::user();

            if (strval($userId) !== strval($authUser->id)) {
                return apiResponse(__('Unauthenticated.'), null, Response::HTTP_UNAUTHORIZED);
            }

            $redisKey = "attendance:$userId";

            $attendanceRecords = Redis::hgetall($redisKey);

            $attendances = [];
            $checkInTimes = [];
            $checkOutTimes = [];

            foreach ($attendanceRecords as $time => $record) {
                $data = json_decode($record, true);

                $attendances[] = [
                    'check_in_time' => $time,
                    'user_id' => $data['user_id']
                ];

                $checkInTimes[] = strtotime($time);
                $checkOutTimes[] = strtotime($time);
            }

            if (!empty($checkInTimes) && !empty($checkOutTimes)) {
                $firstCheckIn = min($checkInTimes);
                $lastCheckOut = max($checkOutTimes);

                $firstCheckInTime = date('Y-m-d H:i:s', $firstCheckIn);
                $lastCheckOutTime = date('Y-m-d H:i:s', $lastCheckOut);
            } else {
                $firstCheckInTime = null;
                $lastCheckOutTime = null;
            }

            $data = [
                'attendances' => $attendances,
                'check_in_time' => $firstCheckInTime,
                'check_out_time' => $lastCheckOutTime
            ];

            return apiResponse(__('Query successfully!'), $data);
        } catch (\Exception $e) {
            return apiResponse(__("Something went wrong on our end"), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAttendanceByUser(Request $request, $userId) {
        try {
            $authUser = JWTAuth::user();

            if (strval($userId) !== strval($authUser->id)) {
                return apiResponse(__('Unauthenticated.'), null, Response::HTTP_UNAUTHORIZED);
            }

            $month = $request->input('month', null);

            if($month){
                $attendances = $this->attendanceRepo->findByUserAndMonth($authUser, $month);
            }else{
                $attendances = $this->attendanceRepo->findByUser($authUser);
            }

            return apiResponse(__('Query successfully!'), ["attendances" => $attendances]);
        } catch (\Exception $e) {
            return apiResponse(__("Something went wrong on our end"), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
