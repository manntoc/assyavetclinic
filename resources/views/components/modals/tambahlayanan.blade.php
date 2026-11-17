<div class="modal fade" id="tambahlayanan" tabindex="-1" aria-labelledby="tambahlayananTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header text-white" style="background-color: #2d8aba; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <h5 class="modal-title text-white fw-bold" id="tambahlayananTitle">
                    <i class="bi bi-gear-fill me-2"></i> Tambah Layanan Klinik Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('storelayanan') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="InputName" class="form-label small fw-bold text-dark">Nama Layanan <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" id="InputName" placeholder="Nama layanan" required>
                    </div>
                    <div class="mb-3">
                        <label for="exampleDataList" class="form-label small fw-bold text-dark">Kategori / Jenis Layanan <span class="text-danger">*</span></label>
                        <select name="jenis" class="form-select" required>
                            <option value="Jasa Dokter" selected>Jasa Dokter</option>
                            <option value="Vaksin">Vaksin</option>
                            <option value="Grooming">Grooming</option>
                            <option value="Operasi">Operasi</option>
                            <option value="Diagnosa Penunjang">Diagnosa Penunjang</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="InputHarga" class="form-label small fw-bold text-dark">Tarif / Harga Layanan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="harga" class="form-control" id="InputHarga" placeholder="0" required>
                    </div>
                    <div class="modal-footer px-0 pb-0 pt-3 border-top">
                        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-kreloses-blue px-4 fw-bold">Simpan Layanan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
