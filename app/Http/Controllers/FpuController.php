<?php

namespace App\Http\Controllers;

use App\Models\FpuRequest;
use App\Models\FpuMaterial;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FpuController extends Controller
{
    public function show(FpuRequest $fpu)
    {
        $fpu->load('workOrder', 'materials', 'verifiedByHrga', 'knownByManager', 'approvedByGm');
        return view('fpu.show', compact('fpu'));
    }

    public function update(Request $request, FpuRequest $fpu)
    {
        // Menyimpan data Form FPU (Oleh Admin / HRGA)
        $request->validate([
            'priority' => 'required|string',
            'executor_type' => 'required|string',
            'type' => 'required|string',
            'category' => 'required|string',
            'materials' => 'required|array',
            'materials.*.name' => 'required|string',
            'materials.*.qty' => 'required|numeric',
        ]);

        DB::transaction(function () use ($request, $fpu) {
            $fpu->update([
                'priority' => $request->priority,
                'executor_type' => $request->executor_type,
                'type' => $request->type,
                'category' => $request->category,
            ]);

            // Sync materials
            $fpu->materials()->delete(); // Clear old materials
            
            foreach ($request->materials as $mat) {
                $fpu->materials()->create([
                    'material_name' => $mat['name'],
                    'specification' => $mat['spec'] ?? null,
                    'qty' => $mat['qty'],
                    'unit' => $mat['unit'] ?? 'Pcs',
                    'notes' => $mat['notes'] ?? null,
                ]);
            }

            if ($request->action === 'verify') {
                $fpu->update([
                    'status_approval' => 'Menunggu Admin GA',
                    'verified_by_hrga' => auth()->id(),
                ]);
            }
        });

        $msg = 'Draft FPU berhasil disimpan.';
        if ($request->action === 'verify') {
            $msg = 'FPU berhasil disimpan & diverifikasi.';
        } elseif ($request->action === 'update') {
            $msg = 'Perubahan FPU berhasil disimpan.';
        }

        return redirect()->route('fpu.show', $fpu->id)->with('success', $msg);
    }

    // Approval Admin (Single Step)
    public function approveAll(FpuRequest $fpu)
    {
        $fpu->update([
            'status_approval' => 'Disetujui (Selesai)',
            'verified_by_hrga' => auth()->id(),
            'known_by_manager' => auth()->id(),
            'approved_by_gm' => auth()->id(),
            'approved_by_gm_at' => now(),
        ]);

        // Kembalikan WO ke status pengerjaan
        $fpu->workOrder->update(['status' => 'Sedang Dikerjakan']);

        return back()->with('success', 'FPU telah disetujui oleh Admin.');
    }

    public function print(FpuRequest $fpu)
    {
        $fpu->load('materials', 'workOrder.user', 'workOrder.assignedEngineer', 'verifiedByHrga', 'knownByManager', 'approvedByGm');
        return view('print.fpu', compact('fpu'));
    }
}
