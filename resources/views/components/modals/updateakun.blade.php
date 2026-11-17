@foreach ($users as $item)
<div class="modal fade" id="updateModalCenter-{{ $item->id }}" tabindex="-1" aria-labelledby="updateModalCenterTitle-{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header text-white" style="background-color: #2d8aba; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <h5 class="modal-title text-white fw-bold" id="updateModalCenterTitle-{{ $item->id }}">
                    <i class="bi bi-pencil-square me-2"></i> Edit Akun Karyawan: {{ $item->name }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('updateakunkaryawan', ['id' => $item->id]) }}" method="POST">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="id" value="{{ $item->id }}">
                    <div class="mb-3">
                        <label for="InputName-{{ $item->id }}" class="form-label small fw-bold text-dark">Nama Lengkap Karyawan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="InputName-{{ $item->id }}" value="{{ $item->name }}" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputEmail1-{{ $item->id }}" class="form-label small fw-bold text-dark">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="exampleInputEmail1-{{ $item->id }}" value="{{ $item->email }}" name="email" required>
                        <div class="form-text small text-muted">*Pastikan email yang digunakan dalam keadaan aktif.</div>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputPassword1-{{ $item->id }}" class="form-label small fw-bold text-dark">Password Baru (Opsional)</label>
                        <input type="password" class="form-control" id="exampleInputPassword1-{{ $item->id }}" placeholder="Kosongkan jika tidak ingin mengubah password" name="password">
                    </div>
                    <input type="hidden" value="karyawan" name="role">

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