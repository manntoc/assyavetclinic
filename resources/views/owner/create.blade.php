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
                        <i class="bi bi-person-plus-fill me-2"></i>Tambah Owner / Customer Baru
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Tambahkan data akun pemilik beserta profil hewan peliharaannya
                    </small>
                </div>
            </div>
            <div>
                <a href="{{ route('owner') }}" class="btn btn-sm btn-light border px-3 fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        <section class="section">
            <form action="{{ route('store.owner') }}" method="POST">
                @csrf

                <!-- DATA OWNER -->
                <div class="kreloses-card mb-4">
                    <h5 class="fw-bold mb-3" style="color: #2d8aba;">
                        <i class="bi bi-person-badge me-2"></i> Informasi Akun Pemilik (Owner)
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Nama Lengkap Owner <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama pemilik" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">No. Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" name="telepon" class="form-control" placeholder="Nomor telepon / WhatsApp" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Alamat Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Alamat email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Alamat Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="alamat" class="form-control" placeholder="Alamat lengkap" required>
                        </div>
                    </div>
                </div>

                <!-- DATA PET -->
                <div class="kreloses-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0" style="color: #2d8aba;">
                            <i class="bi bi-heart-pulse-fill me-2"></i> Profil Hewan Peliharaan (Pets)
                        </h5>
                        <button type="button" id="addPet" class="btn-kreloses-blue shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Hewan
                        </button>
                    </div>

                    <div id="pet-wrapper">
                        <div class="pet-item border rounded-3 p-3 mb-3 bg-light">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Nama Hewan <span class="text-danger">*</span></label>
                                    <input type="text" name="pets[0][nama]" class="form-control" placeholder="Nama hewan" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Jenis / Spesies</label>
                                    <select name="pets[0][jenis_hewan]" class="form-select">
                                        <option value="Kucing">Kucing</option>
                                        <option value="Anjing">Anjing</option>
                                        <option value="Kelinci">Kelinci</option>
                                        <option value="Burung">Burung</option>
                                        <option value="Reptil">Reptil</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Berat (Kg)</label>
                                    <input type="number" step="0.1" name="pets[0][berat_hewan]" class="form-control" placeholder="0.0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Ras / Trah</label>
                                    <input type="text" name="pets[0][ras_hewan]" class="form-control" placeholder="Ras / trah">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-kreloses-blue px-4 py-2 shadow-sm fs-6">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Data Owner & Pet
                    </button>
                    <a href="{{ route('owner') }}" class="btn btn-light border px-4 py-2">Batal</a>
                </div>
            </form>
        </section>

        @include('components.footer.footer_admin')
    </div>
</div>

<script>
    let index = 1;

    document.getElementById('addPet').addEventListener('click', function() {
        const html = `
            <div class="pet-item border rounded-3 p-3 mb-3 bg-light">
                <div class="text-end mb-2">
                    <button type="button" class="btn btn-danger btn-sm removePet">
                        <i class="bi bi-trash"></i> Hapus Hewan
                    </button>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-dark">Nama Hewan <span class="text-danger">*</span></label>
                        <input type="text" name="pets[${index}][nama]" class="form-control" placeholder="Nama hewan" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-dark">Jenis / Spesies</label>
                        <select name="pets[${index}][jenis_hewan]" class="form-select">
                            <option value="Kucing">Kucing</option>
                            <option value="Anjing">Anjing</option>
                            <option value="Kelinci">Kelinci</option>
                            <option value="Burung">Burung</option>
                            <option value="Reptil">Reptil</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-dark">Berat (Kg)</label>
                        <input type="number" step="0.1" name="pets[${index}][berat_hewan]" class="form-control" placeholder="Berat">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-dark">Ras / Trah</label>
                        <input type="text" name="pets[${index}][ras_hewan]" class="form-control" placeholder="Ras">
                    </div>
                </div>
            </div>
        `;

        document.getElementById('pet-wrapper').insertAdjacentHTML('beforeend', html);
        index++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('removePet') || e.target.closest('.removePet')) {
            const petItem = e.target.closest('.pet-item');
            if (petItem) petItem.remove();
        }
    });
</script>
@endsection