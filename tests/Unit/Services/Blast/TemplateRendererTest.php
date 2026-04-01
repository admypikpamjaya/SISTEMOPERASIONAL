<?php

namespace Tests\Unit\Services\Blast;

use App\Models\BlastRecipient;
use App\Services\Blast\TemplateRenderer;
use PHPUnit\Framework\TestCase;

class TemplateRendererTest extends TestCase
{
    public function test_render_replaces_default_recipient_placeholders(): void
    {
        $renderer = new TemplateRenderer();

        $recipient = new BlastRecipient([
            'nama_siswa' => 'Christo',
            'kelas' => 'Sistem Informasi',
            'nama_wali' => 'Ibu Christo',
            'email_wali' => 'wali@example.com',
            'wa_wali' => '6281234567890',
            'wa_wali_2' => null,
        ]);

        $template = 'Halo {nama_wali}, siswa {nama_siswa} kelas {kelas}.';

        $rendered = $renderer->render($template, $recipient);

        $this->assertSame(
            'Halo Ibu Christo, siswa Christo kelas Sistem Informasi.',
            $rendered
        );
    }

    public function test_render_supports_tunggakan_context_and_rupiah_format(): void
    {
        $renderer = new TemplateRenderer();

        $recipient = new BlastRecipient([
            'nama_siswa' => 'Audy',
            'kelas' => 'Sistem Informasi',
            'nama_wali' => 'Ibu Audy',
            'wa_wali' => '6281234500000',
        ]);

        $template = 'Tunggakan {nama_siswa} bulan {bulan_tunggakan} sebesar {nilai_tunggakan_rupiah}.';

        $rendered = $renderer->render($template, $recipient, [
            'bulan_tunggakan' => 'Januari-Februari',
            'nilai_tunggakan' => 20000000,
        ]);

        $this->assertSame(
            'Tunggakan Audy bulan Januari-Februari sebesar Rp 20.000.000.',
            $rendered
        );
    }

    public function test_render_supports_double_brace_placeholders_without_leaving_extra_braces(): void
    {
        $renderer = new TemplateRenderer();

        $recipient = new BlastRecipient([
            'nama_siswa' => 'Christopher Atera Putra',
            'kelas' => '4 SDIA 23 Jatikramat',
            'nama_wali' => 'Bapak Christopher',
            'wa_wali' => '6281234500000',
        ]);

        $template = 'SPP ananda {{ nama_siswa }} memiliki tunggakan sebesar {{ nominal_tunggakan_rupiah }}.';

        $rendered = $renderer->render($template, $recipient, [
            'nominal_tunggakan' => 150000,
        ]);

        $this->assertSame(
            'SPP ananda Christopher Atera Putra memiliki tunggakan sebesar Rp 150.000.',
            $rendered
        );
    }
}
