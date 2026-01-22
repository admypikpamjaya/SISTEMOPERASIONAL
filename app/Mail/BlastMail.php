<?php

namespace App\Mail;

use App\DataTransferObjects\BlastPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BlastMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectText,
        public BlastPayload $payload
    ) {}

    public function build()
    {
        $mail = $this
            ->subject($this->subjectText)
            ->view('emails.blast', [
                'messageContent' => $this->payload->message,
            ]);

        foreach ($this->payload->attachments as $attachment) {
            $mail->attach(
                $attachment->path,
                [
                    'as' => $attachment->filename,
                    'mime' => $attachment->mime,
                ]
            );
        }

        return $mail;
    }
}
