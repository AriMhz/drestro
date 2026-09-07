<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StaffInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inviteUrl;
    public $restaurantName;
    public $role;

    public function __construct($inviteUrl, $restaurantName, $role)
    {
        $this->inviteUrl = $inviteUrl;
        $this->restaurantName = $restaurantName;
        $this->role = $role;
    }

    public function build()
    {
        return $this->subject("You're invited to join {$this->restaurantName}!")
                    ->view('emails.staff-invitation');
    }
}
