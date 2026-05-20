<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\FpuRequest;
use App\Models\User;
use App\Helpers\FileUploadHelper;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function index()
    {
        // Mendapatkan work order sesuai role
        $user = auth()->user();
        $query = WorkOrder::query()->with('user', 'assignedEngineer', 'fpuRequest');

        if ($user->role === 'client') {
            // Client/User hanya melihat tiket mereka sendiri
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'developer' || $user->role === 'engineer') {
            // Engineer melihat tiket yang ditugaskan kepada mereka
            $query->where('assigned_engineer_id', $user->id);
        }

        $workOrders = $query->latest()->get();

        // Anda bisa membuat view resources/views/work-orders/index.blade.php
        return view('work-orders.index', compact('workOrders'));
    }

    public function create()
    {
        // Hanya untuk client/user
        return view('work-orders.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'attachment'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = auth()->user();
        
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = FileUploadHelper::upload(
                $request->file('attachment'),
                'work_orders',
                'req'
            );
        }

        WorkOrder::create([
            'user_id'            => $user->id,
            'department'         => $user->company, // Menggunakan company sebagai department sementara
            'description'        => $request->description,
            'attachment_request' => $attachmentPath,
            'status'             => 'Menunggu Penugasan',
        ]);

        return redirect()->route('work-orders.index')->with('success', 'Work Order berhasil dibuat.');
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load('user', 'assignedEngineer', 'fpuRequest.materials', 'handover', 'progresses');
        
        // Ambil daftar engineer untuk assignment (untuk Admin/SPV)
        $engineers = User::whereIn('role', ['developer', 'engineer'])->get();

        return view('work-orders.show', compact('workOrder', 'engineers'));
    }

    public function assign(Request $request, WorkOrder $workOrder)
    {
        // Hanya Admin / SPV
        $request->validate([
            'assigned_engineer_id' => 'required|exists:users,id',
        ]);

        $workOrder->update([
            'assigned_engineer_id' => $request->assigned_engineer_id,
            'status'               => 'Pengecekan Engineer',
        ]);

        return back()->with('success', 'Teknisi berhasil ditugaskan.');
    }

    public function updateCheck(Request $request, WorkOrder $workOrder)
    {
        // Engineer update hasil pengecekan
        $request->validate([
            'check_result'   => 'required|string',
            'engineer_needs' => 'nullable|string',
            'needs_material' => 'nullable|boolean',
            'attachment'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $needsMaterial = $request->boolean('needs_material');
        $status        = $needsMaterial ? 'Menunggu Pengadaan Material' : 'Sedang Dikerjakan';
        
        $attachmentPath = $workOrder->attachment_check;
        if ($request->hasFile('attachment')) {
            // Hapus file lama jika ada sebelum upload yang baru
            FileUploadHelper::delete($workOrder->attachment_check);

            $attachmentPath = FileUploadHelper::upload(
                $request->file('attachment'),
                'work_orders',
                'chk'
            );
        }

        $workOrder->update([
            'check_result'     => $request->check_result,
            'engineer_needs'   => $request->engineer_needs,
            'attachment_check' => $attachmentPath,
            'status'           => $status,
        ]);

        // Trigger logic untuk FPU jika butuh material
        if ($needsMaterial && !$workOrder->fpuRequest) {
            $fpu = $workOrder->fpuRequest()->create([
                'priority'        => $request->priority ?? 'Biasa',
                'executor_type'   => $request->executor_type ?? 'Internal',
                'type'            => $request->type ?? 'Lainnya',
                'category'        => $request->category ?? 'Pengadaan',
                'status_approval' => 'Menunggu Verifikasi HRGA'
            ]);
            
            if ($request->has('materials') && is_array($request->materials)) {
                foreach ($request->materials as $mat) {
                    if (!empty($mat['name'])) {
                        $fpu->materials()->create([
                            'material_name' => $mat['name'],
                            'specification' => $mat['spec'] ?? null,
                            'qty'           => $mat['qty'] ?? 1,
                            'unit'          => 'Pcs',
                        ]);
                    }
                }
            }
        }

        return back()->with('success', 'Hasil pengecekan & pengajuan material berhasil diupdate.');
    }

    public function startWork(WorkOrder $workOrder)
    {
        // Pastikan tiket sedang dalam status yang valid untuk dimulai
        if ($workOrder->status === 'Menunggu Pengadaan Material') {
            $workOrder->update(['status' => 'Sedang Dikerjakan']);
            
            // Tambahkan initial progress log automatically
            $workOrder->progresses()->create([
                'description' => 'Mulai pengerjaan setelah material tersedia / disetujui.',
            ]);

            return back()->with('success', 'Status pengerjaan dimulai (Sedang Dikerjakan).');
        }

        return back()->with('error', 'Tidak dapat memulai pengerjaan dari status ini.');
    }

    public function storeProgress(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'description' => 'required|string',
            'attachment'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = FileUploadHelper::upload(
                $request->file('attachment'),
                'work_orders',
                'prog'
            );
        }

        $workOrder->progresses()->create([
            'description' => $request->description,
            'attachment'  => $attachmentPath,
        ]);

        return back()->with('success', 'Progress pekerjaan berhasil ditambahkan.');
    }

    public function updateReport(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'engineer_report' => 'required|string',
            'attachment'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        
        $attachmentPath = $workOrder->attachment_report;
        if ($request->hasFile('attachment')) {
            // Hapus file laporan lama jika ada
            FileUploadHelper::delete($workOrder->attachment_report);

            $attachmentPath = FileUploadHelper::upload(
                $request->file('attachment'),
                'work_orders',
                'rep'
            );
        }

        $workOrder->update([
            'engineer_report'   => $request->engineer_report,
            'attachment_report' => $attachmentPath,
            'status'            => 'Menunggu Serah Terima',
        ]);

        return back()->with('success', 'Laporan pekerjaan berhasil dikirim.');
    }

    public function print(WorkOrder $workOrder)
    {
        $workOrder->load('user', 'assignedEngineer', 'fpuRequest.materials');
        return view('print.work-order', compact('workOrder'));
    }
}
