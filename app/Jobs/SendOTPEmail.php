<?php

namespace App\Jobs;

use App\Mail\OTPMail;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOTPEmail implements ShouldQueue
{
    use Queueable;

    protected $user, $otp, $subject;
    /**
     * Create a new job instance.
     */
    public function __construct($user, $otp, $subject)
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->subject = $subject;
        // Log::info("OTP email __construct ");
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Log::info("Sending OTP email");
            Mail::to($this->user->email)->send(new OTPMail($this->subject, $this->otp, $this->user));
        }catch(Exception $e) {
            Log::error("OTP EMAIL Sending:".$e->getMessage());
            throw $e;
        }
    }
}
