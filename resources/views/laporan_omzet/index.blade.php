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
                        <i class="bi bi-graph-up-arrow me-2"></i>Laporan Omzet & Keuangan Detail
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Modul analisis performa finansial, visualisasi omzet, dan rincian transaksi klinik
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('laporan-omzet.pdf', request()->all()) }}" target="_blank" class="btn btn-primary btn-sm px-3 shadow-none font-weight-medium" style="background:#2d8aba;border-color:#2d8aba;">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF Resmi
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm px-3 shadow-none" onclick="window.print();">
                    <i class="bi bi-printer me-1"></i> Cetak Tampilan
                </button>
            </div>
        </div>

        <!-- FILTER TOOLBAR CARD (MINIMALIST & ALIGNED) -->
        <div class="card border-0 shadow-sm mb-4" style="background: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0 !important;">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('laporan-omzet.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Preset Range -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label text-muted small fw-semibold mb-1">
                                <i class="bi bi-calendar3 me-1 text-primary"></i>Periode Laporan
                            </label>
                            <select name="range" id="filter-range" class="form-select form-select-sm fw-medium" style="color: #334155;" onchange="toggleCustomDate(this.value)">
                                <option value="today" {{ $filters['range'] == 'today' ? 'selected' : '' }}>Hari Ini</option>
                                <option value="7days" {{ $filters['range'] == '7days' ? 'selected' : '' }}>7 Hari Terakhir</option>
                                <option value="this_month" {{ $filters['range'] == 'this_month' ? 'selected' : '' }}>Bulan Ini ({{ date('M Y') }})</option>
                                <option value="last_month" {{ $filters['range'] == 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                                <option value="this_year" {{ $filters['range'] == 'this_year' ? 'selected' : '' }}>Tahun Ini ({{ date('Y') }})</option>
                                <option value="custom" {{ $filters['range'] == 'custom' ? 'selected' : '' }}>Kustom Tanggal</option>
                            </select>
                        </div>

                        <!-- Custom Date Range Inputs -->
                        <div class="col-6 col-sm-3 col-md-2 custom-date-field" style="{{ $filters['range'] == 'custom' ? '' : 'display: none;' }}">
                            <label class="form-label text-muted small fw-semibold mb-1">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $filters['start_date']->format('Y-m-d') }}">
                        </div>
                        <div class="col-6 col-sm-3 col-md-2 custom-date-field" style="{{ $filters['range'] == 'custom' ? '' : 'display: none;' }}">
                            <label class="form-label text-muted small fw-semibold mb-1">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $filters['end_date']->format('Y-m-d') }}">
                        </div>

                        <!-- Status Filter -->
                        <div class="col-6 col-sm-6 col-md-2">
                            <label class="form-label text-muted small fw-semibold mb-1">
                                <i class="bi bi-funnel me-1 text-primary"></i>Status Transaksi
                            </label>
                            <select name="status" class="form-select form-select-sm fw-medium">
                                <option value="completed" {{ $filters['status'] == 'completed' ? 'selected' : '' }}>Telah Diselesaikan</option>
                                <option value="all" {{ $filters['status'] == 'all' ? 'selected' : '' }}>Semua Booking</option>
                                <option value="Pembayaran" {{ $filters['status'] == 'Pembayaran' ? 'selected' : '' }}>Pembayaran</option>
                                <option value="Rawat Inap" {{ $filters['status'] == 'Rawat Inap' ? 'selected' : '' }}>🟤 Rawat Inap</option>
                            </select>
                        </div>

                        <!-- Payment Method Filter -->
                        <div class="col-6 col-sm-6 col-md-2">
                            <label class="form-label text-muted small fw-semibold mb-1">
                                <i class="bi bi-credit-card me-1 text-primary"></i>Metode Bayar
                            </label>
                            <select name="metode_pembayaran" class="form-select form-select-sm fw-medium">
                                <option value="all" {{ $filters['metode_pembayaran'] == 'all' ? 'selected' : '' }}>Semua Metode</option>
                                <option value="Tunai" {{ $filters['metode_pembayaran'] == 'Tunai' ? 'selected' : '' }}>Tunai / Cash</option>
                                <option value="Transfer" {{ $filters['metode_pembayaran'] == 'Transfer' ? 'selected' : '' }}>Transfer Bank</option>
                                <option value="QRIS" {{ $filters['metode_pembayaran'] == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                                <option value="Debet" {{ $filters['metode_pembayaran'] == 'Debet' ? 'selected' : '' }}>Kartu Debet/Kredit</option>
                                <option value="Belum Ditentukan" {{ $filters['metode_pembayaran'] == 'Belum Ditentukan' ? 'selected' : '' }}>Belum Ditentukan</option>
                            </select>
                        </div>

                        <!-- Search Box & Actions -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label text-muted small fw-semibold mb-1">
                                <i class="bi bi-search me-1 text-primary"></i>Pencarian
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Owner / hewan / kode..." value="{{ $filters['search'] }}">
                                <button type="submit" class="btn btn-primary px-3 shadow-none">
                                    <i class="bi bi-search"></i>
                                </button>
                                @if($filters['search'] || $filters['range'] != 'this_month' || $filters['status'] != 'completed' || $filters['metode_pembayaran'] != 'all')
                                    <a href="{{ route('laporan-omzet.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- KPI SUMMARY CARDS (MINIMALIST PRD STANDARD) -->
        <div class="row g-3 mb-4">
            <!-- 1. Omzet Bersih -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 p-3" style="border-radius: 10px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Omzet Bersih</span>
                        <div class="rounded-2 p-2 d-flex align-items-center justify-content-center" style="background: #eef7fc; color: #2d8aba; width: 36px; height: 36px;">
                            <i class="bi bi-cash-stack fs-5"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-dark mb-1" style="font-size: 1.35rem; letter-spacing: -0.3px;">
                        Rp {{ number_format($totalOmzetClean, 0, ',', '.') }}
                    </div>
                    <div class="small text-muted d-flex align-items-center gap-1">
                        <i class="bi bi-check-circle text-success me-1"></i> Net revenue periode ini
                    </div>
                </div>
            </div>

            <!-- 2. Omzet Kotor -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 p-3" style="border-radius: 10px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem; letter-spacing: 0.5px;">Omzet Kotor (Gross)</span>
                        <div class="rounded-2 p-2 d-flex align-items-center justify-content-center" style="background: #f0f9ff; color: #0284c7; width: 36px; height: 36px;">
                            <i class="bi bi-receipt-cutoff fs-5"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-dark mb-1" style="font-size: 1.35rem; letter-spacing: -0.3px;">
                        Rp {{ number_format($totalGross, 0, ',', '.') }}
                    </div>
                    <div class="small text-muted">
                        Diskon: <span class="text-danger">Rp {{ number_format($totalDiskon, 0, ',', '.') }}</span> | Pajak: <span class="text-primary">Rp {{ number_format($totalPajak, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- 3. Volume Transaksi & AOV -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 p-3" style="border-radius: 10px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Transaksi (Volume)</span>
                        <div class="rounded-2 p-2 d-flex align-items-center justify-content-center" style="background: #ecfdf5; color: #10b981; width: 36px; height: 36px;">
                            <i class="bi bi-bag-check fs-5"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-dark mb-1" style="font-size: 1.35rem; letter-spacing: -0.3px;">
                        {{ number_format($totalTransaksi, 0, ',', '.') }} <span class="fs-6 font-normal text-muted">Transaksi</span>
                    </div>
                    <div class="small text-muted">
                        Rata-rata Order (AOV): <strong class="text-dark">Rp {{ number_format($aov, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <!-- 4. Uang Muka (DP) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 p-3" style="border-radius: 10px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total DP Terkumpul</span>
                        <div class="rounded-2 p-2 d-flex align-items-center justify-content-center" style="background: #f5f3ff; color: #8b5cf6; width: 36px; height: 36px;">
                            <i class="bi bi-wallet2 fs-5"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-dark mb-1" style="font-size: 1.35rem; letter-spacing: -0.3px;">
                        Rp {{ number_format($totalDp, 0, ',', '.') }}
                    </div>
                    <div class="small text-muted">
                        Uang muka dari transaksi terdaftar
                    </div>
                </div>
            </div>
        </div>

        <!-- VISUAL CHARTS SECTION (PRD STANDARD GRID) -->
        <div class="row g-3 mb-4">
            <!-- Chart 1: Line Chart Tren Omzet -->
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 10px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0" style="color: #0f172a; font-size: 1rem;">
                                <i class="bi bi-graph-up me-2 text-primary"></i>Grafik Tren Pendapatan Omzet
                            </h6>
                            <small class="text-muted">Periode {{ $filters['start_date']->format('d M Y') }} - {{ $filters['end_date']->format('d M Y') }}</small>
                        </div>
                        <span class="badge bg-light text-secondary border px-2 py-1">
                            {{ count($chartTrend['labels']) }} Titik Data
                        </span>
                    </div>
                    <div class="card-body pt-2" style="position: relative; height: 310px;">
                        <canvas id="chartOmzetTrend"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart 3: Doughnut Chart Metode Pembayaran -->
            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 10px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                        <h6 class="fw-bold mb-0" style="color: #0f172a; font-size: 1rem;">
                            <i class="bi bi-pie-chart me-2 text-primary"></i>Metode Pembayaran
                        </h6>
                        <small class="text-muted">Proporsi kanal pembayaran transaksi</small>
                    </div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center pt-2" style="position: relative; height: 310px;">
                        <canvas id="chartOmzetPayment" style="max-height: 240px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart 2: Bar Chart Breakdown per Kategori -->
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 10px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0" style="color: #0f172a; font-size: 1rem;">
                                <i class="bi bi-bar-chart-steps me-2 text-primary"></i>Breakdown Omzet per Kategori Layanan & Produk
                            </h6>
                            <small class="text-muted">Kontribusi omzet dari Jasa Dokter, Grooming, Vaksin, Operasi, Diagnosa, dan Penjualan Obat</small>
                        </div>
                    </div>
                    <div class="card-body pt-2" style="position: relative; height: 270px;">
                        <canvas id="chartOmzetCategory"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- TRANSACTIONS TABLE CARD (PRD CLEAN MINIMALIST TABLE) -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <h6 class="fw-bold mb-0" style="color: #0f172a; font-size: 1rem;">
                        <i class="bi bi-list-columns-reverse me-2 text-primary"></i>Rincian Transaksi Omzet Pasien
                    </h6>
                    <small class="text-muted">Menampilkan {{ $transactionsList->firstItem() ?? 0 }} - {{ $transactionsList->lastItem() ?? 0 }} dari {{ $transactionsList->total() }} transaksi</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead style="background-color: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                            <tr>
                                <th class="ps-3 py-3 font-weight-semibold">Kode / Tanggal</th>
                                <th class="py-3 font-weight-semibold">Owner / Pelanggan</th>
                                <th class="py-3 font-weight-semibold">Pasien (Hewan)</th>
                                <th class="py-3 font-weight-semibold">Komponen Layanan & Obat</th>
                                <th class="py-3 font-weight-semibold">Metode Bayar</th>
                                <th class="py-3 font-weight-semibold text-end">Diskon & Pajak</th>
                                <th class="pe-3 py-3 font-weight-semibold text-end">Total Omzet (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactionsList as $item)
                                @php
                                    $totalObatItem = $item->transaksis->sum('total');
                                @endphp
                                <tr>
                                    <td class="ps-3 py-3">
                                        <div class="fw-bold text-primary">{{ $item->kode_booking ?? ('BOOK-'.$item->id) }}</div>
                                        <small class="text-muted"><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark">{{ $item->nama ?? '-' }}</div>
                                        <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $item->telpon ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark">{{ $item->nama_hewan ?: ($item->namahewan ?: '-') }}</div>
                                        <span class="badge bg-light text-secondary border px-2 py-0" style="font-size: 0.7rem;">
                                            {{ $item->jenis_hewan ?: ($item->jenishewan ?: 'Hewan') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($item->jasa_dokter_harga > 0)
                                                <span class="badge bg-light text-dark border" style="font-size: 0.725rem;">Dokter: Rp{{ number_format($item->jasa_dokter_harga, 0, ',', '.') }}</span>
                                            @endif
                                            @if($item->vaksin_harga > 0)
                                                <span class="badge bg-light text-primary border border-primary" style="font-size: 0.725rem;">Vaksin: Rp{{ number_format($item->vaksin_harga, 0, ',', '.') }}</span>
                                            @endif
                                            @if($item->grooming_harga > 0)
                                                <span class="badge bg-light text-warning border border-warning" style="font-size: 0.725rem;">Grooming: Rp{{ number_format($item->grooming_harga, 0, ',', '.') }}</span>
                                            @endif
                                            @if($item->operasi_harga > 0)
                                                <span class="badge bg-light text-danger border border-danger" style="font-size: 0.725rem;">Operasi: Rp{{ number_format($item->operasi_harga, 0, ',', '.') }}</span>
                                            @endif
                                            @if($item->diagnosa_penunjang_harga > 0)
                                                <span class="badge bg-light text-secondary border" style="font-size: 0.725rem;">Diagnosa: Rp{{ number_format($item->diagnosa_penunjang_harga, 0, ',', '.') }}</span>
                                            @endif
                                            @if($totalObatItem > 0)
                                                <span class="badge bg-light text-success border border-success" style="font-size: 0.725rem;">Obat ({{ $item->transaksis->count() }}): Rp{{ number_format($totalObatItem, 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if(!empty($item->metode_pembayaran))
                                            <span class="badge bg-light text-dark border px-2 py-1" style="font-weight: 500;">
                                                <i class="bi bi-credit-card me-1"></i>{{ ucfirst($item->metode_pembayaran) }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1">Belum Ditentukan</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($item->diskon > 0)
                                            <small class="text-danger d-block">- Rp{{ number_format($item->diskon, 0, ',', '.') }}</small>
                                        @endif
                                        @if($item->pajak > 0)
                                            <small class="text-primary d-block">+ Rp{{ number_format($item->pajak, 0, ',', '.') }}</small>
                                        @endif
                                        @if($item->dp > 0)
                                            <small class="text-success d-block">DP: Rp{{ number_format($item->dp, 0, ',', '.') }}</small>
                                        @endif
                                        @if($item->diskon == 0 && $item->pajak == 0 && $item->dp == 0)
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="pe-3 text-end fw-bold text-dark" style="font-size: 0.9rem;">
                                        Rp {{ number_format($item->total, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                        Tidak ada transaksi omzet terdaftar pada periode terfilter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($transactionsList->hasPages())
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                    <small class="text-muted">Halaman {{ $transactionsList->currentPage() }} dari {{ $transactionsList->lastPage() }}</small>
                    <div>
                        {{ $transactionsList->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- SCRIPTS FOR CHART.JS VISUALIZATION -->
<script>
function toggleCustomDate(val) {
    const fields = document.querySelectorAll('.custom-date-field');
    fields.forEach(f => {
        f.style.display = (val === 'custom') ? 'block' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const formatRp = (num) => 'Rp ' + (new Number(num)).toLocaleString('id-ID');

    // 1. Line / Area Chart: Tren Omzet Pendapatan
    const trendCtx = document.getElementById('chartOmzetTrend').getContext('2d');
    const trendGradient = trendCtx.createLinearGradient(0, 0, 0, 280);
    trendGradient.addColorStop(0, 'rgba(45, 138, 186, 0.25)');
    trendGradient.addColorStop(1, 'rgba(45, 138, 186, 0.0)');

    const chartTrendLabels = {!! json_encode($chartTrend['labels']) !!};
    const chartTrendValues = {!! json_encode($chartTrend['values']) !!};
    const chartTrendCounts = {!! json_encode($chartTrend['counts']) !!};

    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: chartTrendLabels,
            datasets: [{
                label: 'Omzet Pendapatan (Rp)',
                data: chartTrendValues,
                borderColor: '#2d8aba',
                borderWidth: 2.5,
                backgroundColor: trendGradient,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#2d8aba',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 3.5,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 10,
                    titleFont: { family: 'Inter', size: 13, weight: '600' },
                    bodyFont: { family: 'Inter', size: 12 },
                    callbacks: {
                        label: function(context) {
                            const index = context.dataIndex;
                            const val = context.parsed.y;
                            const count = chartTrendCounts[index] || 0;
                            return [
                                ' Omzet: ' + formatRp(val),
                                ' Transaksi: ' + count + ' Pasien'
                            ];
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { family: 'Inter', size: 11 } }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        color: '#64748b',
                        font: { family: 'Inter', size: 11 },
                        callback: function(value) {
                            if (value >= 1000000) return (value / 1000000).toFixed(1) + ' Jt';
                            if (value >= 1000) return (value / 1000).toFixed(0) + ' Rb';
                            return value;
                        }
                    }
                }
            }
        }
    });

    // 2. Bar Chart: Breakdown Omzet per Kategori
    const categoryCtx = document.getElementById('chartOmzetCategory').getContext('2d');
    const categoryData = {!! json_encode($categoryBreakdown) !!};
    const categoryLabels = Object.keys(categoryData);
    const categoryValues = Object.values(categoryData);

    const categoryColors = [
        '#0284c7', // Jasa Dokter
        '#2563eb', // Vaksin
        '#f59e0b', // Grooming
        '#ef4444', // Operasi
        '#64748b', // Diagnosa Penunjang
        '#10b981', // Obat & Inventori
        '#8b5cf6', // Transportasi
        '#475569'  // Lainnya
    ];

    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: categoryLabels,
            datasets: [{
                label: 'Total Nominal (Rp)',
                data: categoryValues,
                backgroundColor: categoryColors,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return ' Omzet Kotor: ' + formatRp(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#334155', font: { family: 'Inter', size: 11, weight: '500' } }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        color: '#64748b',
                        font: { family: 'Inter', size: 11 },
                        callback: function(value) {
                            if (value >= 1000000) return (value / 1000000).toFixed(1) + ' Jt';
                            if (value >= 1000) return (value / 1000).toFixed(0) + ' Rb';
                            return value;
                        }
                    }
                }
            }
        }
    });

    // 3. Doughnut Chart: Metode Pembayaran
    const paymentCtx = document.getElementById('chartOmzetPayment').getContext('2d');
    const paymentData = {!! json_encode($paymentBreakdown) !!};
    const paymentLabels = Object.keys(paymentData);
    const paymentTotals = paymentLabels.map(k => paymentData[k].total);
    const paymentCounts = paymentLabels.map(k => paymentData[k].count);

    const paymentColors = ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#64748b'];

    new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: paymentLabels,
            datasets: [{
                data: paymentTotals,
                backgroundColor: paymentColors,
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, font: { family: 'Inter', size: 11 } }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            const index = context.dataIndex;
                            const totalVal = context.parsed;
                            const count = paymentCounts[index] || 0;
                            return [
                                ' ' + context.label + ': ' + formatRp(totalVal),
                                ' Volume: ' + count + ' Transaksi'
                            ];
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });
});
</script>
@endsection
