@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
    <li class="breadcrumb-item active">Persetujuan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card support-shell-card mb-4">
            <div class="card-header border-0 bg-white pt-4 px-4 pb-2">
                <h3 class="card-title mb-0 font-weight-bold" style="font-size: 1.15rem;">Persetujuan Proyek Tertunda</h3>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div class="table-responsive">
                    <table class="table table-hover align-middle data-table mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-4 border-bottom-0 d-none d-md-table-cell">ID</th>
                                <th class="border-bottom-0 d-none d-sm-table-cell">Ticket</th>
                                <th class="border-bottom-0">Nama Proyek</th>
                                <th class="d-none d-md-table-cell border-bottom-0">Client</th>
                                <th class="border-bottom-0">Status Tiket</th>
                                <th class="d-none d-lg-table-cell border-bottom-0">Diajukan</th>
                                <th class="pr-4 border-bottom-0 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingApprovals as $approval)
                                <tr>
                                    <td class="pl-4 d-none d-md-table-cell">{{ $approval->projectRequest->id }}</td>
                                    <td class="d-none d-sm-table-cell"><span class="badge badge-dark">{{ $approval->projectRequest->ticket_number ?? '-' }}</span></td>
                                    <td>{{ $approval->projectRequest->project_name }}</td>
                                    <td class="d-none d-md-table-cell">{{ $approval->projectRequest->client->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $approval->projectRequest->ticket_status_badge_class }}" style="white-space: normal; text-align: center; display: inline-block;">{{ $approval->projectRequest->ticket_status_label }}</span>
                                    </td>
                                    <td class="d-none d-lg-table-cell">{{ $approval->created_at->format('d M Y H:i') }}</td>
                                    <td class="pr-4 text-right">
                                        <a href="{{ route('approvals.show', $approval) }}" class="btn btn-primary btn-sm rounded-pill shadow-sm">
                                            <i class="fas fa-eye"></i> <span class="d-none d-sm-inline">Tinjau</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 px-4 pb-4 pt-0">
                {{ $pendingApprovals->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
