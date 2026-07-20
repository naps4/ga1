<?php

namespace App\Http\Controllers;

use App\Models\SecurityCheckpoint;
use App\Models\SecurityCheckpointLog;
use App\Models\SecurityShift;
use App\Models\SecurityAssignment;
use App\Models\User;
use App\Helpers\FileUploadHelper;
use App\Exports\SecurityCheckpointExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class SecurityCheckpointController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Tampilan Master Area Checkpoint (Admin / Super Admin)
     */
    public function index()
    {
        $user = auth()->user();

        // Security Guard diarahkan langsung ke halaman scan
        if ($user->role === 'security') {
            return redirect()->route('security.scan');
        }

        // Hanya Admin & Super Admin yang bisa mengakses master area
        if (!$user->hasRole(['admin', 'super_admin'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        $checkpoints = SecurityCheckpoint::orderBy('plant')->orderBy('sub_area')->orderBy('area_name')->get();
        $existingPlants   = SecurityCheckpoint::pluck('plant')->filter()->unique()->values();
        $existingSubAreas = SecurityCheckpoint::pluck('sub_area')->filter()->unique()->values();

        return view('security.checkpoints.index', compact('checkpoints', 'existingPlants', 'existingSubAreas'));
    }

    /**
     * Tampilan Rekap Log Hasil Patroli (Admin / Super Admin)
     */
    public function logsIndex(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'security') {
            return redirect()->route('security.scan');
        }

        if (!$user->hasRole(['admin', 'super_admin', 'manager'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        $checkpoints = SecurityCheckpoint::orderBy('plant')->orderBy('area_name')->get();
        $existingPlants = SecurityCheckpoint::pluck('plant')->filter()->unique()->values();

        // Query Logs
        $logsQuery = SecurityCheckpointLog::with('user', 'checkpoint');

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

        return view('security.logs.index', compact('checkpoints', 'logs', 'existingPlants'));
    }

    /**
     * Simpan Area Checkpoint Baru (Admin / Super Admin)
     */
    public function storeCheckpoint(Request $request)
    {
        $request->validate([
            'plant'           => 'required|string|max:100',
            'sub_area'        => 'nullable|string|max:100',
            'area_name'       => 'required|string|max:100',
            'target_duration' => 'required|integer|min:1|max:120',
        ]);

        // Generate Code secara otomatis berdasarkan nama Plant/Gedung
        $plantClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->plant));
        if ($plantClean === 'PLANTA' || $plantClean === 'A') {
            $plantCode = 'A';
        } elseif ($plantClean === 'PLANTB' || $plantClean === 'B') {
            $plantCode = 'B';
        } else {
            $plantCode = substr($plantClean, 0, 5);
        }

        $cleanAreaName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '-', $request->area_name));
        $code = 'CP-' . $plantCode . '-' . $cleanAreaName;

        // Cek keunikan code
        $exists = SecurityCheckpoint::where('code', $code)->exists();
        if ($exists) {
            $code .= '-' . rand(10, 99);
        }

        SecurityCheckpoint::create([
            'plant'           => $request->plant,
            'sub_area'        => $request->sub_area ?: null,
            'area_name'       => $request->area_name,
            'code'            => $code,
            'target_duration' => $request->target_duration,
        ]);

        return back()->with('success', 'Area Checkpoint berhasil ditambahkan dengan Kode: ' . $code);
    }

    /**
     * Halaman Scan QR Code / Check-in untuk Petugas
     */
    public function scanPage()
    {
        $user = auth()->user();
        $shifts = SecurityShift::orderBy('start_time')->get();
        
        // 1. Cek apakah ada penugasan khusus hari ini
        $assignment = SecurityAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereDate('assigned_date', Carbon::today())
            ->first();

        // 1.5. Cek penugasan permanen / default (assigned_date is null) jika tidak ada penugasan hari ini
        if (!$assignment) {
            $assignment = SecurityAssignment::with('shift')
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
                if ($s->start_time > $s->end_time) { // Shift melewati tengah malam (cross-day)
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

        // Ambil semua area checkpoint — filter per plant penempatan jika ada
        $checkpointQuery = SecurityCheckpoint::orderBy('plant')->orderBy('sub_area')->orderBy('area_name');
        if ($assignment && $assignment->plant) {
            $checkpointQuery->where('plant', $assignment->plant);
        }
        $checkpoints = $checkpointQuery->get();

        // Hitung area yang sudah dikunjungi petugas hari ini
        $todayCheckedCodes = SecurityCheckpointLog::whereDate('checked_at', Carbon::today())
            ->where('is_evident_report', false)
            ->where('user_id', auth()->id())
            ->pluck('checkpoint_id')
            ->toArray();

        $assignedPlant = $assignment->plant ?? null;

        return view('security.scan', compact('currentShift', 'shifts', 'checkpoints', 'todayCheckedCodes', 'assignedPlant'));
    }

    /**
     * Memproses Laporan / Audit Checkpoint yang dikirim Guard
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

        // Cari checkpoint berdasarkan barcode_code
        $checkpoint = SecurityCheckpoint::where('code', $request->barcode_code)->first();

        if (!$checkpoint && !$isEvident) {
            return back()->withErrors(['barcode_code' => 'Kode Barcode tidak valid.'])->withInput();
        }

        // Validasi: Selfie Wajib untuk regular checkpoint
        if (!$isEvident && !$request->hasFile('selfie')) {
            return back()->withErrors(['selfie' => 'Bukti foto selfie di area wajib diunggah untuk monitoring area.'])->withInput();
        }

        // Validasi: Attachment Wajib jika status bukan Aman (regular checkpoint)
        if (!$isEvident && $request->status !== 'Aman' && !$request->hasFile('attachment')) {
            return back()->withErrors(['attachment' => 'Bukti foto temuan wajib diunggah untuk status area bermasalah/temuan.'])->withInput();
        }

        // Upload Selfie jika ada
        $selfiePath = null;
        if ($request->hasFile('selfie')) {
            $selfiePath = FileUploadHelper::upload(
                $request->file('selfie'),
                'security_selfies',
                'sf'
            );
        }

        // Upload Attachment jika ada
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = FileUploadHelper::upload(
                $request->file('attachment'),
                'security_attachments',
                'att'
            );
        }

        // Simpan log
        $log = SecurityCheckpointLog::create([
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

        return redirect()->route('security.scan')->with('success', 'Laporan monitoring untuk ' . ($checkpoint ? $checkpoint->area_name : 'Eviden Report') . ' berhasil disimpan!');
    }

    /**
     * Ekspor Rekap Log ke Excel (Format mirip Google Sheet)
     */
    public function exportExcel(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasRole(['admin', 'super_admin', 'manager'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        $filters = $request->only(['date_from', 'date_to', 'plant', 'shift', 'status', 'timeframe']);
        $filename = 'rekap_security_checkpoint_' . now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new SecurityCheckpointExport($filters), $filename);
    }

    /**
     * Update Area Checkpoint (Admin / Super Admin)
     */
    public function updateCheckpoint(Request $request, SecurityCheckpoint $checkpoint)
    {
        $request->validate([
            'plant'           => 'required|string|max:100',
            'sub_area'        => 'nullable|string|max:100',
            'area_name'       => 'required|string|max:100',
            'target_duration' => 'required|integer|min:1|max:120',
        ]);

        // Generate Code secara otomatis jika ada perubahan plant/area_name
        $plantClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->plant));
        if ($plantClean === 'PLANTA' || $plantClean === 'A') {
            $plantCode = 'A';
        } elseif ($plantClean === 'PLANTB' || $plantClean === 'B') {
            $plantCode = 'B';
        } else {
            $plantCode = substr($plantClean, 0, 5);
        }

        $cleanAreaName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '-', $request->area_name));
        $code = 'CP-' . $plantCode . '-' . $cleanAreaName;

        // Cek keunikan code (kecuali checkpoint saat ini)
        if ($checkpoint->code !== $code) {
            $exists = SecurityCheckpoint::where('code', $code)->where('id', '!=', $checkpoint->id)->exists();
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

        return back()->with('success', 'Area Checkpoint berhasil diperbarui.');
    }

    /**
     * Hapus Area Checkpoint (Admin / Super Admin)
     */
    public function destroyCheckpoint(SecurityCheckpoint $checkpoint)
    {
        $checkpoint->delete();
        return back()->with('success', 'Area Checkpoint berhasil dihapus.');
    }

    /**
     * Tampilan CRUD Shifts (Admin / Super Admin)
     */
    public function shiftsIndex()
    {
        $shifts = SecurityShift::orderBy('start_time')->get();
        return view('security.shifts.index', compact('shifts'));
    }

    /**
     * Simpan Shift Baru
     */
    public function storeShift(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:security_shifts,name',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        SecurityShift::create($request->all());
        return back()->with('success', 'Shift berhasil ditambahkan.');
    }

    /**
     * Update Shift
     */
    public function updateShift(Request $request, SecurityShift $shift)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:security_shifts,name,' . $shift->id,
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $shift->update($request->all());
        return back()->with('success', 'Shift berhasil diperbarui.');
    }

    /**
     * Hapus Shift
     */
    public function destroyShift(SecurityShift $shift)
    {
        $shift->delete();
        return back()->with('success', 'Shift berhasil dihapus.');
    }

    /**
     * Tampilan CRUD Assignments (Admin / Super Admin)
     */
    public function assignmentsIndex(Request $request)
    {
        $guards      = User::where('role', 'security')->orderBy('name')->get();
        $shifts      = SecurityShift::orderBy('start_time')->get();
        $plantsList  = SecurityCheckpoint::pluck('plant')->filter()->unique()->sort()->values();

        // Query penugasan
        $query = SecurityAssignment::with('user', 'shift');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('assigned_date', $request->date);
        }

        $assignments = $query->orderBy('assigned_date', 'desc')->paginate(20)->withQueryString();

        return view('security.assignments.index', compact('guards', 'shifts', 'assignments', 'plantsList'));
    }

    /**
     * Simpan Penugasan Security Baru
     */
    public function storeAssignment(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'shift_id'      => 'nullable|exists:security_shifts,id',
            'plant'         => 'nullable|string|max:100',
            'assigned_date' => 'nullable|date',
            'notes'         => 'nullable|string',
        ]);

        // Cek duplikasi penugasan untuk user pada tanggal tersebut atau penempatan permanen
        $query = SecurityAssignment::where('user_id', $request->user_id);
        if ($request->filled('assigned_date')) {
            $query->whereDate('assigned_date', $request->assigned_date);
        } else {
            $query->whereNull('assigned_date');
        }

        $exists = $query->exists();

        if ($exists) {
            $errMsg = $request->filled('assigned_date') 
                ? 'Petugas keamanan ini sudah ditugaskan pada tanggal tersebut.'
                : 'Petugas keamanan ini sudah memiliki penempatan permanen (tanpa tanggal).';
            return back()->withErrors(['assigned_date' => $errMsg])->withInput();
        }

        SecurityAssignment::create([
            'user_id'       => $request->user_id,
            'shift_id'      => $request->shift_id ?: null,
            'plant'         => $request->plant ?: null,
            'assigned_date' => $request->assigned_date ?: null,
            'notes'         => $request->notes,
        ]);

        return back()->with('success', 'Jadwal penugasan security berhasil ditambahkan.');
    }

    /**
     * Hapus Penugasan Security
     */
    public function destroyAssignment(SecurityAssignment $assignment)
    {
        $assignment->delete();
        return back()->with('success', 'Jadwal penugasan security berhasil dihapus.');
    }
}
