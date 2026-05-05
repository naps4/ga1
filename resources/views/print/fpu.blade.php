@extends('layouts.print')

@section('title', 'FPU - ' . str_pad($fpu->id, 4, '0', STR_PAD_LEFT))

@section('content')
    <h4 class="doc-title">FORMULIR PELAYANAN UMUM (FPU)</h4>
    <p class="doc-number">Terkait Tiket No: WO-{{ str_pad($fpu->workOrder->id, 4, '0', STR_PAD_LEFT) }}</p>

    <table class="table table-bordered table-print mb-4">
        <tr>
            <th width="25%">Prioritas</th>
            <td width="25%">{{ $fpu->priority ?? '-' }}</td>
            <th width="25%">Jenis Pekerjaan</th>
            <td width="25%">{{ $fpu->type ?? '-' }}</td>
        </tr>
        <tr>
            <th>Pelaksana</th>
            <td>{{ $fpu->executor_type ?? '-' }}</td>
            <th>Kategori</th>
            <td>{{ $fpu->category ?? '-' }}</td>
        </tr>
        <tr>
            <th>Pemohon / Dept</th>
            <td colspan="3">{{ $fpu->workOrder->user->name ?? '-' }} ({{ $fpu->workOrder->department ?? '-' }})</td>
        </tr>
    </table>

    <h5 class="mt-4">DAFTAR KEBUTUHAN MATERIAL (FORM C)</h5>
    <table class="table table-bordered table-print">
        <thead>
            <tr>
                <th width="5%" class="text-center">No.</th>
                <th>Nama Barang / Material</th>
                <th>Spesifikasi</th>
                <th width="10%" class="text-center">Qty</th>
                <th width="15%" class="text-center">Satuan</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fpu->materials as $index => $mat)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $mat->material_name }}</td>
                    <td>{{ $mat->specification ?? '-' }}</td>
                    <td class="text-center">{{ $mat->qty }}</td>
                    <td class="text-center">{{ $mat->unit }}</td>
                    <td>{{ $mat->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada daftar material.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-box mt-5 clearfix">
        <div class="signature-col">
            <p>Dibuat Oleh,</p>
            <div class="signature-space">
                <!-- Spasi / Ttd Digital -->
            </div>
            <p class="signature-name">{{ $fpu->workOrder->assignedEngineer->name ?? '.......................' }}</p>
            <small>Teknisi</small>
        </div>
        <div class="signature-col">
            <p>Diverifikasi Oleh,</p>
            <div class="signature-space">
                @if($fpu->verified_by_hrga)
                    <span style="display:inline-block; margin-top:30px; font-style:italic; font-size:12px; color:green;">V. Approved</span>
                @endif
            </div>
            <p class="signature-name">{{ $fpu->verifiedByHrga->name ?? '.......................' }}</p>
            <small>Admin HRGA</small>
        </div>
        <div class="signature-col">
            <p>Diketahui Oleh,</p>
            <div class="signature-space">
                @if($fpu->known_by_manager)
                    <span style="display:inline-block; margin-top:30px; font-style:italic; font-size:12px; color:green;">V. Approved</span>
                @endif
            </div>
            <p class="signature-name">{{ $fpu->knownByManager->name ?? '.......................' }}</p>
            <small>Oprt. Manager</small>
        </div>
        <div class="signature-col">
            <p>Disetujui Oleh,</p>
            <div class="signature-space">
                @if($fpu->approved_by_gm)
                    <span style="display:inline-block; margin-top:30px; font-style:italic; font-size:12px; color:green;">V. Approved</span>
                @endif
            </div>
            <p class="signature-name">{{ $fpu->approvedByGm->name ?? '.......................' }}</p>
            <small>Direktur / GM</small>
        </div>
    </div>
@endsection
