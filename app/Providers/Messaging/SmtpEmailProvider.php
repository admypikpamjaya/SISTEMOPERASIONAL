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
        $context = $this->preparedMailContext();

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

    private function preparedMailContext(): array
    {
        $context = $this->mailContext();

        if ($this->usesLocalMailCatcher($context['host'])) {
            return $context;
        }

        if ($this->configurationIssues($context) === []) {
            return $context;
        }

        $reloadedContext = $this->reloadMailConfigurationFromDotEnv();

        return $reloadedContext ?? $context;
    }

    private function assertConfigurationIsReady(array $context): void
    {
        if ($this->usesLocalMailCatcher($context['host'])) {
            return;
        }

        $issues = $this->configurationIssues($context);

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
     * @return string[]
     */
    private function configurationIssues(array $context): array
    {
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

        return $issues;
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

    private function reloadMailConfigurationFromDotEnv(): ?array
    {
        $envPath = (string) config('mail.runtime_env_path', base_path('.env'));
        if ($envPath === '' || !is_file($envPath)) {
            return null;
        }

        $entries = $this->parseSimpleDotEnv($envPath);
        if ($entries === []) {
            return null;
        }

        $candidateContext = [
            'host' => $this->normalizeConfigValue((string) ($entries['MAIL_HOST'] ?? '')),
            'username' => $this->normalizeConfigValue((string) ($entries['MAIL_USERNAME'] ?? '')),
            'password' => $this->normalizeConfigValue((string) ($entries['MAIL_PASSWORD'] ?? '')),
            'from_address' => $this->normalizeConfigValue((string) ($entries['MAIL_FROM_ADDRESS'] ?? '')),
        ];

        if ($this->usesLocalMailCatcher($candidateContext['host'])) {
            return null;
        }

        if ($this->configurationIssues($candidateContext) !== []) {
            return null;
        }

        config([
            'mail.default' => $this->normalizeConfigValue((string) ($entries['MAIL_MAILER'] ?? config('mail.default', 'smtp'))),
            'mail.mailers.smtp.host' => $candidateContext['host'],
            'mail.mailers.smtp.port' => $this->normalizeConfigValue((string) ($entries['MAIL_PORT'] ?? config('mail.mailers.smtp.port'))),
            'mail.mailers.smtp.encryption' => $this->normalizeNullableConfigValue((string) ($entries['MAIL_ENCRYPTION'] ?? config('mail.mailers.smtp.encryption'))),
            'mail.mailers.smtp.username' => $candidateContext['username'],
            'mail.mailers.smtp.password' => $candidateContext['password'],
            'mail.mailers.smtp.local_domain' => $this->normalizeNullableConfigValue((string) ($entries['MAIL_EHLO_DOMAIN'] ?? config('mail.mailers.smtp.local_domain'))),
            'mail.from.address' => $candidateContext['from_address'],
            'mail.from.name' => $this->normalizeConfigValue((string) ($entries['MAIL_FROM_NAME'] ?? config('mail.from.name', ''))),
        ]);

        app('mail.manager')->purge('smtp');

        Log::warning('[SMTP EMAIL CONFIG RELOADED FROM ENV]', [
            'env_path' => $envPath,
            'host' => $candidateContext['host'],
            'username' => $candidateContext['username'],
            'from_address' => $candidateContext['from_address'],
        ]);

        return $this->mailContext();
    }

    /**
     * @return array<string, string>
     */
    private function parseSimpleDotEnv(string $path): array
    {
        $lines = @file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return [];
        }

        $entries = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $rawValue] = explode('=', $line, 2);

            $key = trim($key);
            if ($key === '') {
                continue;
            }

            $entries[$key] = $this->normalizeEnvFileValue($rawValue);
        }

        return $entries;
    }

    private function normalizeConfigValue(string $value): string
    {
        return trim($value, " \t\n\r\0\x0B\"'");
    }

    private function normalizeNullableConfigValue(string $value): ?string
    {
        $normalized = $this->normalizeConfigValue($value);
        $lowerValue = strtolower($normalized);

        if ($normalized === '' || $lowerValue === 'null') {
            return null;
        }

        return $normalized;
    }

    private function normalizeEnvFileValue(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $firstChar = $value[0];
        $lastChar = $value[strlen($value) - 1];

        if (($firstChar === '"' && $lastChar === '"') || ($firstChar === "'" && $lastChar === "'")) {
            return substr($value, 1, -1);
        }

        return $value;
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
