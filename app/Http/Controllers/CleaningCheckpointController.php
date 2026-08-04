<?php

namespace App\Http\Controllers;

use App\Models\CleaningCheckpoint;
use App\Models\CleaningCheckpointLog;
use App\Models\CleaningShift;
use App\Models\CleaningAssignment;
use App\Models\User;
use App\Helpers\FileUploadHelper;
use App\Exports\CleaningCheckpointExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CleaningShiftsImport;
use App\Imports\CleaningAssignmentsImport;
use App\Exports\CleaningShiftsTemplateExport;
use App\Exports\CleaningAssignmentsTemplateExport;

class CleaningCheckpointController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Tampilan Master Area Checkpoint Kebersihan (Admin / Super Admin)
     */
    public function index()
    {
        $user = auth()->user();

        // Petugas Kebersihan diarahkan langsung ke halaman scan
        if ($user->role === 'cleaning') {
            return redirect()->route('cleaning.scan');
        }

        // Hanya Admin & Super Admin yang bisa mengakses master area
        if (!$user->hasRole(['admin', 'super_admin'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        $checkpoints = CleaningCheckpoint::orderBy('plant')->orderBy('sub_area')->orderBy('area_name')->get();
        $existingPlants   = CleaningCheckpoint::pluck('plant')->filter()->unique()->values();
        $existingSubAreas = CleaningCheckpoint::pluck('sub_area')->filter()->unique()->values();

        return view('cleaning.checkpoints.index', compact('checkpoints', 'existingPlants', 'existingSubAreas'));
    }

    /**
     * Tampilan Rekap Log Hasil Pengecekan Kebersihan (Admin / Super Admin / Manager)
     */
    public function logsIndex(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'cleaning') {
            return redirect()->route('cleaning.scan');
        }

        if (!$user->hasRole(['admin', 'super_admin', 'manager'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        $checkpoints = CleaningCheckpoint::orderBy('plant')->orderBy('area_name')->get();
        $existingPlants = CleaningCheckpoint::pluck('plant')->filter()->unique()->values();

        // Query Logs
        $logsQuery = CleaningCheckpointLog::with('user', 'checkpoint');

        $timeframe = $request->input('timeframe', 'custom');

        if ($timeframe === 'today') {
            $logsQuery->whereDate('checked_at', Carbon::today());
        } elseif ($timeframe === 'this_week') {
            $logsQuery->whereBetween('checked_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($timeframe === 'this_month') {
            $logsQuery->whereMonth('checked_at', Carbon::now()->month)
                      ->whereYear('checked_at', Carbon::now()->year);
        } else {
            if ($request->filled('date_from')) {
                $logsQuery->whereDate('checked_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $logsQuery->whereDate('checked_at', '<=', $request->date_to);
            }
        }
        if ($request->filled('plant')) {
            $logsQuery->whereHas('checkpoint', function($q) use ($request) {
                $q->where('plant', $request->plant);
            });
        }
        if ($request->filled('shift')) {
            $logsQuery->where('shift', $request->shift);
        }
        if ($request->filled('status')) {
            $logsQuery->where('status', $request->status);
        }

        $logs = $logsQuery->latest('checked_at')->paginate(20)->withQueryString();

        return view('cleaning.logs.index', compact('checkpoints', 'logs', 'existingPlants'));
    }

    /**
     * Simpan Area Checkpoint Kebersihan Baru (Admin / Super Admin)
     */
    public function storeCheckpoint(Request $request)
    {
        $request->validate([
            'plant'           => 'required|string|max:100',
            'sub_area'        => 'nullable|string|max:100',
            'area_name'       => 'required|string|max:100',
            'target_duration' => 'required|integer|min:1|max:120',
        ]);

        // Generate Code otomatis
        $plantClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->plant));
        if ($plantClean === 'PLANTA' || $plantClean === 'A') {
            $plantCode = 'A';
        } elseif ($plantClean === 'PLANTB' || $plantClean === 'B') {
            $plantCode = 'B';
        } else {
            $plantCode = substr($plantClean, 0, 5);
        }

        $cleanAreaName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '-', $request->area_name));
        $code = 'CP-CLN-' . $plantCode . '-' . $cleanAreaName;

        // Cek keunikan code
        $exists = CleaningCheckpoint::where('code', $code)->exists();
        if ($exists) {
            $code .= '-' . rand(10, 99);
        }

        CleaningCheckpoint::create([
            'plant'           => $request->plant,
            'sub_area'        => $request->sub_area ?: null,
            'area_name'       => $request->area_name,
            'code'            => $code,
            'target_duration' => $request->target_duration,
        ]);

        return back()->with('success', 'Area Checkpoint Kebersihan berhasil ditambahkan dengan Kode: ' . $code);
    }

    /**
     * Halaman Scan QR Code / Check-in untuk Petugas Kebersihan
     */
    public function scanPage()
    {
        $user = auth()->user();
        $shifts = CleaningShift::orderBy('start_time')->get();
        
        // 1. Cek penugasan khusus hari ini
        $assignment = CleaningAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereDate('assigned_date', Carbon::today())
            ->first();

        // 1.5. Cek penugasan permanen jika tidak ada penugasan khusus hari ini
        if (!$assignment) {
            $assignment = CleaningAssignment::with('shift')
                ->where('user_id', $user->id)
                ->whereNull('assigned_date')
                ->first();
        }

        $currentShift = null;
        if ($assignment && $assignment->shift) {
            $currentShift = $assignment->shift->name;
        } else {
            // 2. Fallback deteksi berdasarkan waktu sekarang
            $timeStr = now()->format('H:i:s');
            foreach ($shifts as $s) {
                if ($s->start_time > $s->end_time) { // Shift cross-day
                    if ($timeStr >= $s->start_time || $timeStr < $s->end_time) {
                        $currentShift = $s->name;
                        break;
                    }
                } else {
                    if ($timeStr >= $s->start_time && $timeStr < $s->end_time) {
                        $currentShift = $s->name;
                        break;
                    }
                }
            }
        }

        // Default fallback
        if (!$currentShift) {
            $currentShift = $shifts->first()->name ?? 'Shift 1 - Pagi';
        }

        // Ambil semua area checkpoint kebersihan — filter per plant penempatan jika ada
        $checkpointQuery = CleaningCheckpoint::orderBy('plant')->orderBy('sub_area')->orderBy('area_name');
        if ($assignment && $assignment->plant) {
            $checkpointQuery->where('plant', $assignment->plant);
        }
        $checkpoints = $checkpointQuery->get();

        // Hitung area yang sudah dikunjungi petugas kebersihan hari ini
        $todayCheckedCodes = CleaningCheckpointLog::whereDate('checked_at', Carbon::today())
            ->where('is_evident_report', false)
            ->where('user_id', auth()->id())
            ->pluck('checkpoint_id')
            ->toArray();

        $assignedPlant = $assignment->plant ?? null;

        return view('cleaning.scan', compact('currentShift', 'shifts', 'checkpoints', 'todayCheckedCodes', 'assignedPlant'));
    }

    /**
     * Memproses Laporan / Audit Kebersihan yang dikirim Petugas
     */
    public function submitLog(Request $request)
    {
        $request->validate([
            'barcode_code' => 'required|string',
            'status' => 'required|string',
            'shift' => 'required|string',
            'notes' => 'nullable|string',
            'duration_minutes' => 'nullable|integer',
            'is_evident_report' => 'nullable|boolean',
            'selfie' => 'nullable|image|max:10240', // max 10MB
            'attachment' => 'nullable|image|max:10240', // max 10MB
        ]);

        $isEvident = $request->boolean('is_evident_report');

        // Cari checkpoint kebersihan berdasarkan barcode_code
        $checkpoint = CleaningCheckpoint::where('code', $request->barcode_code)->first();

        if (!$checkpoint && !$isEvident) {
            return back()->withErrors(['barcode_code' => 'Kode Barcode tidak valid.'])->withInput();
        }

        // Validasi: Selfie Wajib untuk regular checkpoint
        if (!$isEvident && !$request->hasFile('selfie')) {
            return back()->withErrors(['selfie' => 'Bukti foto selfie di area kebersihan wajib diunggah.'])->withInput();
        }

        // Validasi: Attachment Wajib jika status bukan Bersih & Rapi
        if (!$isEvident && !in_array($request->status, ['Bersih & Rapi', 'Sangat Bersih']) && !$request->hasFile('attachment')) {
            return back()->withErrors(['attachment' => 'Bukti foto temuan wajib diunggah untuk status area kotor/bermasalah.'])->withInput();
        }

        // Upload Selfie jika ada
        $selfiePath = null;
        if ($request->hasFile('selfie')) {
            $selfiePath = FileUploadHelper::upload(
                $request->file('selfie'),
                'cleaning_selfies',
                'cln_sf'
            );
        }

        // Upload Attachment jika ada
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = FileUploadHelper::upload(
                $request->file('attachment'),
                'cleaning_attachments',
                'cln_att'
            );
        }

        // Simpan log
        CleaningCheckpointLog::create([
            'user_id' => auth()->id(),
            'checkpoint_id' => $checkpoint ? $checkpoint->id : null,
            'shift' => $request->shift,
            'checked_at' => now(),
            'status' => $request->status,
            'notes' => $request->notes,
            'selfie_path' => $selfiePath,
            'attachment_path' => $attachmentPath,
            'is_evident_report' => $isEvident,
            'duration_minutes' => $request->duration_minutes ?? ($checkpoint ? $checkpoint->target_duration : 0),
        ]);

        return redirect()->route('cleaning.scan')->with('success', 'Laporan pengecekan kebersihan untuk ' . ($checkpoint ? $checkpoint->area_name : 'Eviden Report Kebersihan') . ' berhasil disimpan!');
    }

    /**
     * Ekspor Rekap Log Kebersihan ke Excel
     */
    public function exportExcel(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasRole(['admin', 'super_admin', 'manager'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        $filters = $request->only(['date_from', 'date_to', 'plant', 'shift', 'status', 'timeframe']);
        $filename = 'rekap_cleaning_checkpoint_' . now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new CleaningCheckpointExport($filters), $filename);
    }

    /**
     * Update Area Checkpoint Kebersihan (Admin / Super Admin)
     */
    public function updateCheckpoint(Request $request, CleaningCheckpoint $checkpoint)
    {
        $request->validate([
            'plant'           => 'required|string|max:100',
            'sub_area'        => 'nullable|string|max:100',
            'area_name'       => 'required|string|max:100',
            'target_duration' => 'required|integer|min:1|max:120',
        ]);

        $plantClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->plant));
        if ($plantClean === 'PLANTA' || $plantClean === 'A') {
            $plantCode = 'A';
        } elseif ($plantClean === 'PLANTB' || $plantClean === 'B') {
            $plantCode = 'B';
        } else {
            $plantCode = substr($plantClean, 0, 5);
        }

        $cleanAreaName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '-', $request->area_name));
        $code = 'CP-CLN-' . $plantCode . '-' . $cleanAreaName;

        if ($checkpoint->code !== $code) {
            $exists = CleaningCheckpoint::where('code', $code)->where('id', '!=', $checkpoint->id)->exists();
            if ($exists) {
                $code .= '-' . rand(10, 99);
            }
        } else {
            $code = $checkpoint->code;
        }

        $checkpoint->update([
            'plant'           => $request->plant,
            'sub_area'        => $request->sub_area ?: null,
            'area_name'       => $request->area_name,
            'code'            => $code,
            'target_duration' => $request->target_duration,
        ]);

        return back()->with('success', 'Area Checkpoint Kebersihan berhasil diperbarui.');
    }

    /**
     * Hapus Area Checkpoint Kebersihan (Admin / Super Admin)
     */
    public function destroyCheckpoint(CleaningCheckpoint $checkpoint)
    {
        $checkpoint->delete();
        return back()->with('success', 'Area Checkpoint Kebersihan berhasil dihapus.');
    }

    /**
     * Tampilan CRUD Shifts Kebersihan (Admin / Super Admin)
     */
    public function shiftsIndex()
    {
        $shifts = CleaningShift::orderBy('start_time')->get();
        return view('cleaning.shifts.index', compact('shifts'));
    }

    /**
     * Simpan Shift Kebersihan Baru
     */
    public function storeShift(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:cleaning_shifts,name',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        CleaningShift::create($request->all());
        return back()->with('success', 'Shift kebersihan berhasil ditambahkan.');
    }

    /**
     * Update Shift Kebersihan
     */
    public function updateShift(Request $request, CleaningShift $shift)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:cleaning_shifts,name,' . $shift->id,
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $shift->update($request->all());
        return back()->with('success', 'Shift kebersihan berhasil diperbarui.');
    }

    /**
     * Hapus Shift Kebersihan
     */
    public function destroyShift(CleaningShift $shift)
    {
        $shift->delete();
        return back()->with('success', 'Shift kebersihan berhasil dihapus.');
    }

    /**
     * Tampilan CRUD Assignments Kebersihan (Admin / Super Admin)
     */
    public function assignmentsIndex(Request $request)
    {
        $cleaners   = User::where('role', 'cleaning')->orderBy('name')->get();
        $shifts     = CleaningShift::orderBy('start_time')->get();
        $plantsList = CleaningCheckpoint::pluck('plant')->filter()->unique()->sort()->values();

        $query = CleaningAssignment::with('user', 'shift');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('assigned_date', $request->date);
        }

        $assignments = $query->orderBy('assigned_date', 'desc')->paginate(20)->withQueryString();

        return view('cleaning.assignments.index', compact('cleaners', 'shifts', 'assignments', 'plantsList'));
    }

    /**
     * Simpan Penugasan Kebersihan Baru
     */
    public function storeAssignment(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'shift_id'      => 'nullable|exists:cleaning_shifts,id',
            'plant'         => 'nullable|string|max:100',
            'assigned_date' => 'nullable|date',
            'notes'         => 'nullable|string',
        ]);

        $query = CleaningAssignment::where('user_id', $request->user_id);
        if ($request->filled('assigned_date')) {
            $query->whereDate('assigned_date', $request->assigned_date);
        } else {
            $query->whereNull('assigned_date');
        }

        if ($query->exists()) {
            $errMsg = $request->filled('assigned_date') 
                ? 'Petugas kebersihan ini sudah ditugaskan pada tanggal tersebut.'
                : 'Petugas kebersihan ini sudah memiliki penempatan permanen (tanpa tanggal).';
            return back()->withErrors(['assigned_date' => $errMsg])->withInput();
        }

        CleaningAssignment::create([
            'user_id'       => $request->user_id,
            'shift_id'      => $request->shift_id ?: null,
            'plant'         => $request->plant ?: null,
            'assigned_date' => $request->assigned_date ?: null,
            'notes'         => $request->notes,
        ]);

        return back()->with('success', 'Jadwal penugasan kebersihan berhasil ditambahkan.');
    }

    /**
     * Hapus Penugasan Kebersihan
     */
    public function destroyAssignment(CleaningAssignment $assignment)
    {
        $assignment->delete();
        return back()->with('success', 'Jadwal penugasan kebersihan berhasil dihapus.');
    }

    /**
     * Import Shift Kebersihan dari Excel
     */
    public function importShifts(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new CleaningShiftsImport;
            Excel::import($import, $request->file('file'));

            if (!empty($import->skippedRows)) {
                $warningMsg = '<strong>Import selesai, tetapi beberapa baris dilewati:</strong><br><ul class="mb-0 mt-1 pl-3">';
                foreach ($import->skippedRows as $skip) {
                    $warningMsg .= "<li>Baris {$skip['row']} ({$skip['shift']}): {$skip['reason']}</li>";
                }
                $warningMsg .= '</ul>';
                return back()->with('success', 'Data Shift kebersihan selesai diproses.')->with('info', $warningMsg);
            }

            return back()->with('success', 'Semua data Shift kebersihan berhasil di-import.');
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Gagal mengimpor file: ' . $e->getMessage()]);
        }
    }

    /**
     * Import Penjadwalan Kebersihan dari Excel
     */
    public function importAssignments(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new CleaningAssignmentsImport;
            Excel::import($import, $request->file('file'));

            if (!empty($import->skippedRows)) {
                $warningMsg = '<strong>Import selesai, tetapi beberapa baris dilewati:</strong><br><ul class="mb-0 mt-1 pl-3">';
                foreach ($import->skippedRows as $skip) {
                    $warningMsg .= "<li>Baris {$skip['row']} ({$skip['petugas']}): {$skip['reason']}</li>";
                }
                $warningMsg .= '</ul>';
                return back()->with('success', 'Data Penjadwalan kebersihan selesai diproses.')->with('info', $warningMsg);
            }

            return back()->with('success', 'Semua data Penjadwalan kebersihan berhasil di-import.');
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Gagal mengimpor file: ' . $e->getMessage()]);
        }
    }

    /**
     * Download Excel Template untuk Shift Kebersihan
     */
    public function downloadShiftTemplate()
    {
        return Excel::download(new CleaningShiftsTemplateExport, 'template_shift_kebersihan.xlsx');
    }

    /**
     * Download Excel Template untuk Penjadwalan Kebersihan
     */
    public function downloadAssignmentTemplate()
    {
        return Excel::download(new CleaningAssignmentsTemplateExport, 'template_penjadwalan_kebersihan.xlsx');
    }
}
