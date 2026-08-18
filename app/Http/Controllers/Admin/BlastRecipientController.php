<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlastEmployeeRecipient;
use App\Models\BlastEmployeeYpikRecipient;
use App\Models\BlastGeneralRecipient;
use App\Models\BlastRecipient;
use App\Services\Recipient\EmployeeRecipientBulkSaver;
use App\Services\Recipient\EmployeeRecipientNormalizer;
use App\Services\Recipient\EmployeeYpikRecipientBulkSaver;
use App\Services\Recipient\ExcelImportService;
use App\Services\Recipient\GeneralRecipientBulkSaver;
use App\Services\Recipient\GeneralRecipientNormalizer;
use App\Services\Recipient\RecipientBulkSaver;
use App\Services\Recipient\RecipientGroupingService;
use App\Services\Recipient\RecipientNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BlastRecipientController extends Controller
{
    public function index(Request $request, RecipientGroupingService $groupingService)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'kelas' => 'nullable|string|max:100',
            'education_level' => 'nullable|string|max:30',
            'academic_year' => 'nullable|string|max:20',
            'student_status' => 'nullable|string|max:30',
            'per_page' => 'nullable|integer|min:1|max:500',
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $selectedClass = trim((string) ($validated['kelas'] ?? ''));
        $selectedEducationLevel = trim((string) ($validated['education_level'] ?? ''));
        $selectedAcademicYear = trim((string) ($validated['academic_year'] ?? ''));
        $selectedStudentStatus = trim((string) ($validated['student_status'] ?? ''));
        $allowedPerPage = [20, 50, 100, 200];
        $perPage = (int) ($validated['per_page'] ?? 50);

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 50;
        }

        $query = $this->applyStudentRecipientFilters(
            BlastRecipient::query(),
            $search,
            $selectedClass,
            $selectedEducationLevel,
            $selectedAcademicYear,
            $selectedStudentStatus
        );

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
        $academicYearOptions = BlastRecipient::query()
            ->whereNotNull('academic_year')
            ->where('academic_year', '!=', '')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');
        $educationLevelOptions = $groupingService->educationLevelOptions();
        $studentStatusOptions = $groupingService->statusOptions();

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
            'selectedEducationLevel',
            'selectedAcademicYear',
            'selectedStudentStatus',
            'academicYearOptions',
            'educationLevelOptions',
            'studentStatusOptions',
            'allowedPerPage',
            'perPage',
            'totalRecipients',
            'completeCount',
            'incompleteCount',
            'validCount'
        ));
    }

    public function exportStudents(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'kelas' => 'nullable|string|max:100',
            'education_level' => 'nullable|string|max:30',
            'academic_year' => 'nullable|string|max:20',
            'student_status' => 'nullable|string|max:30',
        ]);

        $recipients = $this->applyStudentRecipientFilters(
            BlastRecipient::query(),
            trim((string) ($validated['q'] ?? '')),
            trim((string) ($validated['kelas'] ?? '')),
            trim((string) ($validated['education_level'] ?? '')),
            trim((string) ($validated['academic_year'] ?? '')),
            trim((string) ($validated['student_status'] ?? ''))
        )
            ->orderBy('education_level')
            ->orderBy('kelas')
            ->orderBy('nama_siswa')
            ->get();

        return response()->streamDownload(function () use ($recipients): void {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Recipient Siswa');
            $sheet->fromArray([
                [
                    'Nama Siswa',
                    'Jenjang',
                    'Kelas',
                    'Tahun Ajaran',
                    'Status Siswa',
                    'Nama Wali',
                    'WhatsApp 1',
                    'WhatsApp 2',
                    'Email Wali',
                    'Valid',
                    'Catatan',
                ],
            ]);

            $rowNumber = 2;
            foreach ($recipients as $recipient) {
                $sheet->fromArray([[
                    $recipient->nama_siswa,
                    $recipient->education_level,
                    $recipient->kelas,
                    $recipient->academic_year,
                    $recipient->student_status,
                    $recipient->nama_wali,
                    $recipient->wa_wali,
                    $recipient->wa_wali_2,
                    $recipient->email_wali,
                    $recipient->is_valid ? 'Ya' : 'Tidak',
                    $recipient->catatan,
                ]], null, 'A' . $rowNumber);
                $rowNumber++;
            }

            $sheet->getStyle('A1:K1')->getFont()->setBold(true);
            $sheet->freezePane('A2');
            foreach (range('A', 'K') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'kontak-recipient-siswa-' . now()->format('Y-m-d-His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate(string $template)
    {
        $templateMap = [
            'siswa' => [
                'path' => resource_path('recipient-templates/recipent siswa.xlsx'),
                'download_name' => 'recipent siswa.xlsx',
            ],
            'karyawan' => [
                'path' => resource_path('recipient-templates/Template_karyawan.xlsx'),
                'download_name' => 'Template_karyawan.xlsx',
            ],
            'umum' => [
                'path' => resource_path('recipient-templates/data penerima umum.xlsx'),
                'download_name' => 'data penerima umum.xlsx',
            ],
        ];

        $templateConfig = $templateMap[$template] ?? null;
        if ($templateConfig === null) {
            abort(404);
        }

        if (!is_file($templateConfig['path'])) {
            return back()->with('error', __('app.blast.recipient_template_missing'));
        }

        return response()->download(
            $templateConfig['path'],
            $templateConfig['download_name'],
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function bulkMoveStudents(Request $request, RecipientGroupingService $groupingService)
    {
        $data = $request->validate([
            'selected_ids' => ['nullable', 'array'],
            'selected_ids.*' => ['uuid', 'distinct'],
            'apply_all_filtered' => ['nullable', 'boolean'],
            'filter_q' => ['nullable', 'string', 'max:255'],
            'filter_kelas' => ['nullable', 'string', 'max:100'],
            'filter_education_level' => ['nullable', 'string', 'max:30'],
            'filter_academic_year' => ['nullable', 'string', 'max:20'],
            'filter_student_status' => ['nullable', 'string', 'max:30'],
            'kelas' => ['nullable', 'string', 'max:100'],
            'education_level' => ['nullable', 'string', 'max:30'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'student_status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if (
            empty($data['kelas'])
            && empty($data['education_level'])
            && empty($data['academic_year'])
            && empty($data['student_status'])
        ) {
            return back()
                ->withInput()
                ->with('error', __('app.blast.recipient_group_target_required'));
        }

        $recipientIds = array_values($data['selected_ids'] ?? []);
        if (!empty($data['apply_all_filtered'])) {
            $recipientIds = $this->applyStudentRecipientFilters(
                BlastRecipient::query(),
                trim((string) ($data['filter_q'] ?? '')),
                trim((string) ($data['filter_kelas'] ?? '')),
                trim((string) ($data['filter_education_level'] ?? '')),
                trim((string) ($data['filter_academic_year'] ?? '')),
                trim((string) ($data['filter_student_status'] ?? ''))
            )
                ->pluck('id')
                ->all();
        }

        if ($recipientIds === []) {
            return back()->with('error', __('app.blast.choose_min_one_recipient_delete'));
        }

        $updated = $groupingService->moveRecipients(
            $recipientIds,
            $data,
            auth()->id() ? (string) auth()->id() : null
        );

        return back()->with(
            'success',
            __('app.blast.recipient_group_update_success', ['count' => $updated])
        );
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

    public function generalIndex(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'event_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:all,valid,invalid',
            'per_page' => 'nullable|integer|min:1|max:500',
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $selectedEventName = trim((string) ($validated['event_name'] ?? ''));
        $selectedStatus = strtolower((string) ($validated['status'] ?? 'all'));
        $allowedPerPage = [20, 50, 100, 200];
        $perPage = (int) ($validated['per_page'] ?? 50);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 50;
        }

        $query = BlastGeneralRecipient::query();

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('whatsapp', 'like', '%' . $search . '%')
                    ->orWhere('instansi', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('sertifikat', 'like', '%' . $search . '%')
                    ->orWhere('event_name', 'like', '%' . $search . '%')
                    ->orWhere('catatan', 'like', '%' . $search . '%');
            });
        }

        if ($selectedEventName !== '') {
            $query->where('event_name', $selectedEventName);
        }

        if ($selectedStatus === 'valid') {
            $query->where('is_valid', true);
        } elseif ($selectedStatus === 'invalid') {
            $query->where('is_valid', false);
        }

        $recipients = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $baseStatsQuery = BlastGeneralRecipient::query();
        $totalRecipients = (clone $baseStatsQuery)->count();
        $validCount = (clone $baseStatsQuery)->where('is_valid', true)->count();
        $invalidCount = (clone $baseStatsQuery)->where('is_valid', false)->count();
        $eventOptions = BlastGeneralRecipient::query()
            ->whereNotNull('event_name')
            ->where('event_name', '!=', '')
            ->select('event_name')
            ->distinct()
            ->orderBy('event_name')
            ->pluck('event_name');

        return view('admin.blast.recipients.general', compact(
            'recipients',
            'search',
            'selectedEventName',
            'selectedStatus',
            'allowedPerPage',
            'perPage',
            'totalRecipients',
            'validCount',
            'invalidCount',
            'eventOptions'
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

    public function generalCreate()
    {
        return view('admin.blast.recipients.general-form', [
            'isEdit' => false,
            'recipient' => null,
        ]);
    }

    public function generalStore(
        Request $request,
        GeneralRecipientNormalizer $normalizer
    ) {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:50',
            'instansi' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'sertifikat' => 'nullable|string|max:2048',
            'event_name' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ]);

        $dto = $normalizer->normalize([
            'nama' => $data['nama'],
            'wa' => $data['whatsapp'],
            'instansi' => $data['instansi'] ?? null,
            'email' => $data['email'] ?? null,
            'sertifikat' => $data['sertifikat'] ?? null,
            'event_name' => $data['event_name'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        if (!$dto->isValid) {
            return back()->withErrors([
                'whatsapp' => implode(', ', $dto->errors),
            ])->withInput();
        }

        $exists = BlastGeneralRecipient::query()
            ->where('whatsapp', $dto->phone)
            ->where(function (Builder $query) use ($dto): void {
                $this->applyGeneralEventScope($query, $dto->eventName);
            })
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'whatsapp' => 'Data dengan WhatsApp tersebut sudah ada.',
            ])->withInput();
        }

        BlastGeneralRecipient::query()->create([
            'nama' => $dto->nama,
            'whatsapp' => $dto->phone,
            'instansi' => $dto->instansi,
            'email' => $dto->email,
            'sertifikat' => $dto->sertifikat,
            'event_name' => $dto->eventName,
            'catatan' => $dto->catatan,
            'source' => 'manual:admin_general',
            'is_valid' => true,
            'validation_error' => null,
        ]);

        return redirect()
            ->route('admin.blast.recipients.general.index')
            ->with('success', 'Penerima umum berhasil ditambahkan.');
    }

    public function generalEdit(string $id)
    {
        $recipient = BlastGeneralRecipient::findOrFail($id);

        return view('admin.blast.recipients.general-form', [
            'isEdit' => true,
            'recipient' => $recipient,
        ]);
    }

    public function generalUpdate(
        Request $request,
        string $id,
        GeneralRecipientNormalizer $normalizer
    ) {
        $recipient = BlastGeneralRecipient::findOrFail($id);

        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:50',
            'instansi' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'sertifikat' => 'nullable|string|max:2048',
            'event_name' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ]);

        $dto = $normalizer->normalize([
            'nama' => $data['nama'],
            'wa' => $data['whatsapp'],
            'instansi' => $data['instansi'] ?? null,
            'email' => $data['email'] ?? null,
            'sertifikat' => $data['sertifikat'] ?? null,
            'event_name' => $data['event_name'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        if (!$dto->isValid) {
            return back()->withErrors([
                'whatsapp' => implode(', ', $dto->errors),
            ])->withInput();
        }

        $exists = BlastGeneralRecipient::query()
            ->where('id', '!=', $recipient->id)
            ->where('whatsapp', $dto->phone)
            ->where(function (Builder $query) use ($dto): void {
                $this->applyGeneralEventScope($query, $dto->eventName);
            })
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'whatsapp' => 'Data dengan WhatsApp tersebut sudah ada.',
            ])->withInput();
        }

        $recipient->update([
            'nama' => $dto->nama,
            'whatsapp' => $dto->phone,
            'instansi' => $dto->instansi,
            'email' => $dto->email,
            'sertifikat' => $dto->sertifikat,
            'event_name' => $dto->eventName,
            'catatan' => $dto->catatan,
            'source' => $recipient->source ?: 'manual:admin_general',
            'is_valid' => true,
            'validation_error' => null,
        ]);

        return redirect()
            ->route('admin.blast.recipients.general.index')
            ->with('success', 'Penerima umum berhasil diperbarui.');
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
        RecipientNormalizer $normalizer,
        RecipientGroupingService $groupingService
    ) {
        $data = $request->validate([
            'nama_siswa' => 'required|string',
            'kelas' => 'required|string',
            'education_level' => 'nullable|string|max:30',
            'academic_year' => 'nullable|string|max:20',
            'student_status' => 'nullable|string|max:30',
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
            'education_level' => ($data['education_level'] ?? null) ?: $groupingService->inferEducationLevel($dto->kelas),
            'academic_year' => ($data['academic_year'] ?? null) ?: $groupingService->currentAcademicYear(),
            'student_status' => ($data['student_status'] ?? null) ?: RecipientGroupingService::STATUS_ACTIVE,
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
        $recipient = BlastRecipient::query()
            ->with([
                'classHistories' => fn ($query) => $query
                    ->latest()
                    ->limit(20),
            ])
            ->findOrFail($id);

        return view('admin.blast.recipients.edit', compact('recipient'));
    }

    /**
     * UPDATE DATA (DENGAN NORMALIZATION)
     */
    public function update(
        Request $request,
        string $id,
        RecipientNormalizer $normalizer,
        RecipientGroupingService $groupingService
    ) {
        $recipient = BlastRecipient::findOrFail($id);

        $data = $request->validate([
            'nama_siswa' => 'required|string',
            'kelas' => 'required|string',
            'education_level' => 'nullable|string|max:30',
            'academic_year' => 'nullable|string|max:20',
            'student_status' => 'nullable|string|max:30',
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

        $targetGrouping = [
            'kelas' => $dto->kelas,
            'education_level' => ($data['education_level'] ?? null)
                ?: $groupingService->inferEducationLevel($dto->kelas),
            'academic_year' => ($data['academic_year'] ?? null)
                ?: $recipient->academic_year
                ?: $groupingService->currentAcademicYear(),
            'student_status' => ($data['student_status'] ?? null)
                ?: $recipient->student_status
                ?: RecipientGroupingService::STATUS_ACTIVE,
            'notes' => 'Perubahan melalui form edit recipient.',
        ];

        $recipient->update([
            'nama_siswa' => $dto->namaSiswa,
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

        if (
            $recipient->kelas !== $targetGrouping['kelas']
            || $recipient->education_level !== $targetGrouping['education_level']
            || $recipient->academic_year !== $targetGrouping['academic_year']
            || $recipient->student_status !== $targetGrouping['student_status']
        ) {
            $groupingService->moveRecipients(
                [(string) $recipient->id],
                $targetGrouping,
                auth()->id() ? (string) auth()->id() : null
            );
        }

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
        EmployeeRecipientBulkSaver $employeeBulkSaver,
        RecipientGroupingService $groupingService
    ) {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
            'import_type' => 'nullable|in:siswa,karyawan',
            'education_level' => 'nullable|string|max:30',
            'academic_year' => 'nullable|string|max:20',
            'student_status' => 'nullable|string|max:30',
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
            : $bulkSaver->save(collect($result->valid), [
                'education_level' => $request->input('education_level'),
                'academic_year' => $request->input('academic_year') ?: $groupingService->currentAcademicYear(),
                'student_status' => $request->input('student_status') ?: RecipientGroupingService::STATUS_ACTIVE,
            ]);
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

    public function importGeneral(
        Request $request,
        ExcelImportService $importService,
        GeneralRecipientBulkSaver $bulkSaver
    ) {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
            'event_name' => 'nullable|string|max:255',
        ]);

        $uploadedFile = $request->file('file');

        if ($uploadedFile === null) {
            return redirect()
                ->route('admin.blast.recipients.general.index')
                ->with('error', 'Import gagal: file tidak ditemukan.');
        }

        $eventName = $this->normalizeGeneralEventName($request->input('event_name'));
        if ($eventName === null) {
            $eventName = $this->normalizeGeneralEventName(
                pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME)
            );
        }

        try {
            $result = $importService->importGeneral($uploadedFile->getPathname(), $eventName);
        } catch (\Throwable $e) {
            Log::error('[GENERAL RECIPIENT IMPORT FAILED]', [
                'file' => $uploadedFile->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.blast.recipients.general.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }

        if (empty($result->valid) && empty($result->invalid)) {
            return redirect()
                ->route('admin.blast.recipients.general.index')
                ->with('error', 'Import gagal: file tidak berisi data yang dapat diproses.');
        }

        $summary = $bulkSaver->save(collect($result->valid));
        $invalidCount = count($result->invalid) + (int) ($summary['invalid'] ?? 0);
        $message = 'Import penerima umum selesai. '
            . "Inserted: {$summary['inserted']}, Duplicate: {$summary['duplicates']}, Invalid: {$invalidCount}";
        if ($eventName !== null) {
            $message .= ". Event: {$eventName}";
        }

        if ((int) $summary['inserted'] === 0) {
            return redirect()
                ->route('admin.blast.recipients.general.index')
                ->with('error', $message . ' Tidak ada data baru yang disimpan.');
        }

        return redirect()
            ->route('admin.blast.recipients.general.index')
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

    public function destroyGeneral(string $id)
    {
        BlastGeneralRecipient::findOrFail($id)->delete();

        return back()->with('success', 'Penerima umum berhasil dihapus.');
    }

    public function destroySelectedGeneral(Request $request)
    {
        $data = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['uuid'],
        ]);

        $deleted = BlastGeneralRecipient::query()
            ->whereIn('id', $data['selected_ids'])
            ->delete();

        return back()->with('success', "Penerima umum terpilih berhasil dihapus ({$deleted} data).");
    }

    public function destroyAllGeneral()
    {
        $total = BlastGeneralRecipient::query()->count();
        BlastGeneralRecipient::query()->delete();

        return back()->with('success', "Semua penerima umum berhasil dihapus ({$total} data).");
    }

    private function normalizeGeneralEventName(?string $value): ?string
    {
        $eventName = trim((string) $value);

        return $eventName !== '' ? $eventName : null;
    }

    private function applyGeneralEventScope(Builder $query, ?string $eventName): Builder
    {
        $normalizedEventName = $this->normalizeGeneralEventName($eventName);

        if ($normalizedEventName !== null) {
            return $query->where('event_name', $normalizedEventName);
        }

        return $query->where(function (Builder $builder): void {
            $builder->whereNull('event_name')
                ->orWhere('event_name', '');
        });
    }

    private function applyStudentRecipientFilters(
        Builder $query,
        string $search,
        string $class,
        string $educationLevel,
        string $academicYear,
        string $studentStatus
    ): Builder {
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('nama_siswa', 'like', '%' . $search . '%')
                    ->orWhere('kelas', 'like', '%' . $search . '%')
                    ->orWhere('education_level', 'like', '%' . $search . '%')
                    ->orWhere('academic_year', 'like', '%' . $search . '%')
                    ->orWhere('nama_wali', 'like', '%' . $search . '%')
                    ->orWhere('wa_wali', 'like', '%' . $search . '%')
                    ->orWhere('wa_wali_2', 'like', '%' . $search . '%')
                    ->orWhere('email_wali', 'like', '%' . $search . '%');
            });
        }

        if ($class !== '') {
            $query->where('kelas', $class);
        }

        if ($educationLevel !== '') {
            $query->where('education_level', $educationLevel);
        }

        if ($academicYear !== '') {
            $query->where('academic_year', $academicYear);
        }

        if ($studentStatus !== '') {
            $query->where('student_status', $studentStatus);
        }

        return $query;
    }

    public function pdamIndex(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:500',
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $allowedPerPage = [20, 50, 100, 200];
        $perPage = (int) ($validated['per_page'] ?? 50);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 50;
        }

        $query = \App\Models\BlastPdamRecipient::query();

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('nama_lengkap', 'like', '%' . $search . '%')
                    ->orWhere('nomor_telpon', 'like', '%' . $search . '%')
                    ->orWhere('instansi_pekerjaan', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('sertifikat', 'like', '%' . $search . '%');
            });
        }

        $recipients = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $totalRecipients = \App\Models\BlastPdamRecipient::count();

        return view('admin.blast.recipients.pdam', compact(
            'recipients',
            'search',
            'allowedPerPage',
            'perPage',
            'totalRecipients'
        ));
    }

    public function pdamCreate()
    {
        return view('admin.blast.recipients.pdam-form', [
            'isEdit' => false,
            'recipient' => null,
        ]);
    }

    public function pdamStore(Request $request)
    {
        $validated = $request->validate([
            'timestamp_excel' => 'nullable|string|max:255',
            'nama_lengkap' => 'required|string|max:255',
            'instansi_pekerjaan' => 'nullable|string|max:255',
            'nomor_telpon' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'sertifikat' => 'nullable|string|max:255',
        ]);

        \App\Models\BlastPdamRecipient::create($validated);

        return redirect()->route('admin.blast.recipients.pdam.index')
            ->with('success', __('app.data_added_successfully'));
    }

    public function pdamEdit(string $id)
    {
        $recipient = \App\Models\BlastPdamRecipient::findOrFail($id);

        return view('admin.blast.recipients.pdam-form', [
            'isEdit' => true,
            'recipient' => $recipient,
        ]);
    }

    public function pdamUpdate(Request $request, string $id)
    {
        $recipient = \App\Models\BlastPdamRecipient::findOrFail($id);

        $validated = $request->validate([
            'timestamp_excel' => 'nullable|string|max:255',
            'nama_lengkap' => 'required|string|max:255',
            'instansi_pekerjaan' => 'nullable|string|max:255',
            'nomor_telpon' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'sertifikat' => 'nullable|string|max:255',
        ]);

        $recipient->update($validated);

        return redirect()->route('admin.blast.recipients.pdam.index')
            ->with('success', __('app.data_updated_successfully'));
    }

    public function importPdam(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            
            $highestRow = $worksheet->getHighestDataRow();
            $highestColumn = $worksheet->getHighestDataColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            $rows = [];
            for ($row = 2; $row <= $highestRow; ++$row) {
                $rowData = [];
                for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    $columnString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $rowData[] = $worksheet->getCell($columnString . $row)->getValue();
                }

                if (empty(trim((string)($rowData[1] ?? '')))) {
                    continue;
                }

                $rows[] = [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'timestamp_excel' => trim((string)($rowData[0] ?? '')),
                    'nama_lengkap' => trim((string)($rowData[1] ?? '')),
                    'instansi_pekerjaan' => trim((string)($rowData[2] ?? '')),
                    'nomor_telpon' => trim((string)($rowData[3] ?? '')),
                    'email' => trim((string)($rowData[4] ?? '')),
                    'sertifikat' => trim((string)($rowData[5] ?? '')),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($rows)) {
                $chunks = array_chunk($rows, 500);
                foreach ($chunks as $chunk) {
                    \App\Models\BlastPdamRecipient::insert($chunk);
                }
            }

            return redirect()->back()->with('success', 'Data Penerima PDAM berhasil diimpor.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PDAM Import Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }
    }

    public function destroyAllPdam()
    {
        \App\Models\BlastPdamRecipient::truncate();

        return redirect()->back()->with('success', 'Semua Data Penerima PDAM berhasil dihapus.');
    }

    public function destroySelectedPdam(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|string',
        ]);

        \App\Models\BlastPdamRecipient::whereIn('id', $validated['ids'])->delete();

        return redirect()->back()->with('success', 'Data terpilih berhasil dihapus.');
    }

    public function destroyPdam(string $id)
    {
        $recipient = \App\Models\BlastPdamRecipient::findOrFail($id);
        $recipient->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }
}
