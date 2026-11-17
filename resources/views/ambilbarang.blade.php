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
                        <i class="bi bi-cart-check-fill me-2"></i>Catatan Pengambilan Barang
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Riwayat & transaksi obat/inventori yang diambil untuk pasien
                    </small>
                </div>
            </div>
            <div>
                @if(in_array(auth()->user()->role, ['karyawan', 'admin', 'superadmin']))
                    <button type="button" class="btn btn-primary" style="background:#2d8aba;border-color:#2d8aba;" data-bs-toggle="modal" data-bs-target="#exampleModalCenter">
                        <i class="bi bi-cart-plus-fill me-1"></i> Ambil Barang / Obat
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
                            <th width="30%">Nama Barang / Obat</th>
                            <th width="15%">Jumlah Pengambilan</th>
                            <th width="20%">Total Harga</th>
                            <th width="15%">Terakhir Diperbarui</th>
                            <th width="15%" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transaksi as $item)
                        <tr>
                            <td><strong>{{ $loop->iteration }}</strong></td>
                            <td class="fw-semibold text-dark">
                                <i class="bi bi-capsule me-2" style="color: #546e7a;"></i> {{ $item->Inventori->nama_barang ?? 'Tidak ada nama' }}
                            </td>
                            <td>
                                <span class="badge bg-secondary px-2 py-1">
                                    {{ $item->jumlah }} Unit
                                </span>
                            </td>
                            <td class="fw-semibold text-dark">
                                Rp {{ number_format($item->total, 0, ',', '.') }}
                            </td>
                            <td style="color: #546e7a;" class="small">
                                {{ $item->updated_at ? $item->updated_at->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#updateModal-{{ $item->id }}" title="Edit Pengambilan">
                                        <i class="bi bi-pencil-square text-secondary"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border text-danger ms-1" onclick="confirmDeletion({{ $item->id }}, '{{ $item->Inventori->nama_barang ?? 'Barang' }}')" title="Hapus Catatan">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5" style="color: #546e7a;">
                                <i class="bi bi-cart-x fs-1 d-block mb-2 text-muted"></i>
                                <div class="fw-semibold mb-1">Belum Ada Catatan Pengambilan Barang</div>
                                <small class="text-muted d-block mb-3">Catat penggunaan obat atau barang habis pakai untuk pasien yang sedang dirawat.</small>
                                @if(in_array(auth()->user()->role, ['karyawan', 'admin', 'superadmin']))
                                    <button type="button" class="btn-kreloses-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModalCenter">
                                        <i class="bi bi-cart-plus-fill me-1"></i> Catat Pengambilan Pertama
                                    </button>
                                @endif
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

<!-- MODAL EDIT PENGAMBILAN BARANG -->
@foreach ($transaksi as $item)
<div class="modal fade" id="updateModal-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle-{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header text-white" style="background-color: #546e7a; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <h5 class="modal-title text-white fw-bold" id="exampleModalCenterTitle-{{ $item->id }}">
                    Update Pengambilan Barang
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('updatetransaksi') }}" method="POST">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $item->booking->id ?? '' }}">
                    <input type="hidden" name="inventori_id" value="{{ $item->Inventori->id ?? '' }}">
                    <input type="hidden" id="harga-{{ $item->id }}" value="{{ $item->Inventori->harga ?? 0 }}">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pemilik Booking</label>
                        <input type="text" class="form-control" value="{{ $item->booking->nama ?? 'Tidak ada nama' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Barang</label>
                        <input type="text" class="form-control" value="{{ $item->Inventori->nama_barang ?? 'Tidak ada nama' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pembaruan Kuantitas</label>
                        <select class="form-select" id="selectJumlah-{{ $item->id }}" required>
                            <option value="">Pilih Jenis Perubahan Kuantitas</option>
                            <option value="tambah">Penambahan Kuantitas</option>
                            <option value="kurang">Pengurangan Kuantitas</option>
                        </select>
                    </div>
                    <div class="mb-3" id="penambahanContainer-{{ $item->id }}" style="display: none;">
                        <label class="form-label small fw-bold">Jumlah Penambahan</label>
                        <input type="number" class="form-control" id="penambahan-{{ $item->id }}" name="penambahan" value="0" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Total Penambahan (Rp)</label>
                        <input type="number" class="form-control" id="totalpenambahan-{{ $item->id }}" name="totalpenambahan" value="0" readonly>
                    </div>
                    <div class="mb-3" id="penguranganContainer-{{ $item->id }}" style="display: none;">
                        <label class="form-label small fw-bold">Jumlah Pengurangan</label>
                        <input type="number" class="form-control" id="pengurangan-{{ $item->id }}" name="pengurangan" value="0" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Total Pengurangan (Rp)</label>
                        <input type="number" class="form-control" id="totalpengurangan-{{ $item->id }}" name="totalpengurangan" value="0" readonly>
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

<!-- MODAL TAMBAH PENGAMBILAN -->
@include('components.modals.ambil')

<script>
    function confirmDeletion(itemId, name) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: `Kamu ingin menghapus catatan pengambilan "${name}"`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/delete-item/' + itemId;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        @foreach ($transaksi as $item)
        const selectJumlah{{ $item->id }} = document.getElementById('selectJumlah-{{ $item->id }}');
        const penambahanContainer{{ $item->id }} = document.getElementById('penambahanContainer-{{ $item->id }}');
        const penguranganContainer{{ $item->id }} = document.getElementById('penguranganContainer-{{ $item->id }}');
        const penambahanInput{{ $item->id }} = document.getElementById('penambahan-{{ $item->id }}');
        const penguranganInput{{ $item->id }} = document.getElementById('pengurangan-{{ $item->id }}');
        const totalPenambahan{{ $item->id }} = document.getElementById('totalpenambahan-{{ $item->id }}');
        const totalPengurangan{{ $item->id }} = document.getElementById('totalpengurangan-{{ $item->id }}');
        const hargaEl{{ $item->id }} = document.getElementById('harga-{{ $item->id }}');
        const harga{{ $item->id }} = hargaEl{{ $item->id }} ? parseFloat(hargaEl{{ $item->id }}.value) : 0;

        if (selectJumlah{{ $item->id }}) {
            selectJumlah{{ $item->id }}.addEventListener('change', function () {
                penambahanContainer{{ $item->id }}.style.display = this.value === 'tambah' ? 'block' : 'none';
                penguranganContainer{{ $item->id }}.style.display = this.value === 'kurang' ? 'block' : 'none';
                calculateTotal{{ $item->id }}();
            });
        }

        if (penambahanInput{{ $item->id }}) {
            penambahanInput{{ $item->id }}.addEventListener('input', calculateTotal{{ $item->id }});
        }
        if (penguranganInput{{ $item->id }}) {
            penguranganInput{{ $item->id }}.addEventListener('input', calculateTotal{{ $item->id }});
        }

        function calculateTotal{{ $item->id }}() {
            let jumlah = 0;
            if (selectJumlah{{ $item->id }}.value === 'tambah') {
                jumlah = parseFloat(penambahanInput{{ $item->id }}.value) || 0;
                totalPenambahan{{ $item->id }}.value = jumlah * harga{{ $item->id }};
                totalPengurangan{{ $item->id }}.value = 0;
            } else if (selectJumlah{{ $item->id }}.value === 'kurang') {
                jumlah = parseFloat(penguranganInput{{ $item->id }}.value) || 0;
                totalPengurangan{{ $item->id }}.value = jumlah * harga{{ $item->id }};
                totalPenambahan{{ $item->id }}.value = 0;
            }
        }
        @endforeach
    });
</script>
@endsection