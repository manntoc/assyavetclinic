@extends('layouts.admin')
@section('content')

@include('components.headers.navbar')

<div id="main" class="pt-3">
    <header class="mb-3 d-xl-none">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 px-1">
        <div class="d-flex align-items-center gap-3">
            <button class="sidebar-toggle-btn me-2" title="Buka/Tutup Sidebar">
                <i class="bi bi-layout-sidebar-inset fs-5"></i>
            </button>
            <div>
                <h4 class="fw-bold mb-0" style="color: #2d8aba; font-size: 1.25rem;">
                    <i class="bi bi-instagram me-2"></i>Instagram Content Studio
                </h4>
                <small style="color: #a29f9f; font-size: 0.75rem;">
                    Buat dan kelola konten Instagram klinik dari data aktual
                </small>
            </div>
        </div>
        <div>
            <a href="{{ route('instagram-studio.create') }}" class="btn btn-primary" style="background:#2d8aba;border-color:#2d8aba;">
                <i class="bi bi-plus-circle-fill me-1"></i> Buat Konten Baru
            </a>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-1 mb-3" role="alert" style="border-radius:10px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="kreloses-card mb-4 p-3">
        <form method="GET" action="{{ route('instagram-studio.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6 col-12">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0"
                           placeholder="Cari kode atau topik konten..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3 col-12">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\InstagramContent::$statusLabels as $k => $v)
                        <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Cari</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('instagram-studio.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Content Grid --}}
    @if($contents->isEmpty())
        <div class="kreloses-card p-5 text-center">
            <i class="bi bi-instagram" style="font-size: 3rem; color: #e2e8f0;"></i>
            <p class="mt-3 text-muted fw-medium">Belum ada konten Instagram.</p>
            <a href="{{ route('instagram-studio.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-plus-circle-fill me-1"></i> Buat Konten Pertama
            </a>
        </div>
    @else
        <div class="row g-3 mb-4">
            @foreach($contents as $item)
                <div class="col-md-6 col-lg-4">
                    <div class="kreloses-card p-3 h-100 d-flex flex-column">
                        {{-- Status badge --}}
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge" style="background: {{ $item->status_color }}; font-size: 0.7rem; padding: 4px 10px; border-radius: 8px;">
                                {{ $item->status_label }}
                            </span>
                            <small class="text-muted" style="font-size: 0.7rem;">{{ $item->kode_konten }}</small>
                        </div>

                        {{-- Topic --}}
                        <h6 class="fw-semibold mb-1" style="font-size: 0.9rem; color: #1e293b;">
                            {{ $item->topic }}
                        </h6>

                        {{-- Meta --}}
                        <div class="mb-2" style="font-size: 0.75rem; color: #64748b;">
                            <i class="bi bi-layers me-1"></i>{{ $item->slide_count }} slide
                            &nbsp;&bull;&nbsp;
                            <i class="bi bi-tag me-1"></i>{{ $item->content_type }}
                            &nbsp;&bull;&nbsp;
                            <i class="bi bi-person me-1"></i>{{ $item->creator?->name ?? 'N/A' }}
                        </div>

                        <small class="text-muted d-block mb-3" style="font-size: 0.7rem;">
                            {{ $item->created_at->format('d M Y H:i') }}
                        </small>

                        {{-- Actions --}}
                        <div class="mt-auto d-flex gap-2">
                            <a href="{{ route('instagram-studio.preview', $item->id) }}"
                               class="btn btn-sm btn-primary flex-fill" style="background:#2d8aba;border-color:#2d8aba;">
                                <i class="bi bi-eye me-1"></i>Preview
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-content"
                                    data-id="{{ $item->id }}" data-topic="{{ $item->topic }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        {{ $contents->links() }}
    @endif
</div>

{{-- Delete forms (hidden) --}}
@foreach($contents as $item)
    <form id="delete-form-{{ $item->id }}" method="POST" action="{{ route('instagram-studio.destroy', $item->id) }}" style="display:none;">
        @csrf @method('DELETE')
    </form>
@endforeach

<script>
document.querySelectorAll('.btn-delete-content').forEach(btn => {
    btn.addEventListener('click', function() {
        const id    = this.dataset.id;
        const topic = this.dataset.topic;
        Swal.fire({
            title: 'Hapus Konten?',
            html: `Konten "<b>${topic}</b>" akan dihapus permanen termasuk semua slide.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    });
});
</script>

@endsection
