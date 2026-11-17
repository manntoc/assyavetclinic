<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Omzet Keuangan - Assyavet Clinic</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            background: #ffffff;
        }

        /* HEADER / KOP SURAT */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #2d8aba;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .clinic-title {
            font-size: 20px;
            font-weight: bold;
            color: #2d8aba;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .clinic-sub {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
        }

        .doc-title {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }

        .doc-period {
            text-align: right;
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
        }

        /* KPI SUMMARY BOXES */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .kpi-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            text-align: center;
        }

        .kpi-label {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 14px;
            font-weight: bold;
            color: #2d8aba;
        }

        .kpi-sub {
            font-size: 8px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* TABLES */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 8px;
            border-left: 3px solid #2d8aba;
            padding-left: 6px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 15px;
        }

        .data-table th {
            background-color: #2d8aba;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #246e96;
        }

        .data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .text-success { color: #10b981; }
        .text-danger { color: #ef4444; }

        /* FOOTER / SIGNATURE */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .footer-table td {
            vertical-align: top;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT HEADER -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="clinic-title">ASSYAVET ANIMAL CLINIC</div>
                <div class="clinic-sub">Sistem Informasi Manajemen Pelayanan Klinik & Farmasi Hewan</div>
                <div class="clinic-sub">Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d F Y H:i') }} WIB</div>
            </td>
            <td style="width: 40%;">
                <div class="doc-title">LAPORAN OMZET DETAIL</div>
                <div class="doc-period">
                    Periode: <strong>{{ $filters['start_date']->format('d M Y') }}</strong> s/d <strong>{{ $filters['end_date']->format('d M Y') }}</strong>
                </div>
                <div class="doc-period">
                    Filter Status: <strong>{{ $filters['status'] == 'completed' ? 'Telah Diselesaikan' : 'Semua Status' }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- RINGKASAN FINANSIAL KPI -->
    <table class="kpi-table">
        <tr>
            <td style="width: 25%; padding-right: 5px;">
                <div class="kpi-box" style="border-left: 3px solid #2d8aba;">
                    <div class="kpi-label">Total Omzet Bersih</div>
                    <div class="kpi-value" style="color: #2d8aba;">Rp {{ number_format($totalOmzetClean, 0, ',', '.') }}</div>
                    <div class="kpi-sub">Setelan Diskon & Pajak</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 5px;">
                <div class="kpi-box">
                    <div class="kpi-label">Omzet Kotor (Gross)</div>
                    <div class="kpi-value" style="color: #0284c7;">Rp {{ number_format($totalGross, 0, ',', '.') }}</div>
                    <div class="kpi-sub">Sebelum Diskon</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 5px;">
                <div class="kpi-box">
                    <div class="kpi-label">Total Transaksi / AOV</div>
                    <div class="kpi-value" style="color: #10b981;">{{ $totalTransaksi }} Pasien</div>
                    <div class="kpi-sub">AOV: Rp {{ number_format($aov, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 5px;">
                <div class="kpi-box">
                    <div class="kpi-label">DP / Uang Muka</div>
                    <div class="kpi-value" style="color: #8b5cf6;">Rp {{ number_format($totalDp, 0, ',', '.') }}</div>
                    <div class="kpi-sub">Total Diskon: Rp {{ number_format($totalDiskon, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- RINGKASAN BREAKDOWN PER KATEGORI -->
    <div class="section-title">Breakdown Pendapatan per Kategori Layanan & Obat</div>
    <table class="data-table" style="margin-bottom: 20px;">
        <thead>
            <tr>
                @foreach($categoryBreakdown as $catName => $catVal)
                    <th class="text-center" style="font-size: 9px;">{{ $catName }}</th>
                @endforeach
                <th class="text-right" style="font-size: 9px; background-color: #1e293b;">TOTAL GROSS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach($categoryBreakdown as $catName => $catVal)
                    <td class="text-right fw-bold">Rp {{ number_format($catVal, 0, ',', '.') }}</td>
                @endforeach
                <td class="text-right fw-bold text-success" style="background-color: #f1f5f9;">
                    Rp {{ number_format($totalGross, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- RINCIAN TRANSAKSI PASIEN -->
    <div class="section-title">Rincian Transaksi Pasien Omzet (Total {{ count($bookings) }} Data)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">Kode / Tanggal</th>
                <th style="width: 18%;">Nama Owner</th>
                <th style="width: 16%;">Nama & Jenis Hewan</th>
                <th style="width: 25%;">Komponen Layanan & Obat</th>
                <th style="width: 10%;">Metode Bayar</th>
                <th style="width: 15%; text-align: right;">Total Omzet (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $index => $b)
                @php
                    $totalObat = $b->transaksis->sum('total');
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong style="color: #2d8aba;">{{ $b->kode_booking ?? ('BOOK-'.$b->id) }}</strong><br>
                        <span class="text-muted">{{ \Carbon\Carbon::parse($b->tanggal)->format('d/m/Y') }}</span>
                    </td>
                    <td>
                        <strong>{{ $b->nama ?? '-' }}</strong><br>
                        <span class="text-muted">{{ $b->telpon ?? '-' }}</span>
                    </td>
                    <td>
                        <strong>{{ $b->nama_hewan ?: ($b->namahewan ?: '-') }}</strong><br>
                        <span class="text-muted">({{ $b->jenis_hewan ?: ($b->jenishewan ?: 'Hewan') }})</span>
                    </td>
                    <td style="font-size: 8.5px;">
                        @if($b->jasa_dokter_harga > 0)
                            • Jasa Dokter: Rp{{ number_format($b->jasa_dokter_harga, 0, ',', '.') }}<br>
                        @endif
                        @if($b->vaksin_harga > 0)
                            • Vaksin: Rp{{ number_format($b->vaksin_harga, 0, ',', '.') }}<br>
                        @endif
                        @if($b->grooming_harga > 0)
                            • Grooming: Rp{{ number_format($b->grooming_harga, 0, ',', '.') }}<br>
                        @endif
                        @if($b->operasi_harga > 0)
                            • Operasi: Rp{{ number_format($b->operasi_harga, 0, ',', '.') }}<br>
                        @endif
                        @if($b->diagnosa_penunjang_harga > 0)
                            • Diagnosa: Rp{{ number_format($b->diagnosa_penunjang_harga, 0, ',', '.') }}<br>
                        @endif
                        @if($totalObat > 0)
                            • Obat/Inventori: Rp{{ number_format($totalObat, 0, ',', '.') }}<br>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ !empty($b->metode_pembayaran) ? ucfirst($b->metode_pembayaran) : 'Tunai' }}
                    </td>
                    <td class="text-right fw-bold">
                        Rp {{ number_format($b->total, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-3 text-muted">
                        Tidak ada transaksi omzet terdaftar pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td colspan="6" class="text-right">GRAND TOTAL OMZET BERSIH:</td>
                <td class="text-right" style="color: #2d8aba; font-size: 11px;">
                    Rp {{ number_format($totalOmzetClean, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- TANDA TANGAN FOOTER -->
    <table class="footer-table">
        <tr>
            <td style="width: 60%;">
                <span class="text-muted" style="font-size: 9px;">* Dokumen ini dibuat secara otomatis oleh sistem informasi Assyavet Clinic OS dan sah tanpa tanda tangan basah jika dicetak secara resmi.</span>
            </td>
            <td style="width: 40%; text-align: center;">
                <div>Penanggung Jawab Keuangan Klinik,</div>
                <br><br><br>
                <div style="font-weight: bold; text-decoration: underline;">( Admin / Manager Keuangan )</div>
                <div class="text-muted" style="font-size: 9px;">Assyavet Animal Clinic</div>
            </td>
        </tr>
    </table>

</body>
</html>
