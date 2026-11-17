@foreach ($booking as $item)
    @php
        $currentStatus = strtolower($item->status ?? 'kedatangan');
        $isKedatangan = ($currentStatus == 'kedatangan' || $currentStatus == 'belum dikerjakan');
        $isMemulai    = ($currentStatus == 'memulai');
        $isRawatInap  = ($currentStatus == 'rawat inap' || $currentStatus == 'rawat_inap');
        $isApotek     = ($currentStatus == 'apotek');
        $isPembayaran = ($currentStatus == 'pembayaran');
        $isSelesai    = (strpos($currentStatus, 'selesai') !== false || $currentStatus == 'telah diselesaikan');
    @endphp

    <div class="modal fade text-left" id="pengerjaan-{{ $item->id }}" tabindex="-1" role="dialog"
        aria-labelledby="modalLabel-{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header text-white py-3 px-4" style="background-color: #2d8aba; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <div>
                        <span class="badge bg-white px-3 py-1 rounded-pill fw-bold text-uppercase mb-1" style="color: #2d8aba;">
                            <i class="bi bi-tag-fill me-1"></i> {{ $item->kode_booking ?? ('BOOK-' . $item->id) }}
                        </span>
                        <h5 class="modal-title text-white fw-bold" id="modalLabel-{{ $item->id }}">
                            @if($isKedatangan)
                                Menu Kedatangan & Registrasi Pasien
                            @elseif($isMemulai)
                                Menu Examination Dokter & Rincian Tindakan Medis
                            @elseif($isRawatInap)
                                Menu Observasi & Monitoring Rawat Inap
                            @elseif($isApotek)
                                Verifikasi Resep & Pengambilan Obat Apotek
                            @elseif($isPembayaran)
                                Kasir POS & Billing Pembayaran
                            @else
                                Ringkasan Rekam Medis & Invoice Pasien
                            @endif
                        </h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('updatestatusbooking', ['id' => $item->id]) }}" method="POST">
                        @csrf
                        @method('POST')

                        <!-- 📋 SYNC INPUT INFORMASI DASAR -->
                        <input type="hidden" name="nama" value="{{ $item->nama }}">
                        <input type="hidden" name="namahewan" value="{{ $item->namahewan ?: ($item->nama_hewan ?: 'Hewan') }}">
                        <input type="hidden" name="berat" value="{{ $item->berathewan ?: ($item->berat_hewan ?: ($item->berat ?: 1)) }}">
                        <input type="hidden" name="jenis" value="{{ $item->jenishewan ?: ($item->jenis_hewan ?: 'Hewan') }}">
                        <input type="hidden" name="tanggal" value="{{ $item->tanggal }}">

                        <!-- 1. DESKRIPSI DATA HEWAN & PEMILIK PASIEN (TAMPIL DI SEMUA STEP) -->
                        <div class="card border-0 bg-light shadow-sm mb-4" style="border-radius: 14px;">
                            <div class="card-body p-3">
                                <div class="row align-items-center g-3">
                                    <div class="col-md-6 border-end-md">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center justify-content-center bg-white text-primary shadow-sm me-3" style="width:48px;height:48px;border-radius:50%;flex-shrink:0;">
                                                <i class="bi bi-heart-pulse-fill fs-3"></i>
                                            </div>
                                            <div>
                                                <div class="small text-muted fw-semibold mb-1">
                                                    🐾 Spesies: <strong class="text-dark">{{ $item->jenishewan ?: ($item->jenis_hewan ?: 'Hewan') }}</strong>
                                                </div>
                                                <h4 class="fw-bold mb-0 text-dark">
                                                    {{ $item->namahewan ?: ($item->nama_hewan ?: 'Nama Hewan') }}
                                                </h4>
                                                <small class="text-muted">
                                                    Berat: <strong>{{ $item->berathewan ?: ($item->berat_hewan ?: ($item->berat ?: '-')) }} kg</strong>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ps-md-2">
                                            <div class="mb-1">
                                                <span class="text-muted small">Pemilik (Owner):</span>
                                                <strong class="text-dark ms-1">{{ $item->nama }}</strong>
                                            </div>
                                            <div class="mb-1">
                                                <span class="text-muted small">No. Telepon / WA:</span>
                                                <a href="tel:{{ $item->telpon }}" class="text-decoration-none fw-semibold ms-1">
                                                    <i class="bi bi-telephone-fill me-1"></i>{{ $item->telpon }}
                                                </a>
                                            </div>
                                            <div class="mb-0">
                                                <span class="text-muted small">Tipe Kedatangan:</span>
                                                <strong class="text-dark ms-1">{{ ucfirst($item->tipe_kedatangan ?? 'Langsung Datang') }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="p-2 px-3 bg-white rounded-3 border">
                                            <small class="text-muted fw-bold d-block mb-1">
                                                <i class="bi bi-chat-left-dots-fill text-warning me-1"></i> Keluhan Medis Awal:
                                            </small>
                                            <span class="text-dark fw-medium small">
                                                {{ $item->keluhan ?? 'Tidak ada keluhan tertulis' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. TANDA VITAL & RECORD SOAP EMR (TAMPIL DI: KEDATANGAN, MEMULAI, RAWAT INAP, SELESAI) -->
                        @if($isKedatangan || $isMemulai || $isRawatInap || $isSelesai)
                        <div class="card border mb-4 shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white border-bottom py-3">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="bi bi-activity text-danger me-2"></i> Tanda Vital & Rekam Medis (EMR / SOAP)
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Suhu Tubuh (°C)</label>
                                        <input type="number" step="0.1" class="form-control" name="suhu_tubuh" value="{{ $item->suhu_tubuh }}" placeholder="38.5">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Denyut Jantung (BPM)</label>
                                        <input type="number" class="form-control" name="denyut_jantung" value="{{ $item->denyut_jantung }}" placeholder="110">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">CRT (Capillary Refill Time)</label>
                                        <input type="text" class="form-control" name="crt" value="{{ $item->crt }}" placeholder="< 2 detik">
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-primary">S - Subjective (Keluhan Utama)</label>
                                        <textarea class="form-control" name="soap_s" rows="2" placeholder="Keluhan utama">{{ $item->soap_s }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-primary">O - Objective (Hasil Pemeriksaan Fisik)</label>
                                        <textarea class="form-control" name="soap_o" rows="2" placeholder="Pemeriksaan fisik">{{ $item->soap_o }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-primary">A - Assessment (Diagnosa Dokter)</label>
                                        <textarea class="form-control" name="soap_a" rows="2" placeholder="Diagnosa dokter">{{ $item->soap_a }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-primary">P - Plan (Rencana Terapi / Edukasi)</label>
                                        <textarea class="form-control" name="soap_p" rows="2" placeholder="Rencana terapi">{{ $item->soap_p }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- 3. JASA DOKTER & RINCIAN TINDAKAN MEDIS (TAMPIL DI: KEDATANGAN, MEMULAI, RAWAT INAP, SELESAI) -->
                        @if($isKedatangan || $isMemulai || $isRawatInap || $isSelesai)
                        <div class="card mb-4 border shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white border-bottom py-3">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="bi bi-stethoscope text-success me-2"></i> Jasa Dokter & Penugasan Staf
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Dokter Penanggung Jawab / Staf</label>
                                    <select class="form-select" name="karyawan1">
                                        <option value="">Pilih Dokter / Staf</option>
                                    </select>
                                </div>

                                <!-- LAYANAN JASA DOKTER -->
                                <div class="card mb-3 border shadow-sm" style="border-radius: 10px;">
                                    <div class="card-header bg-success text-white py-2 px-3">
                                        <strong class="small"><i class="bi bi-stethoscope me-1"></i> Jasa Dokter Konsultasi</strong>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <select class="form-select form-select-sm choices" id="jasaDokterSelectUpdate-{{ $item->id }}" name="jasa_dokter">
                                                <option value="Tidak menggunakan layanan ini">Tidak Menggunakan Jasa Dokter</option>
                                                @foreach ($jasa_dokter as $jasaDokterItem)
                                                <option value="{{ $jasaDokterItem->nama }}" data-harga="{{ $jasaDokterItem->harga }}" selected>{{ $jasaDokterItem->nama }} (Rp {{ number_format($jasaDokterItem->harga, 0, ',', '.') }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="jumlahJasaDokterUpdate-{{ $item->id }}" name="jasa_dokter_kuantitas" placeholder="Qty" value="1">
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="hargaPerJasaDokterUpdate-{{ $item->id }}" placeholder="Harga Satuan" readonly value="50000">
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="totalBiayaJasaDokterUpdate-{{ $item->id }}" name="jasa_dokter_harga" placeholder="Subtotal" readonly value="50000">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- RINCIAN TINDAKAN MEDIS LENGKAP (TAMPIL DI: MEMULAI, RAWAT INAP, SELESAI) -->
                                @if($isMemulai || $isRawatInap || $isSelesai)
                                <h6 class="fw-bold mb-3 text-dark mt-4">
                                    <i class="bi bi-ui-checks me-2 text-primary"></i> Rincian Tindakan Medis & Service Tambahan
                                </h6>

                                <!-- LAYANAN VAKSIN -->
                                <div class="card mb-3 border shadow-sm" style="border-radius: 10px;">
                                    <div class="card-header bg-primary text-white py-2 px-3">
                                        <strong class="small"><i class="bi bi-shield-plus me-1"></i> Layanan Vaksinasi</strong>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <select class="form-select form-select-sm choices" id="vaksinSelectUpdate-{{ $item->id }}" name="vaksin">
                                                <option selected value="Tidak menggunakan layanan ini">Tidak Menggunakan Layanan Vaksin</option>
                                                @foreach ($vaksin as $vaksinItem)
                                                <option value="{{ $vaksinItem->nama }}" data-price="{{ $vaksinItem->harga }}">{{ $vaksinItem->nama }} (Rp {{ number_format($vaksinItem->harga, 0, ',', '.') }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="jumlahVaksinUpdate-{{ $item->id }}" placeholder="Qty" name="vaksin_kuantitas">
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="hargaPerItemUpdate-{{ $item->id }}" placeholder="Harga Satuan" readonly>
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="totalBiayaUpdate-{{ $item->id }}" name="vaksin_harga" placeholder="Subtotal" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- LAYANAN GROOMING -->
                                <div class="card mb-3 border shadow-sm" style="border-radius: 10px;">
                                    <div class="card-header bg-secondary text-white py-2 px-3">
                                        <strong class="small"><i class="bi bi-scissors me-1"></i> Layanan Grooming & Perawatan</strong>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <select class="form-select form-select-sm choices" id="groomingSelectUpdate-{{ $item->id }}" name="grooming">
                                                <option selected value="Tidak menggunakan layanan ini">Tidak Menggunakan Layanan Grooming</option>
                                                @foreach ($grooming as $groomingItem)
                                                <option value="{{ $groomingItem->nama }}" data-harga="{{ $groomingItem->harga }}">{{ $groomingItem->nama }} (Rp {{ number_format($groomingItem->harga, 0, ',', '.') }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="jumlahGroomingUpdate-{{ $item->id }}" name="grooming_kuantitas" placeholder="Qty">
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="hargaPerGroomingUpdate-{{ $item->id }}" placeholder="Harga Satuan" readonly>
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="totalBiayaGroomingUpdate-{{ $item->id }}" name="grooming_harga" placeholder="Subtotal" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- LAYANAN OPERASI -->
                                <div class="card mb-3 border shadow-sm" style="border-radius: 10px;">
                                    <div class="card-header bg-warning text-dark py-2 px-3">
                                        <strong class="small"><i class="bi bi-bandaid me-1"></i> Tindakan Operasi & Bedah</strong>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <select class="form-select form-select-sm choices" id="operasiSelectUpdate-{{ $item->id }}" name="operasi">
                                                <option selected value="Tidak menggunakan layanan ini">Tidak Menggunakan Layanan Operasi</option>
                                                @foreach ($operasi as $operasiItem)
                                                <option value="{{ $operasiItem->nama }}" data-harga="{{ $operasiItem->harga }}">{{ $operasiItem->nama }} (Rp {{ number_format($operasiItem->harga, 0, ',', '.') }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="jumlahOperasiUpdate-{{ $item->id }}" name="operasi_kuantitas" placeholder="Qty">
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="hargaPerOperasiUpdate-{{ $item->id }}" placeholder="Harga Satuan" readonly>
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="totalBiayaOperasiUpdate-{{ $item->id }}" name="operasi_harga" placeholder="Subtotal" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- LAYANAN DIAGNOSA PENUNJANG -->
                                <div class="card mb-3 border shadow-sm" style="border-radius: 10px;">
                                    <div class="card-header bg-danger text-white py-2 px-3">
                                        <strong class="small"><i class="bi bi-file-medical me-1"></i> Diagnosa Penunjang (Lab / USG / Rontgen)</strong>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <select class="form-select form-select-sm choices" id="diagnosaPenunjangSelectUpdate-{{ $item->id }}" name="diagnosa_penunjang">
                                                <option selected value="Tidak menggunakan layanan ini">Tidak Menggunakan Diagnosa Penunjang</option>
                                                @foreach ($diagnosa as $diagnosaItem)
                                                <option value="{{ $diagnosaItem->nama }}" data-harga="{{ $diagnosaItem->harga }}">{{ $diagnosaItem->nama }} (Rp {{ number_format($diagnosaItem->harga, 0, ',', '.') }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="jumlahDiagnosaPenunjangUpdate-{{ $item->id }}" name="diagnosa_penunjang_kuantitas" placeholder="Qty">
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="hargaPerDiagnosaPenunjangUpdate-{{ $item->id }}" placeholder="Harga Satuan" readonly>
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="totalBiayaDiagnosaPenunjangUpdate-{{ $item->id }}" name="diagnosa_penunjang_harga" placeholder="Subtotal" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- LAYANAN TRANSPORTASI -->
                                <div class="card mb-3 border shadow-sm" style="border-radius: 10px;">
                                    <div class="card-header bg-info text-white py-2 px-3">
                                        <strong class="small"><i class="bi bi-truck me-1"></i> Layanan Transportasi (Antar / Jemput)</strong>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <select class="form-select form-select-sm" id="transportasiSelectUpdate-{{ $item->id }}" name="transportasi">
                                                <option selected value="Tidak menggunakan layanan ini">Tidak Menggunakan Transportasi</option>
                                                <option value="transportasi jemput">Transportasi Jemput</option>
                                                <option value="transportasi antar">Transportasi Antar</option>
                                                <option value="transportasi antar jemput">Transportasi Antar Jemput</option>
                                            </select>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="jumlahTransportasiUpdate-{{ $item->id }}" name="transportasi_kuantitas" placeholder="Qty" value="1">
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="hargaPerTransportasiUpdate-{{ $item->id }}" name="transport_harga" placeholder="Tarif Per Trip" value="0">
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="totalBiayaTransportasiUpdate-{{ $item->id }}" name="total_transport" placeholder="Subtotal" readonly value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- 4. MONITORING & LOG OBSERVASI HARIAN RAWAT INAP (TAMPIL DI: RAWAT INAP, SELESAI) -->
                        @if($isRawatInap || $isSelesai)
                        <div class="card mb-4 border shadow-sm" style="border-radius: 12px; border-color: #cbd5e1 !important; background-color: #f8fafc;">
                            <div class="card-header text-white py-2 px-3 fw-bold d-flex justify-content-between align-items-center" style="background-color: #3b82f6; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                                <span style="font-size: 1.00rem;"><i class="bi bi-hospital me-2"></i> Monitoring & Log Observasi Harian Rawat Inap (Ward Daily Progress)</span>
                                <span class="small opacity-75"><i class="bi bi-clock-history me-1"></i>Pencatatan Harian</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-dark" style="font-size: 0.875rem;">Nomor Kandang / Ruangan</label>
                                        <input type="text" class="form-control form-control-sm" style="font-size: 0.875rem;" name="no_kandang" value="{{ $item->no_kandang ?? '' }}" placeholder="Nomor kandang">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-dark" style="font-size: 0.875rem;">Nafsu Makan Pasien Saat Ini</label>
                                        <select class="form-select form-select-sm" style="font-size: 0.875rem;" name="nafsu_makan">
                                            <option value="Baik" {{ ($item->nafsu_makan ?? '') == 'Baik' ? 'selected' : '' }}>Baik (Lahap)</option>
                                            <option value="Menurun" {{ ($item->nafsu_makan ?? '') == 'Menurun' ? 'selected' : '' }}>Menurun</option>
                                            <option value="Tidak Mau Makan" {{ ($item->nafsu_makan ?? '') == 'Tidak Mau Makan' ? 'selected' : '' }}>Tidak Mau Makan (Perlu Infus)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-dark" style="font-size: 0.875rem;">Perkiraan Tanggal Pulang</label>
                                        <input type="date" class="form-control form-control-sm" style="font-size: 0.875rem;" name="tanggal_kontrol" value="{{ $item->tanggal_kontrol ? \Carbon\Carbon::parse($item->tanggal_kontrol)->format('Y-m-d') : '' }}">
                                    </div>
                                </div>

                                <!-- TABEL DAFTAR LOG OBSERVASI HARIAN TERCATAT -->
                                @php
                                    $dailyLogsList = \App\Models\PetDailyLog::where('booking_id', $item->id)->orderBy('tanggal_log', 'desc')->get();
                                @endphp

                                <label class="form-label fw-bold text-dark mb-2" style="font-size: 0.875rem;">
                                    <i class="bi bi-journal-check me-1 text-primary"></i> Riwayat Progress Observasi Harian Rawat Inap Pasien Ini:
                                </label>

                                @if($dailyLogsList->isEmpty())
                                    <div class="p-2 bg-white rounded border text-muted mb-3 text-center" style="font-size: 0.75rem;">
                                        Belum ada entri pengecekan harian tercatat. Silakan input pengecekan hari ini di bawah.
                                    </div>
                                @else
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-bordered bg-white mb-0" style="font-size: 0.875rem;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="22%">Tanggal & Waktu Checkup</th>
                                                    <th width="12%">Suhu (°C)</th>
                                                    <th width="12%">Pulse (bpm)</th>
                                                    <th width="12%">Berat (kg)</th>
                                                    <th width="14%">Nafsu Makan</th>
                                                    <th>Catatan Progress / Terapi Harian</th>
                                                    <th width="8%" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dailyLogsList as $dlog)
                                                    <tr>
                                                        <td><strong>{{ \Carbon\Carbon::parse($dlog->tanggal_log)->format('d/m/Y H:i') }}</strong></td>
                                                        <td>{{ $dlog->suhu_tubuh ? $dlog->suhu_tubuh . ' °C' : '-' }}</td>
                                                        <td>{{ $dlog->denyut_jantung ? $dlog->denyut_jantung . ' bpm' : '-' }}</td>
                                                        <td>{{ $dlog->berat_badan ? $dlog->berat_badan . ' kg' : '-' }}</td>
                                                        <td>
                                                            @if(($dlog->nafsu_makan ?? '') == 'Baik')
                                                                <span class="badge px-2 py-1" style="background-color:#e8f5e9;color:#1b5e20;border:1px solid #1b5e20;font-size:0.75rem;">Lahap</span>
                                                            @elseif(($dlog->nafsu_makan ?? '') == 'Menurun')
                                                                <span class="badge px-2 py-1" style="background-color:#fff8e1;color:#f57f17;border:1px solid #f57f17;font-size:0.75rem;">Menurun</span>
                                                            @elseif(($dlog->nafsu_makan ?? '') == 'Tidak Mau Makan')
                                                                <span class="badge px-2 py-1" style="background-color:#ffebee;color:#b71c1c;border:1px solid #b71c1c;font-size:0.75rem;">Perlu Infus</span>
                                                            @elseif($dlog->nafsu_makan)
                                                                <span class="badge px-2 py-1" style="background-color:#eceff1;color:#546e7a;border:1px solid #546e7a;font-size:0.75rem;">{{ $dlog->nafsu_makan }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $dlog->catatan_observasi ?? '-' }}</td>
                                                        <td class="text-center">
                                                            <a href="{{ route('delete-pet-log', ['id' => $dlog->id]) }}" class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size: 0.75rem;" title="Hapus Log" onclick="return confirm('Hapus log observasi harian ini?')">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                <!-- FORM INLINE TAMBAH CHECKUP HARIAN RAWAT INAP (TANPA BUAT BOOKING BARU) -->
                                <div class="p-3 bg-white rounded-3 border">
                                    <h6 class="fw-bold text-dark mb-2" style="font-size: 0.875rem;">
                                        <i class="bi bi-plus-circle-fill text-primary me-1"></i> Input Pengecekan Harian Hari Ini (Tanpa Buat Booking Baru)
                                    </h6>
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-3 col-6">
                                            <label class="form-label text-muted mb-1" style="font-size: 0.75rem;">Suhu Tubuh (°C)</label>
                                            <input type="text" class="form-control form-control-sm" style="font-size: 0.875rem;" id="logSuhu-{{ $item->id }}" placeholder="38.6" value="{{ $item->suhu_tubuh }}">
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <label class="form-label text-muted mb-1" style="font-size: 0.75rem;">Denyut Jantung (BPM)</label>
                                            <input type="text" class="form-control form-control-sm" style="font-size: 0.875rem;" id="logPulse-{{ $item->id }}" placeholder="110" value="{{ $item->denyut_jantung }}">
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <label class="form-label text-muted mb-1" style="font-size: 0.75rem;">Berat Badan (kg)</label>
                                            <input type="number" step="0.1" class="form-control form-control-sm" style="font-size: 0.875rem;" id="logBerat-{{ $item->id }}" placeholder="0.0" value="{{ $item->berathewan ?: ($item->berat_hewan ?: ($item->berat ?: '')) }}">
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <label class="form-label text-muted mb-1" style="font-size: 0.75rem;">Nafsu Makan</label>
                                            <select class="form-select form-select-sm" style="font-size: 0.875rem;" id="logNafsu-{{ $item->id }}">
                                                <option value="Baik">Baik (Lahap)</option>
                                                <option value="Menurun">Menurun</option>
                                                <option value="Tidak Mau Makan">Tidak Mau Makan (Infus)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-10">
                                            <label class="form-label text-muted mb-1" style="font-size: 0.75rem;">Catatan Progress Harian / Resep Terapi Ward</label>
                                            <input type="text" class="form-control form-control-sm" style="font-size: 0.875rem;" id="logCatatan-{{ $item->id }}" placeholder="Catatan perkembangan harian...">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-sm btn-primary fw-bold w-100 shadow-sm" style="font-size: 0.875rem;" id="btnSubmitDailyLog-{{ $item->id }}">
                                                <i class="bi bi-save me-1"></i> Simpan Log
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- 5. RESEP OBAT APOTEK & VERIFIKASI STOK (TAMPIL DI: MEMULAI, RAWAT INAP, APOTEK, SELESAI) -->
                        @if($isMemulai || $isRawatInap || $isApotek || $isSelesai)
                        <div class="card mb-4 border shadow-sm" style="border-radius: 12px; border-color: #cbd5e1 !important; background-color: #f8fafc;">
                            <div class="card-header text-white py-2 px-3 fw-bold d-flex justify-content-between align-items-center" style="background-color: #546e7a; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                                <span style="font-size: 1.00rem;"><i class="bi bi-capsule me-2"></i> Penyiapan Resep Obat & Apotek Pasien</span>
                                <span class="badge bg-light text-dark fw-bold" style="font-size: 0.75rem;">Modul Obat & Apotek</span>
                            </div>
                            <div class="card-body p-3">
                                @php
                                    $currentObat = \App\Models\Transaksi::where('booking_id', $item->id)->with('inventori')->get();
                                @endphp

                                <label class="form-label fw-bold text-dark mb-2" style="font-size: 0.875rem;">Daftar Obat Pasien Tercatat:</label>
                                @if($currentObat->isEmpty())
                                    <div class="p-2 bg-white rounded border text-muted mb-3 text-center" style="font-size: 0.75rem;">
                                        Belum ada obat / inventori yang ditambahkan untuk pasien ini.
                                    </div>
                                @else
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-bordered bg-white mb-0" style="font-size: 0.875rem;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nama Obat / Inventori</th>
                                                    <th class="text-center" width="20%">Jumlah</th>
                                                    <th class="text-end" width="30%">Total Harga</th>
                                                    <th class="text-center" width="15%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($currentObat as $ob)
                                                    <tr>
                                                        <td>{{ $ob->inventori->nama_barang ?? 'Obat' }}</td>
                                                        <td class="text-center">{{ $ob->jumlah }} Item</td>
                                                        <td class="text-end">Rp {{ number_format($ob->total, 0, ',', '.') }}</td>
                                                        <td class="text-center">
                                                            <a href="{{ route('delete-item', ['id' => $ob->id]) }}" class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size: 0.75rem;" title="Hapus Obat" onclick="return confirm('Hapus obat ini dari transaksi pasien?')">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                <!-- Form Inline Tambah Obat Pasien (Flow Standar Service - Secondary Theme) -->
                                <div class="p-3 bg-white rounded-3 border">
                                    <h6 class="fw-bold text-dark mb-2" style="font-size: 0.875rem;"><i class="bi bi-plus-circle-fill me-1" style="color: #546e7a;"></i> Tambah Obat / Resep Pasien</h6>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-5">
                                            <label class="form-label text-muted mb-1" style="font-size: 0.75rem;">Pilih Obat / Inventori <span class="text-danger">*</span></label>
                                            <select class="form-select form-select-sm choices" style="font-size: 0.875rem;" id="modalObatSelect-{{ $item->id }}" required>
                                                <option value="">Pilih Obat Inventori</option>
                                                @if(isset($inventori))
                                                    @foreach ($inventori as $data)
                                                        <option value="{{ $data->id }}" data-price="{{ $data->harga }}" data-stok="{{ $data->jumlah }}">{{ $data->nama_barang }} (Stok: {{ $data->jumlah }})</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-6">
                                            <label class="form-label text-muted mb-1" style="font-size: 0.75rem;">Jumlah <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control form-control-sm" style="font-size: 0.875rem;" id="modalObatQty-{{ $item->id }}" placeholder="Qty" min="1" value="1" required>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <label class="form-label text-muted mb-1" style="font-size: 0.75rem;">Total Biaya (Rp) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control form-control-sm bg-light" style="font-size: 0.875rem;" id="modalObatTotal-{{ $item->id }}" readonly required placeholder="Rp 0">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-sm text-white fw-bold w-100 shadow-sm" style="background-color: #546e7a; border-color: #546e7a; font-size: 0.875rem;" id="btnSubmitObat-{{ $item->id }}">
                                                <i class="bi bi-cart-plus me-1"></i> + Obat
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1" id="modalObatStok-{{ $item->id }}" style="font-size: 0.75rem;">Stok Tersedia: -</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- 6. BILLING POS CHECKOUT SECTION (TAMPIL DI: PEMBAYARAN, SELESAI) -->
                        @if($isPembayaran || $isSelesai)
                        <div class="card border-0 bg-white p-3 shadow-sm mb-3" style="border-radius: 12px; border-left: 4px solid #10b981 !important;">
                            <h6 class="fw-bold mb-3 text-dark">
                                <i class="bi bi-receipt text-success me-2"></i> Ringkasan Pembayaran & Billing POS
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Uang Muka / Deposit (DP)</label>
                                    <input type="number" class="form-control" name="dp" value="{{ $item->dp ?? 0 }}" placeholder="Nominal DP">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Potongan Diskon (Rp)</label>
                                    <input type="number" class="form-control" name="diskon" value="{{ $item->diskon ?? 0 }}" placeholder="Potongan Diskon">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Pajak / PPN (Rp)</label>
                                    <input type="number" class="form-control" name="pajak" value="{{ $item->pajak ?? 0 }}" placeholder="Nominal Pajak">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Metode Pembayaran</label>
                                    <select class="form-select" name="metode_pembayaran">
                                        <option value="Tunai" {{ ($item->metode_pembayaran ?? '') == 'Tunai' ? 'selected' : '' }}>Tunai / Cash</option>
                                        <option value="Transfer Bank / QRIS" {{ ($item->metode_pembayaran ?? '') == 'Transfer Bank / QRIS' ? 'selected' : '' }}>Transfer Bank / QRIS</option>
                                        <option value="Kartu Debit/Kredit" {{ ($item->metode_pembayaran ?? '') == 'Kartu Debit/Kredit' ? 'selected' : '' }}>Kartu Debit/Kredit</option>
                                        <option value="Piutang" {{ ($item->metode_pembayaran ?? '') == 'Piutang' ? 'selected' : '' }}>Bayar Nanti / Piutang</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- 7. PILIHAN TAHAP PENGERJAAN & SUBMIT BUTTON (TAMPIL DI SEMUA STEP) -->
                        <div class="card border-0 bg-light p-3 shadow-sm mb-3" style="border-radius: 12px;">
                            <label class="form-label small fw-bold text-dark">Pilih Tahap Selanjutnya / Perbarui Status</label>
                            <select class="form-select fw-bold text-primary" name="status">
                                <option value="Telah diselesaikan" {{ ($item->status == 'Telah diselesaikan' || $item->status == 'Selesai') ? 'selected' : '' }}>🟢 Telah Diselesaikan (Selesai)</option>
                                <option value="Memulai" {{ $item->status == 'Memulai' ? 'selected' : '' }}>🔵 Memulai (Pemeriksaan Medis)</option>
                                <option value="Apotek" {{ $item->status == 'Apotek' ? 'selected' : '' }}>🟠 Apotek (Penyiapan Obat)</option>
                                <option value="Pembayaran" {{ $item->status == 'Pembayaran' ? 'selected' : '' }}>🟣 Pembayaran (Kasir / POS)</option>
                                <option value="Rawat Inap" {{ $item->status == 'Rawat Inap' ? 'selected' : '' }}>🟤 Rawat Inap</option>
                                <option value="belum dikerjakan" {{ ($item->status == 'belum dikerjakan' || $item->status == 'Kedatangan') ? 'selected' : '' }}>🔴 Belum Dikerjakan (Kedatangan)</option>
                            </select>
                        </div>
                </div>
                <div class="modal-footer bg-light py-3 px-4" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <a href="{{ route('estimasi-biaya.create', ['booking_id' => $item->id]) }}" class="btn btn-outline-secondary px-3" title="Buat Perkiraan Biaya Perawatan Pasien Ini" target="_blank">
                        <i class="bi bi-calculator me-1"></i> Buat Nota Estimasi
                    </a>
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal / Tutup</button>
                    <button type="submit" class="btn-kreloses-blue px-4 font-weight-bold shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Perubahan Data
                    </button>
                </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- extension -->
@include('components.extensions.obat')
@include('components.extensions.daily_log')
<!-- end extension -->