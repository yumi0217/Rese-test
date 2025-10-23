<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerBroadcastMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $subjectText, public string $bodyText) {}

    public function build()
    {
        return $this->subject($this->subjectText)
            ->view('mail.owner_broadcast')
            ->with(['bodyText' => $this->bodyText]);
    }
}