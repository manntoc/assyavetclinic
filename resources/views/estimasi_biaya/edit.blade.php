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
                        <i class="bi bi-calculator-fill me-2"></i>Edit Nota Perkiraan Biaya ({{ $estimasi->kode_estimasi }})
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Perbarui rincian estimasi harga perawatan pasien
                    </small>
                </div>
            </div>
            <div>
                <a href="{{ route('estimasi-biaya.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        <form action="{{ route('estimasi-biaya.update', $estimasi->id) }}" method="POST" id="estimasiEditForm">
            @csrf
            @method('PUT')

            <div class="row g-4 mb-4">
                <!-- LEFT COLUMN: PATIENT & OWNER INFO -->
                <div class="col-lg-4 col-12">
                    <div class="kreloses-card">
                        <h6 class="fw-bold mb-3 border-bottom pb-2" style="color: #2d8aba;">
                            <i class="bi bi-person-vcard-fill me-2"></i>Informasi Customer & Pasien
                        </h6>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Nama Pemilik (Owner) <span class="text-danger">*</span></label>
                            <input type="text" name="nama_owner" id="namaOwner" class="form-control" 
                                value="{{ old('nama_owner', $estimasi->nama_owner) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">No. Telepon / WhatsApp</label>
                            <input type="text" name="telepon" id="teleponOwner" class="form-control" 
                                value="{{ old('telepon', $estimasi->telepon) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Nama Pasien Hewan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_hewan" id="namaHewan" class="form-control" 
                                value="{{ old('nama_hewan', $estimasi->nama_hewan) }}" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark">Spesies / Jenis</label>
                                <input type="text" name="jenis_hewan" id="jenisHewan" class="form-control" 
                                    value="{{ old('jenis_hewan', $estimasi->jenis_hewan) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark">Berat (kg)</label>
                                <input type="text" name="berat_hewan" id="beratHewan" class="form-control" 
                                    value="{{ old('berat_hewan', $estimasi->berat_hewan) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Dokter / Penanggung Jawab</label>
                            <input type="text" name="dokter" class="form-control" 
                                value="{{ old('dokter', $estimasi->dokter) }}">
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-bold text-dark">Catatan Tambahan</label>
                            <textarea name="catatan" class="form-control" rows="2">{{ old('catatan', $estimasi->catatan) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: ESTIMATE DYNAMIC ITEM BUILDER -->
                <div class="col-lg-8 col-12">
                    <div class="kreloses-card">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 flex-wrap gap-2">
                            <h6 class="fw-bold mb-0" style="color: #2d8aba;">
                                <i class="bi bi-capsule-fill me-2"></i>Rincian Layanan, Obat, & Tindakan Medis
                            </h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="btnAddLayanan">
                                    + Tambah Layanan
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success fw-bold" id="btnAddObat">
                                    + Tambah Obat/Stok
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" id="btnAddCustom">
                                    + Baris Manual
                                </button>
                            </div>
                        </div>

                        <!-- DYNAMIC ITEM TABLE -->
                        <div class="table-responsive mb-3">
                            <table class="kreloses-table" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 22%;">Kategori</th>
                                        <th style="width: 40%;">Item / Layanan / Obat</th>
                                        <th style="width: 12%; text-align: center;">Qty</th>
                                        <th style="width: 20%; text-align: right;">Harga Satuan (Rp)</th>
                                        <th style="width: 6%; text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    <!-- Rows filled via JS from existing items -->
                                </tbody>
                            </table>
                        </div>

                        <!-- CALCULATION SUMMARY CARD -->
                        <div class="row justify-content-end mt-4">
                            <div class="col-md-7 col-12">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Subtotal Layanan/Tindakan:</span>
                                        <span class="fw-bold text-dark" id="dispSubtotalLayanan">Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Subtotal Obat/Stok:</span>
                                        <span class="fw-bold text-dark" id="dispSubtotalObat">Rp 0</span>
                                    </div>

                                    <div class="row g-2 align-items-center my-2 border-top pt-2">
                                        <div class="col-6">
                                            <label class="form-label small text-muted mb-0">Diskon Potongan (Rp):</label>
                                            <input type="number" name="diskon" id="inputDiskon" class="form-control form-control-sm text-end" value="{{ old('diskon', $estimasi->diskon) }}" min="0">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small text-muted mb-0">Pajak Tambahan (Rp):</label>
                                            <input type="number" name="pajak" id="inputPajak" class="form-control form-control-sm text-end" value="{{ old('pajak', $estimasi->pajak) }}" min="0">
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center border-top border-dark pt-2 mt-2">
                                        <span class="fw-bold fs-6 text-dark">ESTIMASI GRAND TOTAL:</span>
                                        <span class="fw-bold fs-5" style="color: #2d8aba;" id="dispGrandTotal">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ACTION BUTTONS -->
                        <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-3">
                            <button type="submit" name="action" value="save" class="btn btn-secondary px-4 fw-semibold">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                            <button type="submit" name="action" value="save_and_print" class="btn-kreloses-blue px-4">
                                <i class="bi bi-printer-fill me-1"></i> Simpan & Cetak PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @include('components.footer.footer_admin')
    </div>
</div>

<!-- JAVASCRIPT DATA & DYNAMIC ROW BUILDER -->
<script>
const masterLayanan = @json($layananList);
const masterInventori = @json($inventoriList);
const existingItems = @json($estimasi->items ?? []);

let rowIndex = 0;

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('itemsContainer');

    if (existingItems && existingItems.length > 0) {
        existingItems.forEach(item => {
            addRow(item.type || 'custom', item.nama || '', item.qty || 1, item.harga || 0);
        });
    } else {
        addRow('layanan');
    }

    document.getElementById('btnAddLayanan').addEventListener('click', () => addRow('layanan'));
    document.getElementById('btnAddObat').addEventListener('click', () => addRow('obat'));
    document.getElementById('btnAddCustom').addEventListener('click', () => addRow('custom'));

    document.getElementById('inputDiskon').addEventListener('input', calculateTotals);
    document.getElementById('inputPajak').addEventListener('input', calculateTotals);
});

