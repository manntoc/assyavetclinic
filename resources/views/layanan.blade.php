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
                        <i class="bi bi-gear-fill me-2"></i>Manajemen Layanan Klinik
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Daftar jenis & tarif layanan medis, dokter, & perawatan
                    </small>
                </div>
            </div>
            <div>
                <button type="button" class="btn btn-primary" style="background:#2d8aba;border-color:#2d8aba;" data-bs-toggle="modal" data-bs-target="#tambahlayanan">
                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah Layanan
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
                            <th width="35%">Nama Layanan</th>
                            <th width="25%">Kategori Layanan</th>
                            <th width="20%">Tarif Layanan</th>
                            <th width="15%" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($layanan as $item)
                        <tr>
                            <td><strong>{{ $loop->iteration }}</strong></td>
                            <td class="fw-semibold text-dark">
                                <i class="bi bi-activity text-primary me-2"></i> {{ $item->nama }}
                            </td>
                            <td>
                                <span class="badge bg-secondary px-2 py-1">
                                    {{ $item->jenis }}
                                </span>
                            </td>
                            <td class="fw-semibold text-dark">
                                Rp {{ number_format($item->harga, 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#updateModalLayanan-{{ $item->id }}" title="Edit Layanan">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border ms-1 text-danger" onclick="confirmDeletion({{ $item->id }}, '{{ $item->nama }}')" title="Hapus Layanan">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5" style="color: #546e7a;">
                                <i class="bi bi-activity fs-1 d-block mb-2 text-muted"></i>
                                <div class="fw-semibold mb-1">Belum Ada Data Layanan</div>
                                <small class="text-muted d-block mb-3">Tambahkan tindakan medis, grooming, atau jasa rawat inap klinik.</small>
                                <button type="button" class="btn-kreloses-blue btn-sm" data-bs-toggle="modal" data-bs-target="#ModalTambahLayanan">
                                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah Layanan Pertama
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
@include('components.modals.tambahlayanan')
@include('components.modals.updatelayanan')

<script>
    function confirmDeletion(id, name) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: `Kamu ingin menghapus "${name}" dari Layanan Klinik`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.createElement('form');
                form.action = `/deletelayanan/${id}`;
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