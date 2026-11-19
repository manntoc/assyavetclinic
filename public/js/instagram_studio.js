/**
 * Instagram Content Studio — Frontend Logic
 * Handles: slide nav, inline edit with autosave, regenerate, add/remove slide,
 * caption save, caption regenerate, submit review, approve, publish.
 */

'use strict';

// ─── Utilities ────────────────────────────────────────────────────────────────

function apiFetch(url, method, body) {
    const opts = {
        method: method || 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
    };
    if (body) opts.body = JSON.stringify(body);
    return fetch(url, opts).then(r => r.json());
}

function route(name, params) {
    let url = ROUTES[name];
    if (params) {
        Object.keys(params).forEach(k => {
            url = url.replace('{' + k + '}', params[k]);
        });
    }
    return url;
}

function showToast(msg, type) {
    type = type || 'success';
    const color = type === 'success' ? '#10b981' : '#ef4444';
    const div = document.createElement('div');
    div.style.cssText = `position:fixed;bottom:24px;left:24px;z-index:99999;background:${color};color:#fff;padding:10px 18px;border-radius:10px;font-size:0.85rem;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.15);transition:opacity 0.3s;max-width:320px;`;
    div.textContent = msg;
    document.body.appendChild(div);
    setTimeout(() => { div.style.opacity = '0'; setTimeout(() => div.remove(), 300); }, 3000);
}

function setLoading(btn, yes) {
    if (!btn) return;
    if (yes) {
        btn.dataset.origHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
        btn.disabled = true;
    } else {
        btn.innerHTML = btn.dataset.origHtml || btn.innerHTML;
        btn.disabled = false;
    }
}

// ─── Slide Carousel Navigation ─────────────────────────────────────────────────

let currentSlide = 0;
const slides = Array.from(document.querySelectorAll('.ig-slide-preview'));
const dots   = Array.from(document.querySelectorAll('.ig-nav-dot'));
const counter = document.getElementById('slide-counter');

