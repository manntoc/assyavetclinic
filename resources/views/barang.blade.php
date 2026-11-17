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
                        <i class="bi bi-box-seam me-2"></i>Inventaris & Stok Barang
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Manajemen stok obat, vaksin, & produk retail klinik
                    </small>
                </div>
            </div>
            <div>
                @if (Auth::check() && (Auth::user()->role == 'admin' || Auth::user()->role == 'superadmin'))
                    <button type="button" class="btn btn-primary" style="background:#2d8aba;border-color:#2d8aba;" data-bs-toggle="modal" data-bs-target="#ModalTambahBarang">
                        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Barang
                    </button>
                @endif
            </div>
        </div>

        <!-- KRELOSES CARD CONTAINER -->
        <div class="kreloses-card mb-4">
            <div class="table-responsive">
                <table class="kreloses-table" id="table1">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="35%">Nama Barang / Obat</th>
                            <th width="15%">Stok</th>
                            <th width="20%">Harga Satuan</th>
                            <th width="15%">Terakhir Diperbarui</th>
                            @if (Auth::check() && (Auth::user()->role == 'admin' || Auth::user()->role == 'superadmin'))
                                <th width="10%" class="text-end">Aksi</th>
                            @endif                               
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inventori as $item)
                        <tr>
                            <td><strong>{{ $loop->iteration }}</strong></td>
                            <td class="fw-semibold text-dark">
                                <i class="bi bi-box-seam text-secondary me-2"></i> {{ $item->nama_barang }}
                            </td>
                            <td>
                                <span class="badge {{ $item->jumlah < 10 ? 'bg-danger' : 'bg-secondary' }} px-2 py-1">
                                    {{ $item->jumlah }} Unit
                                </span>
                            </td>
                            <td class="fw-semibold text-dark">
                                Rp {{ number_format($item->harga, 0, ',', '.') }}
                            </td>
                            <td class="text-muted small">
                                {{ $item->updated_at ? $item->updated_at->format('d/m/Y') : '-' }}
                            </td>
                            @if (Auth::check() && (Auth::user()->role == 'admin' || Auth::user()->role == 'superadmin'))
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#updateModal-{{ $item->id }}" title="Edit Barang">
                                            <i class="bi bi-pencil-square text-secondary"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border ms-1 text-danger" onclick="confirmDeletion({{ $item->id }}, '{{ $item->nama_barang }}')" title="Hapus Barang">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ (Auth::check() && (Auth::user()->role == 'admin' || Auth::user()->role == 'superadmin')) ? 6 : 5 }}" class="text-center py-5" style="color: #546e7a;">
                                <i class="bi bi-box-seam fs-1 d-block mb-2 text-muted"></i>
                                <div class="fw-semibold mb-1">Belum Ada Data Inventori</div>
                                <small class="text-muted d-block mb-3">Tambahkan obat atau barang medis baru untuk memulai pencatatan stok apotek.</small>
                                @if (Auth::check() && (Auth::user()->role == 'admin' || Auth::user()->role == 'superadmin'))
                                    <button type="button" class="btn-kreloses-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#ModalTambahBarang">
                                        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Barang Pertama
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
@include('components.modals.tambahbarang')
@include('components.modals.updatebarang')

<script>
    function confirmDeletion(id, name) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: `Kamu ingin menghapus "${name}" dari Inventori`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.createElement('form');
                form.action = `/deletebarang/${id}`;
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