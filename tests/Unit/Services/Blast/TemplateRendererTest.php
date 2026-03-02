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
}