function goToSlide(idx) {
    if (idx < 0 || idx >= slides.length) return;
    slides.forEach((s, i) => s.style.display = i === idx ? '' : 'none');
    dots.forEach((d, i) => d.classList.toggle('active', i === idx));
    currentSlide = idx;
    if (counter) counter.textContent = `Slide ${idx + 1} / ${slides.length}`;

    // Highlight corresponding editor
    document.querySelectorAll('.slide-editor-card').forEach(c => c.classList.remove('active-slide'));
    const activeSlideId = slides[idx]?.dataset.slideId;
    if (activeSlideId) {
        const ed = document.getElementById('editor-slide-' + activeSlideId);
        if (ed) {
            ed.classList.add('active-slide');
            ed.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
}

document.getElementById('prev-slide')?.addEventListener('click', () => goToSlide(currentSlide - 1));
document.getElementById('next-slide')?.addEventListener('click', () => goToSlide(currentSlide + 1));
dots.forEach((d, i) => d.addEventListener('click', () => goToSlide(i)));
goToSlide(0);

// ─── Inline Slide Edit (debounced autosave) ─────────────────────────────────

let saveTimers = {};

function scheduleSlideAutosave(slideId) {
    clearTimeout(saveTimers[slideId]);
    saveTimers[slideId] = setTimeout(() => saveSlide(slideId), 1000);
}

function saveSlide(slideId, forceReRender = false) {
    const headline = document.querySelector(`.slide-headline-input[data-slide-id="${slideId}"]`)?.value;
    const body     = document.querySelector(`.slide-body-input[data-slide-id="${slideId}"]`)?.value;
    const layout   = document.querySelector(`.slide-layout-select[data-slide-id="${slideId}"]`)?.value;

    apiFetch(route('updateSlide', { slideId }), 'PUT', { headline, body, layout })
        .then(res => {
            if (res.success) {
                const status = document.getElementById('save-status-' + slideId);
                if (status) { status.style.display = ''; setTimeout(() => status.style.display = 'none', 2000); }
                
                if (forceReRender) {
                    // Re-render canvas HTML immediately when layout changes
                    const canvasRoute = route('slideCanvas', { slideId });
                    fetch(canvasRoute, {
                        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(cr => {
                        if (cr.success && cr.html) {
                            const wrapper = document.querySelector(`.ig-slide-preview[data-slide-id="${slideId}"]`);
                            if (wrapper) wrapper.innerHTML = cr.html;
                        }
                    })
                    .catch(() => {});
                } else {
                    // Update canvas text live
                    updateCanvasLive(slideId, headline, body);
                }
            }
        })
        .catch(() => showToast('Gagal menyimpan slide.', 'error'));
}

function updateCanvasLive(slideId, headline, body) {
    const canvas = document.querySelector(`.ig-slide-preview[data-slide-id="${slideId}"]`);
    if (!canvas) return;
    const h = canvas.querySelector('.ig-headline, .ig-hc-headline, .ig-nf-headline, .ig-wtb-headline, .ig-cg-headline, .ig-sr-headline, .ig-cf-headline');
    if (h) h.textContent = headline || '';
}

document.querySelectorAll('.slide-headline-input, .slide-body-input').forEach(el => {
    el.addEventListener('input', () => scheduleSlideAutosave(el.dataset.slideId));
});

document.querySelectorAll('.slide-layout-select').forEach(el => {
    el.addEventListener('change', () => saveSlide(el.dataset.slideId, true));
});

// ─── Regenerate Single Slide ──────────────────────────────────────────────────

document.querySelectorAll('.btn-regen-slide').forEach(btn => {
    btn.addEventListener('click', function() {
        const slideId = this.dataset.slideId;
        if (!confirm('Regenerate slide ini? Teks saat ini akan diganti oleh AI.')) return;
        setLoading(this, true);
        const origBtn = this;

        apiFetch(route('regenSlide', { slideId }), 'POST')
            .then(res => {
                if (!res.success) {
                    setLoading(origBtn, false);
                    showToast(res.error || 'Gagal regenerate slide.', 'error');
                    return;
                }
                // Update editor form inputs
                const hInput = document.querySelector(`.slide-headline-input[data-slide-id="${slideId}"]`);
                const bInput = document.querySelector(`.slide-body-input[data-slide-id="${slideId}"]`);
                if (hInput) hInput.value = res.slide.headline || '';
                if (bInput) bInput.value = res.slide.body || '';

                // Fetch re-rendered canvas HTML (layout mungkin berubah, harus swap innerHTML)
                const canvasRoute = route('slideCanvas', { slideId });
                fetch(canvasRoute, {
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(cr => {
                    setLoading(origBtn, false);
                    if (cr.success && cr.html) {
                        const wrapper = document.querySelector(`.ig-slide-preview[data-slide-id="${slideId}"]`);
                        if (wrapper) wrapper.innerHTML = cr.html;
                    }
                    showToast('Slide berhasil diregenerasi.');
                })
                .catch(() => {
                    setLoading(origBtn, false);
                    showToast('Slide diregenerasi. Refresh untuk lihat layout baru.');
                });
            })
            .catch(() => { setLoading(origBtn, false); showToast('Gagal regenerate slide.', 'error'); });
    });
});

// ─── Remove Slide ─────────────────────────────────────────────────────────────

document.querySelectorAll('.btn-remove-slide').forEach(btn => {
    btn.addEventListener('click', function() {
        const slideId  = this.dataset.slideId;
        const slideNum = this.dataset.slideNum;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Slide ' + slideNum + '?',
                text: 'Slide ini akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then(result => { if (result.isConfirmed) doRemoveSlide(slideId); });
        } else {
            if (confirm('Hapus slide ' + slideNum + '?')) doRemoveSlide(slideId);
        }
    });
});

function doRemoveSlide(slideId) {
    const url = '/instagram-studio/' + CONTENT_ID + '/slides/' + slideId;
    fetch(url, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showToast('Slide dihapus. Memuat ulang...');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(res.error || 'Gagal hapus slide.', 'error');
        }
    });
}

// ─── Add Slide ────────────────────────────────────────────────────────────────

document.getElementById('btn-add-slide')?.addEventListener('click', function() {
    setLoading(this, true);
    const btn = this;
    apiFetch(route('addSlide'), 'POST')
        .then(res => {
            setLoading(btn, false);
            if (res.success) {
                showToast('Slide ditambahkan. Memuat ulang...');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(res.error || 'Gagal tambah slide.', 'error');
            }
        })
        .catch(() => { setLoading(btn, false); showToast('Gagal tambah slide.', 'error'); });
});

// ─── Image Upload ─────────────────────────────────────────────────────────────

document.querySelectorAll('.slide-image-upload').forEach(input => {
    input.addEventListener('change', function() {
        const slideId = this.dataset.slideId;
        const file    = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('image', file);
        formData.append('_token', CSRF_TOKEN);

        fetch('/instagram-studio/' + CONTENT_ID + '/slides/' + slideId + '/image', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData,
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('Foto berhasil diupload.');
                // Update canvas image live
                const canvas = document.querySelector(`.ig-slide-preview[data-slide-id="${slideId}"]`);
                if (canvas) {
                    const imgArea = canvas.querySelector('.ig-image-area');
                    if (imgArea) {
                        imgArea.innerHTML = `<img src="${res.image_url}" alt="" style="width:100%;height:100%;object-fit:cover;">`;
                    }
                    // For hero layout update background
                    if (canvas.classList.contains('ig-layout-hero')) {
                        canvas.style.background = `url('${res.image_url}') center/cover no-repeat`;
                    }
                }
            } else {
                showToast('Gagal upload foto.', 'error');
            }
        });
    });
});

// ─── Caption: char count & save ───────────────────────────────────────────────

const captionInput  = document.getElementById('caption-input');
const hashtagsInput = document.getElementById('hashtags-input');
const captionCount  = document.getElementById('caption-char-count');
const hashtagCount  = document.getElementById('hashtag-count');

function updateCaptionCounts() {
    if (captionInput && captionCount)
        captionCount.textContent = (captionInput.value || '').length;
    if (hashtagsInput && hashtagCount) {
        const tags = (hashtagsInput.value || '').split(/[\s,]+/).filter(t => t.trim().length > 0);
        hashtagCount.textContent = tags.length;
    }
}
captionInput?.addEventListener('input', updateCaptionCounts);
hashtagsInput?.addEventListener('input', updateCaptionCounts);
updateCaptionCounts();

document.getElementById('btn-save-caption')?.addEventListener('click', function() {
    setLoading(this, true);
    const btn = this;
    apiFetch(route('updateCaption'), 'PUT', {
        caption: captionInput?.value,
        hashtags: hashtagsInput?.value,
    })
    .then(res => {
        setLoading(btn, false);
        showToast(res.success ? 'Caption tersimpan.' : 'Gagal simpan caption.', res.success ? 'success' : 'error');
    })
    .catch(() => { setLoading(btn, false); showToast('Gagal.', 'error'); });
});

// ─── Regenerate Caption ────────────────────────────────────────────────────────

document.getElementById('btn-regen-caption')?.addEventListener('click', function() {
    setLoading(this, true);
    const btn = this;
    apiFetch(route('regenCaption'), 'POST')
        .then(res => {
            setLoading(btn, false);
            if (res.success) {
                if (captionInput)  captionInput.value  = res.caption  || '';
                if (hashtagsInput) hashtagsInput.value = (res.hashtags || []).join(' ');
                updateCaptionCounts();
                showToast('Caption berhasil diregenerasi.');
            } else {
                showToast(res.error || 'Gagal regenerate caption.', 'error');
            }
        })
        .catch(() => { setLoading(btn, false); showToast('Gagal.', 'error'); });
});

// ─── Save Draft ───────────────────────────────────────────────────────────────

document.getElementById('btn-save-draft')?.addEventListener('click', function() {
    setLoading(this, true);
    const btn = this;
    apiFetch(route('saveDraft'), 'POST')
        .then(res => {
            setLoading(btn, false);
            showToast(res.success ? 'Draft tersimpan.' : 'Gagal.', res.success ? 'success' : 'error');
        })
        .catch(() => { setLoading(btn, false); showToast('Gagal.', 'error'); });
});

// ─── Submit Review ────────────────────────────────────────────────────────────

document.getElementById('btn-submit-review')?.addEventListener('click', function() {
    document.querySelector('form#review-form')?.submit();
    // If no form, use fetch + redirect
    fetch('/instagram-studio/' + CONTENT_ID + '/review', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
    }).then(() => location.reload());
});

