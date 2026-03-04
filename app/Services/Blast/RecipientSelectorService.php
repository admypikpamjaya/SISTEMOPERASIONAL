<?php

namespace App\Services\Blast;

use App\Models\BlastEmployeeRecipient;
use App\Models\BlastEmployeeYpikRecipient;
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
            ->map(fn (BlastRecipient $recipient) => $recipient->toArray());

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
            });

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
            });

        return $students
            ->merge($employees)
            ->merge($employeesYpik)
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
            ->map(fn (BlastRecipient $recipient) => $recipient->toArray());

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
            });

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
            });

        return $students
            ->merge($employees)
            ->merge($employeesYpik)
            ->values();
    }
}
