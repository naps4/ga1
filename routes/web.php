<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectRequestController;
use App\Http\Controllers\ProjectApprovalController;
use App\Http\Controllers\ProjectProgressController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DailyLogController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\FpuController;
use App\Http\Controllers\HandoverController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Fallback route untuk melayani file dari storage lokal tanpa perlu symlink public/storage
// Ini mengatasi masalah "file lampiran tidak bisa dibuka di hosting" karena symlink terputus.
Route::get('/storage/{path}', function ($path) {
    $absolutePath = realpath(storage_path('app/public/' . $path));
    $publicStoragePath = realpath(storage_path('app/public'));

    // Pastikan file valid dan berada secara rekursif di dalam direktori storage/app/public 
    // untuk mencegah directory traversal vulnerability (misal membaca file .env).
    if (!$absolutePath || !str_starts_with($absolutePath, $publicStoragePath) || !file_exists($absolutePath)) {
        abort(404);
    }

    // Hindari melayani folder (hanya file)
    if (is_dir($absolutePath)) {
        abort(404);
    }

    return response()->file($absolutePath);
})->where('path', '.*');

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    $user = auth()->user();
    
    $viewData = ['user' => $user];
    
    if ($user->isClient()) {
        $viewData['myTickets'] = \App\Models\WorkOrder::where('user_id', $user->id)->count();
        $viewData['activeTickets'] = \App\Models\WorkOrder::where('user_id', $user->id)
            ->whereNotIn('status', ['Selesai', 'Ditolak'])->count();
        $viewData['recentWO'] = \App\Models\WorkOrder::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')->take(5)->get();
    } elseif ($user->isDeveloper()) {
        $viewData['myTasks'] = \App\Models\WorkOrder::where('assigned_engineer_id', $user->id)
            ->whereNotIn('status', ['Selesai'])->count();
        $viewData['pendingChecks'] = \App\Models\WorkOrder::where('assigned_engineer_id', $user->id)
            ->where('status', 'Pengecekan Engineer')->count();
        $viewData['recentTasks'] = \App\Models\WorkOrder::where('assigned_engineer_id', $user->id)
            ->orderBy('updated_at', 'desc')->take(5)->get();
    } elseif ($user->isAdmin() || $user->isSuperAdmin()) {
        $viewData['totalWO'] = \App\Models\WorkOrder::count();
        $viewData['pendingAssignment'] = \App\Models\WorkOrder::where('status', 'Menunggu Penugasan')->count();
        $viewData['activeWO'] = \App\Models\WorkOrder::whereNotIn('status', ['Selesai', 'Ditolak'])->count();
        
        $viewData['pendingFpu'] = \App\Models\FpuRequest::whereIn('status_approval', ['Menunggu Verifikasi HRGA', 'Menunggu Admin GA'])->count();
        $viewData['recentWO'] = \App\Models\WorkOrder::orderBy('created_at', 'desc')->take(5)->get();

        // Security Checkpoint Stats untuk Admin
        $viewData['totalAreas'] = \App\Models\SecurityCheckpoint::count();
        $viewData['checkedToday'] = \App\Models\SecurityCheckpointLog::whereDate('checked_at', \Carbon\Carbon::today())
            ->where('is_evident_report', false)
            ->distinct('checkpoint_id')
            ->count();
        $viewData['anomaliesToday'] = \App\Models\SecurityCheckpointLog::whereDate('checked_at', \Carbon\Carbon::today())
            ->where('status', '!=', 'Aman')
            ->count();

        // Jadwal Shift Security Hari Ini
        $specificToday = \App\Models\SecurityAssignment::with('user', 'shift')
            ->whereDate('assigned_date', \Carbon\Carbon::today())
            ->get();
        $excludedUserIds = $specificToday->pluck('user_id')->toArray();
        $permanentToday = \App\Models\SecurityAssignment::with('user', 'shift')
            ->whereNull('assigned_date')
            ->whereNotIn('user_id', $excludedUserIds)
            ->get();
        $viewData['todayAssignments'] = $specificToday->merge($permanentToday);

        // Semua Jadwal Penugasan Security (Jadwal Keseluruhan)
        $viewData['allAssignments'] = \App\Models\SecurityAssignment::with('user', 'shift')
            ->orderByRaw('assigned_date IS NULL DESC')
            ->orderBy('assigned_date', 'desc')
            ->get();

    } elseif ($user->isManager()) {
        $viewData['pendingManagerAtk'] = \App\Models\AtkRequest::where('status_approval', 'Menunggu Persetujuan Manager')->count();
        $viewData['totalManagerAtkApproved'] = \App\Models\AtkRequest::where('manager_approved_by', $user->id)->count();
        $viewData['recentAtkRequests'] = \App\Models\AtkRequest::where('status_approval', 'Menunggu Persetujuan Manager')
            ->orWhere('manager_approved_by', $user->id)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // Security Checkpoint Stats untuk Manager
        $viewData['totalAreas'] = \App\Models\SecurityCheckpoint::count();
        $viewData['checkedToday'] = \App\Models\SecurityCheckpointLog::whereDate('checked_at', \Carbon\Carbon::today())
            ->where('is_evident_report', false)
            ->distinct('checkpoint_id')
            ->count();
        $viewData['anomaliesToday'] = \App\Models\SecurityCheckpointLog::whereDate('checked_at', \Carbon\Carbon::today())
            ->where('status', '!=', 'Aman')
            ->count();

        // Jadwal Shift Security Hari Ini
        $specificToday = \App\Models\SecurityAssignment::with('user', 'shift')
            ->whereDate('assigned_date', \Carbon\Carbon::today())
            ->get();
        $excludedUserIds = $specificToday->pluck('user_id')->toArray();
        $permanentToday = \App\Models\SecurityAssignment::with('user', 'shift')
            ->whereNull('assigned_date')
            ->whereNotIn('user_id', $excludedUserIds)
            ->get();
        $viewData['todayAssignments'] = $specificToday->merge($permanentToday);

        // Semua Jadwal Penugasan Security (Jadwal Keseluruhan)
        $viewData['allAssignments'] = \App\Models\SecurityAssignment::with('user', 'shift')
            ->orderByRaw('assigned_date IS NULL DESC')
            ->orderBy('assigned_date', 'desc')
            ->get();

    } elseif ($user->role === 'security') {
        // Security Guard langsung diarahkan ke halaman scan
        return redirect()->route('security.scan');
    }
    
    return view('dashboard', $viewData);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Project Request Routes (Authenticated Users)
