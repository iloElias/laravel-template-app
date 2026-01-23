<?php

namespace App\Jobs;

use App\Facades\Sms;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSms implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $phoneNumber;

    public string $message;

    public int $tries;

    /**
     * Create a new job instance.
     */
    public function __construct(string $phoneNumber, string $message, int $tries = 1)
    {
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
        $this->tries = $tries;
    }

    /**
     * Execute the job to send the SMS.
     */
    public function handle(): void
    {
        Sms::to($this->phoneNumber, $this->message);
    }
}
