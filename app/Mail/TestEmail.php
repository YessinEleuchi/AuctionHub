<?php
use Illuminate\Mail\Mailable;

class TestEmail extends Mailable
{

    public $user;
    public $otp;

    public function __construct($user, $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject('Verify your email address')
                    ->view('emails.activation'); // Crée ce fichier dans resources/views/emails/activation.blade.php
    }
}
