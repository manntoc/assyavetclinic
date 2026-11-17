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
                        <i class="bi bi-people-fill me-2"></i>Data Master Owner / Customer
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Kelola data akun pemilik beserta hewan peliharaannya
                    </small>
                </div>
            </div>
            <div>
                <button type="button" class="btn btn-primary" style="background:#2d8aba;border-color:#2d8aba;" data-bs-toggle="modal" data-bs-target="#modalTambahOwner">
                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah Owner Baru
                </button>
            </div>
        </div>

        <!-- KRELOSES CARD CONTAINER -->
        <div class="kreloses-card mb-4">
            <div class="table-responsive">
                <table class="kreloses-table" id="table1">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="20%">Nama Owner</th>
                            <th width="15%">No. Telepon</th>
                            <th width="16%">Email</th>
                            <th width="18%">Alamat</th>
                            <th width="16%">Hewan</th>
                            <th width="10%" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($owners as $owner)
                        <tr>
                            <td><strong>{{ $loop->iteration }}</strong></td>
                            <td class="fw-semibold">
                                <a href="#" class="kreloses-owner-link" style="color: #2d8aba;" data-bs-toggle="modal" data-bs-target="#modalDetailOwner-{{ $owner->id }}">
                                    <i class="bi bi-person-fill me-1" style="color: #2d8aba;"></i>{{ $owner->nama }}
                                </a>
                            </td>
                            <td>
                                <a href="tel:{{ $owner->telepon }}" class="text-decoration-none" style="color: #546e7a;">
                                    {{ $owner->telepon }}
                                </a>
                            </td>
                            <td style="color: #546e7a;">{{ $owner->email ?? '-' }}</td>
                            <td class="text-muted small">{{ $owner->alamat }}</td>
                            <td>
                                @if($owner->pets->count() > 0)
                                    <div class="fw-bold text-dark mb-0">{{ $owner->pets->count() }} Hewan</div>
                                    <small class="text-muted d-block text-truncate" style="max-width: 170px;" title="{{ $owner->pets->pluck('nama')->implode(', ') }}">
                                        🐾 {{ $owner->pets->pluck('nama')->implode(', ') }}
                                    </small>
                                @else
                                    <span class="text-muted small">Belum ada hewan</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#modalDetailOwner-{{ $owner->id }}" title="Detail Owner">
                                        <i class="bi bi-eye text-primary"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border ms-1" data-bs-toggle="modal" data-bs-target="#modalEditOwner-{{ $owner->id }}" title="Edit Owner">
                                        <i class="bi bi-pencil-square text-warning"></i>
                                    </button>
                                    <button type="button" onclick="confirmDeletion({{ $owner->id }}, '{{ $owner->nama }}')" class="btn btn-sm btn-light border text-danger ms-1" title="Hapus Owner">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5" style="color: #546e7a;">
                                <i class="bi bi-people fs-1 d-block mb-2 text-muted"></i>
                                <div class="fw-semibold mb-1">Belum Ada Data Owner</div>
                                <small class="text-muted d-block mb-3">Tambahkan pelanggan baru untuk mencatat profil dan jadwal perawatan pasien hewan.</small>
                                <button type="button" class="btn-kreloses-blue btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahOwner">
                                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah Owner Pertama
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @include('components.footer.footer_admin')
    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL TAMBAH OWNER BARU -->
<!-- ========================================================= -->
<div class="modal fade" id="modalTambahOwner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header text-white" style="background-color: #2d8aba; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-person-plus-fill me-2"></i> Tambah Owner / Customer Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('store.owner') }}" method="POST">
                    @csrf

                    <!-- DATA OWNER -->
                    <h6 class="fw-bold mb-3" style="color: #2d8aba;">
                        <i class="bi bi-person-badge me-2"></i> Informasi Akun Pemilik
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Nama Lengkap Owner <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama pemilik" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">No. Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" name="telepon" class="form-control" placeholder="Nomor telepon" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Alamat Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Email (opsional)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Alamat Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="alamat" class="form-control" placeholder="Alamat rumah" required>
                        </div>
                    </div>

                    <!-- DATA PET -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="color: #2d8aba;">
                            <i class="bi bi-heart-pulse-fill me-2"></i> Profil Hewan Peliharaan (Pets)
                        </h6>
                        <button type="button" id="addPetBtn" class="btn btn-sm btn-outline-primary fw-bold">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Hewan
                        </button>
                    </div>

                    <div id="tambah-pet-wrapper">
                        <div class="pet-item border rounded-3 p-3 mb-3 bg-light">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Nama Hewan <span class="text-danger">*</span></label>
                                    <input type="text" name="pets[0][nama]" class="form-control form-control-sm" placeholder="Nama hewan" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Jenis / Spesies</label>
                                    <select name="pets[0][jenis_hewan]" class="form-select form-select-sm">
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
                                    <input type="number" step="0.1" name="pets[0][berat_hewan]" class="form-control form-control-sm" placeholder="Berat">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Ras / Trah</label>
                                    <input type="text" name="pets[0][ras_hewan]" class="form-control form-control-sm" placeholder="Ras">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer px-0 pb-0 pt-3 border-top">
                        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-kreloses-blue px-4 fw-bold">Simpan Owner & Pet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL DETAIL & EDIT OWNER PER ITEM -->
