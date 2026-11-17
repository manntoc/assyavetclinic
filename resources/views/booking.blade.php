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

        <!-- KRELOSES TOP HEADER BAR -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-1">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle-btn me-2" title="Buka/Tutup Sidebar">
                    <i class="bi bi-layout-sidebar-inset fs-5"></i>
                </button>
                <div>
                    <h2 class="kreloses-header-title mb-0" style="color: #2d8aba;">Tambah Booking & Janji Temu Pasien</h2>
                    <small style="color: #546e7a;">Formulir pendaftaran kunjungan klinik, jadwal appointment, dan penugasan medis</small>
                </div>
            </div>
            <div>
                <a href="{{ route('owner') }}" class="btn btn-outline-secondary btn-sm px-3 rounded-2 fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Owner
                </a>
            </div>
        </div>

        <section class="section mb-4">
            <form action="{{ route('storebook') }}" method="POST">
                @csrf

                <input type="hidden" name="owner_id" value="{{ $owner->id }}">
                <input type="hidden" name="owner_pet_id" value="{{ $pet->id }}">

                <!-- CARD 1: RINGKASAN PROFIL CUSTOMER & PASIEN -->
                <div class="kreloses-card mb-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <span class="badge rounded-circle p-2 me-2" style="background-color: #eef7fc; color: #2d8aba;">
                                <i class="bi bi-person-vcard fs-5"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" style="font-size: 16px;">Ringkasan Data Customer & Pasien</h5>
                                <small class="text-muted" style="font-size: 12px;">Informasi pemilik dan hewan yang akan diperiksa</small>
                            </div>
                        </div>
                        <span class="badge bg-light text-secondary border fw-medium px-3 py-2" style="font-size: 12px;">
                            ID Owner: #{{ $owner->id }} | ID Pasien: #{{ $pet->id }}
                        </span>
                    </div>

                    <div class="row g-3">
                        <!-- INFORMASI OWNER -->
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 h-100" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-white p-2 border me-2 text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="bi bi-person-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 14px;">{{ $owner->nama }}</div>
                                        <small class="text-muted" style="font-size: 12px;">Customer / Pemilik Hewan</small>
                                    </div>
                                </div>
                                <div class="row g-2 small text-secondary">
                                    <div class="col-6">
                                        <i class="bi bi-telephone-fill me-1 text-muted"></i> <strong>Telepon:</strong><br>
                                        <span class="text-dark">{{ $owner->telepon }}</span>
                                    </div>
                                    <div class="col-6">
                                        <i class="bi bi-envelope-fill me-1 text-muted"></i> <strong>Email:</strong><br>
                                        <span class="text-dark">{{ $owner->email ?? '-' }}</span>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <i class="bi bi-geo-alt-fill me-1 text-muted"></i> <strong>Alamat:</strong><br>
                                        <span class="text-dark">{{ $owner->alamat }}</span>
                                    </div>
                                </div>
                                <input type="hidden" name="nama" value="{{ $owner->nama }}">
                                <input type="hidden" name="telpon" value="{{ $owner->telepon }}">
                                <input type="hidden" name="alamat" value="{{ $owner->alamat }}">
                            </div>
                        </div>

                        <!-- INFORMASI PASIEN / HEWAN -->
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 h-100" style="background-color: #eef7fc; border: 1px solid #bae6fd;">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-white p-2 border me-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; color: #2d8aba;">
                                        <i class="bi bi-heart-pulse-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 15px; color: #2d8aba;">🐾 {{ $pet->nama }}</div>
                                        <small class="text-muted" style="font-size: 12px;">Pasien Klinik</small>
                                    </div>
                                </div>
                                <div class="row g-2 small text-secondary">
                                    <div class="col-6">
                                        <i class="bi bi-tag-fill me-1 text-muted"></i> <strong>Spesies/Jenis:</strong><br>
                                        <span class="text-dark fw-medium">{{ $pet->jenis_hewan }}</span>
                                    </div>
                                    <div class="col-6">
                                        <i class="bi bi-bookmarks-fill me-1 text-muted"></i> <strong>Ras Hewan:</strong><br>
                                        <span class="text-dark fw-medium">{{ $pet->ras_hewan ?? '-' }}</span>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <i class="bi bi-speedometer2 me-1 text-muted"></i> <strong>Berat Badan:</strong><br>
                                        <span class="text-dark fw-bold">{{ $pet->berat_hewan }} Kg</span>
                                    </div>
                                </div>
                                <input type="hidden" name="namahewan" value="{{ $pet->nama }}">
                                <input type="hidden" name="jenishewan" value="{{ $pet->jenis_hewan }}">
                                <input type="hidden" name="berathewan" value="{{ $pet->berat_hewan }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: INFORMASI APPOINTMENT & JADWAL -->
                <div class="kreloses-card mb-4">
                    <div class="d-flex align-items-center pb-3 mb-3 border-bottom">
                        <span class="badge rounded-circle p-2 me-2" style="background-color: #eef7fc; color: #2d8aba;">
                            <i class="bi bi-calendar-event fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark" style="font-size: 16px;">Detail Jadwal & Saluran Appointment</h5>
                            <small class="text-muted" style="font-size: 12px;">Tentukan waktu kunjungan, layanan medis, dan dokter piket</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label small fw-bold text-dark">Tipe Kedatangan / Saluran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-signpost-split text-primary"></i></span>
                                <select name="tipe_kedatangan" class="form-select fw-medium" required>
                                    <option value="Walk-in" selected>Walk-in (Langsung Datang)</option>
                                    <option value="Janji Temu">Janji Temu (Booking Online)</option>
                                    <option value="Booking Telepon">Booking via Telepon / WA</option>
                                    <option value="Home Visit">Home Visit (Kunjungan Rumah)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <label class="form-label small fw-bold text-dark">Tanggal Appointment <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-calendar3 text-primary"></i></span>
                                <input type="date" name="tanggal" class="form-control fw-medium" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <label class="form-label small fw-bold text-dark">Waktu / Jam Appointment <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-clock text-primary"></i></span>
                                <input type="time" name="jam" class="form-control fw-medium" value="{{ date('H:i') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <label class="form-label small fw-bold text-dark">Layanan Medis Utama <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-file-earmark-medical text-primary"></i></span>
                                <select name="jasa_dokter" class="form-select fw-medium choices" required>
                                    <option value="Jasa Dokter Umum" selected>Jasa Dokter Konsultasi Umum</option>
                                    @if(isset($layananDokter))
                                        @foreach($layananDokter as $l)
                                            <option value="{{ $l->nama_layanan }}">{{ $l->nama_layanan }} (Rp {{ number_format($l->harga ?? 50000, 0, ',', '.') }})</option>
                                        @endforeach
                                    @endif
                                    <option value="Pemeriksaan Vaksinasi">Pemeriksaan Vaksinasi</option>
                                    <option value="Layanan Grooming Medis">Layanan Grooming Medis</option>
                                    <option value="Tindakan Bedah Operasi">Tindakan Bedah Operasi</option>
                                    <option value="Rawat Inap Observasi">Rawat Inap Observasi</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <label class="form-label small fw-bold text-dark">Dokter Penanggung Jawab</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-person-badge text-primary"></i></span>
                                <select name="karyawan1" class="form-select fw-medium choices">
                                    <option value="Belum Ditentukan" selected>Pilih Dokter / Staf Piket</option>
                                    @if(isset($karyawan))
                                        @foreach($karyawan as $k)
                                            <option value="{{ $k->name }}">{{ $k->name }} ({{ ucfirst($k->role) }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <label class="form-label small fw-bold text-dark">Tahap Antrean Awal</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-flag text-primary"></i></span>
                                <select name="status" class="form-select fw-medium">
                                    <option value="belum dikerjakan" selected>1. Kedatangan (Antrean Masuk)</option>
                                    <option value="Memulai">2. Memulai (Pemeriksaan Dokter)</option>
                                    <option value="Rawat Inap">3. Rawat Inap (Ruang Ward)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: TANDA VITAL PASIEN (TRIAGE AWAL - OPSIONAL) -->
                <div class="kreloses-card mb-4">
                    <div class="d-flex align-items-center pb-3 mb-3 border-bottom">
                        <span class="badge rounded-circle p-2 me-2" style="background-color: #eef7fc; color: #2d8aba;">
                            <i class="bi bi-activity fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark" style="font-size: 16px;">Tanda Vital Pasien (Triage Awal - Opsional)</h5>
                            <small class="text-muted" style="font-size: 12px;">Pemeriksaan suhu, denyut jantung, dan capillary refill time</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Suhu Tubuh (°C)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-thermometer-half text-danger"></i></span>
                                <input type="number" step="0.1" name="suhu_tubuh" class="form-control" placeholder="38.5">
                                <span class="input-group-text bg-light">°C</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Denyut Jantung (BPM)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-heart-pulse text-danger"></i></span>
                                <input type="number" name="denyut_jantung" class="form-control" placeholder="120">
                                <span class="input-group-text bg-light">BPM</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Capillary Refill Time (CRT)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-stopwatch text-warning"></i></span>
                                <input type="text" name="crt" class="form-control" placeholder="&lt; 2 Detik">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 4: ANAMNESIS & KELUHAN PASIEN -->
                <div class="kreloses-card mb-4">
                    <div class="d-flex align-items-center pb-3 mb-3 border-bottom">
                        <span class="badge rounded-circle p-2 me-2" style="background-color: #eef7fc; color: #2d8aba;">
                            <i class="bi bi-chat-left-text fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark" style="font-size: 16px;">Anamnesis & Keluhan Utama Pasien</h5>
                            <small class="text-muted" style="font-size: 12px;">Catat keluhan utama, gejala klinis, atau catatan medis awal</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark">Catatan Keluhan / Alasan Kunjungan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white align-items-start pt-2"><i class="bi bi-pencil-square text-primary"></i></span>
                            <textarea name="keluhan" rows="3" class="form-control" placeholder="Tulis keluhan atau alasan kunjungan..." required></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                        <a href="{{ route('owner') }}" class="btn btn-light border px-4 rounded-2 fw-semibold">
                            Batal
                        </a>
                        <button type="submit" class="btn-kreloses-blue px-4 py-2 rounded-2 shadow-sm">
                            <i class="bi bi-check-circle-fill me-1 fs-6"></i> Simpan Appointment & Masukkan Antrean
                        </button>
                    </div>
                </div>
            </form>
        </section>

        @include('components.footer.footer_admin')
    </div>
</div>
@endsection

