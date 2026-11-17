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
                        <i class="bi bi-person-vcard me-2"></i>Detail Owner & Profil Pasien
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Informasi lengkap akun pemilik beserta seluruh hewan peliharaannya
                    </small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('owner') }}" class="btn btn-sm btn-light border px-3 fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
                <a href="{{ route('owner.edit', $owner->id) }}" class="btn btn-primary btn-sm px-3 shadow-sm" style="background:#2d8aba;border-color:#2d8aba;">
                    <i class="bi bi-pencil-square me-1"></i> Edit Owner
                </a>
            </div>
        </div>

        <section class="section">
            <!-- INFORMASI OWNER -->
            <div class="kreloses-card mb-4">
                <h5 class="fw-bold mb-3" style="color: #2d8aba;">
                    <i class="bi bi-person-vcard me-2"></i> Informasi Akun Pemilik (Owner)
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Nama Lengkap Owner</label>
                        <input type="text" class="form-control fw-semibold" value="{{ $owner->nama }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Nomor Telepon / WhatsApp</label>
                        <input type="text" class="form-control fw-semibold" value="{{ $owner->telepon }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Alamat Email</label>
                        <input type="text" class="form-control" value="{{ $owner->email ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Jumlah Hewan Peliharaan</label>
                        <input type="text" class="form-control fw-semibold" value="{{ $owner->pets->count() }} Hewan Terdaftar" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Alamat Lengkap</label>
                        <textarea class="form-control" rows="2" readonly>{{ $owner->alamat }}</textarea>
                    </div>
                </div>
            </div>

            <!-- DAFTAR HEWAN -->
            <div class="kreloses-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="color: #2d8aba;">
                        <i class="bi bi-heart-pulse-fill me-2"></i> Daftar Hewan Peliharaan Terdaftar
                    </h5>
                    <span class="text-muted fw-semibold">{{ $owner->pets->count() }} Pasien Terdaftar</span>
                </div>

                @if($owner->pets->count())
                    <div class="table-responsive">
                        <table class="kreloses-table">
                            <thead>
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="30%">Nama Hewan</th>
                                    <th width="20%">Jenis / Spesies</th>
                                    <th width="25%">Ras / Trah</th>
                                    <th width="20%">Berat Hewan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($owner->pets as $pet)
                                    <tr>
                                        <td><strong>{{ $loop->iteration }}</strong></td>
                                        <td class="fw-semibold text-dark">
                                            🐾 {{ $pet->nama }}
                                        </td>
                                        <td class="fw-medium text-dark">
                                            {{ $pet->jenis_hewan }}
                                        </td>
                                        <td style="color: #546e7a;">{{ $pet->ras_hewan ?? '-' }}</td>
                                        <td class="fw-medium text-dark">{{ $pet->berat_hewan ?? '-' }} Kg</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4" style="color: #546e7a;">
                        <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                        <span>Owner ini belum memiliki data hewan peliharaan terdaftar.</span>
                    </div>
                @endif
            </div>
        </section>

        @include('components.footer.footer_admin')
    </div>
</div>
@endsection