Route::middleware(['auth'])->group(function () {
    Route::resource('project-requests', ProjectRequestController::class);
    Route::post('project-requests/{projectRequest}/submit', [ProjectRequestController::class, 'submitForApproval'])
        ->name('project-requests.submit');
    Route::post('project-requests/{projectRequest}/resolve', [ProjectRequestController::class, 'resolveTicket'])
        ->name('project-requests.resolve');
    Route::post('project-requests/{projectRequest}/pause', [ProjectRequestController::class, 'pauseTicket'])
        ->name('project-requests.pause');
    Route::post('project-requests/{projectRequest}/play', [ProjectRequestController::class, 'playTicket'])
        ->name('project-requests.play');
    Route::post('project-requests/{projectRequest}/close', [ProjectRequestController::class, 'closeTicket'])
        ->name('project-requests.close');
    Route::post('project-requests/{projectRequest}/upload-requirements', [ProjectRequestController::class, 'uploadRequirements'])
        ->name('project-requests.upload-requirements');
    Route::get('project-requirements/{requirement}/view', [ProjectRequestController::class, 'viewRequirement'])
        ->name('project-requirements.view');
    Route::get('project-requirements/{requirement}/download', [ProjectRequestController::class, 'downloadRequirement'])
        ->name('project-requirements.download');
    Route::delete('project-requirements/{requirement}', [ProjectRequestController::class, 'deleteRequirement'])
        ->name('project-requirements.delete');
});

// Queue Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('queues', \App\Http\Controllers\QueueController::class)->only(['index']);
});

// Daily Log Routes (Authenticated Users - own logs only)
Route::middleware(['auth'])->group(function () {
    Route::resource('daily-logs', DailyLogController::class);
});

