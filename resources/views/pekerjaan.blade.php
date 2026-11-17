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
                        <i class="bi bi-briefcase-fill me-2"></i>Pekerjaan Anda
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Informasi data pemeriksaan dan tindakan yang telah Anda tangani
                    </small>
                </div>
            </div>
        </div>

        <!-- KRELOSES CARD CONTAINER -->
        <div class="kreloses-card mb-4">
            <div class="table-responsive">
                <table class="kreloses-table" id="table1">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="25%">Nama Klien & Pasien</th>
                            <th width="15%">No. Telepon</th>
                            <th width="15%">Tanggal Booking</th>
                            <th width="15%">Status</th>
                            @if(in_array(auth()->user()->role, ['karyawan', 'admin', 'superadmin']))
                                <th width="15%" class="text-end">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($booking as $item)
                        <tr>
                            <td><strong>{{ $loop->iteration }}</strong></td>
                            <td>
                                <div class="fw-semibold">
                                    <i class="bi bi-person-fill me-1" style="color: #2d8aba;"></i>{{ $item->nama }}
                                </div>
                                <div class="text-dark small">
                                    <i class="bi bi-heart-pulse-fill me-1" style="color: #2d8aba;"></i><strong>{{ $item->namahewan ?: ($item->nama_hewan ?: 'Pasien') }}</strong>
                                    @if($item->jenishewan || $item->jenis_hewan)
                                        <span class="text-muted">({{ $item->jenishewan ?: $item->jenis_hewan }})</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a href="tel:{{ $item->telpon }}" class="text-decoration-none" style="color: #546e7a;">
                                    {{ $item->telpon }}
                                </a>
                            </td>
                            <td class="text-dark font-medium">
                                {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y') : '-' }}
                            </td>
                            <td>
                                @if ($item->status == 'belum dikerjakan' || $item->status == 'Kedatangan')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1 rounded-2">
                                        <i class="bi bi-clock-history me-1"></i>Belum Dikerjakan
                                    </span>
                                @elseif ($item->status == 'Memulai')
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1 rounded-2">
                                        <i class="bi bi-activity me-1"></i>Memulai
                                    </span>
                                @elseif ($item->status == 'Rawat Inap')
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-2 py-1 rounded-2">
                                        🟤 Rawat Inap
                                    </span>
                                @elseif ($item->status == 'Apotek')
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1 rounded-2">
                                        <i class="bi bi-capsule me-1"></i>Apotek
                                    </span>
                                @elseif ($item->status == 'Pembayaran')
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1 rounded-2">
                                        <i class="bi bi-credit-card me-1"></i>Pembayaran
                                    </span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 rounded-2">
                                        <i class="bi bi-check-circle-fill me-1"></i>{{ ucfirst($item->status) }}
                                    </span>
                                @endif
                            </td>
                            @if(in_array(auth()->user()->role, ['karyawan', 'admin', 'superadmin']))
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#pengerjaan-{{ $item->id }}" title="Perbarui Data">
                                        <i class="bi bi-pencil-square me-1"></i> Perbarui Data
                                    </button>
                                </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ in_array(auth()->user()->role, ['karyawan', 'admin', 'superadmin']) ? 6 : 5 }}" class="text-center py-5" style="color: #546e7a;">
                                <i class="bi bi-briefcase fs-1 d-block mb-2 text-muted"></i>
                                <div class="fw-semibold mb-1">Belum Ada Riwayat Pekerjaan</div>
                                <small class="text-muted d-block">Pekerjaan penanganan dan pemeriksaan pasien yang Anda tangani akan muncul di sini.</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @include('components.footer.footer_admin')
    </div>
</div>

<!-- Modal -->
@include('components.modals.updatebook')
<!-- end Modal -->
@endsection