<!-- ========================================================= -->
@foreach($owners as $owner)

<!-- 1. MODAL DETAIL OWNER -->
<div class="modal fade" id="modalDetailOwner-{{ $owner->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header text-white" style="background-color: #2d8aba; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-person-vcard me-2"></i> Detail Owner: {{ $owner->nama }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Nama Lengkap Owner</label>
                        <div class="p-2 border rounded bg-light fw-bold text-dark"><i class="bi bi-person me-1"></i> {{ $owner->nama }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Nomor Telepon / WhatsApp</label>
                        <div class="p-2 border rounded bg-light fw-bold text-dark"><i class="bi bi-telephone me-1"></i> {{ $owner->telepon }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Email</label>
                        <div class="p-2 border rounded bg-light text-dark">{{ $owner->email ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Alamat Lengkap</label>
                        <div class="p-2 border rounded bg-light text-dark">{{ $owner->alamat }}</div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3" style="color: #2d8aba;">
                    <i class="bi bi-heart-pulse-fill me-2"></i> Daftar Pasien Hewan ({{ $owner->pets->count() }} Hewan)
                </h6>
                
                @if($owner->pets->count())
                    <div class="table-responsive">
                        <table class="kreloses-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Hewan</th>
                                    <th>Jenis / Spesies</th>
                                    <th>Ras / Trah</th>
                                    <th>Berat (Kg)</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($owner->pets as $pet)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-bold text-dark">🐾 {{ $pet->nama }}</td>
                                        <td>{{ $pet->jenis_hewan }}</td>
                                        <td style="color: #546e7a;">{{ $pet->ras_hewan ?? '-' }}</td>
                                        <td class="fw-semibold text-dark">{{ $pet->berat_hewan ?? '-' }} kg</td>
                                        <td class="text-end">
                                            <a href="/booking/create/{{ $owner->id }}/{{ $pet->id }}" class="btn btn-sm btn-primary py-1 px-2 fw-semibold" style="font-size: 0.75rem; background: #2d8aba; border-color: #2d8aba;">
                                                <i class="bi bi-calendar-plus me-1"></i> Buat Booking
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-3 text-muted">Belum ada hewan peliharaan terdaftar.</div>
                @endif

                <div class="modal-footer px-0 pb-0 pt-3 border-top mt-3">
                    <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn-kreloses-blue px-3" data-bs-toggle="modal" data-bs-target="#modalEditOwner-{{ $owner->id }}">
                        <i class="bi bi-pencil-square me-1"></i> Edit Owner Ini
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. MODAL EDIT OWNER -->
<div class="modal fade" id="modalEditOwner-{{ $owner->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header text-white" style="background-color: #2d8aba; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-pencil-square me-2"></i> Edit Owner & Pet: {{ $owner->nama }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('owner.update', $owner->id) }}" method="POST">
                    @csrf

                    <!-- DATA OWNER -->
                    <h6 class="fw-bold mb-3" style="color: #2d8aba;">
                        <i class="bi bi-person-badge me-2"></i> Informasi Owner
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Nama Owner <span class="text-danger">*</span></label>
                            <input type="text" name="nama" value="{{ old('nama', $owner->nama) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">No. Telepon / WA <span class="text-danger">*</span></label>
                            <input type="text" name="telepon" value="{{ old('telepon', $owner->telepon) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Email</label>
                            <input type="email" name="email" value="{{ old('email', $owner->email) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Alamat Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="alamat" value="{{ old('alamat', $owner->alamat) }}" class="form-control" required>
                        </div>
                    </div>

                    <!-- DATA PET -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="color: #2d8aba;">
                            <i class="bi bi-heart-pulse-fill me-2"></i> Profil Hewan Peliharaan (Pets)
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold addPetEditBtn" data-owner-id="{{ $owner->id }}">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Hewan
                        </button>
                    </div>

                    <div id="edit-pet-wrapper-{{ $owner->id }}">
                        @foreach($owner->pets as $i => $pet)
                        <div class="pet-item border rounded-3 p-3 mb-3 bg-light">
                            <input type="hidden" name="pets[{{ $i }}][id]" value="{{ $pet->id }}">
                            <div class="text-end mb-2">
                                <button type="button" class="btn btn-danger btn-sm removePet">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </button>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Nama Hewan <span class="text-danger">*</span></label>
                                    <input type="text" name="pets[{{ $i }}][nama]" value="{{ $pet->nama }}" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Jenis / Spesies</label>
                                    <select name="pets[{{ $i }}][jenis_hewan]" class="form-select form-select-sm">
                                        <option value="Kucing" {{ $pet->jenis_hewan == 'Kucing' ? 'selected' : '' }}>Kucing</option>
                                        <option value="Anjing" {{ $pet->jenis_hewan == 'Anjing' ? 'selected' : '' }}>Anjing</option>
                                        <option value="Kelinci" {{ $pet->jenis_hewan == 'Kelinci' ? 'selected' : '' }}>Kelinci</option>
                                        <option value="Burung" {{ $pet->jenis_hewan == 'Burung' ? 'selected' : '' }}>Burung</option>
                                        <option value="Reptil" {{ $pet->jenis_hewan == 'Reptil' ? 'selected' : '' }}>Reptil</option>
                                        <option value="Lainnya" {{ $pet->jenis_hewan == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Berat (Kg)</label>
                                    <input type="number" step="0.1" name="pets[{{ $i }}][berat_hewan]" value="{{ $pet->berat_hewan }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Ras / Trah</label>
                                    <input type="text" name="pets[{{ $i }}][ras_hewan]" value="{{ $pet->ras_hewan }}" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                        @endforeach
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

<script>
    function confirmDeletion(id, name) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: `Kamu ingin menghapus owner "${name}" beserta seluruh data hewan terdaftar`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.createElement('form');
                form.action = `/delete-owner/${id}`;
                form.method = 'post';

                var csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                var methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        let petTambahIndex = 1;
        const addPetBtn = document.getElementById('addPetBtn');
        if (addPetBtn) {
            addPetBtn.addEventListener('click', function() {
                const html = `
                    <div class="pet-item border rounded-3 p-3 mb-3 bg-light">
                        <div class="text-end mb-2">
                            <button type="button" class="btn btn-danger btn-sm removePet">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Nama Hewan <span class="text-danger">*</span></label>
                                <input type="text" name="pets[${petTambahIndex}][nama]" class="form-control form-control-sm" placeholder="Nama hewan" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Jenis / Spesies</label>
                                <select name="pets[${petTambahIndex}][jenis_hewan]" class="form-select form-select-sm">
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
                                <input type="number" step="0.1" name="pets[${petTambahIndex}][berat_hewan]" class="form-control form-control-sm" placeholder="Berat">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Ras / Trah</label>
                                <input type="text" name="pets[${petTambahIndex}][ras_hewan]" class="form-control form-control-sm" placeholder="Ras">
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('tambah-pet-wrapper').insertAdjacentHTML('beforeend', html);
                petTambahIndex++;
            });
        }

        // Add pet in edit modals
        document.querySelectorAll('.addPetEditBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const ownerId = this.getAttribute('data-owner-id');
                const wrapper = document.getElementById(`edit-pet-wrapper-${ownerId}`);
                const count = wrapper.querySelectorAll('.pet-item').length + 10;

                const html = `
                    <div class="pet-item border rounded-3 p-3 mb-3 bg-light">
                        <div class="text-end mb-2">
                            <button type="button" class="btn btn-danger btn-sm removePet">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Nama Hewan <span class="text-danger">*</span></label>
                                <input type="text" name="pets[${count}][nama]" class="form-control form-control-sm" placeholder="Nama hewan" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Jenis / Spesies</label>
                                <select name="pets[${count}][jenis_hewan]" class="form-select form-select-sm">
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
                                <input type="number" step="0.1" name="pets[${count}][berat_hewan]" class="form-control form-control-sm" placeholder="Berat">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Ras / Trah</label>
                                <input type="text" name="pets[${count}][ras_hewan]" class="form-control form-control-sm" placeholder="Ras">
                            </div>
                        </div>
                    </div>
                `;
                wrapper.insertAdjacentHTML('beforeend', html);
            });
        });

        // Dynamic remove pet item
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('removePet') || e.target.closest('.removePet')) {
                const petItem = e.target.closest('.pet-item');
                if (petItem) petItem.remove();
            }
        });
    });
</script>
@endsection