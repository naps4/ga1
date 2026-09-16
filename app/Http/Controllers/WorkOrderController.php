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

    public function edit(WorkOrder $workOrder)
    {
        // Hanya bisa diedit oleh pembuat dan jika status masih 'Menunggu Penugasan'
        if ($workOrder->user_id !== auth()->id() || $workOrder->status !== 'Menunggu Penugasan') {
            return redirect()->route('work-orders.index')->withErrors(['error' => 'Work Order ini tidak dapat diedit.']);
        }

        return view('work-orders.edit', compact('workOrder'));
    }

    public function update(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->user_id !== auth()->id() || $workOrder->status !== 'Menunggu Penugasan') {
            return redirect()->route('work-orders.index')->withErrors(['error' => 'Work Order ini tidak dapat diubah.']);
        }

        $request->validate([
            'description' => 'required|string',
            'attachment'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $attachmentPath = $workOrder->attachment_request;
        if ($request->hasFile('attachment')) {
            FileUploadHelper::delete($workOrder->attachment_request);
            $attachmentPath = FileUploadHelper::upload(
                $request->file('attachment'),
                'work_orders',
                'req'
            );
        }

        $workOrder->update([
            'description'        => $request->description,
            'attachment_request' => $attachmentPath,
        ]);

        return redirect()->route('work-orders.index')->with('success', 'Work Order berhasil diperbarui.');
    }

    public function destroy(WorkOrder $workOrder)
    {
        if ($workOrder->user_id !== auth()->id() || !in_array($workOrder->status, ['Menunggu Penugasan', 'Ditolak'])) {
            return redirect()->route('work-orders.index')->withErrors(['error' => 'Work Order ini tidak dapat dihapus.']);
        }

        FileUploadHelper::delete($workOrder->attachment_request);
        $workOrder->delete();

        return redirect()->route('work-orders.index')->with('success', 'Work Order berhasil dihapus.');
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

    public function reject(Request $request, WorkOrder $workOrder)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isSuperAdmin() && !$user->isManager()) {
            return back()->withErrors(['error' => 'Hanya Admin dan Operational Manager yang dapat menolak tiket.']);
        }

        if ($workOrder->status !== 'Menunggu Penugasan') {
            return back()->withErrors(['error' => 'Hanya tiket dengan status Menunggu Penugasan yang dapat ditolak.']);
        }

        $request->validate([
            'rejection_note' => 'required|string|max:1000',
        ]);

        $workOrder->update([
            'status'         => 'Ditolak',
            'rejection_note' => $request->rejection_note,
        ]);

        // Catat di timeline progress
        $workOrder->progresses()->create([
            'description' => 'Tiket ditolak oleh ' . $user->name . ' (' . $user->role_label . '). Alasan: ' . $request->rejection_note,
        ]);

        return back()->with('success', 'Tiket Work Order berhasil ditolak.');
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
            'rejection_note'   => null,
            'is_check_approved'=> false,
        ]);

        // Trigger logic untuk FPU jika butuh material
        if ($needsMaterial) {
            if ($workOrder->fpuRequest) {
                // Update FPU yang sudah ada
                $fpu = $workOrder->fpuRequest;
                $fpu->update([
                    'priority'        => $request->priority ?? 'Biasa',
                    'executor_type'   => $request->executor_type ?? 'Internal',
                    'type'            => $request->type ?? 'Lainnya',
                    'category'        => $request->category ?? 'Pengadaan',
                    'status_approval' => 'Menunggu Verifikasi HRGA'
                ]);

                // Sinkronisasi material
                $fpu->materials()->delete();
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
            } else {
                // Buat FPU baru
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
        } else {
            // Jika tidak butuh material tapi sebelumnya ada FpuRequest, hapus FpuRequest
            if ($workOrder->fpuRequest) {
                $workOrder->fpuRequest->delete();
            }
        }

        return back()->with('success', 'Hasil pengecekan & pengajuan material berhasil diupdate.');
    }

    public function rejectCheck(Request $request, WorkOrder $workOrder)
    {
        // Pastikan hanya Admin / Super Admin yang bisa reject
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'super_admin') {
            return back()->withErrors(['error' => 'Hanya Admin yang dapat menolak hasil pengecekan.']);
        }

        $request->validate([
            'rejection_note' => 'required|string',
        ]);

        // Update status FPU jika ada ke Ditolak/Revisi alih-alih menghapusnya
        if ($workOrder->fpuRequest) {
            $workOrder->fpuRequest->update([
                'status_approval' => 'Ditolak/Revisi',
            ]);
        }

        // Update Work Order
        $workOrder->update([
            'status'         => 'Pengecekan Engineer',
            'rejection_note' => $request->rejection_note,
            'is_check_approved' => false,
        ]);

        // Tambah log progress untuk history
        $workOrder->progresses()->create([
            'description' => 'Hasil pengecekan ditolak oleh Admin. Catatan revisi: ' . $request->rejection_note,
        ]);

        return back()->with('success', 'Hasil pengecekan berhasil ditolak dan dikembalikan ke teknisi.');
    }

    public function approveCheck(Request $request, WorkOrder $workOrder)
    {
        // Pastikan hanya Admin / Super Admin yang bisa approve
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'super_admin') {
            return back()->withErrors(['error' => 'Hanya Admin yang dapat menyetujui hasil pengecekan.']);
        }

        // Tentukan status Work Order berikutnya: tetap Menunggu Pengadaan Material jika butuh material, atau langsung Sedang Dikerjakan jika tidak.
        $needsMaterial = $workOrder->fpuRequest ? true : false;
        $status = $needsMaterial ? 'Menunggu Pengadaan Material' : 'Sedang Dikerjakan';

        // Set is_check_approved ke true tetapi jangan langsung menyelesaikan/menyetujui FPU agar Admin dapat mengedit FPU terlebih dahulu
        $workOrder->update([
            'status' => $status,
            'is_check_approved' => true,
        ]);

        // Tambah log progress
        $workOrder->progresses()->create([
            'description' => 'Hasil pengecekan disetujui oleh Admin. ' . ($needsMaterial ? 'Menunggu pengadaan material & FPU.' : 'Pekerjaan dilanjutkan ke tahap pengerjaan.'),
        ]);

        return back()->with('success', 'Hasil pengecekan berhasil disetujui.');
    }

    public function startWork(WorkOrder $workOrder)
    {
        // Pastikan tiket sedang dalam status yang valid untuk dimulai, hasil cek telah disetujui, dan FPU telah diverifikasi/disetujui
        $fpuVerified = $workOrder->fpuRequest && in_array($workOrder->fpuRequest->status_approval, ['Menunggu Admin GA', 'Disetujui (Selesai)']);

        if ($workOrder->status === 'Menunggu Pengadaan Material' && $workOrder->is_check_approved && $fpuVerified) {
            $workOrder->update(['status' => 'Sedang Dikerjakan']);
            
            // Tambahkan initial progress log automatically
            $workOrder->progresses()->create([
                'description' => 'Mulai pengerjaan setelah material tersedia / disetujui.',
            ]);

            return back()->with('success', 'Status pengerjaan dimulai (Sedang Dikerjakan).');
        }

        return back()->with('error', 'Tidak dapat memulai pengerjaan. Pengajuan FPU (Form C) belum diverifikasi.');
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
