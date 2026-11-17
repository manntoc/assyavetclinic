@foreach ($inventori as $item)
<div class="modal fade" id="updateModal-{{ $item->id }}" tabindex="-1" aria-labelledby="updateModalTitle-{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header text-white" style="background-color: #546e7a; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <h5 class="modal-title text-white fw-bold" id="updateModalTitle-{{ $item->id }}">
                    <i class="bi bi-pencil-square me-2"></i> Edit Barang Inventori: {{ $item->nama_barang }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('updateinventori', ['id' => $item->id]) }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="mb-3">
                        <label for="InputBarang-{{ $item->id }}" class="form-label small fw-bold text-dark">Nama Barang / Obat <span class="text-danger">*</span></label>
                        <input type="text" name="nama_barang" class="form-control" value="{{ $item->nama_barang }}" id="InputBarang-{{ $item->id }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="InputStok-{{ $item->id }}" class="form-label small fw-bold text-dark">Jumlah Stok <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah" class="form-control" value="{{ $item->jumlah }}" id="InputStok-{{ $item->id }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="InputHarga-{{ $item->id }}" class="form-label small fw-bold text-dark">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="harga" class="form-control" value="{{ $item->harga }}" id="InputHarga-{{ $item->id }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="InputTanggal-{{ $item->id }}" class="form-label small fw-bold text-dark">Tanggal Pencatatan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="tanggal_pembuatan" value="{{ $item->tanggal_pembuatan }}" id="InputTanggal-{{ $item->id }}" required>
                    </div>
                    <div class="modal-footer px-0 pb-0 pt-3 border-top">
                        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-kreloses-secondary px-4 fw-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach