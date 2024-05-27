<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationMail;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $title;
    protected $message;

    public function __construct($user, $title, $message)
    {
        $this->user = $user;
        $this->title = $title;
        $this->message = $message;
    }

    public function handle()
    {
        Log::info('Sending email to: ' . $this->user->email);
        Log::info('Email title: ' . $this->title);
        Log::info('Email message: ' . $this->message);
        Mail::to($this->user->email)->send(new NotificationMail($this->title, $this->message));
    }
}
