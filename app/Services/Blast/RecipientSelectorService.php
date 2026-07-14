<?php

namespace App\Services\Blast;

use App\Models\BlastEmployeeRecipient;
use App\Models\BlastEmployeeYpikRecipient;
use App\Models\BlastGeneralRecipient;
use App\Models\BlastRecipient;
use Illuminate\Support\Collection;

class RecipientSelectorService
{
    /**
     * channel: email | whatsapp
     * return: collection of recipients (tanpa catatan)
     */
    public function getSelectable(string $channel): Collection
    {
        $studentQuery = BlastRecipient::query()
            ->where('is_valid', true);

        if ($channel === 'email') {
            $studentQuery->whereNotNull('email_wali');
        }

        if ($channel === 'whatsapp') {
            $studentQuery->where(function ($builder) {
                $builder->whereNotNull('wa_wali')
                    ->orWhereNotNull('wa_wali_2');
            });
        }

        $students = $studentQuery
            ->orderBy('nama_siswa')
            ->get([
                'id',
                'nama_siswa',
                'kelas',
                'nama_wali',
                'email_wali',
                'wa_wali',
                'wa_wali_2',
            ])
            ->map(fn (BlastRecipient $recipient) => $recipient->toArray())
            ->values()
            ->toBase();

        $employeeQuery = BlastEmployeeRecipient::query()
            ->where('is_valid', true);

        if ($channel === 'email') {
            $employeeQuery->whereNotNull('email_karyawan');
        }

        if ($channel === 'whatsapp') {
            $employeeQuery->whereNotNull('wa_karyawan');
        }

        $employees = $employeeQuery
            ->orderBy('nama_karyawan')
            ->get()
            ->map(function (BlastEmployeeRecipient $employee) {
                return [
                    'id' => $employee->id,
                    'nama_siswa' => $employee->nama_karyawan,
                    'kelas' => $employee->instansi ?: 'Karyawan',
                    'nama_wali' => $employee->nama_wali ?: $employee->nama_karyawan,
                    'email_wali' => $employee->email_karyawan,
                    'wa_wali' => $employee->wa_karyawan,
                    'wa_wali_2' => null,
                ];
            })
            ->values()
            ->toBase();

        $employeeYpikQuery = BlastEmployeeYpikRecipient::query()
            ->where('is_valid', true);

        if ($channel === 'email') {
            $employeeYpikQuery->whereNotNull('email_karyawan');
        }

        if ($channel === 'whatsapp') {
            $employeeYpikQuery->whereNotNull('wa_karyawan');
        }

        $employeesYpik = $employeeYpikQuery
            ->orderBy('nama_karyawan')
            ->get()
            ->map(function (BlastEmployeeYpikRecipient $employee) {
                return [
                    'id' => $employee->id,
                    'nama_siswa' => $employee->nama_karyawan,
                    'kelas' => $employee->instansi ?: 'Karyawan YPIK',
                    'nama_wali' => $employee->nama_wali ?: $employee->nama_karyawan,
                    'email_wali' => $employee->email_karyawan,
                    'wa_wali' => $employee->wa_karyawan,
                    'wa_wali_2' => null,
                ];
            })
            ->values()
            ->toBase();

        $generalQuery = BlastGeneralRecipient::query()
            ->where('is_valid', true);

        if ($channel === 'email') {
            $generalQuery->whereNotNull('email');
        }

        if ($channel === 'whatsapp') {
            $generalQuery->whereNotNull('whatsapp');
        }

        $generalRecipients = $generalQuery
            ->orderBy('nama')
            ->get()
            ->map(fn (BlastGeneralRecipient $recipient) => $this->mapGeneralRecipient($recipient))
            ->values()
            ->toBase();

        return $students
            ->merge($employees)
            ->merge($employeesYpik)
            ->merge($generalRecipients)
            ->values();
    }

    /**
     * MULTIPLE selector by IDs
     */
    public function getByIds(array $ids): Collection
    {
        $students = BlastRecipient::query()
            ->whereIn('id', $ids)
            ->where('is_valid', true)
            ->get([
                'id',
                'nama_siswa',
                'kelas',
                'nama_wali',
                'email_wali',
                'wa_wali',
                'wa_wali_2',
            ])
            ->map(fn (BlastRecipient $recipient) => $recipient->toArray())
            ->values()
            ->toBase();

        $employees = BlastEmployeeRecipient::query()
            ->whereIn('id', $ids)
            ->where('is_valid', true)
            ->get()
            ->map(function (BlastEmployeeRecipient $employee) {
                return [
                    'id' => $employee->id,
                    'nama_siswa' => $employee->nama_karyawan,
                    'kelas' => $employee->instansi ?: 'Karyawan',
                    'nama_wali' => $employee->nama_wali ?: $employee->nama_karyawan,
                    'email_wali' => $employee->email_karyawan,
                    'wa_wali' => $employee->wa_karyawan,
                    'wa_wali_2' => null,
                ];
            })
            ->values()
            ->toBase();

        $employeesYpik = BlastEmployeeYpikRecipient::query()
            ->whereIn('id', $ids)
            ->where('is_valid', true)
            ->get()
            ->map(function (BlastEmployeeYpikRecipient $employee) {
                return [
                    'id' => $employee->id,
                    'nama_siswa' => $employee->nama_karyawan,
                    'kelas' => $employee->instansi ?: 'Karyawan YPIK',
                    'nama_wali' => $employee->nama_wali ?: $employee->nama_karyawan,
                    'email_wali' => $employee->email_karyawan,
                    'wa_wali' => $employee->wa_karyawan,
                    'wa_wali_2' => null,
                ];
            })
            ->values()
            ->toBase();

        $generalRecipients = BlastGeneralRecipient::query()
            ->whereIn('id', $ids)
            ->where('is_valid', true)
            ->get()
            ->map(fn (BlastGeneralRecipient $recipient) => $this->mapGeneralRecipient($recipient))
            ->values()
            ->toBase();

        return $students
            ->merge($employees)
            ->merge($employeesYpik)
            ->merge($generalRecipients)
            ->values();
    }

    private function mapGeneralRecipient(BlastGeneralRecipient $recipient): array
    {
        $eventName = trim((string) ($recipient->event_name ?? ''));
        $institution = trim((string) ($recipient->instansi ?? ''));

        return [
            'id' => $recipient->id,
            'nama_siswa' => $recipient->nama,
            'kelas' => $eventName !== '' ? $eventName : ($institution !== '' ? $institution : 'Umum'),
            'nama_wali' => $recipient->nama,
            'email_wali' => $recipient->email,
            'wa_wali' => $recipient->whatsapp,
            'wa_wali_2' => null,
            'sertifikat' => $recipient->sertifikat,
            'event_name' => $eventName !== '' ? $eventName : null,
            'source' => 'umum',
        ];
    }
}
