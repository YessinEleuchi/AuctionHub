<?php
namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $activationLink;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->activationLink = url('/api/verify-email/' . $user->id . '?token=' . sha1($user->email));
    }

    public function build()
    {
        return $this->subject('Verify Your Email Address')
                    ->markdown('emails.verify')
                    ->with([
                        'user' => $this->user,
                        'activationLink' => $this->activationLink,
                    ]);
    }
}
