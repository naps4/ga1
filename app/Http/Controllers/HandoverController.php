<?php

namespace App\Http\Controllers;

use App\Models\WoHandover;
use App\Models\WorkOrder;
use Illuminate\Http\Request;

class HandoverController extends Controller
{
    public function show(WorkOrder $workOrder)
    {
        // Pastikan relasi handover terbentuk
        $handover = $workOrder->handover()->firstOrCreate([
            'work_order_id' => $workOrder->id,
        ]);

        $handover->load('workOrder.user', 'preparedBy', 'knownBy', 'acceptedBy');
        
        return view('handovers.show', compact('handover'));
    }

    public function update(Request $request, WoHandover $handover)
    {
        // Menyimpan data Form D (Disiapkan oleh HRGA)
        $request->validate([
            'service_name' => 'required|string',
            'qty' => 'required|numeric',
            'unit' => 'required|string',
            'specification' => 'nullable|string',
            'notes' => 'nullable|string',
            'handover_date' => 'required|date',
        ]);

        $handover->update([
            'service_name' => $request->service_name,
            'qty' => $request->qty,
            'unit' => $request->unit,
            'specification' => $request->specification,
            'notes' => $request->notes,
            'handover_date' => $request->handover_date,
            'prepared_by' => auth()->id(),
        ]);

        return back()->with('success', 'Berita Acara Penyerahan Hasil berhasil disimpan.');
    }

    // Approval Admin (Single Step)
    public function approveAll(WoHandover $handover)
    {
        $handover->update([
            'prepared_by' => auth()->id(),
            'known_by' => auth()->id(),
            'accepted_by' => auth()->id(), // Admin mewakili penerimaan jika perlu
            'accepted_by_user_at' => now(),
        ]);

        // Ubah status utama Work Order menjadi Selesai
        $handover->workOrder->update(['status' => 'Selesai']);

        return back()->with('success', 'Tiket telah diselesaikan secara resmi oleh Admin.');
    }

    public function print(WorkOrder $workOrder)
    {
        $handover = $workOrder->handover;
        if (!$handover) {
            abort(404, 'Berita Acara belum dibuat.');
        }
        
        $handover->load('workOrder.user', 'workOrder.assignedEngineer', 'preparedBy');
        return view('print.handover', compact('handover'));
    }

    public function acceptUser(Request $request, WoHandover $handover)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $handover->update([
            'accepted_by' => auth()->id(),
            'accepted_by_user_at' => now(),
            'rating' => $request->rating,
            'feedback' => $request->feedback,
        ]);

        // Ubah status utama Work Order menjadi Selesai
        $handover->workOrder->update(['status' => 'Selesai']);

        return back()->with('success', 'Anda telah menerima hasil pekerjaan dan memberikan penilaian. Tiket resmi ditutup.');
    }
}
