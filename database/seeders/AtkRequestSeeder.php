<?php

namespace Database\Seeders;

use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkRequest;
use App\Models\AtkRequestItem;
use App\Models\Uom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AtkRequestSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Ensure UOM and Master Barang Units match the case requirement
        Uom::firstOrCreate(['name' => 'Pcs, Pack']);
        AtkItem::where('name', 'Pensil')->update(['unit' => 'Pcs, Pack']);
        AtkItem::where('name', 'Pulpen')->update(['unit' => 'Pcs, Pack']);
        AtkItem::where('name', 'Isi Pulpen')->update(['unit' => 'Pcs']);
        AtkItem::where('name', 'Spidol Papan Tulis')->update(['unit' => 'Pcs']);

        // 1. Ensure required users exist for realistic department requests
        $manager = User::firstOrCreate(
            ['email' => 'oprtmanager@company.com'],
            [
                'name' => 'Oprt. Manager',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '+62812345684',
                'company' => 'Management',
                'bio' => 'Operational Manager',
            ]
        );

        $adminGA = User::firstOrCreate(
            ['email' => 'admin@ga-system.com'],
            [
                'name' => 'Admin HRGA',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '+62812345679',
                'company' => 'HRGA Department',
                'bio' => 'SPV / Admin HRGA',
            ]
        );

        $userMarketing = User::firstOrCreate(
            ['email' => 'user1@company.com'],
            [
                'name' => 'Staff Marketing',
                'password' => Hash::make('password'),
                'role' => 'client',
                'status' => 'active',
                'phone' => '+62812345682',
                'company' => 'Marketing Dept',
                'bio' => 'Staff Pemohon Divisi Marketing',
            ]
        );

        $userProduction = User::firstOrCreate(
            ['email' => 'user2@company.com'],
            [
                'name' => 'Manager Produksi',
                'password' => Hash::make('password'),
                'role' => 'client',
                'status' => 'active',
                'phone' => '+62812345683',
                'company' => 'Production Dept',
                'bio' => 'Pemohon Divisi Produksi',
            ]
        );

        $userFinance = User::firstOrCreate(
            ['email' => 'finance@company.com'],
            [
                'name' => 'Siti Rahma',
                'password' => Hash::make('password'),
                'role' => 'client',
                'status' => 'active',
                'phone' => '+62812345690',
                'company' => 'Finance & Accounting',
                'bio' => 'Staff Accounting & Finance',
            ]
        );

        $userWarehouse = User::firstOrCreate(
            ['email' => 'warehouse@company.com'],
            [
                'name' => 'Doni Saputra',
                'password' => Hash::make('password'),
                'role' => 'client',
                'status' => 'active',
                'phone' => '+62812345691',
                'company' => 'Warehouse & Logistics',
                'bio' => 'Koordinator Gudang & Logistik',
            ]
        );

        $userQA = User::firstOrCreate(
            ['email' => 'qa@company.com'],
            [
                'name' => 'Rina Marlina',
                'password' => Hash::make('password'),
                'role' => 'client',
                'status' => 'active',
                'phone' => '+62812345692',
                'company' => 'Quality Assurance',
                'bio' => 'QA Officer',
            ]
        );

        $userMaintenance = User::firstOrCreate(
            ['email' => 'engineer1@ga-system.com'],
            [
                'name' => 'Budi Teknisi',
                'password' => Hash::make('password'),
                'role' => 'developer',
                'status' => 'active',
                'phone' => '+62812345680',
                'company' => 'Maintenance',
                'bio' => 'Teknisi Internal (Engineer)',
            ]
        );

        // 2. Define Dummy Requests Data
        // Req 1: ATK - Marketing Dept - Disetujui Penuh & Diserahkan
        $req1 = AtkRequest::updateOrCreate(
            [
                'nama' => 'Staff Marketing',
                'divisi' => 'Marketing Dept',
                'periode' => '11/2026',
                'request_type' => 'ATK',
            ],
            [
                'user_id' => $userMarketing->id,
                'tanggal_pengajuan' => Carbon::parse('2026-11-01'),
                'status_approval' => 'Disetujui',
                'manager_approved_by' => $manager->id,
                'manager_approved_at' => Carbon::parse('2026-11-01 10:15:00'),
                'manager_notes' => 'Disetujui untuk mendukung operasional kampanye promosi Q4.',
                'approved_by' => $adminGA->id,
                'approved_at' => Carbon::parse('2026-11-01 14:30:00'),
                'notes' => 'Barang siap diambil di ruang GA.',
                'diberikan_pada' => Carbon::parse('2026-11-02'),
                'receiver_name' => 'Bima (Staff Marketing)',
            ]
        );
        $req1->items()->delete();
        AtkRequestItem::create([
            'atk_request_id' => $req1->id,
            'item_name' => 'Kertas 70 Gr',
            'unit' => 'Rim',
            'description' => 'A4',
            'quantity' => 5,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => 'Disetujui 5 rim',
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req1->id,
            'item_name' => 'Pulpen',
            'unit' => 'Pcs, Pack',
            'description' => 'Hitam',
            'quantity' => 12,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req1->id,
            'item_name' => 'Pensil',
            'unit' => 'Pcs, Pack',
            'description' => '2B',
            'quantity' => 6,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req1->id,
            'item_name' => 'Post IT',
            'unit' => 'Pcs',
            'description' => 'Sign Here, Page Marker',
            'quantity' => 4,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req1->id,
            'item_name' => 'Brosur Holder Akrilik A4',
            'unit' => 'Pcs',
            'description' => 'Display meja resepsionis & counter marketing',
            'quantity' => 2,
            'is_custom' => true,
            'status_approval' => 'Disetujui',
            'notes' => 'Pengadaan khusus disetujui GA',
        ]);

        // Req 2: Rumah Tangga - Production Dept - Disetujui Penuh & Diserahkan
        $req2 = AtkRequest::updateOrCreate(
            [
                'nama' => 'Manager Produksi',
                'divisi' => 'Production Dept',
                'periode' => '11/2026',
                'request_type' => 'Rumah Tangga',
            ],
            [
                'user_id' => $userProduction->id,
                'tanggal_pengajuan' => Carbon::parse('2026-11-02'),
                'status_approval' => 'Disetujui',
                'manager_approved_by' => $manager->id,
                'manager_approved_at' => Carbon::parse('2026-11-02 09:30:00'),
                'manager_notes' => 'Alokasi rutin kebersihan area workshop & line produksi.',
                'approved_by' => $adminGA->id,
                'approved_at' => Carbon::parse('2026-11-02 11:45:00'),
                'notes' => 'Sudah diserahkan lengkap ke perwakilan tim produksi.',
                'diberikan_pada' => Carbon::parse('2026-11-03'),
                'receiver_name' => 'Yanto (Operator Produksi)',
            ]
        );
        $req2->items()->delete();
        AtkRequestItem::create([
            'atk_request_id' => $req2->id,
            'item_name' => 'Sabun Cuci Piring',
            'unit' => 'Pcs',
            'description' => null,
            'quantity' => 6,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req2->id,
            'item_name' => 'Wipol',
            'unit' => 'Botol',
            'description' => null,
            'quantity' => 4,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req2->id,
            'item_name' => 'Tissu Toilet',
            'unit' => 'Roll',
            'description' => null,
            'quantity' => 20,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req2->id,
            'item_name' => 'Trash Bag besar',
            'unit' => 'Pack',
            'description' => null,
            'quantity' => 8,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req2->id,
            'item_name' => 'Sapu Lantai',
            'unit' => 'Pcs',
            'description' => null,
            'quantity' => 3,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req2->id,
            'item_name' => 'Lap Microfiber (lap meja)',
            'unit' => 'Pcs',
            'description' => null,
            'quantity' => 6,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);

        // Req 3: ATK - Finance & Accounting - Menunggu Persetujuan Admin GA
        $req3 = AtkRequest::updateOrCreate(
            [
                'nama' => 'Siti Rahma',
                'divisi' => 'Finance & Accounting',
                'periode' => '11/2026',
                'request_type' => 'ATK',
            ],
            [
                'user_id' => $userFinance->id,
                'tanggal_pengajuan' => Carbon::parse('2026-11-03'),
                'status_approval' => 'Menunggu Persetujuan Admin GA',
                'manager_approved_by' => $manager->id,
                'manager_approved_at' => Carbon::parse('2026-11-03 11:20:00'),
                'manager_notes' => 'Kebutuhan audit & rekap akhir tahun, mohon diprioritaskan oleh GA.',
                'approved_by' => null,
                'approved_at' => null,
                'notes' => null,
                'diberikan_pada' => null,
                'receiver_name' => null,
            ]
        );
        $req3->items()->delete();
        AtkRequestItem::create([
            'atk_request_id' => $req3->id,
            'item_name' => 'Kertas 80 Gr',
            'unit' => 'Rim',
            'description' => 'F4',
            'quantity' => 4,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req3->id,
            'item_name' => 'Ordner',
            'unit' => 'Pcs',
            'description' => 'Folio',
            'quantity' => 8,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req3->id,
            'item_name' => 'Binder Clips',
            'unit' => 'Box Kecil',
            'description' => '107',
            'quantity' => 5,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req3->id,
            'item_name' => 'Kalkulator',
            'unit' => 'Pcs',
            'description' => '12 Digit Check & Correct',
            'quantity' => 1,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req3->id,
            'item_name' => 'Buku Kas Folio Hardcover Akuntansi',
            'unit' => 'Pcs',
            'description' => '200 halaman bergaris kolom saldo',
            'quantity' => 2,
            'is_custom' => true,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);

        // Req 4: Rumah Tangga - Warehouse & Logistics - Menunggu Persetujuan Admin GA
        $req4 = AtkRequest::updateOrCreate(
            [
                'nama' => 'Doni Saputra',
                'divisi' => 'Warehouse & Logistics',
                'periode' => '11/2026',
                'request_type' => 'Rumah Tangga',
            ],
            [
                'user_id' => $userWarehouse->id,
                'tanggal_pengajuan' => Carbon::parse('2026-11-03'),
                'status_approval' => 'Menunggu Persetujuan Admin GA',
                'manager_approved_by' => $manager->id,
                'manager_approved_at' => Carbon::parse('2026-11-03 14:00:00'),
                'manager_notes' => 'Kebersihan area loading dock dan rak gudang.',
                'approved_by' => null,
                'approved_at' => null,
                'notes' => null,
                'diberikan_pada' => null,
                'receiver_name' => null,
            ]
        );
        $req4->items()->delete();
        AtkRequestItem::create([
            'atk_request_id' => $req4->id,
            'item_name' => 'Hand Soap',
            'unit' => 'Pcs',
            'description' => null,
            'quantity' => 4,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req4->id,
            'item_name' => 'Karbol WC',
            'unit' => 'Pack',
            'description' => null,
            'quantity' => 3,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req4->id,
            'item_name' => 'Sapu Lidi',
            'unit' => 'Pcs',
            'description' => null,
            'quantity' => 3,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req4->id,
            'item_name' => 'Alat Pel Gagang Panjang',
            'unit' => 'Pcs',
            'description' => null,
            'quantity' => 2,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);

        // Req 5: ATK - Quality Assurance - Menunggu Persetujuan Manager
        $req5 = AtkRequest::updateOrCreate(
            [
                'nama' => 'Rina Marlina',
                'divisi' => 'Quality Assurance',
                'periode' => '11/2026',
                'request_type' => 'ATK',
            ],
            [
                'user_id' => $userQA->id,
                'tanggal_pengajuan' => Carbon::parse('2026-11-04'),
                'status_approval' => 'Menunggu Persetujuan Manager',
                'manager_approved_by' => null,
                'manager_approved_at' => null,
                'manager_notes' => null,
                'approved_by' => null,
                'approved_at' => null,
                'notes' => null,
                'diberikan_pada' => null,
                'receiver_name' => null,
            ]
        );
        $req5->items()->delete();
        AtkRequestItem::create([
            'atk_request_id' => $req5->id,
            'item_name' => 'Spidol Papan Tulis',
            'unit' => 'Pcs',
            'description' => 'Biru, Hitam, Merah',
            'quantity' => 6,
            'is_custom' => false,
            'status_approval' => 'Pending',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req5->id,
            'item_name' => 'Clear Holder Document',
            'unit' => 'Pcs',
            'description' => '40',
            'quantity' => 4,
            'is_custom' => false,
            'status_approval' => 'Pending',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req5->id,
            'item_name' => 'Paper Clips',
            'unit' => 'Box Kecil',
            'description' => 'No. 3',
            'quantity' => 3,
            'is_custom' => false,
            'status_approval' => 'Pending',
            'notes' => null,
        ]);

        // Req 6: Rumah Tangga - Marketing Dept - Menunggu Persetujuan Manager
        $req6 = AtkRequest::updateOrCreate(
            [
                'nama' => 'Staff Marketing',
                'divisi' => 'Marketing Dept',
                'periode' => '11/2026',
                'request_type' => 'Rumah Tangga',
            ],
            [
                'user_id' => $userMarketing->id,
                'tanggal_pengajuan' => Carbon::parse('2026-11-04'),
                'status_approval' => 'Menunggu Persetujuan Manager',
                'manager_approved_by' => null,
                'manager_approved_at' => null,
                'manager_notes' => null,
                'approved_by' => null,
                'approved_at' => null,
                'notes' => null,
                'diberikan_pada' => null,
                'receiver_name' => null,
            ]
        );
        $req6->items()->delete();
        AtkRequestItem::create([
            'atk_request_id' => $req6->id,
            'item_name' => 'Pewangi Ruangan Spray',
            'unit' => 'Pcs',
            'description' => null,
            'quantity' => 3,
            'is_custom' => false,
            'status_approval' => 'Pending',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req6->id,
            'item_name' => 'Tissu Kotak Besar',
            'unit' => 'Pack',
            'description' => null,
            'quantity' => 6,
            'is_custom' => false,
            'status_approval' => 'Pending',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req6->id,
            'item_name' => 'Hand Sanitizer',
            'unit' => 'Botol',
            'description' => null,
            'quantity' => 4,
            'is_custom' => false,
            'status_approval' => 'Pending',
            'notes' => null,
        ]);

        // Req 7 & 8: REVISI SCENARIO (Parent ditolak/revisi -> Child Revisi 1 disetujui)
        // Parent:
        $reqParent = AtkRequest::updateOrCreate(
            [
                'nama' => 'Budi Teknisi',
                'divisi' => 'Maintenance',
                'periode' => '11/2026',
                'request_type' => 'ATK',
                'revision_number' => 0,
            ],
            [
                'user_id' => $userMaintenance->id,
                'tanggal_pengajuan' => Carbon::parse('2026-11-01'),
                'status_approval' => 'Ditolak/Revisi',
                'manager_approved_by' => $manager->id,
                'manager_approved_at' => Carbon::parse('2026-11-02 08:30:00'),
                'manager_notes' => 'Jumlah Lakban dan Cutter terlalu banyak melampaui limit bulanan divisi maintenance. Mohon ajukan revisi.',
                'approved_by' => null,
                'approved_at' => null,
                'notes' => null,
                'diberikan_pada' => null,
                'receiver_name' => null,
            ]
        );
        $reqParent->items()->delete();
        $oldLakban = AtkRequestItem::create([
            'atk_request_id' => $reqParent->id,
            'item_name' => 'Lakban ( Coklat / Hitam / Bening )*',
            'unit' => 'Pcs',
            'description' => 'Besar, Coklat',
            'quantity' => 15,
            'is_custom' => false,
            'status_approval' => 'Ditolak',
            'notes' => 'Maksimal 5 pcs per bulan untuk maintenance',
        ]);
        $oldCutter = AtkRequestItem::create([
            'atk_request_id' => $reqParent->id,
            'item_name' => 'Cutter',
            'unit' => 'Pcs',
            'description' => 'Besar',
            'quantity' => 10,
            'is_custom' => false,
            'status_approval' => 'Ditolak',
            'notes' => 'Stok di gudang masih ada, kurangi jadi 2 pcs',
        ]);
        $oldBaterai = AtkRequestItem::create([
            'atk_request_id' => $reqParent->id,
            'item_name' => 'Baterai AA',
            'unit' => 'Pack',
            'description' => 'Besar',
            'quantity' => 4,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => 'Disetujui untuk tester kelistrikan',
        ]);

        // Child (Revision 1):
        $reqChild = AtkRequest::updateOrCreate(
            [
                'parent_id' => $reqParent->id,
                'revision_number' => 1,
            ],
            [
                'user_id' => $userMaintenance->id,
                'request_type' => 'ATK',
                'nama' => 'Budi Teknisi',
                'divisi' => 'Maintenance',
                'periode' => '11/2026',
                'tanggal_pengajuan' => Carbon::parse('2026-11-03'),
                'status_approval' => 'Disetujui',
                'revision_note' => "Revisi penyesuaian quantity Lakban dan Cutter sesuai catatan Manager dari {$reqParent->formatted_doc_number}",
                'manager_approved_by' => $manager->id,
                'manager_approved_at' => Carbon::parse('2026-11-03 09:30:00'),
                'manager_notes' => 'Revisi sudah sesuai, kuota diterima.',
                'approved_by' => $adminGA->id,
                'approved_at' => Carbon::parse('2026-11-03 14:00:00'),
                'notes' => 'Disetujui dan siap serah terima.',
                'diberikan_pada' => Carbon::parse('2026-11-04'),
                'receiver_name' => 'Joko (Teknisi)',
            ]
        );
        $reqChild->items()->delete();
        AtkRequestItem::create([
            'atk_request_id' => $reqChild->id,
            'item_name' => 'Lakban ( Coklat / Hitam / Bening )*',
            'unit' => 'Pcs',
            'description' => 'Besar, Coklat',
            'quantity' => 5,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'is_revised' => true,
            'previous_quantity' => 15,
            'previous_description' => 'Besar, Coklat',
            'original_item_id' => $oldLakban->id,
            'notes' => 'Revisi Qty disetujui',
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $reqChild->id,
            'item_name' => 'Cutter',
            'unit' => 'Pcs',
            'description' => 'Besar',
            'quantity' => 2,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'is_revised' => true,
            'previous_quantity' => 10,
            'previous_description' => 'Besar',
            'original_item_id' => $oldCutter->id,
            'notes' => 'Revisi Qty disetujui',
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $reqChild->id,
            'item_name' => 'Baterai AA',
            'unit' => 'Pack',
            'description' => 'Besar',
            'quantity' => 4,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'is_revised' => false,
            'previous_quantity' => null,
            'previous_description' => null,
            'original_item_id' => $oldBaterai->id,
            'notes' => null,
        ]);

        // Req 9: ATK - Draft
        $req9 = AtkRequest::updateOrCreate(
            [
                'nama' => 'Staff Marketing',
                'divisi' => 'Marketing Dept',
                'periode' => '11/2026',
                'status_approval' => 'Draft',
            ],
            [
                'user_id' => $userMarketing->id,
                'request_type' => 'ATK',
                'tanggal_pengajuan' => Carbon::parse('2026-11-04'),
                'manager_approved_by' => null,
                'manager_approved_at' => null,
                'manager_notes' => null,
                'approved_by' => null,
                'approved_at' => null,
                'notes' => null,
                'diberikan_pada' => null,
                'receiver_name' => null,
            ]
        );
        $req9->items()->delete();
        AtkRequestItem::create([
            'atk_request_id' => $req9->id,
            'item_name' => 'Amplop Coklat',
            'unit' => 'Pcs',
            'description' => 'A4, Folio',
            'quantity' => 50,
            'is_custom' => false,
            'status_approval' => 'Pending',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req9->id,
            'item_name' => 'Map Dengan Pengait',
            'unit' => 'Pcs',
            'description' => 'Biru',
            'quantity' => 10,
            'is_custom' => false,
            'status_approval' => 'Pending',
            'notes' => null,
        ]);

        // Req 10: ATK - Periode Bulan Lalu (10/2026) - Warehouse & Logistics - Selesai & Diserahkan
        $req10 = AtkRequest::updateOrCreate(
            [
                'nama' => 'Doni Saputra',
                'divisi' => 'Warehouse & Logistics',
                'periode' => '10/2026',
                'request_type' => 'ATK',
            ],
            [
                'user_id' => $userWarehouse->id,
                'tanggal_pengajuan' => Carbon::parse('2026-10-04'),
                'status_approval' => 'Disetujui',
                'manager_approved_by' => $manager->id,
                'manager_approved_at' => Carbon::parse('2026-10-04 11:00:00'),
                'manager_notes' => 'Disetujui untuk administrasi surat jalan pergudangan.',
                'approved_by' => $adminGA->id,
                'approved_at' => Carbon::parse('2026-10-04 15:30:00'),
                'notes' => 'Pengadaan bulan Oktober selesai.',
                'diberikan_pada' => Carbon::parse('2026-10-05'),
                'receiver_name' => 'Doni (Logistik)',
            ]
        );
        $req10->items()->delete();
        AtkRequestItem::create([
            'atk_request_id' => $req10->id,
            'item_name' => 'Kertas Continuos Form 9 1/2 x 11',
            'unit' => 'Dus',
            'description' => '3 Ply',
            'quantity' => 4,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req10->id,
            'item_name' => 'Double Tape',
            'unit' => 'Pcs',
            'description' => 'Besar',
            'quantity' => 5,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req10->id,
            'item_name' => 'Lakban ( Coklat / Hitam / Bening )*',
            'unit' => 'Pcs',
            'description' => 'Coklat Besar',
            'quantity' => 10,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);

        // Req 11: Rumah Tangga - Periode Bulan Lalu (10/2026) - Finance & Accounting - Selesai & Diserahkan
        $req11 = AtkRequest::updateOrCreate(
            [
                'nama' => 'Siti Rahma',
                'divisi' => 'Finance & Accounting',
                'periode' => '10/2026',
                'request_type' => 'Rumah Tangga',
            ],
            [
                'user_id' => $userFinance->id,
                'tanggal_pengajuan' => Carbon::parse('2026-10-05'),
                'status_approval' => 'Disetujui',
                'manager_approved_by' => $manager->id,
                'manager_approved_at' => Carbon::parse('2026-10-05 10:00:00'),
                'manager_notes' => 'Disetujui.',
                'approved_by' => $adminGA->id,
                'approved_at' => Carbon::parse('2026-10-05 14:00:00'),
                'notes' => 'Barang telah diterima divisi finance.',
                'diberikan_pada' => Carbon::parse('2026-10-06'),
                'receiver_name' => 'Siti Rahma',
            ]
        );
        $req11->items()->delete();
        AtkRequestItem::create([
            'atk_request_id' => $req11->id,
            'item_name' => 'Soklin lantai',
            'unit' => 'Pack',
            'description' => null,
            'quantity' => 3,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req11->id,
            'item_name' => 'Karbol Lemari',
            'unit' => 'Pack',
            'description' => null,
            'quantity' => 4,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $req11->id,
            'item_name' => 'Tissu Kotak Kecil',
            'unit' => 'Pack',
            'description' => null,
            'quantity' => 6,
            'is_custom' => false,
            'status_approval' => 'Disetujui',
            'notes' => null,
        ]);
    }
}