// GA Work Order Routes
Route::middleware(['auth'])->group(function () {
    // Rute utama tiket
    Route::resource('work-orders', WorkOrderController::class);
    Route::post('work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign'])->name('work-orders.assign');
    Route::post('work-orders/{workOrder}/check', [WorkOrderController::class, 'updateCheck'])->name('work-orders.update-check');
    Route::post('work-orders/{workOrder}/reject-check', [WorkOrderController::class, 'rejectCheck'])->name('work-orders.reject-check');
    Route::post('work-orders/{workOrder}/approve-check', [WorkOrderController::class, 'approveCheck'])->name('work-orders.approve-check');
    Route::post('work-orders/{workOrder}/progress', [WorkOrderController::class, 'storeProgress'])->name('work-orders.store-progress');
    Route::post('work-orders/{workOrder}/start', [WorkOrderController::class, 'startWork'])->name('work-orders.start');
    Route::post('work-orders/{workOrder}/report', [WorkOrderController::class, 'updateReport'])->name('work-orders.update-report');
    Route::get('work-orders/{workOrder}/print', [WorkOrderController::class, 'print'])->name('work-orders.print');

    // Rute Form C (FPU & Material)
    Route::get('fpu/{fpu}', [FpuController::class, 'show'])->name('fpu.show');
    Route::post('fpu/{fpu}', [FpuController::class, 'update'])->name('fpu.update');
    Route::post('fpu/{fpu}/approve-all', [FpuController::class, 'approveAll'])->name('fpu.approve-all');
    Route::get('fpu/{fpu}/print', [FpuController::class, 'print'])->name('fpu.print');

    // Rute Form D (Berita Acara / Handover)
    Route::get('handovers/{workOrder}', [HandoverController::class, 'show'])->name('handovers.show');
    Route::post('handovers/{handover}', [HandoverController::class, 'update'])->name('handovers.update');
    Route::post('handovers/{handover}/approve-all', [HandoverController::class, 'approveAll'])->name('handovers.approve-all');
    Route::post('handovers/{handover}/accept', [HandoverController::class, 'acceptUser'])->name('handovers.accept');
    Route::get('handovers/{workOrder}/print', [HandoverController::class, 'print'])->name('handovers.print');

    // Rute Permintaan ATK (Alat Tulis Kantor)
    Route::get('atk-requests-export/pdf', [\App\Http\Controllers\AtkRequestController::class, 'exportPdf'])->name('atk-requests.export-pdf');
    Route::resource('atk-requests', \App\Http\Controllers\AtkRequestController::class);
    Route::post('atk-requests/{atkRequest}/approve', [\App\Http\Controllers\AtkRequestController::class, 'approve'])->name('atk-requests.approve');
    Route::post('atk-requests/{atkRequest}/reject', [\App\Http\Controllers\AtkRequestController::class, 'reject'])->name('atk-requests.reject');
    Route::post('atk-requests/{atkRequest}/handover', [\App\Http\Controllers\AtkRequestController::class, 'handover'])->name('atk-requests.handover');
    Route::get('atk-requests/{atkRequest}/print', [\App\Http\Controllers\AtkRequestController::class, 'print'])->name('atk-requests.print');

    // Rute Security Checkpoint
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/scan', [\App\Http\Controllers\SecurityCheckpointController::class, 'scanPage'])->name('scan');
        Route::post('/scan', [\App\Http\Controllers\SecurityCheckpointController::class, 'submitLog'])->name('submit');
        Route::get('/logs', [\App\Http\Controllers\SecurityCheckpointController::class, 'logsIndex'])->name('logs.index');
        Route::get('/checkpoints', [\App\Http\Controllers\SecurityCheckpointController::class, 'index'])->name('checkpoints.index');
        Route::post('/checkpoints', [\App\Http\Controllers\SecurityCheckpointController::class, 'storeCheckpoint'])->name('checkpoints.store');
        Route::put('/checkpoints/{checkpoint}', [\App\Http\Controllers\SecurityCheckpointController::class, 'updateCheckpoint'])->name('checkpoints.update');
        Route::delete('/checkpoints/{checkpoint}', [\App\Http\Controllers\SecurityCheckpointController::class, 'destroyCheckpoint'])->name('checkpoints.destroy');
        
        // Rute CRUD Shifts
        Route::get('/shifts', [\App\Http\Controllers\SecurityCheckpointController::class, 'shiftsIndex'])->name('shifts.index');
        Route::post('/shifts', [\App\Http\Controllers\SecurityCheckpointController::class, 'storeShift'])->name('shifts.store');
        Route::put('/shifts/{shift}', [\App\Http\Controllers\SecurityCheckpointController::class, 'updateShift'])->name('shifts.update');
        Route::delete('/shifts/{shift}', [\App\Http\Controllers\SecurityCheckpointController::class, 'destroyShift'])->name('shifts.destroy');
        Route::post('/shifts/import', [\App\Http\Controllers\SecurityCheckpointController::class, 'importShifts'])->name('shifts.import');
        Route::get('/shifts/template', [\App\Http\Controllers\SecurityCheckpointController::class, 'downloadShiftTemplate'])->name('shifts.template');

        // Rute CRUD Assignments (Penugasan)
        Route::get('/assignments', [\App\Http\Controllers\SecurityCheckpointController::class, 'assignmentsIndex'])->name('assignments.index');
        Route::post('/assignments', [\App\Http\Controllers\SecurityCheckpointController::class, 'storeAssignment'])->name('assignments.store');
        Route::delete('/assignments/{assignment}', [\App\Http\Controllers\SecurityCheckpointController::class, 'destroyAssignment'])->name('assignments.destroy');
        Route::post('/assignments/import', [\App\Http\Controllers\SecurityCheckpointController::class, 'importAssignments'])->name('assignments.import');
        Route::get('/assignments/template', [\App\Http\Controllers\SecurityCheckpointController::class, 'downloadAssignmentTemplate'])->name('assignments.template');

        Route::get('/export', [\App\Http\Controllers\SecurityCheckpointController::class, 'exportExcel'])->name('export');
    });
});

