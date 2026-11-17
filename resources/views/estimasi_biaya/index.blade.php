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
                        <i class="bi bi-calculator-fill me-2"></i>Nota Perkiraan Biaya (Cost Estimation)
                    </h4>
                    <small style="color: #a29f9f; font-size: 0.75rem;">
                        Kelola dan cetak estimasi harga perawatan tanpa memotong stok inventori
                    </small>
                </div>
            </div>
            <div>
                <a href="{{ route('estimasi-biaya.create') }}" class="btn btn-primary" style="background:#2d8aba;border-color:#2d8aba;">
                    <i class="bi bi-plus-circle-fill me-1"></i> Buat Nota Perkiraan Baru
                </a>
            </div>
        </div>

        <!-- SEARCH BAR CARD -->
        <div class="kreloses-card mb-4 p-3">
            <form action="{{ route('estimasi-biaya.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9 col-12">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            class="form-control border-start-0 ps-0 text-dark"
                            placeholder="Cari estimasi..."
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>
                <div class="col-md-3 col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        Cari Nota
                    </button>
                    @if(request('search'))
                        <a href="{{ route('estimasi-biaya.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- KRELOSES TABLE CARD -->
        <div class="kreloses-card mb-4">
            <div class="table-responsive">
                <table class="kreloses-table">
                    <thead>
                        <tr>
                            <th width="18%">Kode & Tanggal</th>
                            <th width="20%">Nama Owner</th>
                            <th width="18%">Pasien Hewan</th>
                            <th width="16%">Dokter / Staf</th>
                            <th width="10%">Item</th>
                            <th width="18%" class="text-end">Est. Total Biaya</th>
                            <th width="12%" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($estimasis as $item)
                            <tr>
                                <td>
                                    <div class="fw-bold" style="color: #2d8aba;">{{ $item->kode_estimasi }}</div>
                                    <small style="color: #546e7a;">
                                        <i class="bi bi-clock me-1"></i>{{ $item->created_at ? $item->created_at->format('d M Y H:i') : '-' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><i class="bi bi-person-fill text-secondary me-1"></i>{{ $item->nama_owner }}</div>
                                    @if($item->telepon)
                                        <small style="color: #546e7a;"><i class="bi bi-telephone me-1"></i>{{ $item->telepon }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        🐾 {{ $item->nama_hewan ?: 'Pasien Hewan' }}
                                    </div>
                                    @if($item->jenis_hewan)
                                        <small style="color: #546e7a;">
                                            {{ $item->jenis_hewan }}{{ $item->berat_hewan ? ' • ' . $item->berat_hewan . ' kg' : '' }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">{{ $item->dokter ?: '-' }}</div>
                                    <small style="color: #546e7a;">Oleh: {{ $item->created_by ?: 'Admin' }}</small>
                                </td>
                                <td>
                                    @php
                                        $itemsList = is_array($item->items) ? $item->items : [];
                                        $totalCount = count($itemsList);
                                        $layananCount = collect($itemsList)->where('type', 'layanan')->count();
                                        $obatCount = collect($itemsList)->where('type', 'obat')->count();
                                    @endphp
                                    <div class="fw-semibold text-dark">{{ $totalCount }} Item</div>
                                    @if($totalCount > 0)
                                        <small style="color: #546e7a; font-size: 0.75rem;">
                                            @if($layananCount > 0)
                                                {{ $layananCount }} Layanan
                                            @endif
                                            @if($layananCount > 0 && $obatCount > 0)
                                                •
                                            @endif
                                            @if($obatCount > 0)
                                                {{ $obatCount }} Obat
                                            @endif
                                        </small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold fs-6" style="color: #2d8aba;">
                                        Rp {{ number_format($item->grand_total, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('estimasi-biaya.pdf', $item->id) }}" target="_blank" class="btn btn-sm btn-light border" title="Cetak / Preview PDF">
                                            <i class="bi bi-printer text-primary"></i>
                                        </a>
                                        <a href="{{ route('estimasi-biaya.edit', $item->id) }}" class="btn btn-sm btn-light border ms-1" title="Edit Nota">
                                            <i class="bi bi-pencil-square text-warning"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-light border text-danger ms-1" onclick="confirmDeleteEstimasi({{ $item->id }}, '{{ $item->kode_estimasi }}')" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $item->id }}" action="{{ route('estimasi-biaya.destroy', $item->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-file-earmark-text fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada data Nota Perkiraan Biaya. Klik <strong>+ Buat Nota Perkiraan Baru</strong> untuk memulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($estimasis->hasPages())
                <div class="p-3 border-top bg-light">
                    {{ $estimasis->links() }}
                </div>
            @endif
        </div>

        @include('components.footer.footer_admin')
    </div>
</div>

<script>
    function confirmDeleteEstimasi(id, kode) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: `Hapus nota perkiraan biaya "${kode}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-form-${id}`).submit();
            }
        });
    }
</script>
@endsection
