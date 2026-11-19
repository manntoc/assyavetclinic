{{--
    Partial: slide_editor
    One editable card per slide.
    Variables: $slide, $content
--}}
@php
    $purposeLabels = \App\Models\InstagramContentSlide::$purposeLabels;
    $layouts = [
        'hero'        => 'A — Hero Image (Cover / Hook Visual)',
        'text_focus'  => 'B — Text Focus (Edukasi Terstruktur)',
        'split'       => 'C — Split Layout (Foto & Teks Sejajar)',
        'case_study'  => 'D — Case Study (Navy Klinis & Poin)',
        'educational' => 'E — Educational (Grid Tips & Kartu)',
        'cta'         => 'F — Call to Action (Penutup / Kontak)',
    ];

    // Normalisasi layout saat ini agar dropdown selalu terpilih dengan benar
    $currentNormalized = match($slide->layout) {
        'hook_center', 'hero'          => 'hero',
        'white_top_blue', 'text_focus' => 'text_focus',
        'split_reverse', 'split'       => 'split',
        'navy_full', 'case_study'      => 'case_study',
        'card_grid', 'educational'     => 'educational',
        'cta_full', 'cta'              => 'cta',
        default                        => ($slide->purpose === 'cta' ? 'cta' : 'hero'),
    };
@endphp

<div class="slide-editor-card" id="editor-slide-{{ $slide->id }}" data-slide-id="{{ $slide->id }}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge" style="background:#2d8aba;font-size:0.7rem;border-radius:6px;padding:3px 8px;">
                Slide {{ $slide->slide_number }}
            </span>
            <small class="text-muted fw-medium" style="font-size:0.78rem;">
                {{ $purposeLabels[$slide->purpose] ?? $slide->purpose }}
            </small>
        </div>
        <div class="d-flex gap-1">
            <button class="btn btn-outline-secondary btn-sm btn-regen-slide"
                    data-slide-id="{{ $slide->id }}"
                    style="border-radius:6px;font-size:0.75rem;padding:3px 8px;"
                    title="Regenerate slide ini">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
            <button class="btn btn-outline-danger btn-sm btn-remove-slide"
                    data-slide-id="{{ $slide->id }}"
                    data-slide-num="{{ $slide->slide_number }}"
                    style="border-radius:6px;font-size:0.75rem;padding:3px 8px;"
                    title="Hapus slide ini">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>

    {{-- Layout selector --}}
    <div class="mb-2">
        <label class="form-label mb-1 text-muted" style="font-size:0.72rem;font-weight:600;">Tata Letak / Template:</label>
        <select class="form-select form-select-sm slide-layout-select"
                data-slide-id="{{ $slide->id }}"
                style="font-size:0.78rem;border-radius:6px;">
            @foreach($layouts as $k => $v)
                <option value="{{ $k }}" {{ $currentNormalized === $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>

    {{-- Headline --}}
    <div class="mb-2">
        <label class="form-label mb-1 text-muted" style="font-size:0.72rem;font-weight:600;">Headline:</label>
        <input type="text"
               class="form-control form-control-sm slide-headline-input"
               data-slide-id="{{ $slide->id }}"
               placeholder="Headline slide..."
               value="{{ $slide->headline }}"
               style="font-size:0.85rem;border-radius:6px;font-weight:600;">
    </div>

    {{-- Body --}}
    <div class="mb-2">
        <label class="form-label mb-1 text-muted" style="font-size:0.72rem;font-weight:600;">Isi Teks / Poin:</label>
        <textarea class="form-control form-control-sm slide-body-input"
                  data-slide-id="{{ $slide->id }}"
                  placeholder="Isi teks slide..."
                  rows="3"
                  style="font-size:0.82rem;border-radius:6px;">{{ $slide->body }}</textarea>
    </div>

    {{-- Image upload --}}
    <div class="d-flex align-items-center gap-2">
        <label class="btn btn-outline-secondary btn-sm mb-0"
               for="img-upload-{{ $slide->id }}"
               style="border-radius:6px;font-size:0.78rem;cursor:pointer;">
            <i class="bi bi-image me-1"></i>
            {{ !empty($slide->image_path) ? 'Ganti Foto' : 'Upload Foto' }}
        </label>
        <input type="file" id="img-upload-{{ $slide->id }}"
               class="slide-image-upload d-none"
               data-slide-id="{{ $slide->id }}"
               accept="image/jpeg,image/png,image/webp">

        @if(!empty($slide->image_path))
            <small class="text-success" style="font-size:0.72rem;">
                <i class="bi bi-check-circle me-1"></i>Foto terpasang
            </small>
        @else
            <small class="text-muted" style="font-size:0.72rem;">Foto opsional, JPEG/PNG/WebP max 5MB</small>
        @endif
    </div>

    {{-- Auto-save indicator --}}
    <div class="slide-save-status mt-2" id="save-status-{{ $slide->id }}" style="display:none;">
        <small class="text-success" style="font-size:0.72rem;">
            <i class="bi bi-check-circle me-1"></i>Tersimpan
        </small>
    </div>
</div>
