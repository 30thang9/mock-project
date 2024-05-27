<?php

namespace App\Console\Commands;

use App\Mail\NotificationMail;
use App\Models\User;
use Illuminate\Console\Command;
use App\Models\ScheduledNotification;
use App\Jobs\SendNotificationJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessScheduledNotifications extends Command
{
    protected $signature = 'process:scheduled-notifications';

    protected $description = 'Schedule notify';


    public function handle()
    {
        $notifications = ScheduledNotification::where('scheduled_at', '<=', Carbon::now()->setTimezone('Asia/Ho_Chi_Minh'))
            ->where('sent', false)
            ->get();

        foreach ($notifications as $notification) {
            $employees = User::all();
            foreach ($employees as $employee) {
                SendNotificationJob::dispatch($employee, $notification->title, $notification->message);
            }
            $notification->update(['sent' => true]);
        }

        return 0;
    }
}
