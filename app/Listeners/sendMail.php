<?php

namespace App\Listeners;

use App\Events\UserEvent;
use App\Mail\emailMailableForNotify;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class sendMail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserEvent $event): void
    {
        $email='abeerosami1996@gmail.com';
        Mail::to($email)->send(new emailMailableForNotify($event->user));
    }
}
