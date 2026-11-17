@if($items->isEmpty())
    <div class="text-center py-5" style="color: #546e7a;">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        <span class="small fw-medium">Tidak ada pasien di tahap ini.</span>
    </div>
@else
    <div class="table-responsive">
        <table class="kreloses-table">
            <thead>
                <tr>
                    @if($stage == 'kedatangan')
                        <!-- STAGE KEDATANGAN: DATA CUSTOMER, JASA DOKTER, STATUS TAHAP -->
                        <th width="40%">Data Customer & Pasien</th>
                        <th width="30%">Jasa Dokter / Layanan</th>
                        <th width="30%" class="text-end">Status Tahap Pengerjaan</th>

                    @elseif($stage == 'memulai')
                        <!-- STAGE MEMULAI: DATA CUSTOMER, REKAM MEDIS/STAF, STATUS TAHAP -->
                        <th width="35%">Data Customer & Pasien</th>
                        <th width="35%">Dokter & Layanan Medis</th>
                        <th width="30%" class="text-end">Status Tahap Pengerjaan</th>

                    @elseif($stage == 'rawat_inap')
                        <!-- STAGE RAWAT INAP: DATA CUSTOMER, OBSERVASI/RUANG, STATUS TAHAP -->
                        <th width="35%">Data Customer & Pasien</th>
                        <th width="35%">Ruang Rawat & Observasi Inap</th>
                        <th width="30%" class="text-end">Status Tahap Pengerjaan</th>

                    @elseif($stage == 'apotek')
                        <!-- STAGE APOTEK: DATA CUSTOMER, RESEP OBAT, STATUS TAHAP -->
                        <th width="35%">Data Customer & Pasien</th>
                        <th width="35%">Resep Obat & Inventori</th>
                        <th width="30%" class="text-end">Status Tahap Pengerjaan</th>

                    @elseif($stage == 'pembayaran')
                        <!-- STAGE PEMBAYARAN: DATA CUSTOMER, BILLING POS, STATUS TAHAP -->
                        <th width="35%">Data Customer & Pasien</th>
                        <th width="35%">Billing & Tagihan Kasir</th>
                        <th width="30%" class="text-end">Status Tahap Pengerjaan</th>

                    @else
                        <!-- STAGE SELESAI -->
                        <th width="40%">Data Customer & Pasien</th>
                        <th width="30%">Status Akhir</th>
                        <th width="30%" class="text-end">Cetak Invoice</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php
                    $todayDateStr = \Carbon\Carbon::today()->toDateString();
                @endphp
                @foreach ($items as $item)
                    @php
                        $itemDateStr = $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') : '';
                        $isRowVisible = ($stage == 'rawat_inap' || $itemDateStr == $todayDateStr);
                    @endphp
                    <tr class="queue-row"
                        style="{{ !$isRowVisible ? 'display: none;' : '' }}"
                        data-date="{{ $itemDateStr }}"
                        data-type="{{ strtolower($item->tipe_kedatangan ?? 'langsung datang') }}"
                        data-staff="{{ strtolower(($item->karyawan1 ?? '') . ' ' . ($item->karyawan2 ?? '') . ' ' . ($item->karyawan3 ?? '')) }}"
                        data-search="{{ strtolower(($item->nama ?? '') . ' ' . ($item->namahewan ?: ($item->nama_hewan ?: '')) . ' ' . ($item->telpon ?? '') . ' ' . ($item->jenishewan ?: ($item->jenis_hewan ?: '')) . ' ' . ($item->karyawan1 ?? '') . ' ' . ($item->kode_booking ?? '')) }}">

                        <!-- 1. DATA CUSTOMER & PASIEN (NAMA OWNER, NAMA HEWAN, SPESIES, TGL) -->
                        <td>
                            <div>
                                <a href="#" class="kreloses-owner-link fw-bold" style="color: #2d8aba;" data-bs-toggle="modal" data-bs-target="#pengerjaan-{{ $item->id }}" title="Klik nama customer untuk detail">
                                    👤 {{ $item->nama }}
                                </a>
                                @if($item->telpon)
                                    <span class="text-muted small ms-1">({{ $item->telpon }})</span>
                                @endif
                            </div>
                            <div class="text-dark small" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#pengerjaan-{{ $item->id }}" title="Klik nama hewan untuk detail">
                                🐾 <strong>{{ $item->namahewan ?: ($item->nama_hewan ?: 'Pasien') }}</strong>
                                <span style="color: #546e7a;">({{ $item->jenishewan ?: ($item->jenis_hewan ?: 'Kucing') }})</span>
                            </div>
                            <div class="mt-1 small" style="color: #546e7a;">
                                <i class="bi bi-calendar-event me-1"></i> {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y, g:i A') : 'Hari ini' }}
                                <span class="badge-langsung-datang ms-1">{{ strtolower($item->tipe_kedatangan ?? 'langsung datang') }}</span>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 ms-1 rounded-pill open-pet-tracking-btn" style="font-size: 11px; font-weight: 600;" data-booking-id="{{ $item->id }}" data-bs-toggle="modal" data-bs-target="#modal-tracking-hewan" title="Lihat Grafik Perkembangan & Vital Signs Pasien">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </button>
                            </div>
                        </td>

                        @if($stage == 'kedatangan')
                            <!-- 2. JASA DOKTER -->
                            <td>
                                @php
                                    $jasaDokter = $item->jasa_dokter ?? ($item->grooming ?? ($item->operasi ?? ($item->vaksin ?? 'Jasa Dokter Umum')));
                                @endphp
                                <span class="service-dot" style="background-color: #2d8aba;"></span>
                                <span class="fw-bold text-dark">{{ $jasaDokter }}</span>
                                @if($item->karyawan1)
                                    <div class="small text-muted mt-1">Dokter: {{ $item->karyawan1 }}</div>
                                @endif
                            </td>

                            <!-- 3. STATUS TAHAP PENGERJAAN (KEDATANGAN) -->
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <span class="badge bg-warning text-dark px-2 py-1 fw-bold">
                                        Kedatangan
                                    </span>
                                    
                                    <form action="{{ route('booking.update-stage', ['id' => $item->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="target_status" value="Memulai">
                                        <button type="submit" class="btn-kreloses-blue shadow-sm" title="Panggil Pasien & Mulai Pemeriksaan">
                                            <i class="bi bi-play-fill me-1"></i> Mulai
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-light border-0 px-2" data-bs-toggle="modal" data-bs-target="#pengerjaan-{{ $item->id }}" title="Opsi / Detail">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                </div>
                            </td>

                        @elseif($stage == 'memulai')
                            <!-- DOKTER & LAYANAN MEDIS -->
                            <td>
                                <div class="fw-bold text-dark mb-1">
                                    <i class="bi bi-person-badge text-primary me-1"></i> {{ $item->karyawan1 ?: 'Dokter Belum Dipilih' }}
                                </div>
                                <div class="small text-muted">
                                    Layanan: {{ $item->jasa_dokter ?? ($item->grooming ?? ($item->operasi ?? 'Konsultasi EMR')) }}
                                </div>
                            </td>

                            <!-- STATUS TAHAP PENGERJAAN (MEMULAI) -->
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <span class="badge bg-primary text-white px-2 py-1 fw-bold">
                                        Memulai (EMR)
                                    </span>

                                    <form action="{{ route('booking.update-stage', ['id' => $item->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="target_status" value="Apotek">
                                        <button type="submit" class="btn-kreloses-blue shadow-sm" title="Kirim Resep ke Apotek">
                                            <i class="bi bi-capsule me-1"></i> Ke Apotek
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-light border-0 px-2" data-bs-toggle="modal" data-bs-target="#pengerjaan-{{ $item->id }}" title="Opsi / Detail">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                </div>
                            </td>

                        @elseif($stage == 'rawat_inap')
                            <!-- RUANG RAWAT & OBSERVASI INAP -->
                            <td>
                                <div class="fw-bold text-dark mb-1">
                                    <i class="bi bi-hospital text-danger me-1"></i> Ruang Rawat Inap
                                    @if($item->no_kandang)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary ms-1" style="font-size: 0.75rem;">
                                            <i class="bi bi-door-open me-1"></i>{{ $item->no_kandang }}
                                        </span>
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    Dokter/Perawat: {{ $item->karyawan1 ?: 'Staf Piket Rawat Inap' }}
                                </div>
                            </td>

                            <!-- STATUS TAHAP PENGERJAAN (RAWAT INAP) -->
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary fw-semibold py-1 px-2 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#pengerjaan-{{ $item->id }}" title="Buka Catatan Observasi & Monitoring Pasien">
                                        <i class="bi bi-journal-medical me-1"></i> Catatan Observasi
                                    </button>

                                    <form action="{{ route('booking.update-stage', ['id' => $item->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="target_status" value="Apotek">
                                        <button type="submit" class="btn-kreloses-blue shadow-sm" title="Kirim ke Apotek / Persiapan Pulang">
                                            <i class="bi bi-capsule me-1"></i> Ke Apotek
                                        </button>
                                    </form>
                                </div>
                            </td>

                        @elseif($stage == 'apotek')
                            <!-- RESEP OBAT & INVENTORI -->
                            <td>
                                <div class="fw-bold text-dark mb-1">
                                    <i class="bi bi-capsule text-warning me-1"></i> Resep / Obat Pasien
                                </div>
                                <div class="small text-muted">
                                    Pemeriksaan obat & persetujuan stok apotek
                                </div>
                            </td>

                            <!-- STATUS TAHAP PENGERJAAN (APOTEK) -->
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <span class="badge bg-warning text-dark px-2 py-1 fw-bold">
                                        Apotek
                                    </span>

                                    <form action="{{ route('booking.update-stage', ['id' => $item->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="target_status" value="Pembayaran">
                                        <button type="submit" class="btn-kreloses-blue shadow-sm" title="Kirim ke Kasir POS">
                                            <i class="bi bi-check-circle-fill me-1"></i> Ke Kasir
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-light border-0 px-2" data-bs-toggle="modal" data-bs-target="#pengerjaan-{{ $item->id }}" title="Opsi / Detail">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                </div>
                            </td>

                        @elseif($stage == 'pembayaran')
                            <!-- BILLING & TAGIHAN KASIR -->
                            <td>
                                <div class="fw-bold text-success mb-1">
                                    💳 Kasir POS Checkout
                                </div>
                                <div class="small text-muted">
                                    Pembayaran & Penyelesaian Invoice
                                </div>
                            </td>

                            <!-- STATUS TAHAP PENGERJAAN (PEMBAYARAN) -->
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <span class="badge bg-info text-dark px-2 py-1 fw-bold">
                                        Pembayaran
                                    </span>

                                    <form action="{{ route('booking.update-stage', ['id' => $item->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="target_status" value="Telah diselesaikan">
                                        <button type="submit" class="btn-kreloses-blue shadow-sm" title="Selesaikan Transaksi">
                                            <i class="bi bi-check-lg me-1"></i> Selesai
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-light border-0 px-2" data-bs-toggle="modal" data-bs-target="#pengerjaan-{{ $item->id }}" title="Opsi / Detail">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                </div>
                            </td>

                        @else
                            <!-- STATUS AKHIR (SELESAI) -->
                            <td>
                                <span class="badge bg-success px-2 py-1 fw-bold">
                                    Telah Diselesaikan
                                </span>
                            </td>

                            <!-- CETAK INVOICE -->
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <a href="{{ route('nota', ['id' => $item->id]) }}" class="btn-kreloses-blue shadow-sm" title="Cetak Invoice PDF">
                                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Invoice
                                    </a>

                                    <button type="button" class="btn btn-sm btn-light border-0 px-2" data-bs-toggle="modal" data-bs-target="#pengerjaan-{{ $item->id }}" title="Opsi / Detail">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
