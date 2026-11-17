<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Estimasi - {{ $estimasi->kode_estimasi }}</title>
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
        .text-danger { color: #dc2626; }
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
            margin-bottom: 6px;
        }

        .estimate-badge {
            display: inline-block;
            background-color: #f1f5f9;
            color: #0f172a;
            border: 1px dashed #2d8aba;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: 800;
            border-radius: 4px;
            margin: 4px 0;
            letter-spacing: 0.5px;
        }

        .divider-dashed {
            border-top: 1px dashed #94a3b8;
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
            font-size: 9px;
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
            font-size: 11.5px;
            font-weight: 800;
            color: #2d8aba;
            border-top: 1px dashed #475569;
            border-bottom: 1px dashed #475569;
            padding: 5px 0;
        }

        /* Disclaimer Box */
        .disclaimer-box {
            margin-top: 10px;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background-color: #f8fafc;
            font-size: 8.5px;
            color: #475569;
            text-align: justify;
        }

        /* Footer */
        .footer-box {
            margin-top: 10px;
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
        <div class="estimate-badge">
            NOTA ESTIMASI BIAYA PERAWATAN
        </div>
    </div>

    <div class="divider-dashed"></div>

    <!-- ESTIMATE META INFO -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">No. Estimasi:</td>
            <td class="meta-val text-right">{{ $estimasi->kode_estimasi }}</td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal Estimasi:</td>
            <td class="meta-val text-right">
                {{ $estimasi->created_at ? $estimasi->created_at->format('d/m/Y H:i') : date('d/m/Y H:i') }}
            </td>
        </tr>
        <tr>
            <td class="meta-label">Pemilik (Owner):</td>
            <td class="meta-val text-right">{{ $estimasi->nama_owner }}</td>
        </tr>
        @if($estimasi->telepon)
        <tr>
            <td class="meta-label">No. HP:</td>
            <td class="meta-val text-right">{{ $estimasi->telepon }}</td>
        </tr>
        @endif
        <tr>
            <td class="meta-label">Nama Pasien:</td>
            <td class="meta-val text-right">{{ $estimasi->nama_hewan }}</td>
        </tr>
        <tr>
            <td class="meta-label">Spesies / Berat:</td>
            <td class="meta-val text-right">
                {{ $estimasi->jenis_hewan ?: 'Hewan' }}
                @if($estimasi->berat_hewan)
                    ({{ $estimasi->berat_hewan }} kg)
                @endif
            </td>
        </tr>
        @if($estimasi->dokter)
        <tr>
            <td class="meta-label">Dokter / Staf:</td>
            <td class="meta-val text-right">{{ $estimasi->dokter }}</td>
        </tr>
        @endif
    </table>

    <div class="divider-dashed"></div>

    <!-- ITEM BREAKDOWN TABLE -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 55%;">Rincian Item / Perawatan</th>
                <th class="text-center" style="width: 15%;">Qty</th>
                <th class="text-right" style="width: 30%;">Est. Total</th>
            </tr>
        </thead>
        <tbody>
            @if(is_array($estimasi->items) && count($estimasi->items) > 0)
                @foreach($estimasi->items as $item)
                    <tr>
                        <td class="text-left">
                            <div class="item-name">{{ $item['nama'] }}</div>
                            <div class="item-sub">
                                {{ ucfirst($item['type'] ?? 'Item') }} - Rp {{ number_format($item['harga'] ?? 0, 0, ',', '.') }}/unit
                            </div>
                        </td>
                        <td class="text-center">{{ $item['qty'] }}</td>
                        <td class="text-right">Rp {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center text-muted" style="padding: 8px 0;">
                        Belum ada item estimasi tercatat.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="divider-dashed"></div>

    <!-- SUMMARY TOTALS -->
    <table class="summary-table">
        @if(($estimasi->subtotal_layanan ?? 0) > 0)
            <tr>
                <td class="text-left text-muted">Subtotal Layanan/Tindakan:</td>
                <td class="text-right">Rp {{ number_format($estimasi->subtotal_layanan, 0, ',', '.') }}</td>
            </tr>
        @endif

        @if(($estimasi->subtotal_obat ?? 0) > 0)
            <tr>
                <td class="text-left text-muted">Subtotal Obat/Perlengkapan:</td>
                <td class="text-right">Rp {{ number_format($estimasi->subtotal_obat, 0, ',', '.') }}</td>
            </tr>
        @endif

        @if(($estimasi->diskon ?? 0) > 0)
            <tr>
                <td class="text-left text-muted">Diskon (-):</td>
                <td class="text-right text-danger">- Rp {{ number_format($estimasi->diskon, 0, ',', '.') }}</td>
            </tr>
        @endif

        @if(($estimasi->pajak ?? 0) > 0)
            <tr>
                <td class="text-left text-muted">Pajak (+):</td>
                <td class="text-right">+ Rp {{ number_format($estimasi->pajak, 0, ',', '.') }}</td>
            </tr>
        @endif

        <tr class="grand-total-row">
            <td class="text-left">ESTIMASI BIAYA TOTAL:</td>
            <td class="text-right">Rp {{ number_format($estimasi->grand_total ?? 0, 0, ',', '.') }}</td>
        </tr>
    </table>

    @if($estimasi->catatan)
        <div style="margin-top: 6px; font-size: 9px; color: #475569;">
            <strong>Catatan Tambahan:</strong><br>
            {{ $estimasi->catatan }}
        </div>
    @endif

    <!-- DISCLAIMER NOTICE -->
    <div class="disclaimer-box">
        <strong>PERHATIAN & DISCLAIMER:</strong><br>
        Nota ini merupakan <u>estimasi / perkiraan biaya sementara</u> untuk layanan kesehatan hewan di Assya Vet Clinic dan <strong>BUKAN merupakan bukti pembayaran sah atau faktur final</strong>. Biaya aktual dapat berubah sewaktu-waktu tergantung hasil penanganan medis dan obat yang dikonsumsi pasien.
    </div>

    <!-- FOOTER MESSAGE -->
    <div class="footer-box">
        <div class="fw-bold" style="color: #0f172a; margin-bottom: 2px;">Assya Vet Clinic</div>
        <div>Salam sehat untuk <strong>{{ $estimasi->nama_hewan }}</strong></div>
        <div style="margin-top: 4px; font-size: 8px;" class="text-muted">Cetak Otomatis via VetClinic OS</div>
    </div>

</body>
</html>
