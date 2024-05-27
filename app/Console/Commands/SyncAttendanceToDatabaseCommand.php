<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncAttendanceToDatabaseJob;

class SyncAttendanceToDatabaseCommand extends Command
{
    protected $signature = 'sync:attendance';
    protected $description = 'Sync attendance records from Redis to the database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        SyncAttendanceToDatabaseJob::dispatch();
        $this->info('Attendance synchronization job dispatched successfully.');
    }
}
