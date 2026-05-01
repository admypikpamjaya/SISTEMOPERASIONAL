<?php

namespace App\Services\Recipient;

class ContactValueNormalizer
{
    /**
     * @return array{value:?string,error:?string,autofilled:bool}
     */
    public function normalizeEmail(?string $value, bool $autoCompleteDomain = false): array
    {
        $email = trim((string) $value);

        if ($email === '') {
            return [
                'value' => null,
                'error' => null,
                'autofilled' => false,
            ];
        }

        $email = strtolower(str_replace(' ', '', $email));
        $autofilled = false;

        if ($autoCompleteDomain) {
            $defaultDomain = trim(
                (string) config('blast.import.default_email_domain', 'gmail.com'),
                " \t\n\r\0\x0B@"
            );

            if ($defaultDomain !== '') {
                if (!str_contains($email, '@')) {
                    $email .= '@' . $defaultDomain;
                    $autofilled = true;
                } elseif (str_ends_with($email, '@')) {
                    $email .= $defaultDomain;
                    $autofilled = true;
                }
            }
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'value' => null,
                'error' => 'format email tidak valid',
                'autofilled' => $autofilled,
            ];
        }

        return [
            'value' => $email,
            'error' => null,
            'autofilled' => $autofilled,
        ];
    }

    /**
     * @return array{value:?string,error:?string}
     */
    public function normalizeWhatsapp(?string $value): array
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return [
                'value' => null,
                'error' => null,
            ];
        }

        $hasInternationalPrefix = str_starts_with($raw, '+');
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if ($digits === '') {
            return [
                'value' => null,
                'error' => 'format WhatsApp tidak valid',
            ];
        }

        if (!$hasInternationalPrefix && str_starts_with($digits, '021')) {
            return [
                'value' => null,
                'error' => 'nomor telepon rumah tidak bisa digunakan untuk WhatsApp',
            ];
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (!$hasInternationalPrefix && str_starts_with($digits, '08')) {
            $digits = '62' . substr($digits, 1);
        } elseif (!$hasInternationalPrefix && str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        } elseif (!$hasInternationalPrefix && str_starts_with($digits, '0')) {
            return [
                'value' => null,
                'error' => 'format WhatsApp tidak valid',
            ];
        }

        $length = strlen($digits);
        if ($length < 10 || $length > 15) {
            return [
                'value' => null,
                'error' => 'format WhatsApp tidak valid',
            ];
        }

        return [
            'value' => $digits,
            'error' => null,
        ];
    }
}
