<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\EmailProviderInterface;
use App\DataTransferObjects\BlastPayload;
use App\Mail\BlastMail;
use App\Models\BlastEmailAccount;
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
        $context = $this->preparedMailContext($payload);

        $this->assertConfigurationIsReady($context);

        try {
            $mail = new BlastMail($subject, $payload);
            if ($context['from_address'] !== '') {
                $mail->from(
                    $context['from_address'],
                    $context['from_name'] !== '' ? $context['from_name'] : null
                );
            }

            if (!empty($context['mailer_name'])) {
                Mail::mailer($context['mailer_name'])
                    ->to($to)
                    ->send($mail);
            } else {
                Mail::to($to)->send($mail);
            }

            Log::info('[SMTP EMAIL SENT]', [
                'to' => $to,
                'subject' => $subject,
                'source' => $context['source'],
                'account_id' => $context['account_id'],
                'account_label' => $context['account_label'],
                'host' => $context['host'],
                'username' => $context['username'],
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('[SMTP EMAIL FAILED]', [
                'to' => $to,
                'source' => $context['source'],
                'account_id' => $context['account_id'],
                'account_label' => $context['account_label'],
                'host' => $context['host'],
                'username' => $context['username'],
                'from_address' => $context['from_address'],
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function preparedMailContext(BlastPayload $payload): array
    {
        $account = $this->resolvePayloadEmailAccount($payload);

        if ($account !== null) {
            return $this->mailContextFromAccount($account);
        }

        return $this->preparedConfigMailContext();
    }

    private function preparedConfigMailContext(): array
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
            'source' => $context['source'],
            'account_id' => $context['account_id'],
            'account_label' => $context['account_label'],
            'host' => $context['host'],
            'username' => $context['username'],
            'from_address' => $context['from_address'],
            'mailer' => config('mail.default'),
            'issues' => $issues,
        ]);

        throw new RuntimeException(
            'Konfigurasi SMTP belum valid. '
            . implode(' ', $issues)
            . ' Silakan perbarui pengaturan email pengirim atau env mail di server.'
        );
    }

    /**
     * @return string[]
     */
    private function configurationIssues(array $context): array
    {
        $issues = [];

        if ($context['host'] === '') {
            $issues[] = $context['source'] === 'account'
                ? 'Host SMTP akun email belum diisi.'
                : 'MAIL_HOST belum diisi.';
        }

        if ($this->isPlaceholderValue($context['host'])) {
            $issues[] = $context['source'] === 'account'
                ? sprintf('Host SMTP akun email masih placeholder ("%s").', $context['host'])
                : sprintf('MAIL_HOST masih placeholder ("%s").', $context['host']);
        }

        if ($context['username'] === '') {
            $issues[] = $context['source'] === 'account'
                ? 'Username SMTP akun email belum diisi.'
                : 'MAIL_USERNAME belum diisi.';
        }

        if ($this->isPlaceholderValue($context['username'])) {
            $issues[] = $context['source'] === 'account'
                ? sprintf('Username SMTP akun email masih placeholder ("%s").', $context['username'])
                : sprintf('MAIL_USERNAME masih placeholder ("%s").', $context['username']);
        }

        if ($context['password'] === '') {
            $issues[] = $context['source'] === 'account'
                ? 'Password SMTP akun email belum diisi.'
                : 'MAIL_PASSWORD belum diisi.';
        }

        if ($this->isPlaceholderValue($context['password'])) {
            $issues[] = $context['source'] === 'account'
                ? 'Password SMTP akun email masih placeholder.'
                : 'MAIL_PASSWORD masih placeholder.';
        }

        if ($context['from_address'] === '') {
            $issues[] = $context['source'] === 'account'
                ? 'Alamat pengirim akun email belum diisi.'
                : 'MAIL_FROM_ADDRESS belum diisi.';
        }

        if ($this->isPlaceholderAddress($context['from_address'])) {
            $issues[] = $context['source'] === 'account'
                ? sprintf('Alamat pengirim akun email masih placeholder ("%s").', $context['from_address'])
                : sprintf('MAIL_FROM_ADDRESS masih placeholder ("%s").', $context['from_address']);
        }

        return $issues;
    }

    /**
     * @return array{
     *     host:string,
     *     port:int,
     *     encryption:?string,
     *     username:string,
     *     password:string,
     *     from_address:string,
     *     from_name:string,
     *     local_domain:?string,
     *     source:string,
     *     account_id:?string,
     *     account_label:?string,
     *     mailer_name:?string
     * }
     */
    private function mailContext(): array
    {
        return [
            'host' => $this->normalizeConfigValue((string) config('mail.mailers.smtp.host', '')),
            'port' => (int) config('mail.mailers.smtp.port', 587),
            'encryption' => $this->normalizeNullableConfigValue((string) config('mail.mailers.smtp.encryption', '')),
            'username' => $this->normalizeConfigValue((string) config('mail.mailers.smtp.username', '')),
            'password' => $this->normalizeConfigValue((string) config('mail.mailers.smtp.password', '')),
            'from_address' => $this->normalizeConfigValue((string) config('mail.from.address', '')),
            'from_name' => $this->normalizeConfigValue((string) config('mail.from.name', '')),
            'local_domain' => $this->normalizeNullableConfigValue((string) config('mail.mailers.smtp.local_domain', '')),
            'source' => 'config',
            'account_id' => null,
            'account_label' => null,
            'mailer_name' => null,
        ];
    }

    private function mailContextFromAccount(BlastEmailAccount $account): array
    {
        $context = [
            'host' => $this->normalizeConfigValue((string) $account->host),
            'port' => (int) $account->port,
            'encryption' => $this->normalizeNullableConfigValue((string) $account->encryption),
            'username' => $this->normalizeConfigValue((string) ($account->username ?: $account->email_address)),
            'password' => $this->normalizeConfigValue((string) $account->password),
            'from_address' => $this->normalizeConfigValue((string) $account->email_address),
            'from_name' => $this->normalizeConfigValue((string) ($account->from_name ?: $account->label)),
            'local_domain' => $this->normalizeNullableConfigValue((string) config('mail.mailers.smtp.local_domain', '')),
            'source' => 'account',
            'account_id' => (string) $account->id,
            'account_label' => $account->senderLabel(),
            'mailer_name' => null,
        ];

        $context['mailer_name'] = $this->configureAccountMailer($context);

        return $context;
    }

    private function resolvePayloadEmailAccount(BlastPayload $payload): ?BlastEmailAccount
    {
        $accountId = trim((string) ($payload->meta['email_account_id'] ?? ''));

        if ($accountId === '') {
            return null;
        }

        $account = BlastEmailAccount::query()
            ->enabled()
            ->whereKey($accountId)
            ->first();

        if ($account === null) {
            throw new RuntimeException('Akun email pengirim tidak ditemukan atau sedang nonaktif.');
        }

        return $account;
    }

    private function configureAccountMailer(array $context): string
    {
        $mailerName = 'blast_email_account_' . str_replace('-', '_', (string) $context['account_id']);

        config([
            "mail.mailers.{$mailerName}" => [
                'transport' => 'smtp',
                'host' => $context['host'],
                'port' => $context['port'],
                'encryption' => $context['encryption'],
                'username' => $context['username'] !== '' ? $context['username'] : null,
                'password' => $context['password'] !== '' ? $context['password'] : null,
                'timeout' => null,
                'local_domain' => $context['local_domain'],
            ],
        ]);

        app('mail.manager')->purge($mailerName);

        return $mailerName;
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
            'port' => (int) $this->normalizeConfigValue((string) ($entries['MAIL_PORT'] ?? config('mail.mailers.smtp.port', 587))),
            'encryption' => $this->normalizeNullableConfigValue((string) ($entries['MAIL_ENCRYPTION'] ?? config('mail.mailers.smtp.encryption'))),
            'username' => $this->normalizeConfigValue((string) ($entries['MAIL_USERNAME'] ?? '')),
            'password' => $this->normalizeConfigValue((string) ($entries['MAIL_PASSWORD'] ?? '')),
            'from_address' => $this->normalizeConfigValue((string) ($entries['MAIL_FROM_ADDRESS'] ?? '')),
            'from_name' => $this->normalizeConfigValue((string) ($entries['MAIL_FROM_NAME'] ?? config('mail.from.name', ''))),
            'local_domain' => $this->normalizeNullableConfigValue((string) ($entries['MAIL_EHLO_DOMAIN'] ?? config('mail.mailers.smtp.local_domain'))),
            'source' => 'config',
            'account_id' => null,
            'account_label' => null,
            'mailer_name' => null,
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
            'mail.mailers.smtp.port' => $candidateContext['port'],
            'mail.mailers.smtp.encryption' => $candidateContext['encryption'],
            'mail.mailers.smtp.username' => $candidateContext['username'],
            'mail.mailers.smtp.password' => $candidateContext['password'],
            'mail.mailers.smtp.local_domain' => $candidateContext['local_domain'],
            'mail.from.address' => $candidateContext['from_address'],
            'mail.from.name' => $candidateContext['from_name'],
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
