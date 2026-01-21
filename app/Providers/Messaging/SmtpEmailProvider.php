<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\EmailProviderInterface;
use App\DataTransferObjects\BlastPayload;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SmtpEmailProvider implements EmailProviderInterface
{
    public function send(string $to, string $subject, BlastPayload $payload): bool
    {
        try {
            Mail::send([], [], function ($message) use ($to, $subject, $payload) {
                $message->to($to)
                    ->subject($subject)
                    ->html($payload->message);

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

            Log::info('[SMTP EMAIL SENT]', [
                'to' => $to,
                'subject' => $subject,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('[SMTP EMAIL FAILED]', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
