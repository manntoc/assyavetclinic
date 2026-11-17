<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $transaksi->kode_booking ?? ('BOOK-' . $transaksi->id) }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0 auto;
            padding: 12px 10px;
            width: 8cm;
            background-color: #ffffff;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.3;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .uppercase { text-transform: uppercase; }

        /* Header Logo & Info */
        .clinic-logo {
            max-width: 140px;
            max-height: 45px;
            width: auto;
            height: auto;
            margin-bottom: 4px;
        }
        
        .clinic-name {
            font-size: 15px;
            font-weight: 800;
            color: #2d8aba;
            margin: 2px 0 0 0;
            letter-spacing: 0.5px;
        }

        .clinic-sub {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
            margin-bottom: 8px;
        }

        .divider-dashed {
            border-top: 1px dashed #94a3b8;
            margin: 8px 0;
        }

        .divider-solid {
            border-top: 1px solid #cbd5e1;
            margin: 8px 0;
        }

        /* Meta Information Table */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 6px;
        }

        .meta-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }

        .meta-label {
            color: #64748b;
            width: 42%;
        }

        .meta-val {
            color: #0f172a;
            font-weight: 600;
        }

        /* Item Listing Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 4px;
        }

        .items-table th {
            font-size: 9.5px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            border-bottom: 1px dashed #64748b;
            padding: 4px 0;
        }

        .items-table td {
            padding: 4px 0;
            border-bottom: 1px dotted #e2e8f0;
            vertical-align: top;
        }

        .item-name {
            font-weight: 600;
            color: #0f172a;
        }

        .item-sub {
            font-size: 8.5px;
            color: #64748b;
        }

        /* Totals Summary */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin-top: 6px;
        }

        .summary-table td {
            padding: 2px 0;
        }

        .grand-total-row td {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            border-top: 1px dashed #475569;
            border-bottom: 1px dashed #475569;
            padding: 5px 0;
        }

        /* Footer */
        .footer-box {
            margin-top: 12px;
            text-align: center;
            font-size: 9px;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- CLINIC HEADER -->
    <div class="text-center">
        <img src="https://assyavetclinic.com/admin/images/pdf/logo.png" class="clinic-logo" alt="Logo">
        <div class="clinic-name">ASSYA VET CLINIC</div>
        <div class="clinic-sub">
            Jl. Jambangan Persada No.31, Jambangan, Surabaya<br>
            Telp: 0813-3005-9485 | Website: assyavetclinic.com
        </div>
    </div>

    <div class="divider-dashed"></div>

    <!-- INVOICE & PATIENT META INFO -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">No. Invoice:</td>
            <td class="meta-val text-right">{{ $transaksi->kode_booking ?? ('BOOK-' . $transaksi->id) }}</td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal / Waktu:</td>
            <td class="meta-val text-right">
                {{ $transaksi->tanggal ? \Carbon\Carbon::parse($transaksi->tanggal)->format('d/m/Y') : date('d/m/Y') }} 
                ({{ $transaksi->jam ?? date('H:i') }})
            </td>
        </tr>
        <tr>
            <td class="meta-label">Kedatangan:</td>
            <td class="meta-val text-right">{{ $transaksi->tipe_kedatangan ?? 'Walk-in' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Pemilik (Owner):</td>
            <td class="meta-val text-right">{{ $transaksi->nama ?? ($transaksi->owner->nama ?? '-') }}</td>
        </tr>
        <tr>
            <td class="meta-label">No. HP:</td>
            <td class="meta-val text-right">{{ $transaksi->telpon ?? ($transaksi->owner->telepon ?? '-') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Nama Pasien:</td>
            <td class="meta-val text-right">
                {{ $transaksi->namahewan ?: ($transaksi->nama_hewan ?: 'Pasien') }}
            </td>
        </tr>
        <tr>
            <td class="meta-label">Spesies / Berat:</td>
            <td class="meta-val text-right">
                {{ $transaksi->jenishewan ?: ($transaksi->jenis_hewan ?: 'Kucing') }} 
                @php
                    $berat = $transaksi->berat_hewan ?: ($transaksi->berathewan ?: ($transaksi->berat ?: null));
                @endphp
                @if($berat)
                    ({{ $berat }} kg)
                @endif
            </td>
        </tr>
        @if($transaksi->karyawan1)
            <tr>
                <td class="meta-label">Dokter / Staf:</td>
                <td class="meta-val text-right">{{ $transaksi->karyawan1 }}</td>
            </tr>
        @endif
    </table>

    <div class="divider-dashed"></div>

    <!-- ITEM BREAKDOWN TABLE -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 55%;">Layanan / Produk</th>
                <th class="text-center" style="width: 15%;">Qty</th>
                <th class="text-right" style="width: 30%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $hasItems = false; @endphp

            <!-- LAYANAN MEDIS / JASA -->
            @if($transaksi->jasa_dokter && $transaksi->jasa_dokter !== 'tidak ada')
                @php $hasItems = true; @endphp
                <tr>
                    <td class="text-left">
                        <div class="item-name">{{ $transaksi->jasa_dokter }}</div>
                        <div class="item-sub">Tindakan / Konsultasi Dokter</div>
                    </td>
                    <td class="text-center">{{ $transaksi->jasa_dokter_kuantitas ?? 1 }}</td>
                    <td class="text-right">Rp {{ number_format($transaksi->jasa_dokter_harga ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endif

            @if($transaksi->vaksin && $transaksi->vaksin !== 'belum ada')
                @php $hasItems = true; @endphp
                <tr>
                    <td class="text-left">
                        <div class="item-name">Vaksin: {{ $transaksi->vaksin }}</div>
                    </td>
                    <td class="text-center">{{ $transaksi->vaksin_kuantitas ?? 1 }}</td>
                    <td class="text-right">Rp {{ number_format($transaksi->vaksin_harga ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endif

            @if($transaksi->grooming && $transaksi->grooming !== 'belum ada')
                @php $hasItems = true; @endphp
                <tr>
                    <td class="text-left">
                        <div class="item-name">Grooming: {{ $transaksi->grooming }}</div>
                    </td>
                    <td class="text-center">{{ $transaksi->grooming_kuantitas ?? 1 }}</td>
                    <td class="text-right">Rp {{ number_format($transaksi->grooming_harga ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endif

            @if($transaksi->operasi && $transaksi->operasi !== 'belum ada')
                @php $hasItems = true; @endphp
                <tr>
                    <td class="text-left">
                        <div class="item-name">Operasi: {{ $transaksi->operasi }}</div>
                    </td>
                    <td class="text-center">{{ $transaksi->operasi_kuantitas ?? 1 }}</td>
                    <td class="text-right">Rp {{ number_format($transaksi->operasi_harga ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endif

            @if($transaksi->diagnosa_penunjang && $transaksi->diagnosa_penunjang !== 'tidak ada')
                @php $hasItems = true; @endphp
                <tr>
                    <td class="text-left">
                        <div class="item-name">Diagnosa: {{ $transaksi->diagnosa_penunjang }}</div>
                    </td>
                    <td class="text-center">{{ $transaksi->diagnosa_penunjang_kuantitas ?? 1 }}</td>
                    <td class="text-right">Rp {{ number_format($transaksi->diagnosa_penunjang_harga ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endif

            @if($transaksi->transportasi && $transaksi->transportasi !== 'tidak ada')
                @php $hasItems = true; @endphp
                <tr>
                    <td class="text-left">
                        <div class="item-name">Transportasi</div>
                    </td>
                    <td class="text-center">{{ $transaksi->transportasi_kuantitas ?? 1 }}</td>
                    <td class="text-right">Rp {{ number_format($transaksi->total_trasport ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endif

            @if($transaksi->lainnya && $transaksi->lainnya !== 'belum ada')
                @php $hasItems = true; @endphp
                <tr>
                    <td class="text-left">
                        <div class="item-name">{{ $transaksi->lainnya }}</div>
                    </td>
                    <td class="text-center">{{ $transaksi->lainnya_kuantitas ?? 1 }}</td>
                    <td class="text-right">Rp {{ number_format($transaksi->lainnya_harga ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endif

            <!-- OBAT-OBATAN & INVENTORI -->
            @if(isset($obat) && count($obat) > 0)
                @foreach($obat as $item)
                    @php $hasItems = true; @endphp
                    <tr>
                        <td class="text-left">
                            <div class="item-name">{{ $item->inventori->nama_barang ?? 'Obat / Barang' }}</div>
                            <div class="item-sub">Resep / Inventori Klinik</div>
                        </td>
                        <td class="text-center">{{ $item->jumlah }}</td>
                        <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif

            @if(!$hasItems)
                <tr>
                    <td colspan="3" class="text-center text-muted" style="padding: 8px 0;">
                        Belum ada item tindakan atau obat tercatat.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="divider-dashed"></div>

    <!-- SUMMARY TOTALS -->
    <table class="summary-table">
        @if(($totalLayanan ?? 0) > 0)
            <tr>
                <td class="text-left text-muted">Subtotal Layanan:</td>
                <td class="text-right">Rp {{ number_format($totalLayanan, 0, ',', '.') }}</td>
            </tr>
        @endif

        @if(($totalObat ?? 0) > 0)
            <tr>
                <td class="text-left text-muted">Subtotal Obat/Resep:</td>
                <td class="text-right">Rp {{ number_format($totalObat, 0, ',', '.') }}</td>
            </tr>
        @endif

        @if(($diskon ?? 0) > 0)
            <tr>
                <td class="text-left text-muted">Diskon (-):</td>
                <td class="text-right text-danger">- Rp {{ number_format($diskon, 0, ',', '.') }}</td>
            </tr>
        @endif

        @if(($pajak ?? 0) > 0)
            <tr>
                <td class="text-left text-muted">Pajak (+):</td>
                <td class="text-right">+ Rp {{ number_format($pajak, 0, ',', '.') }}</td>
            </tr>
        @endif

        <tr class="grand-total-row">
            <td class="text-left">GRAND TOTAL:</td>
            <td class="text-right">Rp {{ number_format($grandTotal ?? ($subtotal ?? 0), 0, ',', '.') }}</td>
        </tr>

        @if(($dp ?? 0) > 0)
            <tr>
                <td class="text-left text-muted" style="padding-top: 4px;">Uang Muka (DP):</td>
                <td class="text-right" style="padding-top: 4px;">- Rp {{ number_format($dp, 0, ',', '.') }}</td>
            </tr>
        @endif

        <tr>
            <td class="text-left fw-bold" style="padding-top: 4px; font-size: 11px;">SISA PEMBAYARAN:</td>
            <td class="text-right fw-bold" style="padding-top: 4px; font-size: 11px; color: #2d8aba;">
                Rp {{ number_format($sisaPembayaran ?? ($pembayaran ?? 0), 0, ',', '.') }}
            </td>
        </tr>

        <tr>
            <td class="text-left text-muted" style="font-size: 9.5px;">Metode Pembayaran:</td>
            <td class="text-right fw-bold" style="font-size: 9.5px;">
                {{ $transaksi->metode_pembayaran ?? 'Tunai' }}
            </td>
        </tr>
    </table>

    <div class="divider-dashed"></div>

    <!-- FOOTER MESSAGE -->
    <div class="footer-box">
        <div class="fw-bold" style="color: #0f172a; margin-bottom: 2px;">Terima Kasih Atas Kepercayaan Anda!</div>
        <div>Semoga <strong>{{ $transaksi->namahewan ?: ($transaksi->nama_hewan ?: 'Pasien') }}</strong> sehat selalu & lekas pulih.</div>
        <div style="margin-top: 4px; font-size: 8px;" class="text-muted">Assya Vet Clinic: We Care Your Lovely Pets</div>
    </div>

</body>
</html>