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
                        <i class="bi bi-person-badge-fill me-2"></i>Manajemen Staf & Dokter
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Kelola akun staf medis, dokter hewan, & resepsionis klinik
                    </small>
                </div>
            </div>
            <div>
                <button type="button" class="btn btn-primary" style="background:#2d8aba;border-color:#2d8aba;" data-bs-toggle="modal" data-bs-target="#createModalCenter">
                    <i class="bi bi-person-plus-fill me-1"></i> Tambah Staf Baru
                </button>
            </div>
        </div>

        <!-- KRELOSES CARD CONTAINER -->
        <div class="kreloses-card mb-4">
            <div class="table-responsive">
                <table class="kreloses-table" id="table1">
                    <thead>
                        <tr>
                            <th width="10%">ID Staf</th>
                            <th width="35%">Nama Staf / Dokter</th>
                            <th width="25%">Email Login</th>
                            <th width="15%">Terakhir Diperbarui</th>
                            <th width="15%" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $data)
                        <tr>
                            <td><strong>#{{ $data->id }}</strong></td>
                            <td class="fw-semibold text-dark">
                                <i class="bi bi-person-badge text-primary me-2"></i> {{ $data->name }}
                            </td>
                            <td class="text-muted">
                                {{ $data->email }}
                            </td>
                            <td class="text-muted small">
                                {{ $data->updated_at ? $data->updated_at->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#updateModalCenter-{{ $data->id }}" title="Edit Staf">
                                        <i class="bi bi-pencil-square text-primary"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border ms-1 text-danger" onclick="confirmDeletion({{ $data->id }}, '{{ $data->name }}')" title="Hapus Staf">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5" style="color: #546e7a;">
                                <i class="bi bi-people fs-1 d-block mb-2 text-muted"></i>
                                <div class="fw-semibold mb-1">Belum Ada Akun Staf</div>
                                <small class="text-muted d-block mb-3">Buat akun untuk dokter hewan, paramedis, atau kasir klinik.</small>
                                <button type="button" class="btn-kreloses-blue btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModalCenter">
                                    <i class="bi bi-person-plus-fill me-1"></i> Tambah Staf Pertama
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- modal -->
@include('components.modals.tambahakun')
@include('components.modals.updateakun')

<script>
    function confirmDeletion(id, name) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: `Kamu ingin menghapus akun staf "${name}"`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.createElement('form');
                form.action = `/deleteakunkaryawan/${id}`;
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
</script>
@endsection