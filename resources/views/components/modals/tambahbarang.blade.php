<div class="modal fade" id="ModalTambahBarang" tabindex="-1" aria-labelledby="ModalTambahBarangTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header text-white" style="background-color: #546e7a; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <h5 class="modal-title text-white fw-bold" id="ModalTambahBarangTitle">
                    <i class="bi bi-box-seam me-2"></i> Tambah Barang Inventori
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('storeinventori') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="InputBarang" class="form-label small fw-bold text-dark">Nama Barang / Obat <span class="text-danger">*</span></label>
                        <input type="text" name="nama_barang" class="form-control" id="InputBarang" placeholder="Nama barang / obat" required>
                    </div>
                    <div class="mb-3">
                        <label for="InputStok" class="form-label small fw-bold text-dark">Jumlah Stok <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah" class="form-control" id="InputStok" placeholder="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="InputHarga" class="form-label small fw-bold text-dark">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="harga" class="form-control" id="InputHarga" placeholder="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="InputTanggal" class="form-label small fw-bold text-dark">Tanggal Pencatatan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="tanggal_pembuatan" id="InputTanggal" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="modal-footer px-0 pb-0 pt-3 border-top">
                        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-kreloses-secondary px-4 fw-bold">Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