// ─── Approve ──────────────────────────────────────────────────────────────────

document.getElementById('btn-approve')?.addEventListener('click', function() {
    setLoading(this, true);
    const btn = this;
    apiFetch(route('approve'), 'POST')
        .then(res => {
            setLoading(btn, false);
            if (res.success) { showToast('Konten disetujui.'); setTimeout(() => location.reload(), 800); }
            else showToast(res.error || 'Gagal.', 'error');
        })
        .catch(() => { setLoading(btn, false); showToast('Gagal.', 'error'); });
});

// ─── Publish ─────────────────────────────────────────────────────────────────

document.getElementById('btn-publish')?.addEventListener('click', function() {
    if (typeof Swal === 'undefined') {
        if (!confirm('Publish ke Instagram?')) return;
        doPublish();
        return;
    }

    Swal.fire({
        title: 'Siap untuk Publish?',
        html: `
            <div style="text-align:left;font-size:0.875rem;">
                <p class="mb-3">Pastikan Anda sudah memeriksa:</p>
                <ul style="list-style:none;padding:0;">
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Konten sudah direview</li>
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Data pasien sudah dianonimkan</li>
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Caption sudah dicek</li>
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Hashtag sudah sesuai</li>
                </ul>
            </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2d8aba',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="bi bi-instagram me-1"></i>Ya, Publish',
        cancelButtonText: 'Batal',
    }).then(result => { if (result.isConfirmed) doPublish(); });
});

async function doPublish() {
    const btn = document.getElementById('btn-publish');
    setLoading(btn, true);

    try {
        if (typeof html2canvas === 'undefined') {
            throw new Error('Pustaka html2canvas belum termuat. Coba refresh browser Anda.');
        }

        showToast('Sedang merender seluruh canvas slide ke 1080x1350...', 'info');
        await renderAllSlidesCanvas(true);

        showToast('Sedang mengirim ke Instagram...', 'info');
        const res = await apiFetch(route('publish'), 'POST');
        setLoading(btn, false);
        if (res.success) {
            showToast('Berhasil dipublikasikan ke Instagram!');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast(res.message || res.error || 'Gagal publish.', 'error');
        }
    } catch (err) {
        setLoading(btn, false);
        console.error('Publish error:', err);
        showToast('Gagal memproses gambar: ' + (err.message || err), 'error');
    }
}

async function inlineAllImages(container) {
    const images = Array.from(container.querySelectorAll('img'));
    for (const img of images) {
        if (!img.src) continue;
        try {
            let dataUrl = img.src;
            // Fetch as data URL if not already data URL
            if (!img.src.startsWith('data:')) {
                const res = await fetch(img.src, { mode: 'cors' });
                if (res.ok) {
                    const blob = await res.blob();
                    dataUrl = await new Promise(resolve => {
                        const reader = new FileReader();
                        reader.onloadend = () => resolve(reader.result);
                        reader.readAsDataURL(blob);
                    });
                }
            }

            // Pre-crop images that have object-fit: cover so html2canvas doesn't squash or stretch them (gepeng)
            const isCover = img.classList.contains('ig-photo-fill') || window.getComputedStyle(img).objectFit === 'cover';
            const rect = img.getBoundingClientRect();
            if (isCover && rect.width > 10 && rect.height > 10) {
                dataUrl = await cropImageToCover(dataUrl, rect.width, rect.height);
            }

            // For logo, lock width & height explicitly to its natural aspect ratio to prevent html2canvas stretching
            if (img.classList.contains('ig-brand-logo') || img.closest('.ig-brand-lockup')) {
                const nw = img.naturalWidth;
                const nh = img.naturalHeight;
                if (nw && nh && nh > 0) {
                    const naturalAr = nw / nh;
                    const renderedH = img.clientHeight || 30;
                    img.style.width  = Math.round(renderedH * naturalAr) + 'px';
                    img.style.height = renderedH + 'px';
                    img.style.maxWidth = 'none';
                }
            }

            img.src = dataUrl;
        } catch (e) {
            console.warn('Could not inline or crop image:', img.src, e);
        }
    }
}

function cropImageToCover(src, targetWidth, targetHeight) {
    return new Promise(resolve => {
        const image = new Image();
        image.crossOrigin = 'anonymous';
        image.onload = () => {
            const canvas = document.createElement('canvas');
            const cropScale = Math.max(2, 1080 / targetWidth);
            canvas.width = Math.round(targetWidth * cropScale);
            canvas.height = Math.round(targetHeight * cropScale);
            const ctx = canvas.getContext('2d');

            const nw = image.naturalWidth || image.width;
            const nh = image.naturalHeight || image.height;
            if (!nw || !nh) {
                resolve(src);
                return;
            }

            // Calculate center crop covering container (CSS object-fit: cover)
            const sRatio = nw / nh;
            const tRatio = canvas.width / canvas.height;
            let sx, sy, sw, sh;

            if (sRatio > tRatio) {
                sh = nh;
                sw = nh * tRatio;
                sx = (nw - sw) / 2;
                sy = 0;
            } else {
                sw = nw;
                sh = nw / tRatio;
                sx = 0;
                sy = (nh - sh) / 2;
            }

            ctx.drawImage(image, sx, sy, sw, sh, 0, 0, canvas.width, canvas.height);
            resolve(canvas.toDataURL('image/jpeg', 0.95));
        };
        image.onerror = () => resolve(src);
        image.src = src;
    });
}

async function captureSlideCanvas(wrap) {
    const canvasEl = wrap.querySelector('.ig-canvas') || wrap.firstElementChild;
    if (!canvasEl) throw new Error('Elemen canvas tidak ditemukan.');

    // 1. Dapatkan dimensi aktual elemen di layar preview
    const rect = canvasEl.getBoundingClientRect();
    const curWidth = rect.width || canvasEl.offsetWidth || 340;
    // Kunci rasio tepat 4:5 portrait (1350 / 1080 = 1.25)
    const targetHeight = Math.round(curWidth * 1.25);

    const origHeight       = canvasEl.style.height;
    const origMinHeight    = canvasEl.style.minHeight;
    const origMaxHeight    = canvasEl.style.maxHeight;
    const origBorderRadius = canvasEl.style.borderRadius;
    const origBoxShadow    = canvasEl.style.boxShadow;
    const origBorder       = canvasEl.style.border;

    // Hapus rounded corner, shadow, dan border agar background mengisi penuh ke sudut (0px border-radius)
    canvasEl.style.borderRadius = '0px';
    canvasEl.style.boxShadow    = 'none';
    canvasEl.style.border       = 'none';

    // Kunci ukuran tepat pada rasio 4:5 sebelum di-snapshot
    canvasEl.style.height    = targetHeight + 'px';
    canvasEl.style.minHeight = targetHeight + 'px';
    canvasEl.style.maxHeight = targetHeight + 'px';

    try {
        // 2. Pre-inline gambar dan crop sesuai object-fit: cover agar tidak gepeng
        await inlineAllImages(canvasEl);

        // 3. Render dengan html2canvas menggunakan scaling ke 1080px
        const scale = 1080 / curWidth;
        const capturedCanvas = await html2canvas(canvasEl, {
            scale: scale,
            useCORS: true,
            allowTaint: false,
            backgroundColor: null,
            logging: false,
        });

        // 4. Pastikan kanvas output persis berukuran 1080 x 1350 px (Instagram Portrait)
        const finalCanvas = document.createElement('canvas');
        finalCanvas.width = 1080;
        finalCanvas.height = 1350;
        const ctx = finalCanvas.getContext('2d');
        ctx.drawImage(capturedCanvas, 0, 0, 1080, 1350);

        return finalCanvas.toDataURL('image/jpeg', 0.95);
    } finally {
        canvasEl.style.borderRadius = origBorderRadius;
        canvasEl.style.boxShadow    = origBoxShadow;
        canvasEl.style.border       = origBorder;
        canvasEl.style.height       = origHeight;
        canvasEl.style.minHeight    = origMinHeight;
        canvasEl.style.maxHeight    = origMaxHeight;
    }
}

async function renderAllSlidesCanvas(throwOnError = false) {
    if (!slides.length) return;
    const initialSlideIdx = currentSlide;

    try {
        for (let i = 0; i < slides.length; i++) {
            // Tampilkan slide di carousel secara bergantian
            goToSlide(i);
            showToast(`Merender slide ${i + 1}/${slides.length} ke 1080x1350...`, 'info');

            // Tunggu sebentar agar slide tampil sempurna di layar
            await new Promise(r => setTimeout(r, 200));

            const wrap = slides[i];
            const slideId = wrap.dataset.slideId;
            if (!slideId) continue;

            const dataUrl = await captureSlideCanvas(wrap);
            const saveUrl = route('saveRendered', { slideId: slideId });
            const res = await apiFetch(saveUrl, 'POST', { image: dataUrl });

            if (!res.success && throwOnError) {
                throw new Error(res.error || `Server gagal menyimpan hasil render slide ${i + 1}`);
            }
        }
    } finally {
        goToSlide(initialSlideIdx);
    }
}

// ─── Button: Render Semua Slide (1080x1350) ───────────────────────────────────
document.getElementById('btn-render-all-slides')?.addEventListener('click', async function() {
    const btn = this;
    setLoading(btn, true);
    try {
        if (typeof html2canvas === 'undefined') {
            throw new Error('Pustaka html2canvas belum termuat. Coba refresh browser Anda.');
        }
        await renderAllSlidesCanvas(true);
        showToast('Semua slide berhasil dirender ke gambar 1080x1350!', 'success');
        const statusText = document.getElementById('render-status-text');
        if (statusText) {
            statusText.innerHTML = '<div class="text-success"><i class="bi bi-check-circle-fill me-1"></i><strong>Semua slide sudah siap dipublish!</strong> (1080x1350)</div>';
        }
    } catch (err) {
        console.error(err);
        showToast('Gagal merender slide: ' + (err.message || err), 'error');
    } finally {
        setLoading(btn, false);
    }
});

// ─── Download Active Slide as 1080x1350 Image ────────────────────────────────
document.getElementById('btn-download-slide')?.addEventListener('click', async function() {
    const btn = this;
    const activeSlide = slides[currentSlide] || slides[0];
    if (!activeSlide) return;

    setLoading(btn, true);
    showToast('Sedang menyiapkan gambar slide 1080x1350 px...', 'info');

    try {
        const dataUrl = await captureSlideCanvas(activeSlide);
        const slideId = activeSlide.dataset.slideId || (currentSlide + 1);

        const link = document.createElement('a');
        link.download = `instagram_${CONTENT_ID}_slide_${slideId}_1080x1350.jpg`;
        link.href = dataUrl;
        link.click();

        showToast('Gambar slide 1080x1350 berhasil didownload!', 'success');
    } catch (err) {
        console.error(err);
        showToast('Gagal mendownload gambar slide: ' + (err.message || err), 'error');
    } finally {
        setLoading(btn, false);
    }
});

// ─── Generate Images (AI) ─────────────────────────────────────────────────────

document.getElementById('btn-generate-images')?.addEventListener('click', function() {
    const btn = this;
    setLoading(btn, true);
    showToast('Sedang generate gambar AI... harap tunggu (~30-60 detik)', 'info');

    apiFetch(route('generateImages'), 'POST')
        .then(res => {
            setLoading(btn, false);
            if (res.success) {
                showToast(res.message || 'Gambar berhasil digenerate!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(res.message || 'Gagal generate gambar.', 'error');
            }
        })
        .catch(() => {
            setLoading(btn, false);
            showToast('Gagal generate gambar.', 'error');
        });
});
