<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\FpuRequest;
use App\Models\User;
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
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = auth()->user();
        
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_req_' . str_replace(' ', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/work_orders'), $filename);
            $attachmentPath = 'uploads/work_orders/' . $filename;
        }

        WorkOrder::create([
            'user_id' => $user->id,
            'department' => $user->company, // Menggunakan company sebagai department sementara
            'description' => $request->description,
            'attachment_request' => $attachmentPath,
            'status' => 'Menunggu Penugasan',
        ]);

        return redirect()->route('work-orders.index')->with('success', 'Work Order berhasil dibuat.');
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load('user', 'assignedEngineer', 'fpuRequest.materials', 'handover');
        
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
            'status' => 'Pengecekan Engineer',
        ]);

        return back()->with('success', 'Teknisi berhasil ditugaskan.');
    }

    public function updateCheck(Request $request, WorkOrder $workOrder)
    {
        // Engineer update hasil pengecekan
        $request->validate([
            'check_result' => 'required|string',
            'engineer_needs' => 'nullable|string',
            'needs_material' => 'required|boolean',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $status = $request->needs_material ? 'Menunggu Pengadaan Material' : 'Sedang Dikerjakan';
        
        $attachmentPath = $workOrder->attachment_check;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_chk_' . str_replace(' ', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/work_orders'), $filename);
            $attachmentPath = 'uploads/work_orders/' . $filename;
        }

        $workOrder->update([
            'check_result' => $request->check_result,
            'engineer_needs' => $request->engineer_needs,
            'attachment_check' => $attachmentPath,
            'status' => $status,
        ]);

        // Trigger logic untuk FPU jika butuh material
        if ($request->needs_material && !$workOrder->fpuRequest) {
            $fpu = $workOrder->fpuRequest()->create([
                'priority' => $request->priority ?? 'Biasa',
                'executor_type' => $request->executor_type ?? 'Internal',
                'type' => $request->type ?? 'Lainnya',
                'category' => $request->category ?? 'Pengadaan',
                'status_approval' => 'Menunggu Verifikasi HRGA'
            ]);
            
            if ($request->has('materials') && is_array($request->materials)) {
                foreach ($request->materials as $mat) {
                    if (!empty($mat['name'])) {
                        $fpu->materials()->create([
                            'material_name' => $mat['name'],
                            'specification' => $mat['spec'] ?? null,
                            'qty' => $mat['qty'] ?? 1,
                            'unit' => 'Pcs',
                        ]);
                    }
                }
            }
        }

        return back()->with('success', 'Hasil pengecekan & pengajuan material berhasil diupdate.');
    }

    public function updateReport(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'engineer_report' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        
        $attachmentPath = $workOrder->attachment_report;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_rep_' . str_replace(' ', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/work_orders'), $filename);
            $attachmentPath = 'uploads/work_orders/' . $filename;
        }

        $workOrder->update([
            'engineer_report' => $request->engineer_report,
            'attachment_report' => $attachmentPath,
            'status' => 'Menunggu Serah Terima',
        ]);

        return back()->with('success', 'Laporan pekerjaan berhasil dikirim.');
    }

    public function print(WorkOrder $workOrder)
    {
        $workOrder->load('user', 'assignedEngineer');
        return view('print.work-order', compact('workOrder'));
    }
}
