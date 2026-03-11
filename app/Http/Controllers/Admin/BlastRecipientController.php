<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlastEmployeeRecipient;
use App\Models\BlastEmployeeYpikRecipient;
use App\Models\BlastRecipient;
use App\Services\Recipient\EmployeeRecipientBulkSaver;
use App\Services\Recipient\EmployeeRecipientNormalizer;
use App\Services\Recipient\EmployeeYpikRecipientBulkSaver;
use App\Services\Recipient\ExcelImportService;
use App\Services\Recipient\RecipientBulkSaver;
use App\Services\Recipient\RecipientNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlastRecipientController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'kelas' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:500',
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $selectedClass = trim((string) ($validated['kelas'] ?? ''));
        $allowedPerPage = [20, 50, 100, 200];
        $perPage = (int) ($validated['per_page'] ?? 50);

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 50;
        }

        $query = BlastRecipient::query();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_siswa', 'like', '%' . $search . '%')
                    ->orWhere('kelas', 'like', '%' . $search . '%')
                    ->orWhere('nama_wali', 'like', '%' . $search . '%')
                    ->orWhere('wa_wali', 'like', '%' . $search . '%')
                    ->orWhere('wa_wali_2', 'like', '%' . $search . '%')
                    ->orWhere('email_wali', 'like', '%' . $search . '%');
            });
        }

        if ($selectedClass !== '') {
            $query->where('kelas', $selectedClass);
        }

        $recipients = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $kelasOptions = BlastRecipient::query()
            ->select('kelas')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->orderBy('kelas')
            ->pluck('kelas');

        $baseStatsQuery = BlastRecipient::query();
        $totalRecipients = (clone $baseStatsQuery)->count();
        $completeCount = (clone $baseStatsQuery)
            ->whereNotNull('nama_siswa')
            ->whereNotNull('kelas')
            ->whereNotNull('nama_wali')
            ->where(function ($query) {
                $query->whereNotNull('wa_wali')
                    ->orWhereNotNull('wa_wali_2');
            })
            ->whereNotNull('email_wali')
            ->count();
        $incompleteCount = max(0, $totalRecipients - $completeCount);
        $validCount = (clone $baseStatsQuery)
            ->where('is_valid', true)
            ->count();

        return view('admin.blast.recipients.index', compact(
            'recipients',
            'kelasOptions',
            'search',
            'selectedClass',
            'allowedPerPage',
            'perPage',
            'totalRecipients',
            'completeCount',
            'incompleteCount',
            'validCount'
        ));
    }

    public function employeeIndex(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'instansi' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:500',
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $selectedInstansi = trim((string) ($validated['instansi'] ?? ''));
        $allowedPerPage = [20, 50, 100, 200];
        $perPage = (int) ($validated['per_page'] ?? 50);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 50;
        }

        $query = BlastEmployeeRecipient::query();
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_karyawan', 'like', '%' . $search . '%')
                    ->orWhere('instansi', 'like', '%' . $search . '%')
                    ->orWhere('nama_wali', 'like', '%' . $search . '%')
                    ->orWhere('wa_karyawan', 'like', '%' . $search . '%')
                    ->orWhere('email_karyawan', 'like', '%' . $search . '%');
            });
        }

        if ($selectedInstansi !== '') {
            $query->where('instansi', $selectedInstansi);
        }

        $employees = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $instansiOptions = BlastEmployeeRecipient::query()
            ->select('instansi')
            ->whereNotNull('instansi')
            ->where('instansi', '!=', '')
            ->distinct()
            ->orderBy('instansi')
            ->pluck('instansi');

        $baseStatsQuery = BlastEmployeeRecipient::query();
        $totalEmployees = (clone $baseStatsQuery)->count();
        $validCount = (clone $baseStatsQuery)->where('is_valid', true)->count();
        $incompleteCount = (clone $baseStatsQuery)
            ->where(function ($query) {
                $query->whereNull('wa_karyawan')->orWhere('wa_karyawan', '');
            })
            ->where(function ($query) {
                $query->whereNull('email_karyawan')->orWhere('email_karyawan', '');
            })
            ->count();

        return view('admin.blast.recipients.employees', compact(
            'employees',
            'instansiOptions',
            'search',
            'selectedInstansi',
            'allowedPerPage',
            'perPage',
            'totalEmployees',
            'validCount',
            'incompleteCount'
        ));
    }

    public function employeeYpikIndex(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'instansi' => 'nullable|string|max:255',
            'status' => 'nullable|in:all,valid,invalid',
            'per_page' => 'nullable|integer|min:1|max:500',
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $selectedInstansi = trim((string) ($validated['instansi'] ?? ''));
        $selectedStatus = strtolower((string) ($validated['status'] ?? 'all'));
        $selectedDataset = 'ypik';
        $allowedPerPage = [20, 50, 100, 200];
        $perPage = (int) ($validated['per_page'] ?? 50);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 50;
        }

        $query = BlastEmployeeYpikRecipient::query()
            ->where('dataset', $selectedDataset);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_karyawan', 'like', '%' . $search . '%')
                    ->orWhere('instansi', 'like', '%' . $search . '%')
                    ->orWhere('nama_wali', 'like', '%' . $search . '%')
                    ->orWhere('wa_karyawan', 'like', '%' . $search . '%')
                    ->orWhere('email_karyawan', 'like', '%' . $search . '%');
            });
        }

        if ($selectedInstansi !== '') {
            $query->where('instansi', $selectedInstansi);
        }

        if ($selectedStatus === 'valid') {
            $query->where('is_valid', true);
        } elseif ($selectedStatus === 'invalid') {
            $query->where('is_valid', false);
        }

        $employees = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $instansiOptions = BlastEmployeeYpikRecipient::query()
            ->select('instansi')
            ->whereNotNull('instansi')
            ->where('instansi', '!=', '')
            ->where('dataset', $selectedDataset)
            ->distinct()
            ->orderBy('instansi')
            ->pluck('instansi');

        $baseStatsQuery = BlastEmployeeYpikRecipient::query()
            ->where('dataset', $selectedDataset);
        $totalEmployees = (clone $baseStatsQuery)->count();
        $validCount = (clone $baseStatsQuery)->where('is_valid', true)->count();
        $incompleteCount = (clone $baseStatsQuery)
            ->where(function ($query) {
                $query->whereNull('wa_karyawan')->orWhere('wa_karyawan', '');
            })
            ->where(function ($query) {
                $query->whereNull('email_karyawan')->orWhere('email_karyawan', '');
            })
            ->count();

        return view('admin.blast.recipients.employees-ypik', compact(
            'employees',
            'instansiOptions',
            'search',
            'selectedInstansi',
            'selectedStatus',
            'selectedDataset',
            'allowedPerPage',
            'perPage',
            'totalEmployees',
            'validCount',
            'incompleteCount'
        ));
    }

    public function employeeYpikPamJayaIndex(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'instansi' => 'nullable|string|max:255',
            'status' => 'nullable|in:all,valid,invalid',
            'per_page' => 'nullable|integer|min:1|max:500',
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $selectedInstansi = trim((string) ($validated['instansi'] ?? ''));
        $selectedStatus = strtolower((string) ($validated['status'] ?? 'all'));
        $selectedDataset = 'pam_jaya';
        $allowedPerPage = [20, 50, 100, 200];
        $perPage = (int) ($validated['per_page'] ?? 50);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 50;
        }

        $query = BlastEmployeeYpikRecipient::query()
            ->where('dataset', $selectedDataset);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_karyawan', 'like', '%' . $search . '%')
                    ->orWhere('instansi', 'like', '%' . $search . '%')
                    ->orWhere('nama_wali', 'like', '%' . $search . '%')
                    ->orWhere('wa_karyawan', 'like', '%' . $search . '%')
                    ->orWhere('email_karyawan', 'like', '%' . $search . '%');
            });
        }

        if ($selectedInstansi !== '') {
            $query->where('instansi', $selectedInstansi);
        }

        if ($selectedStatus === 'valid') {
            $query->where('is_valid', true);
        } elseif ($selectedStatus === 'invalid') {
            $query->where('is_valid', false);
        }

        $employees = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $instansiOptions = BlastEmployeeYpikRecipient::query()
            ->select('instansi')
            ->whereNotNull('instansi')
            ->where('instansi', '!=', '')
            ->where('dataset', $selectedDataset)
            ->distinct()
            ->orderBy('instansi')
            ->pluck('instansi');

        $baseStatsQuery = BlastEmployeeYpikRecipient::query()
            ->where('dataset', $selectedDataset);
        $totalEmployees = (clone $baseStatsQuery)->count();
        $validCount = (clone $baseStatsQuery)->where('is_valid', true)->count();
        $incompleteCount = (clone $baseStatsQuery)
            ->where(function ($query) {
                $query->whereNull('wa_karyawan')->orWhere('wa_karyawan', '');
            })
            ->where(function ($query) {
                $query->whereNull('email_karyawan')->orWhere('email_karyawan', '');
            })
            ->count();

        return view('admin.blast.recipients.employees-ypik-pamjaya', compact(
            'employees',
            'instansiOptions',
            'search',
            'selectedInstansi',
            'selectedStatus',
            'selectedDataset',
            'allowedPerPage',
            'perPage',
            'totalEmployees',
            'validCount',
            'incompleteCount'
        ));
    }

    public function employeeCreate()
    {
        return view('admin.blast.recipients.employee-manual-form', [
            'variant' => 'koperasi',
            'isEdit' => false,
            'employee' => null,
        ]);
    }

    public function employeeStore(
        Request $request,
        EmployeeRecipientNormalizer $normalizer
    ) {
        $data = $request->validate([
            'nama_karyawan' => 'required|string',
            'instansi' => 'nullable|string',
            'nama_wali' => 'nullable|string',
            'email_karyawan' => 'nullable|email',
            'wa_karyawan' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        if (empty($data['email_karyawan']) && empty($data['wa_karyawan'])) {
            return back()->withErrors([
                'email_karyawan' => 'Email atau WhatsApp wajib diisi',
            ])->withInput();
        }

        $dto = $normalizer->normalize([
            'nama_karyawan' => $data['nama_karyawan'],
            'instansi' => $data['instansi'] ?? null,
            'nama_wali' => $data['nama_wali'] ?? null,
            'email' => $data['email_karyawan'] ?? null,
            'wa' => $data['wa_karyawan'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        if ($dto->email !== null || $dto->phone !== null) {
            $exists = BlastEmployeeRecipient::query()
                ->where(function ($query) use ($dto) {
                    if ($dto->email !== null) {
                        $query->orWhere('email_karyawan', $dto->email);
                    }

                    if ($dto->phone !== null) {
                        $query->orWhere('wa_karyawan', $dto->phone);
                    }
                })
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'email_karyawan' => 'Data dengan email/WhatsApp tersebut sudah ada.',
                ])->withInput();
            }
        }

        BlastEmployeeRecipient::query()->create([
            'nama_karyawan' => $dto->namaKaryawan,
            'instansi' => $dto->instansi,
            'nama_wali' => $dto->namaWali,
            'wa_karyawan' => $dto->phone,
            'email_karyawan' => $dto->email,
            'catatan' => $dto->catatan,
            'source' => 'manual:admin',
            'is_valid' => empty($dto->errors),
            'validation_error' => empty($dto->errors)
                ? null
                : implode(', ', $dto->errors),
        ]);

        return redirect()
            ->route('admin.blast.recipients.employees.index')
            ->with('success', 'Data karyawan koperasi berhasil ditambahkan.');
    }

    public function employeeEdit(string $id)
    {
        $employee = BlastEmployeeRecipient::findOrFail($id);

        return view('admin.blast.recipients.employee-manual-form', [
            'variant' => 'koperasi',
            'isEdit' => true,
            'employee' => $employee,
        ]);
    }

    public function employeeUpdate(
        Request $request,
        string $id,
        EmployeeRecipientNormalizer $normalizer
    ) {
        $employee = BlastEmployeeRecipient::findOrFail($id);

        $data = $request->validate([
            'nama_karyawan' => 'required|string',
            'instansi' => 'nullable|string',
            'nama_wali' => 'nullable|string',
            'email_karyawan' => 'nullable|email',
            'wa_karyawan' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        if (empty($data['email_karyawan']) && empty($data['wa_karyawan'])) {
            return back()->withErrors([
                'email_karyawan' => 'Email atau WhatsApp wajib diisi',
            ])->withInput();
        }

        $dto = $normalizer->normalize([
            'nama_karyawan' => $data['nama_karyawan'],
            'instansi' => $data['instansi'] ?? null,
            'nama_wali' => $data['nama_wali'] ?? null,
            'email' => $data['email_karyawan'] ?? null,
            'wa' => $data['wa_karyawan'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        if ($dto->email !== null || $dto->phone !== null) {
            $exists = BlastEmployeeRecipient::query()
                ->where('id', '!=', $employee->id)
                ->where(function ($query) use ($dto) {
                    if ($dto->email !== null) {
                        $query->orWhere('email_karyawan', $dto->email);
                    }

                    if ($dto->phone !== null) {
                        $query->orWhere('wa_karyawan', $dto->phone);
                    }
                })
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'email_karyawan' => 'Data dengan email/WhatsApp tersebut sudah ada.',
                ])->withInput();
            }
        }

        $employee->update([
            'nama_karyawan' => $dto->namaKaryawan,
            'instansi' => $dto->instansi,
            'nama_wali' => $dto->namaWali,
            'wa_karyawan' => $dto->phone,
            'email_karyawan' => $dto->email,
            'catatan' => $dto->catatan,
            'source' => $employee->source ?: 'manual:admin',
            'is_valid' => empty($dto->errors),
            'validation_error' => empty($dto->errors)
                ? null
                : implode(', ', $dto->errors),
        ]);

        return redirect()
            ->route('admin.blast.recipients.employees.index')
            ->with('success', 'Data karyawan koperasi berhasil diperbarui.');
    }

    public function employeeYpikCreate(Request $request)
    {
        return view('admin.blast.recipients.employee-manual-form', [
            'variant' => 'ypik',
            'dataset' => $request->input('dataset', 'ypik'),
            'isEdit' => false,
            'employee' => null,
        ]);
    }

    public function employeeYpikStore(
        Request $request,
        EmployeeRecipientNormalizer $normalizer
    ) {
        $data = $request->validate([
            'nama_karyawan' => 'required|string',
            'instansi' => 'nullable|string',
            'nama_wali' => 'nullable|string',
            'email_karyawan' => 'nullable|email',
            'wa_karyawan' => 'nullable|string',
            'catatan' => 'nullable|string',
            'dataset' => 'nullable|in:ypik,pam_jaya',
        ]);

        if (empty($data['email_karyawan']) && empty($data['wa_karyawan'])) {
            return back()->withErrors([
                'email_karyawan' => 'Email atau WhatsApp wajib diisi',
            ])->withInput();
        }

        $dataset = $data['dataset'] ?? 'ypik';
        $dto = $normalizer->normalize([
            'nama_karyawan' => $data['nama_karyawan'],
            'instansi' => $data['instansi'] ?? null,
            'nama_wali' => $data['nama_wali'] ?? null,
            'email' => $data['email_karyawan'] ?? null,
            'wa' => $data['wa_karyawan'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        if ($dto->email !== null || $dto->phone !== null) {
            $exists = BlastEmployeeYpikRecipient::query()
                ->where('dataset', $dataset)
                ->where(function ($query) use ($dto) {
                    if ($dto->email !== null) {
                        $query->orWhere('email_karyawan', $dto->email);
                    }

                    if ($dto->phone !== null) {
                        $query->orWhere('wa_karyawan', $dto->phone);
                    }
                })
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'email_karyawan' => 'Data dengan email/WhatsApp tersebut sudah ada.',
                ])->withInput();
            }
        }

        BlastEmployeeYpikRecipient::query()->create([
            'nama_karyawan' => $dto->namaKaryawan,
            'instansi' => $dto->instansi,
            'nama_wali' => $dto->namaWali,
            'wa_karyawan' => $dto->phone,
            'email_karyawan' => $dto->email,
            'catatan' => $dto->catatan,
            'source' => 'manual:admin_ypik',
            'dataset' => $dataset,
            'is_valid' => empty($dto->errors),
            'validation_error' => empty($dto->errors)
                ? null
                : implode(', ', $dto->errors),
        ]);

        return redirect()
            ->route(
                $dataset === 'pam_jaya'
                    ? 'admin.blast.recipients.employees-ypik-pamjaya.index'
                    : 'admin.blast.recipients.employees-ypik.index'
            )
            ->with('success', 'Data karyawan YPIK berhasil ditambahkan.');
    }

    public function employeeYpikEdit(string $id)
    {
        $employee = BlastEmployeeYpikRecipient::findOrFail($id);

        return view('admin.blast.recipients.employee-manual-form', [
            'variant' => 'ypik',
            'dataset' => $employee->dataset ?: 'ypik',
            'isEdit' => true,
            'employee' => $employee,
        ]);
    }

    public function employeeYpikUpdate(
        Request $request,
        string $id,
        EmployeeRecipientNormalizer $normalizer
    ) {
        $employee = BlastEmployeeYpikRecipient::findOrFail($id);

        $data = $request->validate([
            'nama_karyawan' => 'required|string',
            'instansi' => 'nullable|string',
            'nama_wali' => 'nullable|string',
            'email_karyawan' => 'nullable|email',
            'wa_karyawan' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        if (empty($data['email_karyawan']) && empty($data['wa_karyawan'])) {
            return back()->withErrors([
                'email_karyawan' => 'Email atau WhatsApp wajib diisi',
            ])->withInput();
        }

        $dataset = $employee->dataset ?: 'ypik';
        $dto = $normalizer->normalize([
            'nama_karyawan' => $data['nama_karyawan'],
            'instansi' => $data['instansi'] ?? null,
            'nama_wali' => $data['nama_wali'] ?? null,
            'email' => $data['email_karyawan'] ?? null,
            'wa' => $data['wa_karyawan'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        if ($dto->email !== null || $dto->phone !== null) {
            $exists = BlastEmployeeYpikRecipient::query()
                ->where('id', '!=', $employee->id)
                ->where('dataset', $dataset)
                ->where(function ($query) use ($dto) {
                    if ($dto->email !== null) {
                        $query->orWhere('email_karyawan', $dto->email);
                    }

                    if ($dto->phone !== null) {
                        $query->orWhere('wa_karyawan', $dto->phone);
                    }
                })
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'email_karyawan' => 'Data dengan email/WhatsApp tersebut sudah ada.',
                ])->withInput();
            }
        }

        $employee->update([
            'nama_karyawan' => $dto->namaKaryawan,
            'instansi' => $dto->instansi,
            'nama_wali' => $dto->namaWali,
            'wa_karyawan' => $dto->phone,
            'email_karyawan' => $dto->email,
            'catatan' => $dto->catatan,
            'source' => $employee->source ?: 'manual:admin_ypik',
            'dataset' => $dataset,
            'is_valid' => empty($dto->errors),
            'validation_error' => empty($dto->errors)
                ? null
                : implode(', ', $dto->errors),
        ]);

        return redirect()
            ->route(
                $dataset === 'pam_jaya'
                    ? 'admin.blast.recipients.employees-ypik-pamjaya.index'
                    : 'admin.blast.recipients.employees-ypik.index'
            )
            ->with('success', 'Data karyawan YPIK berhasil diperbarui.');
    }

    public function create()
    {
        return view('admin.blast.recipients.create');
    }

    /**
     * INPUT MANUAL (DENGAN NORMALIZATION)
     */
    public function store(
        Request $request,
        RecipientNormalizer $normalizer
    ) {
        $data = $request->validate([
            'nama_siswa' => 'required|string',
            'kelas' => 'required|string',
            'nama_wali' => 'required|string',
            'email_wali' => 'nullable|email',
            'wa_wali' => 'nullable|string',
            'wa_wali_2' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        if (empty($data['email_wali']) && empty($data['wa_wali']) && empty($data['wa_wali_2'])) {
            return back()->withErrors([
                'email_wali' => 'Email atau WhatsApp wajib diisi'
            ])->withInput();
        }

        $dto = $normalizer->normalize([
            'nama_siswa' => $data['nama_siswa'],
            'kelas' => $data['kelas'],
            'nama_wali' => $data['nama_wali'],
            'email' => $data['email_wali'],
            'wa' => $data['wa_wali'],
            'wa_2' => $data['wa_wali_2'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        BlastRecipient::create([
            'nama_siswa' => $dto->namaSiswa,
            'kelas' => $dto->kelas,
            'nama_wali' => $dto->namaWali,
            'email_wali' => $dto->email,
            'wa_wali' => $dto->phone,
            'wa_wali_2' => $dto->phoneSecondary,
            'catatan' => $dto->catatan,
            'is_valid' => empty($dto->errors),
            'validation_error' => empty($dto->errors)
                ? null
                : implode(', ', $dto->errors),
        ]);

        return redirect()
            ->route('admin.blast.recipients.index')
            ->with('success', 'Penerima berhasil ditambahkan');
    }

    /**
     * FORM EDIT
     */
    public function edit(string $id)
    {
        $recipient = BlastRecipient::findOrFail($id);

        return view('admin.blast.recipients.edit', compact('recipient'));
    }

    /**
     * UPDATE DATA (DENGAN NORMALIZATION)
     */
    public function update(
        Request $request,
        string $id,
        RecipientNormalizer $normalizer
    ) {
        $recipient = BlastRecipient::findOrFail($id);

        $data = $request->validate([
            'nama_siswa' => 'required|string',
            'kelas' => 'required|string',
            'nama_wali' => 'required|string',
            'email_wali' => 'nullable|email',
            'wa_wali' => 'nullable|string',
            'wa_wali_2' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        if (empty($data['email_wali']) && empty($data['wa_wali']) && empty($data['wa_wali_2'])) {
            return back()->withErrors([
                'email_wali' => 'Email atau WhatsApp wajib diisi'
            ])->withInput();
        }

        $dto = $normalizer->normalize([
            'nama_siswa' => $data['nama_siswa'],
            'kelas' => $data['kelas'],
            'nama_wali' => $data['nama_wali'],
            'email' => $data['email_wali'],
            'wa' => $data['wa_wali'],
            'wa_2' => $data['wa_wali_2'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        $recipient->update([
            'nama_siswa' => $dto->namaSiswa,
            'kelas' => $dto->kelas,
            'nama_wali' => $dto->namaWali,
            'email_wali' => $dto->email,
            'wa_wali' => $dto->phone,
            'wa_wali_2' => $dto->phoneSecondary,
            'catatan' => $dto->catatan,
            'is_valid' => empty($dto->errors),
            'validation_error' => empty($dto->errors)
                ? null
                : implode(', ', $dto->errors),
        ]);

        return redirect()
            ->route('admin.blast.recipients.index')
            ->with('success', 'Data penerima berhasil diperbarui');
    }

    /**
     * IMPORT EXCEL
     */
    public function import(
        Request $request,
        ExcelImportService $importService,
        RecipientBulkSaver $bulkSaver,
        EmployeeRecipientBulkSaver $employeeBulkSaver
    ) {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
            'import_type' => 'nullable|in:siswa,karyawan',
        ]);

        $uploadedFile = $request->file('file');
        $importType = (string) $request->input('import_type', 'siswa');

        if ($uploadedFile === null) {
            return redirect()
                ->route(
                    $importType === 'karyawan'
                        ? 'admin.blast.recipients.employees.index'
                        : 'admin.blast.recipients.index'
                )
                ->with('error', 'Import gagal: file tidak ditemukan.');
        }

        try {
            $result = $importType === 'karyawan'
                ? $importService->importEmployees($uploadedFile->getPathname())
                : $importService->import($uploadedFile->getPathname());
        } catch (\Throwable $e) {
            Log::error('[RECIPIENT IMPORT FAILED]', [
                'file' => $uploadedFile->getClientOriginalName(),
                'import_type' => $importType,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route(
                    $importType === 'karyawan'
                        ? 'admin.blast.recipients.employees.index'
                        : 'admin.blast.recipients.index'
                )
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }

        if (empty($result->valid) && empty($result->invalid)) {
            return redirect()
                ->route(
                    $importType === 'karyawan'
                        ? 'admin.blast.recipients.employees.index'
                        : 'admin.blast.recipients.index'
                )
                ->with('error', 'Import gagal: file tidak berisi data yang dapat diproses.');
        }

        $summary = $importType === 'karyawan'
            ? $employeeBulkSaver->save(collect($result->valid))
            : $bulkSaver->save(collect($result->valid));
        $invalidCount = count($result->invalid) + (int) ($summary['invalid'] ?? 0);
        $messagePrefix = $importType === 'karyawan'
            ? 'Import data karyawan selesai.'
            : 'Import data siswa selesai.';
        $message = "{$messagePrefix} Inserted: {$summary['inserted']}, Duplicate: {$summary['duplicates']}, Invalid: {$invalidCount}";

        if ((int) $summary['inserted'] === 0) {
            return redirect()
                ->route(
                    $importType === 'karyawan'
                        ? 'admin.blast.recipients.employees.index'
                        : 'admin.blast.recipients.index'
                )
                ->with('error', $message . ' Tidak ada data baru yang disimpan.');
        }

        return redirect()
            ->route(
                $importType === 'karyawan'
                    ? 'admin.blast.recipients.employees.index'
                    : 'admin.blast.recipients.index'
            )
            ->with('success', $message);
    }

    public function importEmployeeYpik(
        Request $request,
        ExcelImportService $importService,
        EmployeeYpikRecipientBulkSaver $bulkSaver
    ) {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
            'dataset' => 'nullable|in:ypik,pam_jaya',
        ]);

        $uploadedFile = $request->file('file');
        $dataset = (string) $request->input('dataset', 'ypik');

        if ($uploadedFile === null) {
            return redirect()
                ->route(
                    $dataset === 'pam_jaya'
                        ? 'admin.blast.recipients.employees-ypik-pamjaya.index'
                        : 'admin.blast.recipients.employees-ypik.index'
                )
                ->with('error', 'Import gagal: file tidak ditemukan.');
        }

        try {
            $result = $importService->importEmployees($uploadedFile->getPathname());
        } catch (\Throwable $e) {
            Log::error('[RECIPIENT YPIK IMPORT FAILED]', [
                'file' => $uploadedFile->getClientOriginalName(),
                'dataset' => $dataset,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route(
                    $dataset === 'pam_jaya'
                        ? 'admin.blast.recipients.employees-ypik-pamjaya.index'
                        : 'admin.blast.recipients.employees-ypik.index'
                )
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }

        if (empty($result->valid) && empty($result->invalid)) {
            return redirect()
                ->route(
                    $dataset === 'pam_jaya'
                        ? 'admin.blast.recipients.employees-ypik-pamjaya.index'
                        : 'admin.blast.recipients.employees-ypik.index'
                )
                ->with('error', 'Import gagal: file tidak berisi data yang dapat diproses.');
        }

        $summary = $bulkSaver->save(collect($result->valid), $dataset);
        $invalidCount = count($result->invalid) + (int) ($summary['invalid'] ?? 0);
        $datasetLabel = $dataset === 'pam_jaya' ? 'YPIK Pam Jaya' : 'YPIK';
        $message = 'Import data karyawan ' . $datasetLabel . ' selesai. '
            . "Inserted: {$summary['inserted']}, Duplicate: {$summary['duplicates']}, Invalid: {$invalidCount}";

        if ((int) $summary['inserted'] === 0) {
            return redirect()
                ->route(
                    $dataset === 'pam_jaya'
                        ? 'admin.blast.recipients.employees-ypik-pamjaya.index'
                        : 'admin.blast.recipients.employees-ypik.index'
                )
                ->with('error', $message . ' Tidak ada data baru yang disimpan.');
        }

        return redirect()
            ->route(
                $dataset === 'pam_jaya'
                    ? 'admin.blast.recipients.employees-ypik-pamjaya.index'
                    : 'admin.blast.recipients.employees-ypik.index'
            )
            ->with('success', $message);
    }

    public function destroy(string $id)
    {
        BlastRecipient::findOrFail($id)->delete();

        return back()->with('success', 'Penerima dihapus');
    }

    public function destroySelectedStudents(Request $request)
    {
        $data = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['uuid'],
        ]);

        $deleted = BlastRecipient::query()
            ->whereIn('id', $data['selected_ids'])
            ->delete();

        return back()->with('success', "Recipient siswa terpilih berhasil dihapus ({$deleted} data).");
    }

    public function destroyAllStudents()
    {
        $total = BlastRecipient::query()->count();
        BlastRecipient::query()->delete();

        return back()->with('success', "Semua data recipient siswa berhasil dihapus ({$total} data).");
    }

    public function destroyEmployee(string $id)
    {
        BlastEmployeeRecipient::findOrFail($id)->delete();

        return back()->with('success', 'Data karyawan dihapus');
    }

    public function destroySelectedEmployees(Request $request)
    {
        $data = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['uuid'],
        ]);

        $deleted = BlastEmployeeRecipient::query()
            ->whereIn('id', $data['selected_ids'])
            ->delete();

        return back()->with('success', "Recipient karyawan koperasi terpilih berhasil dihapus ({$deleted} data).");
    }

    public function destroyAllEmployees()
    {
        $total = BlastEmployeeRecipient::query()->count();
        BlastEmployeeRecipient::query()->delete();

        return back()->with('success', "Semua data recipient karyawan koperasi berhasil dihapus ({$total} data).");
    }

    public function destroyEmployeeYpik(string $id)
    {
        BlastEmployeeYpikRecipient::findOrFail($id)->delete();

        return back()->with('success', 'Data karyawan YPIK dihapus');
    }

    public function destroySelectedEmployeesYpik(Request $request)
    {
        $data = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['uuid'],
        ]);

        $deleted = BlastEmployeeYpikRecipient::query()
            ->whereIn('id', $data['selected_ids'])
            ->delete();

        return back()->with('success', "Recipient karyawan YPIK terpilih berhasil dihapus ({$deleted} data).");
    }

    public function destroyAllEmployeesYpik()
    {
        $total = BlastEmployeeYpikRecipient::query()
            ->where('dataset', 'ypik')
            ->count();
        BlastEmployeeYpikRecipient::query()
            ->where('dataset', 'ypik')
            ->delete();

        return back()->with('success', "Semua data recipient karyawan YPIK berhasil dihapus ({$total} data).");
    }

    public function destroyAllEmployeesYpikPamJaya()
    {
        $total = BlastEmployeeYpikRecipient::query()
            ->where('dataset', 'pam_jaya')
            ->count();

        BlastEmployeeYpikRecipient::query()
            ->where('dataset', 'pam_jaya')
            ->delete();

        return back()->with('success', "Semua data recipient YPIK Pam Jaya berhasil dihapus ({$total} data).");
    }
}
