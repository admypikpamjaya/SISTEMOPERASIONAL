<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\EmailProviderInterface;
use App\DataTransferObjects\BlastPayload;
use App\Mail\BlastMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SmtpEmailProvider implements EmailProviderInterface
{
    public function send(
        string $to,
        string $subject,
        BlastPayload $payload
    ): bool {
        $context = $this->mailContext();

        $this->assertConfigurationIsReady($context);

        try {
            Mail::to($to)->send(
                new BlastMail($subject, $payload)
            );

            Log::info('[SMTP EMAIL SENT]', [
                'to' => $to,
                'subject' => $subject,
                'host' => $context['host'],
                'username' => $context['username'],
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('[SMTP EMAIL FAILED]', [
                'to' => $to,
                'host' => $context['host'],
                'username' => $context['username'],
                'from_address' => $context['from_address'],
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function assertConfigurationIsReady(array $context): void
    {
        if ($this->usesLocalMailCatcher($context['host'])) {
            return;
        }

        $issues = [];

        if ($this->isPlaceholderValue($context['host'])) {
            $issues[] = sprintf('MAIL_HOST masih placeholder ("%s").', $context['host']);
        }

        if ($this->isPlaceholderValue($context['username'])) {
            $issues[] = sprintf('MAIL_USERNAME masih placeholder ("%s").', $context['username']);
        }

        if ($this->isPlaceholderValue($context['password'])) {
            $issues[] = 'MAIL_PASSWORD masih placeholder.';
        }

        if ($this->isPlaceholderAddress($context['from_address'])) {
            $issues[] = sprintf('MAIL_FROM_ADDRESS masih placeholder ("%s").', $context['from_address']);
        }

        if ($issues === []) {
            return;
        }

        Log::error('[SMTP EMAIL CONFIG INVALID]', [
            'host' => $context['host'],
            'username' => $context['username'],
            'from_address' => $context['from_address'],
            'mailer' => config('mail.default'),
            'issues' => $issues,
        ]);

        throw new RuntimeException(
            'Konfigurasi SMTP belum valid. '
            . implode(' ', $issues)
            . ' Silakan perbarui env mail di server lalu refresh cache konfigurasi.'
        );
    }

    /**
     * @return array{
     *     host:string,
     *     username:string,
     *     password:string,
     *     from_address:string
     * }
     */
    private function mailContext(): array
    {
        return [
            'host' => $this->normalizeConfigValue((string) config('mail.mailers.smtp.host', '')),
            'username' => $this->normalizeConfigValue((string) config('mail.mailers.smtp.username', '')),
            'password' => $this->normalizeConfigValue((string) config('mail.mailers.smtp.password', '')),
            'from_address' => $this->normalizeConfigValue((string) config('mail.from.address', '')),
        ];
    }

    private function normalizeConfigValue(string $value): string
    {
        return trim($value, " \t\n\r\0\x0B\"'");
    }

    private function usesLocalMailCatcher(string $host): bool
    {
        return in_array(strtolower($host), ['mailpit', 'mailhog', 'localhost', '127.0.0.1'], true);
    }

    private function isPlaceholderValue(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        $upperValue = strtoupper($value);

        return str_starts_with($upperValue, 'ISI_')
            || str_starts_with($upperValue, 'YOUR_')
            || str_contains($upperValue, 'CHANGE_ME')
            || in_array($upperValue, ['NULL', 'MAIL_USERNAME', 'MAIL_PASSWORD'], true);
    }

    private function isPlaceholderAddress(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        return $this->isPlaceholderValue($value)
            || in_array(strtolower($value), [
                'hello@example.com',
                'example@example.com',
                'noreply@example.com',
            ], true);
    }
}
