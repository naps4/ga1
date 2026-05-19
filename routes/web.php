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
        
        $viewData['pendingFpu'] = \App\Models\FpuRequest::whereIn('status_approval', ['Menunggu Verifikasi HRGA', 'Menunggu Oprt. Manager'])->count();
        $viewData['recentWO'] = \App\Models\WorkOrder::orderBy('created_at', 'desc')->take(5)->get();
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
