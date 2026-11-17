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
                    <i class="bi bi-instagram me-2"></i>Buat Konten Instagram Baru
                </h4>
                <small style="color: #a29f9f; font-size: 0.75rem;">
                    Pilih sumber data, topik, dan generate konten dengan AI
                </small>
            </div>
        </div>
        <div>
            <a href="{{ route('instagram-studio.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Error flash --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mx-1 mb-3" role="alert" style="border-radius:10px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('instagram-studio.generate') }}" id="generate-form">
        @csrf

        <div class="row g-4">

            {{-- LEFT: Configuration --}}
            <div class="col-lg-7">

                {{-- Sumber Konten --}}
                <div class="kreloses-card p-4 mb-4">
                    <h6 class="fw-semibold mb-3" style="color:#2d8aba; font-size:0.95rem;">
                        <i class="bi bi-database me-2"></i>Sumber Konten
                    </h6>

                    <div class="row g-2 mb-3">
                        @foreach($sourceTypes as $key => $label)
                            <div class="col-6 col-md-4">
                                <input type="radio" class="btn-check source-type-radio" name="source_type"
                                       id="st_{{ $key }}" value="{{ $key }}"
                                       {{ ($key === 'edukasi' || old('source_type') === $key) ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary w-100 text-start" for="st_{{ $key }}"
                                       style="font-size:0.8rem; border-radius:8px; padding: 8px 12px;">
                                    <i class="bi {{ match($key) {
                                        'kasus_klinik' => 'bi-clipboard2-pulse',
                                        'edukasi'      => 'bi-book',
                                        'layanan'      => 'bi-heart-pulse',
                                        'promo'        => 'bi-megaphone',
                                        'custom'       => 'bi-pencil-square',
                                        default        => 'bi-circle'
                                    } }} me-1"></i>{{ $label }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    {{-- Source: Kasus Klinik --}}
                    <div id="panel-kasus_klinik" class="source-panel d-none">
                        <label class="form-label small fw-medium">Pilih Kasus (opsional — jika kosong AI buat konten umum):</label>
                        <select name="source_id" class="form-select choices" style="font-size:0.85rem;">
                            <option value="">Konten Edukasi Umum</option>
                            @foreach($bookings as $b)
                                <option value="{{ $b->id }}" {{ old('source_id') == $b->id ? 'selected' : '' }}>
                                    {{ \Illuminate\Support\Str::limit($b->assessment, 60) }}
                                    — {{ $b->jenis_hewan }} ({{ \Carbon\Carbon::parse($b->tanggal)->format('d M Y') }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted mt-1 d-block">
                            <i class="bi bi-shield-check me-1"></i>
                            Data pasien dianonimkan: nama pemilik, nomor telepon, dan data pribadi tidak digunakan.
                        </small>
                    </div>

                    {{-- Source: Layanan --}}
                    <div id="panel-layanan" class="source-panel d-none">
                        <label class="form-label small fw-medium">Pilih Layanan (opsional):</label>
                        <select name="source_id" class="form-select choices" style="font-size:0.85rem;">
                            <option value="">Semua Layanan</option>
                            @foreach($layanans as $l)
                                <option value="{{ $l->id }}">{{ $l->nama }} ({{ $l->jenis }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Topik & Konfigurasi --}}
                <div class="kreloses-card p-4 mb-4">
                    <h6 class="fw-semibold mb-3" style="color:#2d8aba; font-size:0.95rem;">
                        <i class="bi bi-sliders me-2"></i>Konfigurasi Konten
                    </h6>

                    <div class="mb-3">
                        <label class="form-label fw-medium" style="font-size:0.875rem;">
                            Topik Konten <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="topic" class="form-control @error('topic') is-invalid @enderror"
                               placeholder="Tulis topik atau ide konten..."
                               value="{{ old('topic') }}" required style="font-size:0.875rem;">
                        <small class="text-muted">Jelaskan topik dengan jelas — semakin spesifik, semakin baik konten yang dihasilkan.</small>
                        @error('topic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium" style="font-size:0.875rem;">Tipe Konten</label>
                            <select name="content_type" class="form-select" style="font-size:0.875rem;">
                                @foreach($contentTypes as $k => $v)
                                    <option value="{{ $k }}" {{ old('content_type') === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium" style="font-size:0.875rem;">Nada Tulisan</label>
                            <select name="tone" class="form-select" style="font-size:0.875rem;">
                                @foreach($tones as $k => $v)
                                    <option value="{{ $k }}" {{ old('tone') === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Slide & Template --}}
                <div class="kreloses-card p-4 mb-4">
                    <h6 class="fw-semibold mb-3" style="color:#2d8aba; font-size:0.95rem;">
                        <i class="bi bi-collection me-2"></i>Slide & Layout
                    </h6>

                    <label class="form-label fw-medium" style="font-size:0.875rem;">Jumlah Slide</label>
                    <div class="d-flex gap-2 mb-4">
                        @foreach([1,2,3,4,5] as $n)
                            <input type="radio" class="btn-check" name="slide_count" id="sc_{{ $n }}"
                                   value="{{ $n }}" {{ (old('slide_count', 3) == $n) ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="sc_{{ $n }}"
                                   style="width:48px; height:48px; border-radius:10px; font-size:1rem; font-weight:700; display:flex;align-items:center;justify-content:center; border-color:#2d8aba; color:#2d8aba;">
                                {{ $n }}
                            </label>
                        @endforeach
                    </div>

                    <label class="form-label fw-medium" style="font-size:0.875rem;">Template Layout</label>
                    <div class="row g-2">
                        @foreach($templates as $k => $v)
                            <div class="col-6 col-md-4">
                                <input type="radio" class="btn-check" name="template_style"
                                       id="tpl_{{ $k }}" value="{{ $k }}"
                                       {{ ($k === 'auto' || old('template_style') === $k) ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary w-100 text-start" for="tpl_{{ $k }}"
                                       style="font-size:0.78rem; border-radius:8px; padding:8px 12px;">
                                    {{ $v }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- RIGHT: Brand Preview + Generate Button --}}
            <div class="col-lg-5">
                <div class="kreloses-card p-4 mb-4" style="position:sticky;top:80px;">
                    <h6 class="fw-semibold mb-3" style="color:#2d8aba; font-size:0.95rem;">
                        <i class="bi bi-badge-ad me-2"></i>Brand Klinik
                    </h6>

                    <div class="d-flex align-items-center gap-3 mb-3 p-3"
                         style="background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;">
                        @if(!empty($brand['logo_url']))
                            <img src="{{ $brand['logo_url'] }}" alt="Logo Klinik"
                                 style="height:48px; object-fit:contain; max-width:80px;">
                        @else
                            <div style="width:48px;height:48px;background:#2d8aba;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-heart-pulse-fill text-white" style="font-size:22px;"></i>
                            </div>
                        @endif
                        <div>
                            <div class="fw-semibold" style="font-size:0.9rem; color:#1e293b;">{{ $brand['clinic_name'] }}</div>
                            @if(!empty($brand['tagline']))
                                <small class="text-muted">{{ $brand['tagline'] }}</small>
                            @endif
                        </div>
                    </div>

                    @if(!empty($brand['phone']))
                        <small class="text-muted d-block mb-1"><i class="bi bi-telephone me-1"></i>{{ $brand['phone'] }}</small>
                    @endif
                    @if(!empty($brand['email']))
                        <small class="text-muted d-block mb-3"><i class="bi bi-envelope me-1"></i>{{ $brand['email'] }}</small>
                    @endif

                    <hr>

                    {{-- Slide flow preview --}}
                    <div class="mb-3" id="slide-flow-preview">
                        <small class="fw-semibold text-muted d-block mb-2" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">
                            Alur Storytelling
                        </small>
                        <div id="flow-pills" class="d-flex flex-wrap gap-1"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold" id="btn-generate"
                            style="background:#2d8aba;border-color:#2d8aba; border-radius:10px; font-size:0.95rem;">
                        <i class="bi bi-stars me-2"></i>Generate dengan AI
                    </button>
                    <small class="text-muted d-block mt-2 text-center" style="font-size:0.72rem;">
                        Proses membutuhkan 5-15 detik tergantung koneksi AI
                    </small>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Source type panel toggle
const sourceRadios = document.querySelectorAll('.source-type-radio');
const panels = document.querySelectorAll('.source-panel');

function toggleSourcePanel() {
    const active = document.querySelector('.source-type-radio:checked')?.value;
    panels.forEach(p => p.classList.add('d-none'));
    const target = document.getElementById('panel-' + active);
    if (target) target.classList.remove('d-none');
}

sourceRadios.forEach(r => r.addEventListener('change', toggleSourcePanel));
toggleSourcePanel();

// Slide count flow preview
const purposeMap = {
    1: ['hook'],
    2: ['hook', 'cta'],
    3: ['hook', 'solution', 'cta'],
    4: ['hook', 'problem', 'solution', 'cta'],
    5: ['hook', 'problem', 'solution', 'tips', 'cta'],
};
const purposeLabels = {
    hook: 'Hook', problem: 'Masalah', solution: 'Solusi', tips: 'Tips', cta: 'CTA'
};
const purposeColors = {
    hook: '#2d8aba', problem: '#f59e0b', solution: '#10b981', tips: '#0284c7', cta: '#546e7a'
};

function updateFlowPreview() {
    const count = parseInt(document.querySelector('input[name="slide_count"]:checked')?.value || 3);
    const pills = purposeMap[count] || [];
    const container = document.getElementById('flow-pills');
    container.innerHTML = pills.map((p, i) =>
        `<span class="badge" style="background:${purposeColors[p]};font-size:0.7rem;border-radius:6px;padding:4px 10px;">${i+1}. ${purposeLabels[p]}</span>`
        + (i < pills.length - 1 ? '<span class="text-muted" style="font-size:0.7rem;align-self:center;">→</span>' : '')
    ).join('');
}

document.querySelectorAll('input[name="slide_count"]').forEach(r => r.addEventListener('change', updateFlowPreview));
updateFlowPreview();

// Generate button loading state
document.getElementById('generate-form').addEventListener('submit', function() {
    const btn = document.getElementById('btn-generate');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
});
</script>

@endsection
