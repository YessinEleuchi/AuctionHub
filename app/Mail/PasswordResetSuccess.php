<?php
use Illuminate\Mail\Mailable;

class PasswordResetSuccess extends Mailable
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Your Password Has Been Reset')
                    ->view('emails.password-reset-success');
    }
}