// Approval Routes (Admin and Super Admin only)
Route::middleware(['auth', 'role:admin,super_admin'])->prefix('approvals')->name('approvals.')->group(function () {
    Route::get('/', [ProjectApprovalController::class, 'index'])->name('index');
    Route::get('/{approval}', [ProjectApprovalController::class, 'show'])->name('show');
    Route::post('/{approval}/approve', [ProjectApprovalController::class, 'approve'])->name('approve');
    Route::post('/{approval}/reject', [ProjectApprovalController::class, 'reject'])->name('reject');
    Route::post('/{approval}/request-revision', [ProjectApprovalController::class, 'requestRevision'])->name('request-revision');
});

// Progress Tracking Routes (Authenticated Users)
Route::middleware(['auth'])->prefix('progress')->name('progress.')->group(function () {
    Route::get('/{queue}', [ProjectProgressController::class, 'show'])->name('show');
    Route::get('/{queue}/timeline', [ProjectProgressController::class, 'timeline'])->name('timeline');
    Route::post('/{queue}/update-stage', [ProjectProgressController::class, 'updateStage'])->name('update-stage');
    Route::post('/{queue}/log-activity', [ProjectProgressController::class, 'logActivity'])->name('log-activity');
});

// Chat Routes (Authenticated Users)
Route::middleware(['auth'])->prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('/create', [ChatController::class, 'create'])->name('create');
    Route::post('/', [ChatController::class, 'store'])->name('store');
    Route::get('/{conversation}', [ChatController::class, 'show'])->name('show');
    Route::post('/{conversation}/send', [ChatController::class, 'sendMessage'])->name('send');
    Route::post('/{conversation}/upload', [ChatController::class, 'uploadFile'])->name('upload');
    Route::post('/{conversation}/mark-read', [ChatController::class, 'markAsRead'])->name('mark-read');
    Route::post('/{conversation}/close', [ChatController::class, 'close'])->name('close');
    Route::post('/{conversation}/reopen', [ChatController::class, 'reopen'])->name('reopen');
    Route::get('/{conversation}/messages', [ChatController::class, 'getMessages'])->name('messages');
});

Route::middleware(['auth', 'role:admin,super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::resource('users', UserManagementController::class);
    Route::post('/users/{user}/activate', [UserManagementController::class, 'activate'])->name('users.activate');
    Route::post('/users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/users/{user}/suspend', [UserManagementController::class, 'suspend'])->name('users.suspend');
});

// Super Admin Routes
Route::middleware(['auth', 'super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/activity-logs', [SuperAdminController::class, 'activityLogs'])->name('activity-logs');
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [SuperAdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/reports', [SuperAdminController::class, 'reports'])->name('reports');
    Route::get('/reports/technical', [SuperAdminController::class, 'technicalReports'])->name('reports.technical');
    Route::get('/reports/technical/export/csv', [SuperAdminController::class, 'exportTechnicalReportCsv'])->name('reports.technical.export.csv');
    Route::get('/reports/technical/export/pdf', [SuperAdminController::class, 'exportTechnicalReportPdf'])->name('reports.technical.export.pdf');
    Route::get('/reports/export', [SuperAdminController::class, 'exportMonthlyCumulativeReport'])->name('reports.export');
    Route::get('/reports/export/pdf', [SuperAdminController::class, 'exportWorkOrderReportPdf'])->name('reports.export.pdf');

    // Master Barang / Categories & Items CRUD
    Route::resource('master-barang', App\Http\Controllers\MasterBarangController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('master-barang/items', [App\Http\Controllers\MasterBarangController::class, 'storeItem'])->name('master-barang.items.store');
    Route::put('master-barang/items/{item}', [App\Http\Controllers\MasterBarangController::class, 'updateItem'])->name('master-barang.items.update');
    Route::delete('master-barang/items/{item}', [App\Http\Controllers\MasterBarangController::class, 'destroyItem'])->name('master-barang.items.destroy');
});

// Chat API Routes for Popup Widget
Route::middleware(['auth'])->prefix('api/chat')->name('api.chat.')->group(function () {
    Route::get('/conversations', [App\Http\Controllers\ChatController::class, 'index'])->name('conversations');
    Route::get('/{conversation}/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('messages');
    Route::post('/{conversation}/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('send');
});

// Notification Routes (Authenticated)
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/counts', [App\Http\Controllers\NotificationController::class, 'getCounts'])->name('counts');
    Route::get('/list', [App\Http\Controllers\NotificationController::class, 'getNotifications'])->name('list');
    Route::get('/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read-get');
    Route::post('/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
});

// Report Routes (Export PDF)
Route::middleware(['auth'])->group(function () {
    Route::get('/reports/projects/pdf', [App\Http\Controllers\ReportController::class, 'exportPdf'])->name('reports.projects.pdf');
});

require __DIR__.'/auth.php';
