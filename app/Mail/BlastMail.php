<?php

namespace App\Mail;

use App\DataTransferObjects\BlastPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BlastMail extends Mailable
{
    use Queueable, SerializesModels;

    public BlastPayload $payload;
    public string $subjectText;

    public function __construct(string $subject, BlastPayload $payload)
    {
        $this->subjectText = $subject;
        $this->payload = $payload;
    }

    public function build()
    {
        $mail = $this->subject($this->subjectText)
            ->view('emails.blast')
            ->with([
                'messageBody' => $this->payload->message,
                'meta' => $this->payload->meta,
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
