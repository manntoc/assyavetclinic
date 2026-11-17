<div class="modal fade" id="createModalCenter" tabindex="-1" aria-labelledby="createModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header text-white" style="background-color: #2d8aba; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <h5 class="modal-title text-white fw-bold" id="createModalCenterTitle">
                    <i class="bi bi-person-plus-fill me-2"></i> Tambah Akun Karyawan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('storeakunkaryawan') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="InputName" class="form-label small fw-bold text-dark">Nama Lengkap Karyawan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="InputName" placeholder="Masukkan nama lengkap" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label small fw-bold text-dark">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="exampleInputEmail1" placeholder="email@klinik.com" name="email" required>
                        <div class="form-text small text-muted">*Pastikan email yang digunakan dalam keadaan aktif.</div>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputPassword1" class="form-label small fw-bold text-dark">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Masukkan kata sandi" name="password" required>
                    </div>
                    <input type="hidden" value="karyawan" name="role">

                    <div class="modal-footer px-0 pb-0 pt-3 border-top">
                        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-kreloses-blue px-4 fw-bold">Daftarkan Karyawan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>