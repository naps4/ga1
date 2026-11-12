{{-- MODAL MUTASI ASET (Shared Master & Plant Views) --}}
<div class="modal fade" id="modalMutateAsset" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="formMutateAsset" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;" autocomplete="off">
            @csrf
            <div class="modal-header bg-info text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-exchange-alt mr-2"></i> Mutasi Lokasi Aset</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3">
                    <strong id="mutateAssetName" class="d-block text-dark">Nama Aset</strong>
                    <code id="mutateAssetCode" class="text-primary font-weight-bold">KODE ASET</code>
                    <div class="mt-2 pt-2 border-top d-flex flex-wrap align-items-center" style="gap: 6px;">
                        <span class="small font-weight-bold text-dark mr-1">Posisi Saat Ini:</span>
                        <span id="mutateOriginPlantBadge" class="badge badge-primary">-</span>
                        <span id="mutateOriginRoomBadge" class="badge badge-secondary">-</span>
                        <span id="mutateOriginDeptBadge" class="badge badge-light border text-dark">-</span>
                    </div>
                    <div class="small text-muted mt-2">Stiker kode aset fisik tidak akan diubah selama mutasi.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Plant Tujuan <span class="text-danger">*</span></label>
                    <select name="target_plant_code" id="mutateTargetPlant" class="form-control" required>
                        @foreach($plants as $p)
                            <option value="{{ $p->code }}">{{ $p->name }} ({{ $p->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Ruangan / Sub-Area Tujuan <small class="text-muted font-weight-normal">(Lampiran C)</small></label>
                    <select name="target_location_id" id="mutateTargetLocation" class="form-control">
                        <option value="">-- Lintas Area / Tanpa Ruangan Khusus --</option>
                    </select>
                    <small id="mutateRoomHint" class="text-muted d-block mt-1"></small>
                </div>

                <div class="row">
                    <div class="col-md-7 col-12 mb-3">
                        <label class="form-label font-weight-bold">Departemen Tujuan <span class="text-danger">*</span></label>
                        <select name="target_dept_id" id="mutateTargetDept" class="form-control" required>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 col-12 mb-3">
                        <label class="form-label font-weight-bold">Tanggal Mutasi</label>
                        <input type="date" name="mutation_date" id="mutateDateInput" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Nama PIC / Penanggung Jawab <span class="text-danger">*</span></label>
                    <input type="text" name="pic_name" id="mutatePicInput" class="form-control" value="{{ auth()->user()->name }}" required>
                </div>

                <div class="mb-2">
                    <label class="form-label font-weight-bold">Alasan Pemindahan <span class="text-danger">*</span></label>
                    <textarea name="reason" id="mutateReasonInput" class="form-control" rows="2" placeholder="Contoh: Kebutuhan ekspansi fasilitas, pindah ruangan, mutasi staf..." required></textarea>
                </div>

                <div id="mutateNoOpAlert" class="alert alert-warning py-2 px-3 small mb-0 mt-2 d-none">
                    <i class="fas fa-info-circle mr-1"></i>
                    Posisi tujuan masih sama dengan posisi aset saat ini. Ubah Plant, Ruangan, atau Departemen tujuan terlebih dahulu.
                </div>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                <button type="submit" id="mutateSubmitBtn" class="btn btn-info rounded-pill px-4"><i class="fas fa-check mr-1"></i> Eksekusi Mutasi</button>
            </div>
        </form>
    </div>
</div>

@php
    $mutateLocationsPayload = $mutateLocations->map(function ($loc) {
        return [
            'id' => $loc->id,
            'room_name' => $loc->room_name,
            'plant_code' => $loc->plant_code,
            'plant_name' => $loc->plant->name ?? ('Plant ' . $loc->plant_code),
        ];
    })->values();
@endphp

@push('scripts')
<script>
(function() {
    const MUTATE_LOCATIONS = @json($mutateLocationsPayload);

    let mutateOrigin = {
        plantCode: '',
        locationId: '',
        deptId: ''
    };

    function formatMutatePlantLabel(code, plantObj) {
        if (plantObj && plantObj.name) return plantObj.name + ' (' + code + ')';
        if (code === '01') return 'Plant A (01)';
        if (code === '02') return 'Plant B (02)';
        if (code === '00') return 'Umum / Mobile (00)';
        return 'Plant ' + (code || '-');
    }

    function populateMutateLocations(plantCode, preselectLocId) {
        const locSelect = document.getElementById('mutateTargetLocation');
        const roomHint = document.getElementById('mutateRoomHint');
        if (!locSelect) return;

        locSelect.innerHTML = '';

        if (String(plantCode) === '00') {
            const mobileOpt = document.createElement('option');
            mobileOpt.value = '';
            mobileOpt.textContent = '-- Area Mobile / Lintas Plant (Tanpa Ruangan) --';
            locSelect.appendChild(mobileOpt);
            locSelect.value = '';
            locSelect.disabled = true;
            if (roomHint) {
                roomHint.textContent = 'Aset Mobile (00) tidak terikat ruangan gedung dan otomatis dilepas dari daftar Lampiran C.';
            }
            return;
        }

        locSelect.disabled = false;
        const emptyOpt = document.createElement('option');
        emptyOpt.value = '';
        emptyOpt.textContent = '-- Lintas Area / Tanpa Ruangan Khusus --';
        locSelect.appendChild(emptyOpt);

        const matchingLocs = MUTATE_LOCATIONS.filter(loc => String(loc.plant_code) === String(plantCode));
        matchingLocs.forEach(loc => {
            const opt = document.createElement('option');
            opt.value = String(loc.id);
            opt.textContent = loc.room_name;
            locSelect.appendChild(opt);
        });

        const desired = preselectLocId ? String(preselectLocId) : '';
        if (desired && matchingLocs.some(loc => String(loc.id) === desired)) {
            locSelect.value = desired;
        } else {
            locSelect.value = '';
        }

        if (roomHint) {
            roomHint.textContent = matchingLocs.length > 0
                ? 'Menampilkan ruangan di Plant terpilih. Memilih ruangan menyinkronkan aset ke Lampiran C ruangan tersebut.'
                : 'Belum ada master ruangan terdaftar untuk Plant ini.';
        }
    }

    function checkMutateChanged() {
        const plantSelect = document.getElementById('mutateTargetPlant');
        const locSelect = document.getElementById('mutateTargetLocation');
        const deptSelect = document.getElementById('mutateTargetDept');
        const noOpAlert = document.getElementById('mutateNoOpAlert');
        const submitBtn = document.getElementById('mutateSubmitBtn');

        if (!plantSelect || !locSelect || !deptSelect || !noOpAlert) return true;

        const targetPlant = String(plantSelect.value || '');
        const targetLoc = locSelect.disabled ? '' : String(locSelect.value || '');
        const targetDept = String(deptSelect.value || '');

        const unchanged = (
            targetPlant === String(mutateOrigin.plantCode || '') &&
            targetLoc === String(mutateOrigin.locationId || '') &&
            targetDept === String(mutateOrigin.deptId || '')
        );

        noOpAlert.classList.toggle('d-none', !unchanged);
        if (submitBtn) {
            submitBtn.disabled = unchanged;
        }
        return !unchanged;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const plantSelect = document.getElementById('mutateTargetPlant');
        const locSelect = document.getElementById('mutateTargetLocation');
        const deptSelect = document.getElementById('mutateTargetDept');
        const form = document.getElementById('formMutateAsset');

        if (plantSelect) {
            plantSelect.addEventListener('change', function() {
                const keepOrigRoom = (String(this.value) === String(mutateOrigin.plantCode)) ? mutateOrigin.locationId : '';
                populateMutateLocations(this.value, keepOrigRoom);
                checkMutateChanged();
            });
        }
        if (locSelect) {
            locSelect.addEventListener('change', checkMutateChanged);
        }
        if (deptSelect) {
            deptSelect.addEventListener('change', checkMutateChanged);
        }
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!checkMutateChanged()) {
                    e.preventDefault();
                }
            });
        }
    });

    window.openMutateModal = function(asset) {
        document.getElementById('formMutateAsset').action = '/assets/' + asset.id + '/mutate';
        document.getElementById('mutateAssetName').innerText = asset.item_name || '-';
        document.getElementById('mutateAssetCode').innerText = asset.asset_code || '-';

        const currentPlantCode = String(asset.current_plant_code || '');
        const currentLocationId = asset.location_id ? String(asset.location_id) : '';
        const currentDeptId = asset.department_id ? String(asset.department_id) : '';

        mutateOrigin = {
            plantCode: currentPlantCode,
            locationId: currentPlantCode === '00' ? '' : currentLocationId,
            deptId: currentDeptId
        };

        const plantBadge = document.getElementById('mutateOriginPlantBadge');
        if (plantBadge) {
            plantBadge.textContent = formatMutatePlantLabel(currentPlantCode, asset.current_plant || null);
            plantBadge.className = 'badge ' + (currentPlantCode === '01' ? 'badge-primary' : (currentPlantCode === '02' ? 'badge-info' : 'badge-secondary'));
        }

        const roomBadge = document.getElementById('mutateOriginRoomBadge');
        if (roomBadge) {
            const roomText = (asset.location && asset.location.room_name)
                ? asset.location.room_name
                : (currentPlantCode === '00' ? 'Area Mobile (Tanpa Ruangan)' : 'Lintas Area / Tanpa Ruangan');
            roomBadge.textContent = roomText;
        }

        const deptBadge = document.getElementById('mutateOriginDeptBadge');
        if (deptBadge) {
            deptBadge.textContent = asset.department ? (asset.department.name || asset.department.code || '-') : '-';
        }

        const plantSelect = document.getElementById('mutateTargetPlant');
        if (plantSelect) {
            plantSelect.value = currentPlantCode || (plantSelect.options[0] ? plantSelect.options[0].value : '');
        }

        const deptSelect = document.getElementById('mutateTargetDept');
        if (deptSelect && currentDeptId) {
            deptSelect.value = currentDeptId;
        }

        const dateInput = document.getElementById('mutateDateInput');
        if (dateInput) {
            dateInput.value = '{{ date('Y-m-d') }}';
        }

        const reasonInput = document.getElementById('mutateReasonInput');
        if (reasonInput) {
            reasonInput.value = '';
        }

        populateMutateLocations(plantSelect ? plantSelect.value : currentPlantCode, mutateOrigin.locationId);
        checkMutateChanged();
        $('#modalMutateAsset').modal('show');
    };
})();
</script>
@endpush