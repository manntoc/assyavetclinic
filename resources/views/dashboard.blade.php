@extends('layouts.admin')
@section('content')

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

<style>
/* Clean Dashboard Layout */
.card-stat {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
    padding: 16px 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.card-stat .stat-label {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
    margin-bottom: 4px;
}

.card-stat .stat-value {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0;
}

/* Kalender Container */
.calendar-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

#calendar {
    width: 100% !important;
    min-height: 580px;
}

.fc {
    font-family: inherit;
    --fc-border-color: #e2e8f0;
    --fc-page-bg-color: #ffffff;
    --fc-neutral-bg-color: #f8fafc;
    width: 100% !important;
}

.fc .fc-toolbar {
    margin-bottom: 16px !important;
    gap: 8px;
}

.fc .fc-toolbar-title {
    font-size: 1.25rem !important;
    font-weight: 700;
    color: #2d8aba !important;
}

.fc .fc-button {
    background: #ffffff !important;
    color: #475569 !important;
    border: 1px solid #cbd5e1 !important;
    padding: 6px 12px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    box-shadow: none !important;
    border-radius: 6px !important;
}

.fc .fc-button:hover {
    background: #eef7fc !important;
    color: #2d8aba !important;
}

.fc .fc-button-active,
.fc .fc-button-primary:not(:disabled).fc-button-active {
    background: #2d8aba !important;
    color: #ffffff !important;
    border-color: #2d8aba !important;
}

.fc-col-header-cell {
    background: #f8fafc;
    border-color: #e2e8f0 !important;
    padding: 8px 0 !important;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
}

.fc-day-today {
    background: #eef7fc !important;
}

.fc-event {
    cursor: pointer;
    border-radius: 4px !important;
    padding: 2px 5px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
}

/* Responsive Kalender Toolbar */
@media(max-width:768px) {
    .fc .fc-toolbar {
        flex-direction: column;
        align-items: stretch !important;
    }
    .fc .fc-toolbar-title {
        text-align: center;
    }
}
</style>

<div id="app">
    @include('components.headers.navbar')

    <div id="main">
        <header class="mb-3">
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
                        <i class="bi bi-grid-fill me-2"></i>Dashboard Operasional
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Ringkasan operasional klinik & jadwal booking pasien
                    </small>
                </div>
            </div>
        </div>

        <div class="page-content">
            <!-- RINGKASAN CARDS (KRELOSES MINIMALIST STATS) -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="kreloses-card p-3">
                        <div class="text-muted small fw-medium mb-1">Kedatangan</div>
                        <div class="fs-4 fw-bold text-dark mb-0">
                            {{ count($bookingbelum) }} <small class="fs-6 text-muted fw-normal">Pasien</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="kreloses-card p-3">
                        <div class="text-muted small fw-medium mb-1">Ruang Dokter EMR</div>
                        <div class="fs-4 fw-bold text-dark mb-0">
                            {{ \App\Models\booking::where('status', 'Memulai')->count() }} <small class="fs-6 text-muted fw-normal">Pasien</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="kreloses-card p-3">
                        <div class="text-muted small fw-medium mb-1">Resep Apotek</div>
                        <div class="fs-4 fw-bold text-dark mb-0">
                            {{ \App\Models\booking::where('status', 'Apotek')->count() }} <small class="fs-6 text-muted fw-normal">Pasien</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="kreloses-card p-3">
                        <div class="text-muted small fw-medium mb-1">Kasir POS & Selesai</div>
                        <div class="fs-4 fw-bold text-dark mb-0">
                            {{ \App\Models\booking::whereIn('status', ['Pembayaran', 'Telah diselesaikan', 'Selesai'])->count() }} <small class="fs-6 text-muted fw-normal">Pasien</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARI BOOKING / OWNER -->
            <div class="kreloses-card mb-4 p-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control border-start-0 ps-0 text-muted"
                        id="bookingSearch"
                        placeholder="Cari pasien / pemilik..."
                    >
                </div>

                <!-- TABEL HASIL PENCARIAN -->
                <div class="mt-3" id="searchCard" style="display:none;">
                    <div class="table-responsive">
                        <table class="kreloses-table mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Pemilik</th>
                                    <th>Nama Hewan</th>
                                    <th>Jenis Hewan</th>
                                    <th>Ras</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="searchResult"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- KALENDER BOOKING -->
            <div class="calendar-card">
                <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom flex-wrap gap-2">
                    <h6 class="fw-bold mb-0">Kalender Booking</h6>

                    <!-- LEGEND WARNA STATUS BRAND PATTERN -->
                    <div class="d-flex align-items-center gap-3 small">
                        <span>
                            <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #2d8aba;"></span>
                            Belum Dikerjakan
                        </span>
                        <span>
                            <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #546e7a;"></span>
                            Telah Diselesaikan
                        </span>
                    </div>
                </div>

                <!-- CONTAINER FULLCALENDAR -->
                <div id="calendar"></div>
            </div>
        </div>

        @include('components.footer.footer_admin')
    </div>
</div>

