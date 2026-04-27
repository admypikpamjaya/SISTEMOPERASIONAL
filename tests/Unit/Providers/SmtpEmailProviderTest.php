<?php

namespace Tests\Unit\Providers;

use App\DataTransferObjects\BlastPayload;
use App\Mail\BlastMail;
use App\Providers\Messaging\SmtpEmailProvider;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class SmtpEmailProviderTest extends TestCase
{
    public function test_send_throws_clear_exception_when_smtp_username_is_placeholder(): void
    {
        config([
            'mail.runtime_env_path' => storage_path('framework/testing/missing-mail-runtime.env'),
        ]);

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'mail.pradita.website',
            'mail.mailers.smtp.port' => 465,
            'mail.mailers.smtp.encryption' => 'ssl',
            'mail.mailers.smtp.username' => 'ISI_MAIL_USER',
            'mail.mailers.smtp.password' => 'ISI_MAIL_PASSWORD',
            'mail.from.address' => 'ypik@pradita.website',
        ]);

        $provider = new SmtpEmailProvider();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MAIL_USERNAME masih placeholder ("ISI_MAIL_USER").');

        $provider->send(
            'Ridodwikurniawan@gmail.com',
            'Test SMTP Placeholder',
            new BlastPayload('Testing placeholder config')
        );
    }

    public function test_send_recovers_mail_configuration_from_env_file_when_runtime_config_is_stale(): void
    {
        Mail::fake();

        $envPath = storage_path('framework/testing/runtime-mail.env');
        if (!is_dir(dirname($envPath))) {
            mkdir(dirname($envPath), 0777, true);
        }

        file_put_contents($envPath, implode(PHP_EOL, [
            'MAIL_MAILER=smtp',
            'MAIL_HOST=mail.pradita.website',
            'MAIL_PORT=465',
            'MAIL_ENCRYPTION=ssl',
            'MAIL_USERNAME=ypik@pradita.website',
            'MAIL_PASSWORD=@Cookies123',
            'MAIL_FROM_ADDRESS=ypik@pradita.website',
            'MAIL_FROM_NAME="SOY YPIK"',
        ]));

        config([
            'mail.runtime_env_path' => $envPath,
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'ISI_MAIL_HOST',
            'mail.mailers.smtp.port' => 465,
            'mail.mailers.smtp.encryption' => 'ssl',
            'mail.mailers.smtp.username' => 'ISI_MAIL_USER',
            'mail.mailers.smtp.password' => 'ISI_MAIL_PASSWORD',
            'mail.from.address' => 'hello@example.com',
            'mail.from.name' => 'Example',
        ]);

        $provider = new SmtpEmailProvider();

        $result = $provider->send(
            'Ridodwikurniawan@gmail.com',
            'Test Runtime Env Recovery',
            new BlastPayload('Testing runtime mail env recovery')
        );

        $this->assertTrue($result);
        $this->assertSame('ypik@pradita.website', config('mail.mailers.smtp.username'));
        $this->assertSame('ypik@pradita.website', config('mail.from.address'));
        Mail::assertSent(BlastMail::class);

        @unlink($envPath);
    }

    public function test_send_allows_local_mail_catcher_configuration(): void
    {
        Mail::fake();

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'mailpit',
            'mail.mailers.smtp.port' => 1025,
            'mail.mailers.smtp.encryption' => null,
            'mail.mailers.smtp.username' => 'null',
            'mail.mailers.smtp.password' => 'null',
            'mail.from.address' => 'hello@example.com',
        ]);

        $provider = new SmtpEmailProvider();

        $result = $provider->send(
            'Ridodwikurniawan@gmail.com',
            'Test Local Mail Catcher',
            new BlastPayload('Testing local mail catcher')
        );

        $this->assertTrue($result);
        Mail::assertSent(BlastMail::class);
    }
}
