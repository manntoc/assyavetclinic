@foreach ($layanan as $item)
<div class="modal fade" id="updateModalLayanan-{{ $item->id }}" tabindex="-1" aria-labelledby="updatelayananTitle-{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header text-white" style="background-color: #2d8aba; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <h5 class="modal-title text-white fw-bold" id="updatelayananTitle-{{ $item->id }}">
                    <i class="bi bi-pencil-square me-2"></i> Edit Layanan: {{ $item->nama }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('updatelayanan', ['id' => $item->id]) }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="mb-3">
                        <label for="InputName-{{ $item->id }}" class="form-label small fw-bold text-dark">Nama Layanan <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" id="InputName-{{ $item->id }}" value="{{ $item->nama }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Kategori / Jenis Layanan <span class="text-danger">*</span></label>
                        <select name="jenis" class="form-select" required>
                            <option value="Jasa Dokter" {{ $item->jenis == 'Jasa Dokter' ? 'selected' : '' }}>Jasa Dokter</option>
                            <option value="Vaksin" {{ $item->jenis == 'Vaksin' ? 'selected' : '' }}>Vaksin</option>
                            <option value="Grooming" {{ $item->jenis == 'Grooming' ? 'selected' : '' }}>Grooming</option>
                            <option value="Operasi" {{ $item->jenis == 'Operasi' ? 'selected' : '' }}>Operasi</option>
                            <option value="Diagnosa Penunjang" {{ $item->jenis == 'Diagnosa Penunjang' ? 'selected' : '' }}>Diagnosa Penunjang</option>
                            <option value="Lainnya" {{ $item->jenis == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="InputHarga-{{ $item->id }}" class="form-label small fw-bold text-dark">Tarif / Harga Layanan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="harga" class="form-control" value="{{ $item->harga }}" id="InputHarga-{{ $item->id }}" required>
                    </div>
                    <div class="modal-footer px-0 pb-0 pt-3 border-top">
                        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-kreloses-blue px-4 fw-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach