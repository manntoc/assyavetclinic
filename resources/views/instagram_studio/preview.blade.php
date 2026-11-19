@extends('layouts.admin')
@section('content')

@include('components.headers.navbar')

<style>
/* ══════════════════════════════════════════════════════════════════════════════
   Instagram Studio CSS — Assyavet Clinic
   Canvas: 4:5 ratio (1080x1350 target)
   10 Layout Variants + Brand Component System
   ══════════════════════════════════════════════════════════════════════════════ */

/* ── Design Tokens ──────────────────────────────────────────────────────────── */
:root {
    --ig-navy:        #1a58ad;
    --ig-sky:         #009beb;
    --ig-navy-dark:   #0f3d82;
    --ig-sky-light:   #93d9f7;
    --ig-bg-pastel:   #e8f3fc;
    --ig-bg-light:    #f4f9fd;
    --ig-white:       #ffffff;
    --ig-text-dark:   #1e293b;
    --ig-text-mid:    #334155;
    --ig-text-muted:  #475569;
    --ig-border:      #d4e6f6;
    --ig-radius-sm:   8px;
    --ig-radius:      12px;
    --ig-radius-lg:   18px;
    --ig-font:        'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif;
}

/* ── Shared Canvas Base ─────────────────────────────────────────────────────── */
.ig-canvas {
    width:100%; aspect-ratio:4/5; border-radius:16px; overflow:hidden;
    display:flex; flex-direction:column; justify-content:space-between;
    padding:18px 18px 14px; font-family:var(--ig-font);
    user-select:none; position:relative;
    box-shadow:0 8px 28px rgba(28,88,160,.14),0 2px 8px rgba(0,0,0,.06);
    container-type:inline-size;
}
.ig-photo-fill { width:100%; height:100%; object-fit:cover; display:block; }

/* ── Brand Component: Logo (NEVER stretch, NEVER squash) ────────────────────── */
.ig-brand-logo {
    height:28px;
    width:auto;
    object-fit:contain;
    display:block;
    max-width:55%;
}
.ig-brand-text-fallback { display:flex; flex-direction:column; line-height:1.1; }
.ig-brand-title { font-size:.88rem; font-weight:800; color:var(--ig-navy); letter-spacing:-.3px; }
.ig-brand-sub   { font-size:.6rem; font-weight:600; color:var(--ig-sky); letter-spacing:.5px; text-transform:uppercase; }
.ig-brand-text-white .ig-brand-title { color:#fff; }
.ig-brand-text-white .ig-brand-sub   { color:rgba(255,255,255,.75); }

/* ── Brand Component: Top Bar ───────────────────────────────────────────────── */
.ig-top-bar { display:flex; align-items:center; justify-content:space-between; z-index:10; flex-shrink:0; }
.ig-brand-lockup { display:flex; align-items:center; }
.ig-slide-pill { font-size:.58rem; font-weight:700; color:var(--ig-navy);
    background:rgba(26,88,173,.08); padding:3px 9px;
    border-radius:20px; border:1px solid rgba(26,88,173,.15);
    white-space:nowrap; flex-shrink:0; }
.ig-slide-pill-white { color:rgba(255,255,255,.9); background:rgba(255,255,255,.15); border-color:rgba(255,255,255,.3); }

/* ── Brand Component: Bottom Bar (website URL = locked, from DB) ────────────── */
.ig-bottom-bar { display:flex; align-items:center; justify-content:space-between; padding-top:8px; flex-shrink:0; z-index:10; }
.ig-bottom-light { border-top:1px solid rgba(212,230,246,.8); }
.ig-bottom-dark  { border-top:1px solid rgba(255,255,255,.15); }
.ig-slide-dots { display:flex; align-items:center; gap:4px; }
.ig-dot       { width:6px; height:6px; border-radius:50%; background:#cbd5e1; flex-shrink:0; }
.ig-dot.active { width:18px; border-radius:9px; background:var(--ig-sky); }
.ig-dot-white       { background:rgba(255,255,255,.35); }
.ig-dot-white.active { background:#fff; }
.ig-footer-url       { font-size:.58rem; font-weight:700; color:var(--ig-navy); letter-spacing:.3px; }
.ig-footer-url-white { color:rgba(255,255,255,.85); }

/* ── Decorative Elements ────────────────────────────────────────────────────── */
.ig-deco-blob { position:absolute; border-radius:50%; pointer-events:none; z-index:0; }
.ig-blob-tl { width:30%; aspect-ratio:1; top:-12%; left:-8%; background:radial-gradient(circle,rgba(0,155,235,.12) 0%,transparent 70%); }
.ig-blob-br { width:35%; aspect-ratio:1; bottom:-15%; right:-10%; background:radial-gradient(circle,rgba(26,88,173,.1) 0%,transparent 70%); }
.ig-blob-cta { width:60%; aspect-ratio:1; bottom:-20%; right:-15%; background:radial-gradient(circle,rgba(0,155,235,.15) 0%,transparent 70%); }
.ig-deco-ring { position:absolute; border-radius:50%; pointer-events:none; z-index:0; border:2px solid rgba(255,255,255,.08); }
.ig-ring-tr { width:45%; aspect-ratio:1; top:-15%; right:-12%; }
.ig-ring-bl { width:40%; aspect-ratio:1; bottom:-10%; left:-10%; }
.ig-deco-glow { position:absolute; pointer-events:none; z-index:0; background:radial-gradient(circle at 50% 0%,rgba(147,217,247,.25) 0%,transparent 60%); }
.ig-glow-top { top:0; left:0; right:0; height:40%; }
.ig-deco-dots-bg { position:absolute; inset:0; pointer-events:none; z-index:0;
    background-image:radial-gradient(rgba(26,88,173,.05) 1px,transparent 1px); background-size:18px 18px; }
.ig-deco-tape { position:absolute; width:50px; height:18px; z-index:5; pointer-events:none;
    background:rgba(255,213,100,.75); border-radius:2px; box-shadow:0 1px 3px rgba(0,0,0,.1); }
.ig-tape-tl { top:34%; left:4%; transform:rotate(-8deg); }
.ig-tape-tr { top:38%; right:3%; transform:rotate(10deg); }
.ig-deco-paw { position:absolute; pointer-events:none; z-index:0; font-size:clamp(16px,4cqw,24px); opacity:.12; }
.ig-paw-1   { bottom:25%; left:2%; transform:rotate(-20deg); }
.ig-paw-2   { top:45%; right:3%; transform:rotate(30deg); }
.ig-paw-cta-1 { bottom:30%; right:5%; transform:rotate(20deg); opacity:.08; font-size:3rem; }
.ig-paw-cta-2 { top:50%; left:3%; transform:rotate(-15deg); opacity:.06; font-size:2rem; }
.ig-deco-bigchar { position:absolute; top:-5%; left:-5%; font-size:clamp(100px,55cqw,200px);
    font-weight:900; color:rgba(26,88,173,.04); pointer-events:none; z-index:0; line-height:1;
    font-family:var(--ig-font); user-select:none; }

/* ── Photo placeholder ──────────────────────────────────────────────────────── */
.ig-photo-placeholder { width:100%; height:100%; display:flex; flex-direction:column;
    align-items:center; justify-content:center; background:linear-gradient(135deg,#e3f2fd,#bbdefb);
    color:var(--ig-navy); gap:6px; font-size:.72rem; font-weight:600; }
.ig-photo-placeholder i { font-size:2rem; opacity:.55; }

/* ══ LAYOUT 1: hook_center — Cover, big headline + centered square photo ══════ */
.ig-lyt-hook-center { background:linear-gradient(175deg,var(--ig-bg-pastel) 0%,var(--ig-bg-light) 40%,var(--ig-white) 100%); border:1px solid var(--ig-border); }
.ig-hc-body { flex:1; display:flex; flex-direction:column; align-items:center; text-align:center; justify-content:center; gap:7px; padding:4px 0; z-index:1; }
.ig-hc-badge { display:inline-flex; align-items:center; background:linear-gradient(135deg,var(--ig-sky),var(--ig-navy)); color:#fff; font-size:clamp(.6rem,2.2cqw,.7rem); font-weight:700; padding:4px 12px; border-radius:20px; box-shadow:0 3px 8px rgba(0,155,235,.3); }
.ig-hc-headline { font-size:clamp(1.05rem,3.8cqw,1.35rem); font-weight:800; color:var(--ig-navy); line-height:1.25; max-width:90%; }
.ig-hc-photo-wrap { width:min(190px,68%); aspect-ratio:1/1; border-radius:var(--ig-radius-lg); overflow:hidden; margin:4px auto; box-shadow:0 6px 20px rgba(26,88,173,.15); border:3px solid #fff; background:#e2e8f0; }
.ig-hc-subtext { font-size:clamp(.62rem,2.3cqw,.72rem); color:var(--ig-text-muted); line-height:1.4; max-width:88%; }

/* ══ LAYOUT 2: navy_full — Navy full-bleed, white text, numbered list ══════════ */
.ig-lyt-navy-full { background:linear-gradient(160deg,#1a4c9e 0%,#1d6ebd 50%,#0a8fd1 100%); }
.ig-nf-body { flex:1; display:flex; flex-direction:column; gap:8px; padding:4px 0; justify-content:center; z-index:1; }
.ig-nf-tag { font-size:clamp(.55rem,2cqw,.65rem); font-weight:800; letter-spacing:1px; color:var(--ig-sky-light); text-transform:uppercase; }
.ig-nf-headline { font-size:clamp(.92rem,3.6cqw,1.18rem); font-weight:800; color:#fff; line-height:1.3; }
.ig-nf-photo-strip { width:100%; height:80px; border-radius:10px; overflow:hidden; flex-shrink:0; }
.ig-nf-points { display:flex; flex-direction:column; gap:6px; }
.ig-nf-point { display:flex; align-items:flex-start; gap:8px; background:rgba(255,255,255,.1); padding:8px 10px; border-radius:10px; border-left:3px solid var(--ig-sky-light); }
.ig-nf-num { width:18px; height:18px; border-radius:50%; background:var(--ig-sky-light); color:#1a4c9e; font-size:.58rem; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ig-nf-pt-text { font-size:clamp(.6rem,2.2cqw,.7rem); color:rgba(255,255,255,.9); line-height:1.35; }

/* ══ LAYOUT 3: white_top_blue — Top white (headline+photo), blue panel ══════════ */
.ig-lyt-white-top-blue { background:#f0f7fd; border:1px solid var(--ig-border); }
.ig-wtb-top { display:flex; align-items:center; gap:10px; flex-shrink:0; padding:4px 0 6px; }
.ig-wtb-headline { font-size:clamp(.88rem,3.4cqw,1.12rem); font-weight:800; color:var(--ig-navy); line-height:1.3; flex:1; }
.ig-wtb-photo { width:70px; height:70px; border-radius:var(--ig-radius); overflow:hidden; flex-shrink:0; border:2px solid #fff; box-shadow:0 3px 8px rgba(0,0,0,.1); }
.ig-wtb-panel { flex:1; background:linear-gradient(135deg,var(--ig-navy),var(--ig-sky)); border-radius:14px; padding:12px 14px; display:flex; flex-direction:column; gap:8px; box-shadow:0 4px 14px rgba(0,155,235,.25); }
.ig-wtb-point { display:flex; flex-direction:column; }
.ig-wtb-num { font-size:.55rem; font-weight:800; color:var(--ig-sky-light); letter-spacing:.5px; text-transform:uppercase; }
.ig-wtb-text { font-size:clamp(.62rem,2.3cqw,.72rem); color:#fff; line-height:1.35; }

/* ══ LAYOUT 4: card_grid — 2x2 info cards ═══════════════════════════════════ */
.ig-lyt-card-grid { background:#f7fafd; border:1px solid var(--ig-border); }
.ig-cg-body { flex:1; display:flex; flex-direction:column; gap:8px; padding:4px 0; justify-content:center; z-index:1; }
.ig-cg-headline { font-size:clamp(.88rem,3.4cqw,1.12rem); font-weight:800; color:var(--ig-navy); line-height:1.3; }
.ig-cg-grid { display:grid; grid-template-columns:1fr 1fr; gap:7px; }
.ig-cg-card { background:#fff; border-radius:10px; padding:10px 10px 8px; border:1px solid #e2e8f0; border-top:3px solid var(--card-accent,var(--ig-navy)); box-shadow:0 2px 6px rgba(0,0,0,.05); }
.ig-cg-card-icon { font-size:1.05rem; margin-bottom:4px; }
.ig-cg-card-text { font-size:clamp(.6rem,2.2cqw,.7rem); color:var(--ig-text-dark); line-height:1.35; font-weight:500; }
.ig-cg-photo-strip { width:100%; height:60px; border-radius:10px; overflow:hidden; margin-top:4px; }

/* ══ LAYOUT 5: split_reverse — Photo left 40%, text right ═══════════════════ */
.ig-lyt-split { background:#fff; border:1px solid var(--ig-border); }
.ig-sr-content { flex:1; display:flex; gap:10px; padding:4px 0; }
.ig-sr-photo { width:42%; border-radius:14px; overflow:hidden; flex-shrink:0; box-shadow:0 4px 14px rgba(0,0,0,.1); }
.ig-sr-text { flex:1; display:flex; flex-direction:column; justify-content:center; gap:6px; }
.ig-sr-tag { font-size:.55rem; font-weight:800; letter-spacing:.8px; color:var(--ig-sky); text-transform:uppercase; }
.ig-sr-headline { font-size:clamp(.82rem,3.2cqw,1.02rem); font-weight:800; color:var(--ig-navy); line-height:1.3; }
.ig-sr-points { display:flex; flex-direction:column; gap:5px; }
.ig-sr-point { display:flex; align-items:flex-start; gap:6px; }
.ig-sr-dot { width:6px; height:6px; border-radius:50%; background:var(--ig-sky); margin-top:4px; flex-shrink:0; }
.ig-sr-pt { font-size:clamp(.6rem,2.2cqw,.7rem); color:var(--ig-text-mid); line-height:1.35; }

/* ══ LAYOUT 6: blue_gradient — Sky to navy gradient ══════════════════════════ */
.ig-lyt-blue-gradient { background:linear-gradient(170deg,#93d9f7 0%,#2a8cd4 35%,var(--ig-navy) 70%,var(--ig-navy-dark) 100%); }
.ig-bg-body { flex:1; display:flex; flex-direction:column; align-items:center; text-align:center; justify-content:center; gap:8px; padding:4px 0; z-index:1; }
.ig-bg-headline { font-size:clamp(.98rem,3.7cqw,1.22rem); font-weight:800; color:#fff; line-height:1.3; max-width:90%; }
.ig-bg-photo-circle { width:min(130px,50%); aspect-ratio:1/1; border-radius:50%; overflow:hidden; border:3px solid rgba(255,255,255,.5); box-shadow:0 6px 18px rgba(0,0,0,.2); }
.ig-bg-icon-circle { width:65px; height:65px; border-radius:50%; background:rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center; font-size:1.8rem; color:#fff; border:2px solid rgba(255,255,255,.3); }
.ig-bg-points { display:flex; flex-direction:column; gap:6px; width:100%; }
.ig-bg-point { display:flex; align-items:flex-start; background:rgba(255,255,255,.12); padding:7px 10px; border-radius:10px; font-size:clamp(.6rem,2.2cqw,.7rem); color:rgba(255,255,255,.95); line-height:1.35; }

/* ══ LAYOUT 7: editorial — Big slide number + editorial headline ══════════════ */
.ig-lyt-editorial { background:linear-gradient(170deg,#f8fbff 0%,var(--ig-white) 100%); border:1px solid var(--ig-border); }
.ig-ed-body { flex:1; display:flex; flex-direction:column; gap:6px; padding:4px 0; z-index:1; }
.ig-ed-number { font-size:clamp(2.5rem,12cqw,4.5rem); font-weight:900; color:rgba(26,88,173,.07); line-height:1; letter-spacing:-2px; margin-bottom:-8px; }
.ig-ed-rule { height:3px; width:36px; background:var(--ig-sky); border-radius:2px; flex-shrink:0; }
.ig-ed-headline { font-size:clamp(.92rem,3.6cqw,1.18rem); font-weight:800; color:var(--ig-navy); line-height:1.28; }
.ig-ed-photo { width:100%; height:90px; border-radius:var(--ig-radius); overflow:hidden; flex-shrink:0; }
.ig-ed-copy { font-size:clamp(.62rem,2.3cqw,.72rem); color:var(--ig-text-mid); line-height:1.45; }
.ig-ed-bullets { display:flex; flex-direction:column; gap:4px; }
.ig-ed-bullet { font-size:clamp(.6rem,2.2cqw,.7rem); color:var(--ig-text-mid); display:flex; gap:5px; }
.ig-ed-dash { color:var(--ig-sky); font-weight:700; flex-shrink:0; }

/* ══ LAYOUT 8: scrapbook — Sticky note / paper, playful pet feel ═══════════════ */
.ig-lyt-scrapbook { background:linear-gradient(160deg,#fffde7 0%,#fff9e0 40%,#f0f7fd 100%); border:2px solid #ffe082; }
.ig-sb-body { flex:1; display:flex; gap:8px; padding:4px 0; align-items:stretch; z-index:2; }
.ig-sb-note { flex:1; background:#fff; border-radius:var(--ig-radius); padding:10px 12px; display:flex; flex-direction:column; gap:6px; box-shadow:0 3px 12px rgba(0,0,0,.08); border:1px solid #e2e8f0; position:relative; }
.ig-sb-note::before { content:''; position:absolute; top:-8px; left:12px; width:40px; height:16px; background:rgba(255,213,100,.85); border-radius:2px; transform:rotate(-4deg); }
.ig-sb-note-headline { font-size:clamp(.82rem,3.2cqw,1.02rem); font-weight:800; color:var(--ig-navy); line-height:1.3; padding-top:8px; }
.ig-sb-rule { height:2px; background:rgba(26,88,173,.1); border-radius:2px; flex-shrink:0; }
.ig-sb-note-point { display:flex; align-items:flex-start; gap:6px; }
.ig-sb-bullet { font-size:.9rem; flex-shrink:0; }
.ig-sb-pt-text { font-size:clamp(.6rem,2.2cqw,.7rem); color:var(--ig-text-dark); line-height:1.35; }
.ig-sb-photo { width:40%; border-radius:var(--ig-radius); overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,.1); flex-shrink:0; border:2px solid #fff; transform:rotate(2deg); }
.ig-sb-photo-placeholder { width:40%; border-radius:var(--ig-radius); background:rgba(26,88,173,.06); display:flex; align-items:center; justify-content:center; flex-shrink:0; border:2px dashed rgba(26,88,173,.2); }
.ig-sb-ph-emoji { font-size:2rem; opacity:.5; }

/* ══ LAYOUT 9: typography_hero — Big text dominant ══════════════════════════ */
.ig-lyt-typo-hero { background:var(--ig-white); border:1px solid var(--ig-border); }
.ig-th-body { flex:1; display:flex; flex-direction:column; gap:8px; padding:4px 0; z-index:1; justify-content:center; }
.ig-th-headline { font-size:clamp(1.2rem,5cqw,1.7rem); font-weight:900; color:var(--ig-navy); line-height:1.15; letter-spacing:-.5px; }
.ig-th-rule { height:3px; width:40px; background:var(--ig-sky); border-radius:2px; flex-shrink:0; }
.ig-th-body-text { font-size:clamp(.65rem,2.4cqw,.75rem); color:var(--ig-text-mid); line-height:1.5; }
.ig-th-points { display:flex; flex-direction:column; gap:5px; }
.ig-th-point { font-size:clamp(.62rem,2.3cqw,.72rem); color:var(--ig-navy); font-weight:600; display:flex; gap:5px; }
.ig-th-photo-corner { align-self:flex-end; width:min(90px,35%); aspect-ratio:1/1; border-radius:var(--ig-radius-lg); overflow:hidden; box-shadow:0 5px 16px rgba(26,88,173,.18); border:2px solid #fff; margin-top:4px; }

/* ══ LAYOUT 10: cta_full — CTA closing slide ════════════════════════════════ */
.ig-lyt-cta { background:linear-gradient(175deg,var(--ig-bg-pastel),var(--ig-bg-light) 40%,var(--ig-white) 100%); border:1px solid var(--ig-border); }
.ig-cf-body { flex:1; display:flex; flex-direction:column; gap:7px; justify-content:center; z-index:1; }
.ig-cf-tagline { font-size:.6rem; font-weight:700; color:var(--ig-sky); letter-spacing:.5px; }
.ig-cf-headline { font-size:clamp(.92rem,3.6cqw,1.18rem); font-weight:800; color:var(--ig-navy); line-height:1.3; }
.ig-cf-photo { width:100%; height:90px; border-radius:var(--ig-radius); overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,.08); }
.ig-cf-box { display:flex; align-items:center; gap:10px; background:linear-gradient(135deg,var(--ig-navy),var(--ig-sky)); color:#fff; padding:12px 14px; border-radius:14px; box-shadow:0 5px 16px rgba(0,155,235,.25); }
.ig-cf-box-icon { width:36px; height:36px; border-radius:9px; background:rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center; font-size:1.15rem; flex-shrink:0; }
.ig-cf-box-title { font-size:.82rem; font-weight:800; line-height:1.2; }
.ig-cf-box-desc  { font-size:.58rem; opacity:.9; line-height:1.3; margin-top:2px; }
.ig-cf-features { display:flex; justify-content:space-between; gap:5px; }
.ig-cf-feat { flex:1; background:#fff; border-radius:8px; padding:6px 4px; display:flex; flex-direction:column; align-items:center; gap:3px; font-size:.58rem; font-weight:700; color:var(--ig-navy); border:1px solid var(--ig-border); }
.ig-cf-feat i { color:var(--ig-sky); font-size:.78rem; }

/* ── Legacy compat (old class names still used in some layouts) ──────────────── */
.ig-designer-canvas { width:100%; aspect-ratio:4/5; position:relative; overflow:hidden; border-radius:16px; font-family:var(--ig-font); user-select:none; display:flex; flex-direction:column; justify-content:space-between; padding:24px 22px 18px; background:linear-gradient(175deg,var(--ig-bg-pastel) 0%,var(--ig-bg-light) 35%,var(--ig-white) 100%); box-shadow:0 10px 30px rgba(28,88,160,.12),0 2px 8px rgba(0,0,0,.06); border:1px solid var(--ig-border); }
.ig-hook-center { background:linear-gradient(175deg,var(--ig-bg-pastel) 0%,var(--ig-bg-light) 40%,var(--ig-white) 100%); border:1px solid var(--ig-border); }
.ig-navy-full { background:linear-gradient(160deg,#1a4c9e 0%,#1d6ebd 50%,#0a8fd1 100%); }
.ig-white-top-blue { background:#f0f7fd; border:1px solid var(--ig-border); }
.ig-card-grid { background:#f7fafd; border:1px solid var(--ig-border); }
.ig-split-reverse { background:#fff; border:1px solid var(--ig-border); }
.ig-blue-gradient { background:linear-gradient(170deg,#93d9f7 0%,#2a8cd4 35%,var(--ig-navy) 70%,var(--ig-navy-dark) 100%); }
.ig-cta-full { background:linear-gradient(175deg,var(--ig-bg-pastel),var(--ig-bg-light) 40%,var(--ig-white) 100%); border:1px solid var(--ig-border); }
.ig-top-bar-white .ig-brand-title { color:#fff; }
.ig-top-bar-white .ig-brand-sub { color:rgba(255,255,255,.75); }
.ig-bottom-bar { display:flex; align-items:center; justify-content:space-between; padding-top:8px; flex-shrink:0; z-index:10; border-top:1px solid rgba(212,230,246,.8); }
.ig-bottom-bar-white { border-top-color:rgba(255,255,255,.15); }

/* ── Editor panel ────────────────────────────────────────────────────────────── */
.ig-nav-dots { display:flex; gap:6px; justify-content:center; margin-top:10px; }
.ig-nav-dot { width:6px; height:6px; border-radius:50%; background:#e2e8f0; cursor:pointer; transition:background .2s; }
.ig-nav-dot.active { background:#2d8aba; width:18px; border-radius:3px; }
.slide-editor-card { border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin-bottom:12px; background:#fff; }
.slide-editor-card.active-slide { border-color:#2d8aba; box-shadow:0 0 0 3px rgba(45,138,186,.12); }
</style>

<div id="main" class="pt-3">
    <header class="mb-3 d-xl-none">
        <a href="#" class="burger-btn d-block d-xl-none"><i class="bi bi-justify fs-3"></i></a>
    </header>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 px-1">
        <div class="d-flex align-items-center gap-3">
            <button class="sidebar-toggle-btn me-2"><i class="bi bi-layout-sidebar-inset fs-5"></i></button>
            <div>
                <h4 class="fw-bold mb-0" style="color:#2d8aba;font-size:1.25rem;">
                    <i class="bi bi-instagram me-2"></i>Preview & Edit Konten
                </h4>
                <small style="color:#a29f9f;font-size:0.75rem;">
                    {{ $content->kode_konten }} &bull; {{ $content->topic }}
                </small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('instagram-studio.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Daftar
            </a>
            {{-- Status badge --}}
            <span class="badge d-flex align-items-center" style="background:{{ $content->status_color }};font-size:0.75rem;padding:6px 12px;border-radius:8px;">
                {{ $content->status_label }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-1 mb-3" role="alert" style="border-radius:10px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- LEFT: Carousel Preview --}}
        <div class="col-lg-5">
            <div style="position:sticky;top:80px;">
                {{-- Slide Preview --}}
                <div class="kreloses-card p-3 mb-3">
                    <div style="max-width: 340px; margin: 0 auto;">
                        <div id="slide-carousel" style="position:relative;">
                            @foreach($content->slides as $slide)
                                <div class="ig-slide-preview" data-slide-id="{{ $slide->id }}"
                                     style="{{ !$loop->first ? 'display:none;' : '' }}">
                                    @include('instagram_studio.partials.slide_canvas', [
                                        'slide' => $slide,
                                        'brand' => $brand,
                                        'bgColor' => '#2d8aba',
                                    ])
                                </div>
                            @endforeach
                        </div>

                        {{-- Nav dots --}}
                        <div class="ig-nav-dots mt-2" id="ig-nav-dots">
                            @foreach($content->slides as $slide)
                                <div class="ig-nav-dot {{ $loop->first ? 'active' : '' }}"
                                     data-index="{{ $loop->index }}"></div>
                            @endforeach
                        </div>

                        {{-- Slide nav arrows --}}
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <button class="btn btn-sm btn-outline-secondary" id="prev-slide" style="border-radius:8px;">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <small class="text-muted fw-medium" id="slide-counter" style="font-size:0.8rem;">
                                Slide 1 / {{ $content->slides->count() }}
                            </small>
                            <button class="btn btn-sm btn-outline-secondary" id="next-slide" style="border-radius:8px;">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Actions Card: Stage by Stage --}}
                <div class="kreloses-card p-3 mb-3">
                    {{-- Stage 1: Edit & Media --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-uppercase fw-bold text-muted" style="font-size:0.7rem;letter-spacing:0.5px;">
                                <span class="badge bg-secondary me-1" style="font-size:0.65rem;">1</span> Persiapan Konten & Visual
                            </span>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-sm btn-outline-primary" id="btn-generate-images" style="border-radius:8px;border-color:#2d8aba;color:#2d8aba;"
                                    title="Generate gambar AI untuk semua slide yang belum ada foto">
                                <i class="bi bi-stars me-1"></i>Generate Gambar AI
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="btn-save-draft" style="border-radius:8px;"
                                    title="Simpan teks dan perubahan konten saat ini">
                                <i class="bi bi-floppy2 me-1"></i>Simpan Perubahan Draft
                            </button>
                        </div>
                    </div>

                    <div style="border-top:1px dashed #e2e8f0;margin:12px 0;"></div>

                    {{-- Stage 2: Render Canvas 1080x1350 --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-uppercase fw-bold text-muted" style="font-size:0.7rem;letter-spacing:0.5px;">
                                <span class="badge bg-primary me-1" style="font-size:0.65rem;">2</span> Render Canvas (1080x1350)
                            </span>
                        </div>

                        <div id="render-status-badge" class="p-2 mb-2 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:0.75rem;">
                            @php
                                $renderedCount = $content->slides->whereNotNull('rendered_path')->count();
                                $totalSlideCount = $content->slides->count();
                            @endphp
                            <div id="render-status-text">
                                @if($renderedCount === $totalSlideCount && $totalSlideCount > 0)
                                    <div class="text-success"><i class="bi bi-check-circle-fill me-1"></i><strong>Semua {{ $totalSlideCount }} slide sudah siap</strong> (1080x1350)</div>
                                @elseif($renderedCount > 0)
                                    <div class="text-warning"><i class="bi bi-clock-history me-1"></i>{{ $renderedCount }} dari {{ $totalSlideCount }} slide sudah dirender</div>
                                @else
                                    <div class="text-muted"><i class="bi bi-info-circle me-1"></i>Slide belum dirender ke gambar canvas</div>
                                @endif
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" id="btn-render-all-slides" style="background:#2d8aba;border-color:#2d8aba;border-radius:8px;"
                                    title="Render semua slide menjadi gambar portrait 1080x1350 utuh sesuai preview">
                                <i class="bi bi-magic me-1"></i>Render Semua Slide (1080x1350)
                            </button>
                            <button class="btn btn-sm btn-outline-info" id="btn-download-slide" style="border-radius:8px;"
                                    title="Download slide yang sedang aktif sebagai file gambar JPG 1080x1350">
                                <i class="bi bi-download me-1"></i>Download Slide Aktif (1080x1350)
                            </button>
                        </div>
                    </div>

                    <div style="border-top:1px dashed #e2e8f0;margin:12px 0;"></div>

                    {{-- Stage 3: Approval & Publikasi --}}
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-uppercase fw-bold text-muted" style="font-size:0.7rem;letter-spacing:0.5px;">
                                <span class="badge bg-success me-1" style="font-size:0.65rem;">3</span> Alur Publikasi Instagram
                            </span>
                        </div>

                        <div class="d-grid gap-2">
                            @if(in_array($content->status, ['draft', 'generated']))
                                <button class="btn btn-outline-warning text-dark fw-medium" id="btn-submit-review"
                                        style="border-color:#f59e0b;background:#fffbeb;border-radius:8px;">
                                    <i class="bi bi-send-check me-1"></i>Kirim untuk Review
                                </button>
                            @endif

                            @if($content->status === 'review')
                                <button class="btn btn-success fw-medium" id="btn-approve" style="border-radius:8px;">
                                    <i class="bi bi-check2-circle me-1"></i>Setujui Konten (Approve)
                                </button>
                            @endif

                            @if($content->status === 'approved')
                                <button class="btn text-white fw-bold shadow-sm" id="btn-publish"
                                        style="background:linear-gradient(45deg,#f09433,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);border:none;border-radius:8px;padding:10px 16px;">
                                    <i class="bi bi-instagram me-1"></i>Publish ke Instagram Sekarang
                                </button>
                            @endif

                            @if($content->status === 'published')
                                <div class="text-center p-2 rounded" style="background:#ecfdf5;border:1px solid #a7f3d0;font-size:0.8rem;color:#065f46;">
                                    <i class="bi bi-patch-check-fill me-1"></i>Konten sudah dipublikasikan ke Instagram
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Privacy reminder --}}
                    @if($content->source_type === 'kasus_klinik')
                        <div class="mt-3 p-2 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                            <small style="font-size:0.72rem;color:#166534;">
                                <i class="bi bi-shield-check me-1"></i>
                                Data pasien telah dianonimkan. Review konten sebelum mempublikasikan.
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT: Editor Panel --}}
        <div class="col-lg-7">

            {{-- Slide Editors --}}
            <div class="kreloses-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0" style="color:#2d8aba;font-size:0.95rem;">
                        <i class="bi bi-layers me-2"></i>Konten Slide
                        <span class="badge bg-primary rounded-pill ms-1" style="font-size:0.65rem;">
                            {{ $content->slides->count() }}/5
                        </span>
                    </h6>
                    <div class="d-flex gap-2">
                        @if($content->slides->count() < 5)
                            <button class="btn btn-outline-primary btn-sm" id="btn-add-slide" style="border-radius:8px;border-color:#2d8aba;color:#2d8aba;font-size:0.8rem;">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Slide
                            </button>
                        @endif
                    </div>
                </div>

                <div id="slides-editor-container">
                    @foreach($content->slides as $slide)
                        @include('instagram_studio.partials.slide_editor', ['slide' => $slide, 'content' => $content])
                    @endforeach
                </div>
            </div>

            {{-- Caption & Hashtags --}}
            <div class="kreloses-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0" style="color:#2d8aba;font-size:0.95rem;">
                        <i class="bi bi-chat-square-text me-2"></i>Caption & Hashtag
                    </h6>
                    <button class="btn btn-outline-secondary btn-sm" id="btn-regen-caption" style="border-radius:8px;font-size:0.8rem;">
                        <i class="bi bi-arrow-clockwise me-1"></i>Regenerate
                    </button>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium" style="font-size:0.875rem;">Caption</label>
                    <textarea id="caption-input" class="form-control" rows="5" style="font-size:0.85rem;border-radius:8px;"
                              placeholder="Caption Instagram...">{{ $content->caption }}</textarea>
                    <small class="text-muted"><span id="caption-char-count">0</span> karakter</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium" style="font-size:0.875rem;">Hashtag</label>
                    <textarea id="hashtags-input" class="form-control" rows="3" style="font-size:0.85rem;border-radius:8px;"
                              placeholder="#KlinikHewan #DokterHewan ...">{{ is_array($content->hashtags) ? implode(' ', $content->hashtags) : $content->hashtags }}</textarea>
                    <small class="text-muted"><span id="hashtag-count">0</span> hashtag</small>
                </div>

                <button class="btn btn-primary btn-sm" id="btn-save-caption"
                        style="background:#2d8aba;border-color:#2d8aba;border-radius:8px;">
                    <i class="bi bi-floppy2 me-1"></i>Simpan Caption
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden data --}}
<script>
const CONTENT_ID = {{ $content->id }};
const CSRF_TOKEN = '{{ csrf_token() }}';
const ROUTES = {
    updateSlide:      '/instagram-studio/' + CONTENT_ID + '/slides/{slideId}',
    regenSlide:       '/instagram-studio/' + CONTENT_ID + '/slides/{slideId}/regenerate',
    slideCanvas:      '/instagram-studio/' + CONTENT_ID + '/slides/{slideId}/canvas',
    uploadImage:      '/instagram-studio/' + CONTENT_ID + '/slides/{slideId}/image',
    addSlide:         '/instagram-studio/' + CONTENT_ID + '/slides/add',
    removeSlide:      '/instagram-studio/' + CONTENT_ID + '/slides/{slideId}',
    updateCaption:    '/instagram-studio/' + CONTENT_ID + '/caption',
    regenCaption:     '/instagram-studio/' + CONTENT_ID + '/caption/regenerate',
    saveDraft:        '/instagram-studio/' + CONTENT_ID + '/draft',
    submitReview:     '/instagram-studio/' + CONTENT_ID + '/review',
    approve:          '/instagram-studio/' + CONTENT_ID + '/approve',
    publish:          '/instagram-studio/' + CONTENT_ID + '/publish',
    generateImages:   '/instagram-studio/' + CONTENT_ID + '/generate-images',
    saveRendered:     '/instagram-studio/' + CONTENT_ID + '/slides/{slideId}/save-rendered',
};
const BRAND = @json($brand);
const CONTENT_STATUS = '{{ $content->status }}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="{{ asset('js/instagram_studio.js') }}?v={{ time() }}"></script>

@endsection
