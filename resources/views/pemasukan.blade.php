@extends('layouts.admin')

@section('content')
<div id="app">
    @include('components.headers.navbar')
    <div id="main" class="pt-3">
        <header class="mb-3 d-xl-none">
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-1">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle-btn me-2" title="Buka/Tutup Sidebar">
                    <i class="bi bi-layout-sidebar-inset fs-5"></i>
                </button>
                <div>
                    <h4 class="fw-bold mb-0" style="color: #2d8aba; font-size: 1.25rem;">
                        <i class="bi bi-calendar-check-fill me-2"></i>Manajemen Booking & Antrean Pasien
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Kelola alur pengerjaan kunjungan klinik dari kedatangan hingga selesai
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'superadmin')
                    <a href="{{ route('owner') }}" class="btn btn-outline-primary fw-semibold d-flex align-items-center" style="font-size: 0.875rem;">
                        <i class="bi bi-person-plus-fill me-1"></i> Tambah Owner
                    </a>
                    <button type="button" class="btn btn-primary fw-semibold d-flex align-items-center shadow-sm" style="background:#2d8aba;border-color:#2d8aba;font-size: 0.875rem;" data-bs-toggle="modal" data-bs-target="#modalPilihPasienBooking">
                        <i class="bi bi-calendar-plus-fill me-1"></i> Tambah Booking
                    </button>
                @endif
            </div>
        </div>

        @php
            $todayDate = \Carbon\Carbon::today()->toDateString();
            $countKedatangan = $kedatangan->where('tanggal', $todayDate)->count();
            $countMemulai = $memulai->where('tanggal', $todayDate)->count();
            $countApotek = $apotek->where('tanggal', $todayDate)->count();
            $countPembayaran = $pembayaran->where('tanggal', $todayDate)->count();
            $countSelesai = $selesai->where('tanggal', $todayDate)->count();
            $countRawatInap = $rawatInap->count();
        @endphp

        <!-- KRELOSES TAB SYSTEM -->
        <div class="kreloses-tabs-container mb-3">
            <div class="nav kreloses-tabs" id="queueTabs" role="tablist">
                <button class="tab-item nav-link active" id="tab-kedatangan" data-bs-toggle="tab" data-bs-target="#content-kedatangan" type="button" role="tab">
                    🔴 Kedatangan ({{ $countKedatangan }})
                </button>
                <button class="tab-item nav-link" id="tab-memulai" data-bs-toggle="tab" data-bs-target="#content-memulai" type="button" role="tab">
                    🔵 Memulai ({{ $countMemulai }})
                </button>
                <button class="tab-item nav-link" id="tab-apotek" data-bs-toggle="tab" data-bs-target="#content-apotek" type="button" role="tab">
                    🟠 Apotek ({{ $countApotek }})
                </button>
                <button class="tab-item nav-link" id="tab-pembayaran" data-bs-toggle="tab" data-bs-target="#content-pembayaran" type="button" role="tab">
                    🟣 Pembayaran ({{ $countPembayaran }})
                </button>
                <button class="tab-item nav-link" id="tab-selesai" data-bs-toggle="tab" data-bs-target="#content-selesai" type="button" role="tab">
                    🟢 Selesai ({{ $countSelesai }})
                </button>
                <button class="tab-item nav-link ms-auto tab-item-right" id="tab-rawat-inap" data-bs-toggle="tab" data-bs-target="#content-rawat-inap" type="button" role="tab" style="margin-left: auto;">
                    🟤 Rawat Inap ({{ $countRawatInap }})
                </button>
            </div>
        </div>

        <!-- KRELOSES FILTER ROW -->
        <div class="row g-2 mb-3 px-1">
            <div class="col-6 col-md-3">
                <select id="filter-date" class="form-select form-select-sm fw-medium" style="color: #475569;">
                    <option value="today" selected>Hari Ini</option>
                    <option value="all">Semua Hari</option>
                    <option value="7days">Sejak 7 Hari</option>
                    <option value="this_month">Bulan Ini</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select id="filter-type" class="form-select form-select-sm fw-medium" style="color: #475569;">
                    <option value="all" selected>Semua booking</option>
                    <option value="walkin">Walk-in / Langsung Datang</option>
                    <option value="janjitemu">Janji Temu / Online</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select id="filter-staff" class="form-select form-select-sm fw-medium" style="color: #475569;">
                    <option value="all" selected>Semua Staf</option>
                    @if(isset($karyawan))
                        @foreach($karyawan as $k)
                            <option value="{{ strtolower($k->name) }}">{{ $k->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-6 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0" style="color: #546e7a;">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="filter-search" class="form-control border-start-0" placeholder="Cari antrean...">
                </div>
            </div>
        </div>

        <!-- KRELOSES CARD CONTAINER WITH QUEUE TABLE -->
        <div class="kreloses-card mb-4">
            <div class="tab-content" id="queueTabsContent">

                <!-- TAB 1: KEDATANGAN -->
                <div class="tab-pane fade show active" id="content-kedatangan" role="tabpanel">
                    @include('components.queue_table', ['items' => $kedatangan, 'stage' => 'kedatangan'])
                </div>

                <!-- TAB 2: MEMULAI -->
                <div class="tab-pane fade" id="content-memulai" role="tabpanel">
                    @include('components.queue_table', ['items' => $memulai, 'stage' => 'memulai'])
                </div>

                <!-- TAB 3: APOTEK -->
                <div class="tab-pane fade" id="content-apotek" role="tabpanel">
                    @include('components.queue_table', ['items' => $apotek, 'stage' => 'apotek'])
                </div>

                <!-- TAB 4: PEMBAYARAN -->
                <div class="tab-pane fade" id="content-pembayaran" role="tabpanel">
                    @include('components.queue_table', ['items' => $pembayaran, 'stage' => 'pembayaran'])
                </div>

                <!-- TAB 5: SELESAI -->
                <div class="tab-pane fade" id="content-selesai" role="tabpanel">
                    @include('components.queue_table', ['items' => $selesai, 'stage' => 'selesai'])
                </div>

                <!-- TAB 6: RAWAT INAP (PALING KANAN) -->
                <div class="tab-pane fade" id="content-rawat-inap" role="tabpanel">
                    @include('components.queue_table', ['items' => $rawatInap, 'stage' => 'rawat_inap'])
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Pengerjaan & Edit -->
@include('components.modals.donebook')
@include('components.modals.updatebook')
@include('components.modals.pet_tracking_modal')

<!-- MODAL PILIH PASIEN UNTUK BOOKING -->
<div class="modal fade" id="modalPilihPasienBooking" tabindex="-1" aria-labelledby="modalPilihPasienBookingTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header text-white" style="background-color: #2d8aba; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <h5 class="modal-title text-white fw-bold" id="modalPilihPasienBookingTitle">
                    <i class="bi bi-calendar-plus-fill me-2"></i> Buat Booking Pasien Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formPilihPasienBooking" onsubmit="handlePilihPasienBooking(event)">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Pilih Pasien Hewan & Pemilik <span class="text-danger">*</span></label>
                        <select id="selectPasienBooking" class="form-select choices-searchable" required>
                            <option value="">Cari nama hewan atau pemilik...</option>
                            @if(isset($patients))
                                @foreach($patients as $p)
                                    @if($p->owner)
                                        <option value="{{ $p->owner->id }}/{{ $p->id }}">
                                            🐾 {{ $p->nama }} ({{ $p->jenis_hewan ?? 'Hewan' }}) — Owner: {{ $p->owner->nama }} ({{ $p->owner->telepon ?? '-' }})
                                        </option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="alert alert-light border small text-muted mb-3 d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-primary me-2 fs-5"></i>
                        <div>
                            Pasien baru belum terdaftar di klinik?
                            <a href="{{ route('owner') }}" class="fw-bold text-decoration-none" style="color: #2d8aba;">Daftarkan Owner & Hewan di sini</a>.
                        </div>
                    </div>

                    <div class="modal-footer px-0 pb-0 pt-2 border-top">
                        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold" style="background:#2d8aba;border-color:#2d8aba;">
                            Lanjut ke Form Booking <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function handlePilihPasienBooking(e) {
    e.preventDefault();
    const select = document.getElementById('selectPasienBooking');
    const val = select ? select.value : '';
    if (!val) {
        alert('Silakan pilih pasien hewan terlebih dahulu.');
        return;
    }
    window.location.href = '/booking/create/' + val;
}

document.addEventListener('DOMContentLoaded', function() {
    const modalPilihPasien = document.getElementById('modalPilihPasienBooking');
    if (modalPilihPasien) {
        modalPilihPasien.addEventListener('shown.bs.modal', function () {
            if (typeof initSearchableDropdowns === 'function') {
                initSearchableDropdowns(modalPilihPasien);
            }
        });
    }
});
</script>

<!-- REAL-TIME QUEUE FILTER & PET TRACKING SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterDate = document.getElementById('filter-date');
    const filterType = document.getElementById('filter-type');
    const filterStaff = document.getElementById('filter-staff');
    const filterSearch = document.getElementById('filter-search');

    function applyQueueFilters() {
        const dateVal = filterDate ? filterDate.value : 'all';
        const typeVal = filterType ? filterType.value : 'all';
        const staffVal = filterStaff ? filterStaff.value.toLowerCase().trim() : 'all';
        const searchVal = filterSearch ? filterSearch.value.toLowerCase().trim() : '';

        // Calculate dates for filter comparison using local time
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        const todayStr = `${y}-${m}-${d}`;
        
        const sevenDaysAgo = new Date(now.getTime() - (7 * 24 * 60 * 60 * 1000));
        const sy = sevenDaysAgo.getFullYear();
        const sm = String(sevenDaysAgo.getMonth() + 1).padStart(2, '0');
        const sd = String(sevenDaysAgo.getDate()).padStart(2, '0');
        const sevenDaysAgoStr = `${sy}-${sm}-${sd}`;

        const monthStr = `${y}-${m}`;

        const rows = document.querySelectorAll('.queue-row');

        // Counters per stage
        const counters = {
            'content-kedatangan': 0,
            'content-memulai': 0,
            'content-rawat-inap': 0,
            'content-apotek': 0,
            'content-pembayaran': 0,
            'content-selesai': 0
        };

        rows.forEach(row => {
            const rDate = row.getAttribute('data-date') || '';
            const rType = row.getAttribute('data-type') || '';
            const rStaff = row.getAttribute('data-staff') || '';
            const rSearch = row.getAttribute('data-search') || '';
            const parentPane = row.closest('.tab-pane');
            const isRawatInap = parentPane && parentPane.id === 'content-rawat-inap';

            // 1. Check Date Filter (Rawat Inap adalah pasien aktif yang sedang dirawat inap)
            let dateMatch = true;
            if (!isRawatInap) {
                if (dateVal === 'today') {
                    dateMatch = (rDate === todayStr);
                } else if (dateVal === '7days') {
                    dateMatch = (rDate >= sevenDaysAgoStr || rDate === '');
                } else if (dateVal === 'this_month') {
                    dateMatch = rDate.startsWith(monthStr);
                }
            }

            // 2. Check Type Filter
            let typeMatch = true;
            if (typeVal === 'walkin') {
                typeMatch = rType.includes('langsung') || rType.includes('walk');
            } else if (typeVal === 'janjitemu') {
                typeMatch = rType.includes('janji') || rType.includes('online') || rType.includes('booking') || rType.includes('reservasi');
            }

            // 3. Check Staff Filter
            let staffMatch = true;
            if (staffVal !== 'all') {
                staffMatch = rStaff.includes(staffVal);
            }

            // 4. Check Search Filter
            let searchMatch = true;
            if (searchVal !== '') {
                searchMatch = rSearch.includes(searchVal);
            }

            // Combine all filter checks
            if (dateMatch && typeMatch && staffMatch && searchMatch) {
                row.style.display = '';
                if (parentPane && parentPane.id && counters.hasOwnProperty(parentPane.id)) {
                    counters[parentPane.id]++;
                }
            } else {
                row.style.display = 'none';
            }
        });

        // Update tab counters dynamically
        const tabMap = {
            'content-kedatangan': 'tab-kedatangan',
            'content-memulai': 'tab-memulai',
            'content-rawat-inap': 'tab-rawat-inap',
            'content-apotek': 'tab-apotek',
            'content-pembayaran': 'tab-pembayaran',
            'content-selesai': 'tab-selesai'
        };

        const tabNames = {
            'tab-kedatangan': '🔴 Kedatangan',
            'tab-memulai': '🔵 Memulai',
            'tab-apotek': '🟠 Apotek',
            'tab-pembayaran': '🟣 Pembayaran',
            'tab-selesai': '🟢 Selesai',
            'tab-rawat-inap': '🟤 Rawat Inap'
        };

        for (const [paneId, count] of Object.entries(counters)) {
            const tabId = tabMap[paneId];
            const tabBtn = document.getElementById(tabId);
            if (tabBtn) {
                tabBtn.textContent = `${tabNames[tabId]} (${count})`;
            }
        }
    }

    // Attach event listeners to all 4 filter inputs
    if (filterDate) filterDate.addEventListener('change', applyQueueFilters);
    if (filterType) filterType.addEventListener('change', applyQueueFilters);
    if (filterStaff) filterStaff.addEventListener('change', applyQueueFilters);
    if (filterSearch) filterSearch.addEventListener('input', applyQueueFilters);

    // Initial filter run on page load
    applyQueueFilters();

    // PET PROGRESS TRACKING SCRIPT & CHART INSTANCES
    let weightChartInstance = null;
    let vitalsChartInstance = null;

    document.querySelectorAll('.open-pet-tracking-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const bookingId = this.getAttribute('data-booking-id');
            if (!bookingId) return;

            const loadingEl = document.getElementById('tracking-loading');
            const contentEl = document.getElementById('tracking-content');
            if (loadingEl) loadingEl.style.display = 'block';
            if (contentEl) contentEl.style.display = 'none';

            fetch(`/booking/pet-analytics/${bookingId}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert('Gagal mengambil data statistik pasien.');
                        return;
                    }

                    // Update header icon sesuai jenis hewan
                    const petIconEl = document.getElementById('tracking-pet-icon');
                    if (petIconEl) {
                        const jenisKey = (data.pet.jenis || '').toLowerCase().trim();
                        const animalIconMap = {
                            'kucing': '🐱', 'cat': '🐱',
                            'anjing': '🐶', 'dog': '🐶',
                            'kelinci': '🐰', 'rabbit': '🐰',
                            'hamster': '🐹',
                            'burung': '🐦', 'bird': '🐦',
                            'ikan': '🐟', 'fish': '🐟',
                            'kura-kura': '🐢', 'turtle': '🐢', 'kura kura': '🐢',
                            'iguana': '🦎', 'reptil': '🦎',
                            'marmut': '🐹', 'guinea pig': '🐹',
                        };
                        petIconEl.textContent = animalIconMap[jenisKey] || '🐾';
                    }

                    // Update header
                    document.getElementById('tracking-pet-name').textContent = `Grafik Perkembangan: ${data.pet.nama} (${data.pet.jenis})`;
                    document.getElementById('tracking-pet-sub').textContent = `Owner: ${data.pet.owner_nama} (${data.pet.owner_telpon}) | Total Kunjungan: ${data.pet.total_visits} kali`;

                    // Populate Stats
                    document.getElementById('stat-weight-latest').textContent = data.summary.latest_weight;
                    document.getElementById('stat-weight-initial').textContent = data.summary.initial_weight;
                    
                    const diffBadge = document.getElementById('stat-weight-diff-badge');
                    if (diffBadge) {
                        if (data.summary.weight_diff) {
                            diffBadge.textContent = data.summary.weight_diff;
                            if (data.summary.weight_diff.startsWith('+')) {
                                diffBadge.className = 'fw-bold text-success';
                            } else if (data.summary.weight_diff.startsWith('-')) {
                                diffBadge.className = 'fw-bold text-danger';
                            } else {
                                diffBadge.className = 'fw-bold text-muted';
                            }
                        } else {
                            diffBadge.textContent = 'Data awal';
                            diffBadge.className = 'small text-muted';
                        }
                    }

                    document.getElementById('stat-temp-latest').textContent = data.summary.latest_temp;
                    const tempBadge = document.getElementById('stat-temp-badge');
                    if (tempBadge) {
                        if (data.summary.temp_status) {
                            tempBadge.classList.remove('d-none');
                            tempBadge.textContent = data.summary.temp_status;
                            if (data.summary.temp_status.includes('Demam')) {
                                tempBadge.style.backgroundColor = '#fee2e2';
                                tempBadge.style.color = '#991b1b';
                            } else if (data.summary.temp_status.includes('Hipotermia')) {
                                tempBadge.style.backgroundColor = '#fef3c7';
                                tempBadge.style.color = '#92400e';
                            } else {
                                tempBadge.style.backgroundColor = '#dcfce7';
                                tempBadge.style.color = '#166534';
                            }
                        } else {
                            tempBadge.classList.add('d-none');
                        }
                    }

                    document.getElementById('stat-pulse-latest').textContent = data.summary.latest_pulse;
                    document.getElementById('stat-visits-total').textContent = `${data.pet.total_visits} Kunjungan`;

                    // Render Weight Chart
                    const ctxWeight = document.getElementById('chart-weight-canvas').getContext('2d');
                    if (weightChartInstance) weightChartInstance.destroy();

                    weightChartInstance = new Chart(ctxWeight, {
                        type: 'line',
                        data: {
                            labels: data.chart.labels,
                            datasets: [{
                                label: 'Berat Badan (kg)',
                                data: data.chart.weight,
                                borderColor: '#2d8aba',
                                backgroundColor: 'rgba(45, 138, 186, 0.15)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#2d8aba',
                                pointRadius: 5,
                                pointHoverRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    title: { display: true, text: 'Berat (kg)' }
                                },
                                x: {
                                    grid: { display: false }
                                }
                            }
                        }
                    });

                    // Render Vitals Chart
                    const ctxVitals = document.getElementById('chart-vitals-canvas').getContext('2d');
                    if (vitalsChartInstance) vitalsChartInstance.destroy();

                    vitalsChartInstance = new Chart(ctxVitals, {
                        type: 'line',
                        data: {
                            labels: data.chart.labels,
                            datasets: [
                                {
                                    label: 'Suhu Tubuh (°C)',
                                    data: data.chart.temperature,
                                    borderColor: '#ef4444',
                                    backgroundColor: '#ef4444',
                                    borderWidth: 2,
                                    yAxisID: 'yTemp',
                                    tension: 0.35,
                                    pointRadius: 5
                                },
                                {
                                    label: 'Denyut Jantung (bpm)',
                                    data: data.chart.heart_rate,
                                    borderColor: '#10b981',
                                    backgroundColor: '#10b981',
                                    borderWidth: 2,
                                    yAxisID: 'yPulse',
                                    tension: 0.35,
                                    pointRadius: 5
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                yTemp: {
                                    type: 'linear',
                                    position: 'left',
                                    title: { display: true, text: 'Suhu (°C)' },
                                    suggestedMin: 36,
                                    suggestedMax: 41
                                },
                                yPulse: {
                                    type: 'linear',
                                    position: 'right',
                                    title: { display: true, text: 'Pulse (bpm)' },
                                    grid: { drawOnChartArea: false }
                                }
                            }
                        }
                    });

                    // Render EMR Timeline
                    const timelineContainer = document.getElementById('emr-timeline-container');
                    if (timelineContainer) {
                        timelineContainer.innerHTML = '';
                        if (!data.timeline || data.timeline.length === 0) {
                            timelineContainer.innerHTML = '<div class="text-muted small">Belum ada catatan rekam medis.</div>';
                        } else {
                            // Bug 3 fix: HTML escape helper
                            const esc = (str) => {
                                if (str === null || str === undefined) return '-';
                                return String(str)
                                    .replace(/&/g, '&amp;')
                                    .replace(/</g, '&lt;')
                                    .replace(/>/g, '&gt;')
                                    .replace(/"/g, '&quot;');
                            };

                            // Bug 1 fix: status → colored badge (no emoji)
                            const makeStatusBadge = (s) => {
                                const key = (s || '').toLowerCase().trim();
                                const map = {
                                    'belum dikerjakan':  { label: 'Kedatangan',    style: 'background-color:#fff8e1;color:#f57f17;border:1px solid #f57f17;' },
                                    'kedatangan':        { label: 'Kedatangan',    style: 'background-color:#fff8e1;color:#f57f17;border:1px solid #f57f17;' },
                                    'memulai':           { label: 'Memulai',       style: 'background-color:#e3f2fd;color:#1565c0;border:1px solid #1565c0;' },
                                    'rawat inap':        { label: 'Rawat Inap',    style: 'background-color:#efebe9;color:#4e342e;border:1px solid #4e342e;' },
                                    'rawat inap log':    { label: 'Observasi Inap',style: 'background-color:#efebe9;color:#4e342e;border:1px solid #4e342e;' },
                                    'apotek':            { label: 'Apotek',        style: 'background-color:#fff3e0;color:#e65100;border:1px solid #e65100;' },
                                    'pembayaran':        { label: 'Pembayaran',    style: 'background-color:#f3e5f5;color:#6a1b9a;border:1px solid #6a1b9a;' },
                                    'telah diselesaikan':{ label: 'Selesai',       style: 'background-color:#e8f5e9;color:#1b5e20;border:1px solid #1b5e20;' },
                                    'selesai':           { label: 'Selesai',       style: 'background-color:#e8f5e9;color:#1b5e20;border:1px solid #1b5e20;' },
                                };
                                const found = map[key];
                                const label = found ? found.label : esc(s || '-');
                                const style = found ? found.style : 'background-color:#eceff1;color:#546e7a;border:1px solid #546e7a;';
                                return `<span class="badge px-2 py-1 rounded-pill" style="${style}font-size:0.72rem;font-weight:600;">${label}</span>`;
                            };

                            data.timeline.forEach(item => {
                                const el = document.createElement('div');
                                el.className = 'timeline-item';
                                el.innerHTML = `
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-card">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong class="text-primary" style="font-size: 0.875rem;">${esc(item.tanggal)} (${esc(item.jam)})</strong>
                                                <span class="text-muted ms-2 small fw-semibold">${esc(item.kode_booking)}</span>
                                            </div>
                                            ${makeStatusBadge(item.status)}
                                        </div>
                                        <div class="mb-2" style="font-size: 0.875rem;">
                                            <span class="fw-bold me-2">Dokter/Perawat:</span> ${esc(item.dokter)} | 
                                            <span class="fw-bold ms-2 me-1">Layanan:</span> ${esc(item.layanan)}
                                        </div>
                                        <div class="p-2 bg-light rounded text-dark mb-2" style="font-size: 0.875rem;">
                                            <strong>Keluhan:</strong> ${esc(item.keluhan)}
                                        </div>
                                        <div class="row g-2 text-muted" style="font-size: 0.75rem;">
                                            <div class="col-6"><strong>Subjective:</strong> ${esc(item.subjective)}</div>
                                            <div class="col-6"><strong>Objective:</strong> ${esc(item.objective)}</div>
                                            <div class="col-6"><strong>Assessment:</strong> ${esc(item.assessment)}</div>
                                            <div class="col-6"><strong>Plan:</strong> ${esc(item.plan)}</div>
                                        </div>
                                    </div>
                                `;
                                timelineContainer.appendChild(el);
                            });
                        }
                    }

                    // Display Content
                    if (loadingEl) loadingEl.style.display = 'none';
                    if (contentEl) contentEl.style.display = 'block';
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan saat memuat data statistik.');
                    if (loadingEl) loadingEl.style.display = 'none';
                });
        });
    });
});
</script>
@endsection