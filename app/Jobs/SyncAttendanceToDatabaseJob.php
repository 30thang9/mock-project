<?php

namespace App\Jobs;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SyncAttendanceToDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//    private $attendanceRepo;
//
//    public function __construct(AttendanceRepository $attendanceRepo)
//    {
//        $this->attendanceRepo = $attendanceRepo;
//    }

    public function handle(): void
    {
        $userKeys = Redis::keys('attendance:*');

        foreach ($userKeys as $userKey) {
            DB::beginTransaction();

            try {
                Log::info('Processing user key: ' . $userKey);
                $key = explode(':', $userKey)[1];
                $redisKey = "attendance:$key";
                $attendanceRecords = Redis::hgetall($redisKey);
                Log::info('Attendance records (before array_values): ' . json_encode($attendanceRecords));

                $attendanceRecords = array_values($attendanceRecords);
                Log::info('Attendance records (after array_values): ' . json_encode($attendanceRecords));

                if (count($attendanceRecords) > 0) {
                    Log::info('Found ' . count($attendanceRecords) . ' attendance records for user ' . $userKey);

                    $firstRecord = json_decode($attendanceRecords[0], true);
                    $lastRecord = json_decode(end($attendanceRecords), true);

                    $date = date('Y-m-d', strtotime($firstRecord['check_in_time']));
                    Log::info('Date: ' . $date);

                    $attendance = new Attendance([
                        'user_id' => $firstRecord['user_id'],
                        'check_in_time' => $firstRecord['check_in_time'],
                        'check_out_time' => $lastRecord['check_in_time'],
                        'date' => $date
                    ]);

                    Log::info('Attendance object created');

                    $attendance->save();

                    Log::info('Attendance saved: ' . $attendance->check_in_time);
                }

                Redis::del($redisKey);

                DB::commit();
                Log::info('Transaction committed');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error syncing attendance to database: " . $e->getMessage());

                foreach ($attendanceRecords as $timestamp => $record) {
                    Redis::hset($userKey, $timestamp, json_encode($record));
                }

                Log::info('Transaction rolled back');
            }
        }
    }


}
