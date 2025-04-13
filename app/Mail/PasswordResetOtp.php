<?php
use Illuminate\Mail\Mailable;

class PasswordResetOtp extends Mailable
{
    public $user, $otp;

    public function __construct($user, $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject('Reset Your Password')
                    ->view('emails.password-reset-otp');
    }
}
