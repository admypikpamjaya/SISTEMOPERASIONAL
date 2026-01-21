<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\EmailProviderInterface;
use App\DataTransferObjects\BlastPayload;
use Illuminate\Support\Facades\Mail;

class SmtpEmailProvider implements EmailProviderInterface
{
    public function send(
        string $to,
        string $subject,
        BlastPayload $payload
    ): bool {
        Mail::send([], [], function ($message) use ($to, $subject, $payload) {
            $message->to($to)
                ->subject($subject)
                ->html(nl2br(e($payload->message)));

            foreach ($payload->attachments as $attachment) {
                $message->attach(
                    $attachment->path,
                    [
                        'as'   => $attachment->filename,
                        'mime' => $attachment->mime,
                    ]
                );
            }
        });

        return true;
    }
}
