<?php

namespace Database\Seeders;

use App\Models\BlastMessageTemplate;
use Illuminate\Database\Seeder;

class BlastMessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        BlastMessageTemplate::query()->updateOrCreate(
            [
                'channel' => 'whatsapp',
                'name' => 'VA Maybank SPP Otomatis',
            ],
            [
                'content' => $this->maybankWhatsappTemplateContent(),
                'is_active' => true,
                'created_by' => null,
            ]
        );
    }

    private function maybankWhatsappTemplateContent(): string
    {
        return <<<'TEMPLATE'
🌿 Assalamu'alaikum Wr. Wb.
Yth. Bapak/Ibu Orang Tua/Wali Murid: 
1. KB-TKIA Al Azhar 24 Jatikramat
2. SD Islam Al Azhar 23 Jatikramat

Dengan hormat,

💳 Berikut kami sampaikan informasi Nomor Virtual Account (VA) Bank Maybank Indonesia untuk pembayaran SPP bulanan putra/putri Bapak/Ibu.

👤 Nama Siswa : {Nama_Siswa}
🏦 Nomor VA Maybank : {Nomor_VA}
💰 Nominal SPP : Rp {Nominal}

📲 Cara Pembayaran (m-Banking/Internet Banking Bank Lain):
1. Buka aplikasi m-Banking atau Internet Banking yang Anda gunakan.
2. Pilih menu Transfer Antarbank.
3. Pilih Bank Maybank Indonesia (Kode Bank: 016).
4. Masukkan Nomor Virtual Account di atas.
5. Masukkan nominal pembayaran sesuai tagihan.
6. Pastikan nama penerima dan nominal tagihan sudah sesuai, lalu masukkan PIN transaksi untuk menyelesaikan pembayaran.

Demikian informasi ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.

🌿 Wassalamu'alaikum Wr. Wb.

Hormat kami,
YPIK PAM JAYA
TEMPLATE;
    }
}
