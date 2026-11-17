<div class="modal fade" id="exampleModalCenter" tabindex="-1" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <!-- MODAL HEADER: Secondary Color Theme (#546e7a) for Obat/Inventori Module -->
            <div class="modal-header text-white" style="background-color: #546e7a; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <h5 class="modal-title text-white fw-bold" id="exampleModalCenterTitle" style="font-size: 1.25rem;">
                    <i class="bi bi-capsule me-2"></i> Form Pengambilan Barang / Obat Pasien
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('storetransaksi') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="InputName" class="form-label fw-bold text-dark" style="font-size: 0.875rem;">Pasien / Transaksi Booking <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm choices" style="font-size: 0.875rem;" name="booking_id" required>
                            <option value="">Pilih Pasien Booking</option>
                            @foreach ($booking as $item)
                            <option value="{{ $item->id }}">#{{ $item->id }} - {{ $item->nama }} ({{ $item->namahewan ?: ($item->nama_hewan ?: 'Pasien') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="InputBarang" class="form-label fw-bold text-dark" style="font-size: 0.875rem;">Nama Barang / Obat Inventori <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm choices" style="font-size: 0.875rem;" name="inventori_id" id="InputBarang" required>
                            <option value="">Pilih Barang yang diambil</option>
                            @foreach ($inventori as $data)
                            <option value="{{ $data->id }}" data-price="{{ $data->harga }}" data-stok="{{ $data->jumlah }}">{{ $data->nama_barang }} (Stok: {{ $data->jumlah }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="InputJumlah" class="form-label fw-bold text-dark" style="font-size: 0.875rem;">Jumlah Pengambilan <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" style="font-size: 0.875rem;" name="jumlah" id="InputJumlah" placeholder="Masukkan jumlah item" required min="1">
                        <small class="text-muted d-block mt-1" id="stokTersedia" style="font-size: 0.75rem;">Stok Tersedia: -</small>
                    </div>
                    <div class="mb-3">
                        <label for="InputHarga" class="form-label fw-bold text-dark" style="font-size: 0.875rem;">Harga Satuan (Rp)</label>
                        <input type="number" class="form-control form-control-sm bg-light" style="font-size: 0.875rem;" id="InputHarga" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="InputTotal" class="form-label fw-bold text-dark" style="font-size: 0.875rem;">Total Biaya (Rp)</label>
                        <input type="number" class="form-control form-control-sm bg-light fw-bold text-success" style="font-size: 0.875rem;" name="total" id="InputTotal" readonly required>
                    </div>
                    <div class="modal-footer px-0 pb-0 pt-3 border-top">
                        <button type="button" class="btn btn-light border px-3" style="font-size: 0.875rem;" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white px-4 fw-bold shadow-sm" style="background-color: #546e7a; border-color: #546e7a; font-size: 0.875rem;">
                            <i class="bi bi-check-circle-fill me-1"></i> Simpan Transaksi Obat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const barangSelect = document.getElementById('InputBarang');
    const jumlahInput = document.getElementById('InputJumlah');
    const hargaInput = document.getElementById('InputHarga');
    const totalInput = document.getElementById('InputTotal');
    const stokLabel = document.getElementById('stokTersedia');

    function calculateObatTotal() {
        if (!barangSelect || !jumlahInput || !hargaInput || !totalInput) return;
        const selectedOption = barangSelect.querySelector(`option[value="${barangSelect.value}"]`) || barangSelect.options[barangSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            const stok = parseInt(selectedOption.getAttribute('data-stok')) || 0;
            
            hargaInput.value = price;
            if (stokLabel) {
                stokLabel.innerHTML = `Stok Tersedia: <strong class="${stok < 5 ? 'text-danger' : 'text-success'}">${stok} item</strong>`;
            }
            jumlahInput.setAttribute('max', stok);

            const qty = parseInt(jumlahInput.value) || 0;
            totalInput.value = qty * price;
        } else {
            hargaInput.value = '';
            totalInput.value = '';
            if (stokLabel) stokLabel.innerHTML = 'Stok Tersedia: -';
        }
    }

    if (barangSelect) barangSelect.addEventListener('change', calculateObatTotal);
    if (jumlahInput) jumlahInput.addEventListener('input', calculateObatTotal);
});
</script>