function addRow(type = 'layanan', presetNama = '', presetQty = 1, presetHarga = 0) {
    const container = document.getElementById('itemsContainer');
    const idx = rowIndex++;

    let selectHtml = '';
    if (type === 'layanan') {
        selectHtml = `
            <select class="form-select form-select-sm item-select" data-idx="${idx}">
                <option value="">Pilih Layanan / Tindakan</option>
                ${masterLayanan.map(l => `<option value="${l.nama}" data-price="${l.harga}">${l.nama} (Rp ${numberFormat(l.harga)})</option>`).join('')}
                <option value="custom_input">+ Tulis Manual...</option>
            </select>
            <input type="text" name="items[${idx}][nama]" class="form-control form-control-sm item-input mt-1 d-none" placeholder="Nama tindakan/layanan" value="${presetNama}">
        `;
    } else if (type === 'obat') {
        selectHtml = `
            <select class="form-select form-select-sm item-select" data-idx="${idx}">
                <option value="">Pilih Obat / Barang Inventori</option>
                ${masterInventori.map(i => `<option value="${i.nama_barang}" data-price="${i.harga}">${i.nama_barang} (Stok: ${i.jumlah} | Rp ${numberFormat(i.harga)})</option>`).join('')}
                <option value="custom_input">+ Tulis Manual...</option>
            </select>
            <input type="text" name="items[${idx}][nama]" class="form-control form-control-sm item-input mt-1 d-none" placeholder="Nama obat / produk" value="${presetNama}">
        `;
    } else {
        selectHtml = `
            <input type="text" name="items[${idx}][nama]" class="form-control form-control-sm item-input" placeholder="Tulis rincian item..." value="${presetNama}" required>
        `;
    }

    const tr = document.createElement('tr');
    tr.id = `row_${idx}`;
    tr.innerHTML = `
        <td>
            <select name="items[${idx}][type]" class="form-select form-select-sm row-type">
                <option value="layanan" ${type === 'layanan' ? 'selected' : ''}>Layanan / Medis</option>
                <option value="obat" ${type === 'obat' ? 'selected' : ''}>Obat / Inventori</option>
                <option value="custom" ${type === 'custom' ? 'selected' : ''}>Custom / Lainnya</option>
            </select>
        </td>
        <td>
            ${selectHtml}
        </td>
        <td>
            <input type="number" name="items[${idx}][qty]" class="form-control form-control-sm text-center row-qty" value="${presetQty}" min="1" step="0.5">
        </td>
        <td>
            <input type="number" name="items[${idx}][harga]" class="form-control form-control-sm text-end row-harga" value="${presetHarga}" min="0">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-light border text-danger border-0 p-1" onclick="removeRow(${idx})" title="Hapus Baris">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;

    container.appendChild(tr);

    const sel = tr.querySelector('.item-select');
    const inputNama = tr.querySelector('.item-input');
    const inputHarga = tr.querySelector('.row-harga');
    const inputQty = tr.querySelector('.row-qty');

    if (presetNama) {
        if (sel) {
            sel.value = presetNama;
            if (!sel.value) {
                sel.value = 'custom_input';
                if (inputNama) inputNama.classList.remove('d-none');
            }
        }
        if (inputNama) inputNama.value = presetNama;
    }

    if (sel && typeof Choices !== 'undefined') {
        new Choices(sel, {
            searchEnabled: true,
            searchPlaceholderValue: 'Ketik untuk mencari...',
            itemSelectText: '',
            shouldSort: false,
            noResultsText: 'Tidak ditemukan hasil',
        });
    }

    if (sel) {
        sel.addEventListener('change', function () {
            const val = this.value;
            const opt = this.querySelector(`option[value="${val}"]`) || this.options[this.selectedIndex];
            if (val === 'custom_input') {
                if (inputNama) {
                    inputNama.classList.remove('d-none');
                    inputNama.value = '';
                    inputNama.required = true;
                }
            } else {
                if (inputNama) {
                    inputNama.classList.add('d-none');
                    inputNama.value = val;
                    inputNama.required = false;
                }
                const price = opt ? opt.getAttribute('data-price') : null;
                if (price !== null && inputHarga) {
                    inputHarga.value = price;
                }
            }
            calculateTotals();
        });
    }

    if (inputNama && (!sel || sel.value === 'custom_input')) {
        inputNama.name = `items[${idx}][nama]`;
    } else if (sel && inputNama) {
        inputNama.name = `items[${idx}][nama]`;
        if (sel.value && sel.value !== 'custom_input') {
            inputNama.value = sel.value;
        }
    }

    inputQty.addEventListener('input', calculateTotals);
    inputHarga.addEventListener('input', calculateTotals);

    calculateTotals();
}

function removeRow(idx) {
    const tr = document.getElementById(`row_${idx}`);
    if (tr) {
        tr.remove();
        calculateTotals();
    }
}

function calculateTotals() {
    let subtotalLayanan = 0;
    let subtotalObat = 0;

    document.querySelectorAll('#itemsContainer tr').forEach(tr => {
        const type = tr.querySelector('.row-type').value;
        const qty = parseFloat(tr.querySelector('.row-qty').value) || 0;
        const harga = parseFloat(tr.querySelector('.row-harga').value) || 0;
        const subtotal = qty * harga;

        if (type === 'obat') {
            subtotalObat += subtotal;
        } else {
            subtotalLayanan += subtotal;
        }
    });

    const diskon = parseFloat(document.getElementById('inputDiskon').value) || 0;
    const pajak = parseFloat(document.getElementById('inputPajak').value) || 0;

    const grandTotal = Math.max(0, (subtotalLayanan + subtotalObat) - diskon + pajak);

    document.getElementById('dispSubtotalLayanan').innerText = 'Rp ' + numberFormat(subtotalLayanan);
    document.getElementById('dispSubtotalObat').innerText = 'Rp ' + numberFormat(subtotalObat);
    document.getElementById('dispGrandTotal').innerText = 'Rp ' + numberFormat(grandTotal);
}

function numberFormat(num) {
    return new Intl.NumberFormat('id-ID').format(Math.round(num || 0));
}
</script>
@endsection
