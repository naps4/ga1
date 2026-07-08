<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AtkRequest;
use App\Models\AtkRequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AtkApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_atk_approval_flow()
    {
        $this->withoutExceptionHandling();
        
        // 1. Create Users
        $client = User::factory()->create(['role' => 'client']);
        $manager = User::factory()->create(['role' => 'manager']);
        $admin = User::factory()->create(['role' => 'admin']);

        // 2. Create ATK Request in Draft status
        $atkRequest = AtkRequest::create([
            'user_id' => $client->id,
            'nama' => $client->name,
            'divisi' => 'IT',
            'periode' => 'Juli 2026',
            'tanggal_pengajuan' => now(),
            'status_approval' => 'Draft',
            'request_type' => 'ATK',
        ]);

        $item1 = AtkRequestItem::create([
            'atk_request_id' => $atkRequest->id,
            'item_name' => 'Kertas A4',
            'quantity' => 5,
            'unit' => 'Rim',
            'is_custom' => false,
            'status_approval' => 'Pending',
        ]);

        $item2 = AtkRequestItem::create([
            'atk_request_id' => $atkRequest->id,
            'item_name' => 'Spidol Hitam',
            'quantity' => 10,
            'unit' => 'Pcs',
            'is_custom' => false,
            'status_approval' => 'Pending',
        ]);

        // 3. Submit request (transitions to Menunggu Persetujuan Manager)
        $this->actingAs($client);
        $response = $this->put(route('atk-requests.update', $atkRequest->id), [
            'nama' => $client->name,
            'divisi' => 'IT',
            'periode' => 'Juli 2026',
            'tanggal_pengajuan' => now()->format('Y-m-d'),
            'request_type' => 'ATK',
            'items' => [
                'Kertas A4' => [
                    'qty' => 5,
                    'unit' => 'Rim',
                ],
                'Spidol Hitam' => [
                    'qty' => 10,
                    'unit' => 'Pcs',
                ]
            ],
            'action' => 'submit',
        ]);

        $response->assertRedirect();
        $atkRequest->refresh();
        $this->assertEquals('Menunggu Persetujuan Manager', $atkRequest->status_approval);

        // Fetch newly created item IDs
        $items = $atkRequest->items;
        $newItem1 = $items->firstWhere('item_name', 'Kertas A4');
        $newItem2 = $items->firstWhere('item_name', 'Spidol Hitam');

        $this->assertNotNull($newItem1);
        $this->assertNotNull($newItem2);

        // 4. Manager reviews request
        $this->actingAs($manager);

        $response = $this->post(route('atk-requests.approve', $atkRequest->id), [
            'items' => [
                $newItem1->id => ['status' => 'Disetujui', 'notes' => ''],
                $newItem2->id => ['status' => 'Ditolak', 'notes' => 'Stok berlebih'],
            ],
            'manager_notes' => 'Sebagian disetujui.',
        ]);

        $response->assertRedirect();
        $atkRequest->refresh();
        $newItem1->refresh();
        $newItem2->refresh();

        $this->assertEquals('Menunggu Persetujuan Admin GA', $atkRequest->status_approval);
        $this->assertEquals('Disetujui', $newItem1->status_approval);
        $this->assertEquals('Ditolak', $newItem2->status_approval);
        $this->assertEquals('Stok berlebih', $newItem2->notes);
        $this->assertEquals($manager->id, $atkRequest->manager_approved_by);
        $this->assertEquals('Sebagian disetujui.', $atkRequest->manager_notes);

        // 5. Admin GA reviews request
        $this->actingAs($admin);
        $response = $this->post(route('atk-requests.approve', $atkRequest->id), [
            'items' => [
                $newItem1->id => ['status' => 'Disetujui', 'notes' => 'Ok'],
                $newItem2->id => ['status' => 'Ditolak', 'notes' => 'Tetap ditolak karena stok berlebih'],
            ],
            'notes' => 'Selesai diverifikasi GA.',
        ]);

        $response->assertRedirect();
        $atkRequest->refresh();
        $newItem1->refresh();
        $newItem2->refresh();

        $this->assertEquals('Disetujui', $atkRequest->status_approval);
        $this->assertEquals('Disetujui', $newItem1->status_approval);
        $this->assertEquals('Ditolak', $newItem2->status_approval);
        $this->assertEquals('Tetap ditolak karena stok berlebih', $newItem2->notes);
        $this->assertEquals($admin->id, $atkRequest->approved_by);
        $this->assertEquals('Selesai diverifikasi GA.', $atkRequest->notes);
    }

    public function test_manager_rejects_all_items()
    {
        $this->withoutExceptionHandling();

        $client = User::factory()->create(['role' => 'client']);
        $manager = User::factory()->create(['role' => 'manager']);

        $atkRequest = AtkRequest::create([
            'user_id' => $client->id,
            'nama' => $client->name,
            'divisi' => 'IT',
            'periode' => 'Juli 2026',
            'tanggal_pengajuan' => now(),
            'status_approval' => 'Menunggu Persetujuan Manager',
            'request_type' => 'ATK',
        ]);

        $item = AtkRequestItem::create([
            'atk_request_id' => $atkRequest->id,
            'item_name' => 'Kertas A4',
            'quantity' => 5,
            'unit' => 'Rim',
            'is_custom' => false,
            'status_approval' => 'Pending',
        ]);

        $this->actingAs($manager);
        $response = $this->post(route('atk-requests.approve', $atkRequest->id), [
            'items' => [
                $item->id => ['status' => 'Ditolak', 'notes' => 'Tinta printer habis'],
            ],
            'manager_notes' => 'Ditolak seluruhnya.',
        ]);

        $response->assertRedirect();
        $atkRequest->refresh();
        $item->refresh();

        $this->assertEquals('Ditolak/Revisi', $atkRequest->status_approval);
        $this->assertEquals('Ditolak', $item->status_approval);
        $this->assertEquals('Tinta printer habis', $item->notes);
        $this->assertEquals($manager->id, $atkRequest->manager_approved_by);
        $this->assertEquals('Ditolak seluruhnya.', $atkRequest->manager_notes);
    }

    public function test_reject_button_rejects_entire_request()
    {
        $this->withoutExceptionHandling();

        $client = User::factory()->create(['role' => 'client']);
        $manager = User::factory()->create(['role' => 'manager']);

        $atkRequest = AtkRequest::create([
            'user_id' => $client->id,
            'nama' => $client->name,
            'divisi' => 'IT',
            'periode' => 'Juli 2026',
            'tanggal_pengajuan' => now(),
            'status_approval' => 'Menunggu Persetujuan Manager',
            'request_type' => 'ATK',
        ]);

        $item = AtkRequestItem::create([
            'atk_request_id' => $atkRequest->id,
            'item_name' => 'Kertas A4',
            'quantity' => 5,
            'unit' => 'Rim',
            'is_custom' => false,
            'status_approval' => 'Pending',
        ]);

        $this->actingAs($manager);
        $response = $this->post(route('atk-requests.reject', $atkRequest->id), [
            'notes' => 'Tidak mendesak.',
        ]);

        $response->assertRedirect();
        $atkRequest->refresh();
        $item->refresh();

        $this->assertEquals('Ditolak/Revisi', $atkRequest->status_approval);
        $this->assertEquals('Ditolak', $item->status_approval);
        $this->assertEquals($manager->id, $atkRequest->manager_approved_by);
        $this->assertEquals('Tidak mendesak.', $atkRequest->manager_notes);
    }
}