<!-- MODAL DETAIL BOOKING -->
<div class="modal fade" id="bookingDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Detail Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 p-2 bg-light rounded">
                    <small class="text-muted d-block">Status Booking:</small>
                    <span id="modalStatusBadge" class="badge"></span>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted d-block">Nama Hewan</small>
                        <div id="modalNamaHewan" class="fw-semibold"></div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Jenis & Berat</small>
                        <div class="fw-semibold">
                            <span id="modalJenisHewan"></span> (<span id="modalBeratHewan"></span>)
                        </div>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted d-block">Pemilik (Owner)</small>
                        <div id="modalOwner" class="fw-semibold"></div>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted d-block">Telepon</small>
                        <div id="modalTelpon" class="fw-semibold"></div>
                    </div>
                    <div class="col-12 mt-2">
                        <small class="text-muted d-block">Tanggal Booking</small>
                        <div id="modalTanggal" class="fw-semibold"></div>
                    </div>
                    <div class="col-12 mt-2">
                        <small class="text-muted d-block">Keluhan</small>
                        <div id="modalKeluhan" class="fw-medium text-secondary"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
                <a href="{{ route('booking-uncompleted') }}" class="btn btn-primary btn-sm">
                    Kelola Booking
                </a>
            </div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT FULLCALENDAR & SEARCH -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const calendarEl = document.getElementById('calendar');
    const bookingEvents = @json($events);

    // Inisialisasi FullCalendar secara cepat & native
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: window.innerWidth < 768 ? 'timeGridDay' : 'dayGridMonth',
        locale: 'id',
        height: 'auto',
        firstDay: 1,

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },

        buttonText: {
            today:    'Hari Ini',
            month:    'Bulan',
            week:     'Minggu',
            day:      'Hari',
            list:     'Daftar'
        },

        slotMinTime: "08:00:00",
        slotMaxTime: "20:00:00",
        editable: true,
        eventDisplay: 'block',
        events: bookingEvents,

        // Handle update jadwal via Drag & Drop
        eventDrop: function(info) {
            fetch('/calendar/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: info.event.id,
                    start: info.event.start
                })
            })
            .then(res => res.json())
            .catch(err => console.error("Update Error:", err));
        },

        // Click Event -> Tampilkan Modal Detail
        eventClick: function(info) {
            const props = info.event.extendedProps;
            const status = props.status ?? 'belum dikerjakan';
            const isSelesai = status.toLowerCase().includes('selesai');

            document.getElementById('modalTitle').innerText = (props.kode_booking ?? ('BOOK-' + info.event.id));
            document.getElementById('modalNamaHewan').innerText = props.nama_hewan ?? info.event.title;
            document.getElementById('modalJenisHewan').innerText = props.jenis_hewan ?? '-';
            document.getElementById('modalBeratHewan').innerText = props.berat ? (props.berat + ' kg') : '-';
            document.getElementById('modalOwner').innerText = props.owner ?? '-';
            document.getElementById('modalTelpon').innerText = props.telpon ?? '-';
            document.getElementById('modalTanggal').innerText = props.tanggal ?? info.event.startStr;
            document.getElementById('modalKeluhan').innerText = props.keluhan ?? '-';

            const badgeEl = document.getElementById('modalStatusBadge');
            const statusMap = {
                'belum dikerjakan': 'Belum Dikerjakan (Kedatangan)',
                'kedatangan': 'Kedatangan',
                'memulai': 'Memulai',
                'rawat inap': '🟤 Rawat Inap',
                'apotek': 'Apotek',
                'pembayaran': 'Pembayaran',
                'telah diselesaikan': 'Telah Diselesaikan',
                'selesai': 'Telah Diselesaikan'
            };
            const sKey = (status || '').toLowerCase().trim();
            badgeEl.className = 'badge bg-light text-dark border fw-bold px-2 py-1';
            badgeEl.innerText = statusMap[sKey] || status;

            const modalEl = document.getElementById('bookingDetailModal');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            } else {
                $(modalEl).modal('show');
            }
        }
    });

    calendar.render();

    // AUTO RESIZE CALENDAR WHEN SIDEBAR IS TOGGLED OR WINDOW IS RESIZED
    function updateCalendarSize() {
        setTimeout(function() {
            if (calendar) {
                calendar.updateSize();
            }
        }, 320);
    }

    document.querySelectorAll('.sidebar-toggle-btn, .burger-btn').forEach(btn => {
        btn.addEventListener('click', updateCalendarSize);
    });

    window.addEventListener('resize', function() {
        if (calendar) {
            calendar.updateSize();
        }
    });

    // LIVE SEARCH PASIEN / OWNER
    const input = document.getElementById('bookingSearch');
    const result = document.getElementById('searchResult');
    const card = document.getElementById('searchCard');
    let timeout;

    if (input) {
        input.addEventListener('keyup', function() {
            clearTimeout(timeout);

            timeout = setTimeout(() => {
                const keyword = this.value.trim();

                if (keyword.length < 2) {
                    card.style.display = 'none';
                    result.innerHTML = '';
                    return;
                }

                fetch(`{{ url('dashboard/search-booking') }}?keyword=${encodeURIComponent(keyword)}`)
                    .then(res => res.json())
                    .then(data => {
                        result.innerHTML = '';

                        if (data.length === 0) {
                            card.style.display = 'block';
                            result.innerHTML = `
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        Data tidak ditemukan
                                    </td>
                                </tr>
                            `;
                            return;
                        }

                        card.style.display = 'block';

                        data.forEach(item => {
                            result.innerHTML += `
                                <tr>
                                    <td class="fw-semibold">${item.owner ? item.owner.nama : '-'}</td>
                                    <td>${item.nama}</td>
                                    <td>${item.jenis_hewan}</td>
                                    <td>${item.ras_hewan ?? '-'}</td>
                                    <td class="text-end">
                                        <a href="/booking/create/${item.owner ? item.owner.id : 0}/${item.id}"
                                            class="btn btn-primary btn-sm">
                                            Pilih
                                        </a>
                                    </td>
                                </tr>
                            `;
                        });
                    })
                    .catch(err => console.error("Search error:", err));
            }, 250);
        });
    }
});
</script>

@endsection
