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
                        <i class="bi bi-check2-circle me-2"></i>Booking Telah Diselesaikan
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Riwayat kunjungan & transaksi pasien klinik yang telah selesai
                    </small>
                </div>
            </div>
        </div>

        <!-- KRELOSES CARD CONTAINER -->
        <div class="kreloses-card mb-4">
            <div class="table-responsive">
                <table class="kreloses-table">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="25%">Nama Klien & Pasien</th>
                            <th width="15%">No. Telepon</th>
                            <th width="15%">Tanggal Selesai</th>
                            <th width="15%">Dokter / Staf</th>
                            <th width="10%">Status</th>
                            <th width="15%" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($booking as $item)
                        <tr>
                            <td><strong>{{ $loop->iteration }}</strong></td>
                            <td>
                                <div>
                                    <a href="#" class="kreloses-owner-link" data-bs-toggle="modal" data-bs-target="#Informasi-{{ $item->id }}">
                                        <i class="bi bi-person-fill me-1" style="color: #2d8aba;"></i>{{ $item->nama }}
                                    </a>
                                </div>
                                <div class="text-dark small">
                                    <i class="bi bi-heart-pulse-fill me-1" style="color: #2d8aba;"></i><strong>{{ $item->namahewan ?: ($item->nama_hewan ?: 'Pasien') }}</strong>
                                    <span class="text-muted">({{ $item->jenishewan ?: ($item->jenis_hewan ?: 'Kucing') }})</span>
                                </div>
                            </td>
                            <td>
                                <a href="tel:{{ $item->telpon }}" class="text-decoration-none text-muted">
                                    {{ $item->telpon }}
                                </a>
                            </td>
                            <td class="text-dark font-medium">
                                {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y') : '-' }}
                            </td>
                            <td class="text-muted small">
                                {{ $item->karyawan1 ?? 'Dokter Jaga' }}
                            </td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 rounded-2">
                                    <i class="bi bi-check-circle-fill me-1"></i>Selesai
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#Informasi-{{ $item->id }}" title="Detail Informasi">
                                        <i class="bi bi-info-circle"></i>
                                    </button>
                                    <a href="{{ route('nota', ['id' => $item->id]) }}" class="btn-kreloses-blue" title="Cetak Struk Invoice PDF">
                                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Invoice
                                    </a>
                                    @if(in_array(Auth::user()->role, ['admin', 'karyawan', 'superadmin']))
                                        <button type="button" class="btn btn-sm btn-warning text-dark ms-1" data-bs-toggle="modal" data-bs-target="#pengerjaan-{{ $item->id }}" title="Perbarui Data">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5" style="color: #546e7a;">
                                <i class="bi bi-clipboard-check fs-1 d-block mb-2 text-muted"></i>
                                <div class="fw-semibold mb-1">Belum Ada Pelayanan yang Selesai</div>
                                <small class="text-muted d-block">Daftar pemeriksaan, tindakan, dan grooming yang sudah selesai akan diarsipkan di sini.</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
@include('components.modals.infobook')
@include('components.modals.updatebook')
@endsection