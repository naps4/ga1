<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SecurityCheckpoint;
use App\Models\SecurityCheckpointLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\SecurityShift;
use App\Models\SecurityAssignment;
use Tests\TestCase;

class SecurityCheckpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed data awal (Roles dan Checkpoints)
        $this->seed();
    }

    public function test_security_role_can_access_scan_page()
    {
        $securityUser = User::where('role', 'security')->first();

        $response = $this->actingAs($securityUser)
            ->get(route('security.scan'));

        $response->assertStatus(200);
        $response->assertViewIs('security.scan');
    }

    public function test_admin_role_can_access_checkpoints_and_logs_pages()
    {
        $adminUser = User::where('role', 'admin')->first();

        // Verifikasi rute checkpoints.index
        $response = $this->actingAs($adminUser)
            ->get(route('security.checkpoints.index'));

        $response->assertStatus(200);
        $response->assertViewIs('security.checkpoints.index');

        // Verifikasi rute logs.index
        $response2 = $this->actingAs($adminUser)
            ->get(route('security.logs.index'));

        $response2->assertStatus(200);
        $response2->assertViewIs('security.logs.index');
    }

    public function test_submit_regular_checkpoint_requires_selfie()
    {
        Storage::fake('public');
        $securityUser = User::where('role', 'security')->first();
        $checkpoint = SecurityCheckpoint::first();

        $response = $this->actingAs($securityUser)
            ->post(route('security.submit'), [
                'barcode_code' => $checkpoint->code,
                'status' => 'Aman',
                'shift' => 'Shift 2 - Sore',
                'notes' => 'Semua aman',
            ]);

        $response->assertSessionHasErrors('selfie');
        $this->assertEquals(0, SecurityCheckpointLog::count());
    }

    public function test_submit_regular_checkpoint_success_with_selfie()
    {
        Storage::fake('public');
        $securityUser = User::where('role', 'security')->first();
        $checkpoint = SecurityCheckpoint::first();
        $file = UploadedFile::fake()->image('selfie.jpg');

        $response = $this->actingAs($securityUser)
            ->post(route('security.submit'), [
                'barcode_code' => $checkpoint->code,
                'status' => 'Aman',
                'shift' => 'Shift 2 - Sore',
                'notes' => 'Semua aman',
                'selfie' => $file,
            ]);

        $response->assertRedirect(route('security.scan'));
        $response->assertSessionHasNoErrors();
        
        $this->assertEquals(1, SecurityCheckpointLog::count());
        $log = SecurityCheckpointLog::first();
        $this->assertEquals('Aman', $log->status);
        $this->assertNotNull($log->selfie_path);
        Storage::disk('public')->assertExists($log->selfie_path);
    }

    public function test_submit_evident_report_success_without_selfie()
    {
        $securityUser = User::where('role', 'security')->first();
        $checkpoint = SecurityCheckpoint::first();

        $response = $this->actingAs($securityUser)
            ->post(route('security.submit'), [
                'barcode_code' => $checkpoint->code,
                'status' => 'Listrik Belum Dimatikan',
                'shift' => 'Shift 3 - Malam',
                'notes' => 'Lampu ruangan menyala',
                'is_evident_report' => 1,
            ]);

        $response->assertRedirect(route('security.scan'));
        $response->assertSessionHasNoErrors();

        $this->assertEquals(1, SecurityCheckpointLog::count());
        $log = SecurityCheckpointLog::first();
        $this->assertEquals('Listrik Belum Dimatikan', $log->status);
        $this->assertTrue($log->is_evident_report);
        $this->assertNull($log->selfie_path);
    }

    public function test_crud_shift_actions()
    {
        $adminUser = User::where('role', 'super_admin')->first();

        // 1. Store new shift
        $response = $this->actingAs($adminUser)
            ->post(route('security.shifts.store'), [
                'name' => 'Shift Akhir Pekan',
                'start_time' => '12:00',
                'end_time' => '18:00',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('security_shifts', [
            'name' => 'Shift Akhir Pekan',
            'start_time' => '12:00:00',
            'end_time' => '18:00:00',
        ]);

        $shift = SecurityShift::where('name', 'Shift Akhir Pekan')->first();

        // 2. Update shift
        $response = $this->actingAs($adminUser)
            ->put(route('security.shifts.update', $shift->id), [
                'name' => 'Shift Akhir Pekan Edit',
                'start_time' => '13:00',
                'end_time' => '19:00',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('security_shifts', [
            'id' => $shift->id,
            'name' => 'Shift Akhir Pekan Edit',
            'start_time' => '13:00:00',
            'end_time' => '19:00:00',
        ]);

        // 3. Delete shift
        $response = $this->actingAs($adminUser)
            ->delete(route('security.shifts.destroy', $shift->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('security_shifts', [
            'id' => $shift->id,
        ]);
    }

    public function test_crud_assignment_actions()
    {
        $adminUser = User::where('role', 'super_admin')->first();
        $securityUser = User::where('role', 'security')->first();
        $shift = SecurityShift::first();

        // 1. Store assignment
        $response = $this->actingAs($adminUser)
            ->post(route('security.assignments.store'), [
                'user_id' => $securityUser->id,
                'shift_id' => $shift->id,
                'assigned_date' => '2026-07-20',
                'notes' => 'Tugas Senin',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('security_assignments', [
            'user_id' => $securityUser->id,
            'shift_id' => $shift->id,
            'assigned_date' => '2026-07-20',
        ]);

        // 2. Prevent duplicate assignment for same user on same day
        $response = $this->actingAs($adminUser)
            ->post(route('security.assignments.store'), [
                'user_id' => $securityUser->id,
                'shift_id' => $shift->id,
                'assigned_date' => '2026-07-20',
            ]);

        $response->assertSessionHasErrors('assigned_date');

        // 3. Delete assignment
        $assignment = SecurityAssignment::first();
        $response = $this->actingAs($adminUser)
            ->delete(route('security.assignments.destroy', $assignment->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('security_assignments', [
            'id' => $assignment->id,
        ]);
    }

    public function test_scan_page_preselects_assigned_shift()
    {
        $securityUser = User::where('role', 'security')->first();
        
        // Create custom shift
        $shift = SecurityShift::create([
            'name' => 'Shift Khusus Pagi',
            'start_time' => '06:00:00',
            'end_time' => '14:00:00',
        ]);

        // Assign to security user for today
        SecurityAssignment::create([
            'user_id' => $securityUser->id,
            'shift_id' => $shift->id,
            'assigned_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($securityUser)
            ->get(route('security.scan'));

        $response->assertStatus(200);
        $response->assertViewHas('currentShift', 'Shift Khusus Pagi');
    }
}
