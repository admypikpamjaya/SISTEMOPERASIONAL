<?php

namespace App\DataTransferObjects\Blast;

class BlastRecipientDTO
{
    public function __construct(
        public string $nama_siswa,
        public string $kelas,
        public string $nama_wali,
        public ?string $wa_wali,
        public ?string $email_wali,
        public ?string $catatan = null,
    ) {}

    public function toArray(): array
    {
        return [
            'nama_siswa'  => $this->nama_siswa,
            'kelas'       => $this->kelas,
            'nama_wali'   => $this->nama_wali,
            'wa_wali'     => $this->wa_wali,
            'email_wali'  => $this->email_wali,
            'catatan'     => $this->catatan,
        ];
    }
}
