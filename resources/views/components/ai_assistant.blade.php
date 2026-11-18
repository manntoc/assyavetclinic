<!-- VETCLINIC OS - FLOATING CLINIC ASSISTANT WIDGET -->
<div id="ai-assistant-root" style="position: fixed; bottom: 24px; right: 24px; z-index: 99999; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;">
    
    <!-- 1. FLOATING LAUNCHER CONTAINER (WITH POPUP & ANIMATED MASCOT) -->
    <div id="ai-launcher-wrapper" style="position: relative; display: flex; align-items: center; justify-content: flex-end; padding: 6px; margin: -6px;">
        
        <!-- POPUP SPEECH BUBBLE (Tampil saat kursor mendekati / hover) -->
        <div id="ai-launcher-tooltip" 
             role="tooltip"
             style="position: absolute; right: 72px; top: 50%; transform: translateY(-50%) translateX(6px); opacity: 0; visibility: hidden; background: #ffffff; color: #1e293b; padding: 9px 18px; border-radius: 14px; font-size: 13px; font-weight: 600; box-shadow: 0 6px 24px rgba(0, 0, 0, 0.12); border: 1px solid #e2e8f0; white-space: nowrap; cursor: pointer; transition: opacity 0.22s ease, transform 0.22s ease, visibility 0.22s ease; z-index: 99999; user-select: none;">
            <span>Buka Asisten Assyavet</span>
            <!-- Ekor Panah SVG Mulus (Tanpa garis potong border glitch) -->
            <svg style="position: absolute; right: -7px; top: 50%; transform: translateY(-50%); width: 8px; height: 14px; overflow: visible; pointer-events: none;" viewBox="0 0 8 14" fill="none">
                <path d="M0 0.5L6.5 6.3C6.9 6.7 6.9 7.3 6.5 7.7L0 13.5" fill="#ffffff" stroke="#e2e8f0" stroke-width="1.2"/>
                <rect x="-3" y="0" width="4" height="14" fill="#ffffff"/>
            </svg>
        </div>

        <!-- FLOATING LAUNCHER BUTTON -->
        <button id="ai-assistant-toggle-btn" type="button" 
                title="Buka Asisten Klinik" 
                aria-label="Buka Asisten Klinik Assyavet"
                style="width: 58px; height: 58px; border-radius: 50%; background-color: #ffffff; color: #2d8aba; border: 2.5px solid #2d8aba; box-shadow: 0 6px 20px rgba(45, 138, 186, 0.28); cursor: pointer; display: flex; align-items: center; justify-content: center; position: relative; transition: transform 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease, border-color 0.25s ease; padding: 0; outline: none;">
            
            <!-- MASKOT DEFAULT (Desain Bersih & Halus Tanpa Garis Mengambang) -->
            <!-- Note: Bila gambar maskot custom sudah siap, cukup ganti SVG di bawah dengan <img src="{{ asset('path/maskot.png') }}" style="width: 44px; height: 44px; object-fit: contain;"> -->
            <div id="ai-mascot-avatar" class="ai-mascot-floating" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; pointer-events: none;">
                <svg width="42" height="42" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Head Body Capsule -->
                    <rect x="8" y="10" width="32" height="28" rx="10" fill="url(#botHeadGrad)" stroke="#2d8aba" stroke-width="2"/>
                    
                    <!-- Ear Pods (Capsules on side) -->
                    <rect x="4" y="18" width="4" height="12" rx="2" fill="#2d8aba"/>
                    <rect x="40" y="18" width="4" height="12" rx="2" fill="#2d8aba"/>

                    <!-- Dark Visor Screen -->
                    <rect x="12" y="14" width="24" height="17" rx="6" fill="#0f172a"/>
                    
                    <!-- Glowing Blinking Eyes -->
                    <g class="ai-mascot-eyes">
                        <rect x="16" y="18.5" width="4.5" height="7" rx="2.2" fill="#38bdf8"/>
                        <rect x="27.5" y="18.5" width="4.5" height="7" rx="2.2" fill="#38bdf8"/>
                    </g>

                    <!-- Cheeks -->
                    <circle cx="15" cy="27" r="1.5" fill="#f472b6" opacity="0.85"/>
                    <circle cx="33" cy="27" r="1.5" fill="#f472b6" opacity="0.85"/>

                    <!-- Smile -->
                    <path d="M21.5 26.5C22.5 28 25.5 28 26.5 26.5" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round"/>

                    <!-- Medical Cross on Collar -->
                    <rect x="22.5" y="40" width="3" height="5" rx="1" fill="#2d8aba"/>
                    <rect x="21.5" y="41" width="5" height="3" rx="1" fill="#2d8aba"/>

                    <defs>
                        <linearGradient id="botHeadGrad" x1="8" y1="10" x2="40" y2="38" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#f0f9ff"/>
                            <stop offset="1" stop-color="#dbeafe"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <!-- Close Icon (Tampil saat chat terbuka) -->
            <i id="ai-assistant-icon-close" class="bi bi-x-lg" style="font-size: 22px; line-height: 1; width: 22px; height: 22px; display: none; align-items: center; justify-content: center; color: #ffffff;"></i>
            
            <!-- Online Indicator (Active Green) -->
            <span style="position: absolute; top: 1px; right: 1px; width: 12px; height: 12px; background-color: #10b981; border: 2px solid #ffffff; border-radius: 50%; box-shadow: 0 0 6px rgba(16, 185, 129, 0.4);"></span>
        </button>
    </div>

    <!-- 2. CHAT DRAWER / WINDOW MODAL -->
    <div id="ai-assistant-window" class="d-none" role="dialog" aria-modal="true" aria-labelledby="ai-window-title" style="position: fixed; bottom: 88px; right: 24px; width: 420px; max-width: calc(100vw - 28px); height: min(580px, calc(100vh - 110px)); background: #ffffff; border-radius: 16px; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15); border: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; animation: aiDrawerSlideUp 0.25s ease-out; z-index: 99998;">
        
        <!-- CHAT HEADER -->
        <div id="ai-chat-header" style="background-color: #2d8aba; padding: 12px 16px; color: #ffffff; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255, 255, 255, 0.15); transition: background-color 0.25s ease; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #ffffff; border: 2px solid rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.12);">
                    <svg width="26" height="26" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="8" y="10" width="32" height="28" rx="10" fill="#e0f2fe" stroke="#2d8aba" stroke-width="2.5"/>
                        <rect x="4" y="18" width="4" height="12" rx="2" fill="#2d8aba"/>
                        <rect x="40" y="18" width="4" height="12" rx="2" fill="#2d8aba"/>
                        <rect x="12" y="14" width="24" height="17" rx="6" fill="#0f172a"/>
                        <rect x="16" y="18.5" width="4.5" height="7" rx="2.2" fill="#38bdf8"/>
                        <rect x="27.5" y="18.5" width="4.5" height="7" rx="2.2" fill="#38bdf8"/>
                        <circle cx="15" cy="27" r="1.5" fill="#f472b6"/>
                        <circle cx="33" cy="27" r="1.5" fill="#f472b6"/>
                        <path d="M21.5 26.5C22.5 28 25.5 28 26.5 26.5" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round"/>
                        <rect x="22.5" y="40" width="3" height="5" rx="1" fill="#2d8aba"/>
                        <rect x="21.5" y="41" width="5" height="3" rx="1" fill="#2d8aba"/>
                    </svg>
                </div>
                <div style="min-width: 0; flex: 1;">
                    <div style="display: flex; align-items: center; gap: 6px; min-width: 0;">
                        <h6 id="ai-window-title" style="margin: 0; font-size: 13.5px; font-weight: 700; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Asisten Klinik Assyavet</h6>
                        <span id="ai-skill-badge" class="badge d-none" style="font-size: 9.5px; font-weight: 600; padding: 2px 7px; border-radius: 8px; background: rgba(255,255,255,0.25); border: 1px solid rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap; flex-shrink: 0;"></span>
                    </div>
                    <small id="ai-window-subtitle" style="font-size: 11px; color: rgba(255, 255, 255, 0.9); display: flex; align-items: center; gap: 4px; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <span style="width: 7px; height: 7px; background-color: #34d399; border-radius: 50%; display: inline-block; flex-shrink: 0;"></span>
                        <span id="ai-subtitle-text" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Pencarian Data &amp; Panduan Prosedur</span>
                    </small>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 4px; flex-shrink: 0;">
                <button type="button" id="ai-assistant-reset-btn" title="Bersihkan Chat / Mode Umum" aria-label="Bersihkan Riwayat Chat atau Reset Mode" style="background: transparent; border: none; color: rgba(255,255,255,0.85); cursor: pointer; padding: 5px 7px; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='transparent'">
                    <i class="bi bi-arrow-counterclockwise" style="font-size: 16px;"></i>
                </button>
                <button type="button" id="ai-assistant-close-window-btn" title="Tutup" aria-label="Tutup Asisten Klinik" style="background: transparent; border: none; color: rgba(255,255,255,0.85); cursor: pointer; padding: 5px 7px; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='transparent'">
                    <i class="bi bi-x-lg" style="font-size: 16px;"></i>
                </button>
            </div>
        </div>

        <!-- QUICK SKILLS SHORTCUTS BAR -->
        <div id="ai-skills-bar" style="display: flex; align-items: center; gap: 5px; padding: 6px 12px; background: #f1f5f9; border-bottom: 1px solid #e2e8f0; overflow-x: auto; white-space: nowrap; scrollbar-width: none;">
            <span style="font-size: 10.5px; font-weight: 600; color: #64748b; margin-right: 2px; flex-shrink: 0;">
                Mode:
            </span>
            <button type="button" class="ai-skill-chip" data-cmd="/karyawan" title="Spesialis Karyawan &amp; Dokter">/karyawan</button>
            <button type="button" class="ai-skill-chip" data-cmd="/owner" title="Spesialis Data Owner &amp; Pasien">/owner</button>
            <button type="button" class="ai-skill-chip" data-cmd="/booking" title="Spesialis Booking &amp; Antrean">/booking</button>
            <button type="button" class="ai-skill-chip" data-cmd="/emr" title="Spesialis Rekam Medis SOAP">/emr</button>
            <button type="button" class="ai-skill-chip" data-cmd="/inventori" title="Spesialis Stok Obat &amp; Apotek">/inventori</button>
            <button type="button" class="ai-skill-chip" data-cmd="/laporan" title="Spesialis Omzet &amp; Transaksi">/laporan</button>
            <button type="button" class="ai-skill-chip" data-cmd="/layanan" title="Spesialis Tarif Layanan">/layanan</button>
            <button type="button" class="ai-skill-chip" data-cmd="/general" title="Kembali ke Asisten Umum" style="border-style: dashed; color: #64748b;">/general</button>
        </div>

        <!-- CHAT MESSAGES CONTAINER -->
        <div id="ai-assistant-messages" style="flex: 1; min-height: 0; padding: 16px; overflow-y: auto; overflow-x: hidden; background-color: #f8fafc; display: flex; flex-direction: column; gap: 14px;">
            
            <!-- INITIAL WELCOME MESSAGE -->
            <div class="ai-msg-group" style="display: flex; gap: 10px; max-width: 92%;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #ffffff; border: 1.5px solid #2d8aba; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px; box-shadow: 0 2px 6px rgba(45, 138, 186, 0.15);">
                    <svg width="22" height="22" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="8" y="10" width="32" height="28" rx="10" fill="#e0f2fe" stroke="#2d8aba" stroke-width="2.5"/>
                        <rect x="4" y="18" width="4" height="12" rx="2" fill="#2d8aba"/>
                        <rect x="40" y="18" width="4" height="12" rx="2" fill="#2d8aba"/>
                        <rect x="12" y="14" width="24" height="17" rx="6" fill="#0f172a"/>
                        <rect x="16" y="18.5" width="4.5" height="7" rx="2.2" fill="#38bdf8"/>
                        <rect x="27.5" y="18.5" width="4.5" height="7" rx="2.2" fill="#38bdf8"/>
                        <circle cx="15" cy="27" r="1.5" fill="#f472b6"/>
                        <circle cx="33" cy="27" r="1.5" fill="#f472b6"/>
                        <path d="M21.5 26.5C22.5 28 25.5 28 26.5 26.5" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round"/>
                        <rect x="22.5" y="40" width="3" height="5" rx="1" fill="#2d8aba"/>
                        <rect x="21.5" y="41" width="5" height="3" rx="1" fill="#2d8aba"/>
                    </svg>
                </div>
                <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 12px 14px; border-radius: 14px; border-top-left-radius: 2px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); font-size: 13px; color: #334155; line-height: 1.5;">
                    Halo! Saya <strong>Asisten Klinik Assyavet</strong>.<br><br>
                    Anda dapat menanyakan pencarian data pasien, jadwal booking antrean, laporan omzet, maupun ketersediaan stok obat di inventori apotek.
                </div>
            </div>

            <!-- INITIAL SUGGESTION CHIPS -->
            <div id="ai-initial-suggestions" style="display: flex; flex-wrap: wrap; gap: 6px; margin-left: 40px;">
                <button type="button" class="ai-chip-btn" data-query="Siapa pemilik kucing Milo?"><i class="bi bi-search me-1"></i>Pemilik Kucing Milo</button>
                <button type="button" class="ai-chip-btn" data-query="Data bookingan hari ini"><i class="bi bi-calendar-check me-1"></i>Booking Hari Ini</button>
                <button type="button" class="ai-chip-btn" data-query="Berapa omzet hari ini?"><i class="bi bi-cash-stack me-1"></i>Omzet Hari Ini</button>
                <button type="button" class="ai-chip-btn" data-query="Bagaimana alur Queue Booking?"><i class="bi bi-diagram-3 me-1"></i>Alur Antrean</button>
                <button type="button" class="ai-chip-btn" data-query="Cari Stok Obat"><i class="bi bi-capsule me-1"></i>Stok Obat</button>
            </div>

        </div>

        <!-- TYPING INDICATOR -->
        <div id="ai-assistant-typing" class="d-none" style="padding: 8px 16px; background-color: #f8fafc; display: flex; align-items: center; gap: 10px;">
            <div style="width: 26px; height: 26px; border-radius: 50%; background: #ffffff; border: 1.5px solid #2d8aba; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 1px 4px rgba(45, 138, 186, 0.12);">
                <svg width="18" height="18" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="8" y="10" width="32" height="28" rx="10" fill="#e0f2fe" stroke="#2d8aba" stroke-width="2.5"/>
                    <rect x="4" y="18" width="4" height="12" rx="2" fill="#2d8aba"/>
                    <rect x="40" y="18" width="4" height="12" rx="2" fill="#2d8aba"/>
                    <rect x="12" y="14" width="24" height="17" rx="6" fill="#0f172a"/>
                    <rect x="16" y="18.5" width="4.5" height="7" rx="2.2" fill="#38bdf8"/>
                    <rect x="27.5" y="18.5" width="4.5" height="7" rx="2.2" fill="#38bdf8"/>
                    <circle cx="15" cy="27" r="1.5" fill="#f472b6"/>
                    <circle cx="33" cy="27" r="1.5" fill="#f472b6"/>
                    <path d="M21.5 26.5C22.5 28 25.5 28 26.5 26.5" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round"/>
                    <rect x="22.5" y="40" width="3" height="5" rx="1" fill="#2d8aba"/>
                    <rect x="21.5" y="41" width="5" height="3" rx="1" fill="#2d8aba"/>
                </svg>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 12px; font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 6px;">
                <span>Mencari data & menganalisis</span>
                <span class="ai-dot-pulse"></span>
            </div>
        </div>

        <!-- CHAT INPUT FORM -->
        <div style="padding: 12px 14px; background: #ffffff; border-top: 1px solid #e2e8f0;">
            <form id="ai-assistant-form" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                @csrf
                <input type="text" id="ai-assistant-input" 
                       placeholder="Tanya owner, booking, obat, atau estimasi..." 
                       autocomplete="off"
                       aria-label="Pesan untuk asisten klinik"
                       style="flex: 1; border: 1px solid #cbd5e1; border-radius: 20px; padding: 9px 16px; font-size: 13px; line-height: 1.4; height: 38px; box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s;">
                
                <button type="submit" id="ai-assistant-send-btn" 
                        title="Kirim Pesan"
                        aria-label="Kirim Pesan"
                        style="width: 38px; height: 38px; border-radius: 50%; background: #2d8aba; color: #ffffff; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s; flex-shrink: 0;"
                        onmouseover="this.style.background='#246e96'"
                        onmouseout="this.style.background='#2d8aba'">
                    <i class="bi bi-send-fill" style="font-size: 14px; margin-left: 2px;"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- STYLES FOR FLOATING AI ASSISTANT -->
<style>
    @keyframes aiDrawerSlideUp {
        from {
            opacity: 0;
            transform: translateY(16px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Bootstrap Icons inside AI assistant: force consistent centering */
    #ai-assistant-root .bi {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        vertical-align: middle;
    }

    #ai-assistant-toggle-btn:focus-visible,
    #ai-assistant-send-btn:focus-visible,
    #ai-assistant-reset-btn:focus-visible,
    #ai-assistant-close-window-btn:focus-visible {
        outline: 2px solid #2d8aba;
        outline-offset: 2px;
    }

    #ai-assistant-input:focus {
        border-color: #2d8aba !important;
        outline: 2px solid rgba(45, 138, 186, 0.3) !important;
        outline-offset: 1px;
    }

    .ai-chip-btn {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #2d8aba;
        font-size: 11.5px;
        font-weight: 500;
        padding: 5px 12px;
        border-radius: 16px;
        cursor: pointer;
        transition: background-color 0.15s ease, border-color 0.15s ease;
        text-align: left;
    }

    .ai-chip-btn:hover {
        background: #eef7fc;
        border-color: #2d8aba;
    }

    .ai-chip-btn:focus-visible {
        outline: 2px solid #2d8aba;
        outline-offset: 2px;
    }

    .ai-skill-chip {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #334155;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 12px;
        cursor: pointer;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        flex-shrink: 0;
        line-height: 1.2;
    }

    .ai-skill-chip:hover {
        background: #e2e8f0;
        border-color: #94a3b8;
        color: #0f172a;
    }

    .ai-skill-chip:focus-visible {
        outline: 2px solid #2d8aba;
        outline-offset: 1px;
    }

    .ai-skill-chip.active {
        background: #2d8aba;
        border-color: #2d8aba;
        color: #ffffff;
    }

    .ai-user-msg {
        background: #2d8aba;
        color: #ffffff;
        padding: 9px 14px;
        border-radius: 14px;
        border-top-right-radius: 2px;
        font-size: 13px;
        max-width: 82%;
        width: fit-content;
        min-width: 44px;
        box-sizing: border-box;
        word-break: normal;
        overflow-wrap: break-word;
        white-space: pre-wrap;
        line-height: 1.45;
        display: inline-block;
    }

    .ai-response-msg {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 12px 14px;
        border-radius: 14px;
        border-top-left-radius: 2px;
        font-size: 13px;
        color: #1e293b;
        line-height: 1.55;
        word-break: normal;
        overflow-wrap: break-word;
    }

    .ai-result-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #2d8aba;
        border-radius: 8px;
        padding: 10px 12px;
        margin-top: 6px;
        font-size: 12px;
        transition: border-color 0.15s ease;
    }

    .ai-result-card:hover {
        border-color: #2d8aba;
    }

    .ai-dot-pulse {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #2d8aba;
        animation: aiDotPulse 1.2s infinite ease-in-out;
    }

    @keyframes aiDotPulse {
        0%, 100% { opacity: 0.3; transform: scale(0.8); }
        50% { opacity: 1; transform: scale(1.2); }
    }

    /* 1. Mascot Floating Idle Animation */
    .ai-mascot-floating {
        animation: aiMascotFloat 3.2s ease-in-out infinite;
        transform-origin: center bottom;
        transition: transform 0.25s ease;
    }

    @keyframes aiMascotFloat {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-4px);
        }
    }

    /* 2. Mascot Blinking Eyes Animation */
    .ai-mascot-eyes {
        transform-origin: 24px 23px;
        animation: aiMascotEyeBlink 4s ease-in-out infinite;
    }

    @keyframes aiMascotEyeBlink {
        0%, 46%, 54%, 100% {
            transform: scaleY(1);
        }
        50% {
            transform: scaleY(0.12);
        }
    }

    /* 3. Mascot Happy Wave / Bounce on Hover */
    @keyframes aiMascotWave {
        0%, 100% { transform: rotate(0deg) scale(1); }
        20% { transform: rotate(-8deg) scale(1.08); }
        40% { transform: rotate(8deg) scale(1.08); }
        60% { transform: rotate(-4deg) scale(1.05); }
        80% { transform: rotate(4deg) scale(1.02); }
    }

    /* 4. Launcher Hover Interactions */
    /* Jembatan hover transparan: mencegah flicker kursor saat melintas antara tombol dan tooltip */
    #ai-launcher-tooltip::before {
        content: '';
        position: absolute;
        right: -24px;
        top: -12px;
        bottom: -12px;
        width: 30px;
        background: transparent;
    }

    #ai-assistant-toggle-btn:focus,
    #ai-assistant-toggle-btn:active {
        outline: none !important;
        box-shadow: 0 6px 20px rgba(45, 138, 186, 0.28) !important;
    }

    #ai-assistant-toggle-btn:focus-visible {
        outline: none !important;
    }

    #ai-launcher-wrapper:hover #ai-launcher-tooltip {
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateY(-50%) translateX(0) !important;
        pointer-events: auto !important;
    }

    #ai-launcher-wrapper:hover #ai-assistant-toggle-btn {
        transform: scale(1.06);
        box-shadow: 0 8px 26px rgba(45, 138, 186, 0.38) !important;
    }

    #ai-launcher-wrapper:hover .ai-mascot-floating {
        animation: aiMascotWave 0.65s ease-in-out !important;
    }

    /* 5. Chat Open States */
    #ai-launcher-wrapper.chat-open #ai-launcher-tooltip {
        display: none !important;
    }

    #ai-launcher-wrapper.chat-open #ai-assistant-toggle-btn {
        background-color: #2d8aba !important;
        border-color: #2d8aba !important;
        transform: none !important;
        box-shadow: 0 4px 14px rgba(45, 138, 186, 0.35) !important;
    }
</style>

<!-- JAVASCRIPT LOGIC FOR FLOATING CLINIC ASSISTANT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const launcherWrapper = document.getElementById('ai-launcher-wrapper');
    const launcherTooltip = document.getElementById('ai-launcher-tooltip');
    const toggleBtn = document.getElementById('ai-assistant-toggle-btn');
    const mascotAvatar = document.getElementById('ai-mascot-avatar');
    const iconClose = document.getElementById('ai-assistant-icon-close');
    const chatWindow = document.getElementById('ai-assistant-window');
    const closeWindowBtn = document.getElementById('ai-assistant-close-window-btn');
    const resetBtn = document.getElementById('ai-assistant-reset-btn');
    const form = document.getElementById('ai-assistant-form');
    const input = document.getElementById('ai-assistant-input');
    const messagesContainer = document.getElementById('ai-assistant-messages');
    const typingIndicator = document.getElementById('ai-assistant-typing');

    let isChatOpen = false;

    // Toggle Chat Window
    function toggleChat() {
        isChatOpen = !isChatOpen;
        if (isChatOpen) {
            chatWindow.classList.remove('d-none');
            if (launcherWrapper) launcherWrapper.classList.add('chat-open');
            if (mascotAvatar) mascotAvatar.style.display = 'none';
            if (iconClose) iconClose.style.display = 'flex';
            setTimeout(() => input.focus(), 150);
            scrollToBottom();
        } else {
            chatWindow.classList.add('d-none');
            if (launcherWrapper) launcherWrapper.classList.remove('chat-open');
            if (mascotAvatar) mascotAvatar.style.display = 'flex';
            if (iconClose) iconClose.style.display = 'none';
            toggleBtn.focus();
        }
    }

    toggleBtn.addEventListener('click', toggleChat);
    if (launcherTooltip) {
        launcherTooltip.addEventListener('click', toggleChat);
    }
    closeWindowBtn.addEventListener('click', toggleChat);

    // Close on Escape key (R-32 Accessibility)
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isChatOpen) {
            toggleChat();
        }
    });

    // Reset / Clear Chat
    resetBtn.addEventListener('click', function () {
        sendQuery('/general');
    });

    // Scroll chat to bottom
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Bind Chip Buttons
    function bindChipButtons() {
        document.querySelectorAll('.ai-chip-btn').forEach(btn => {
            btn.onclick = function () {
                const query = this.getAttribute('data-query');
                if (query) {
                    sendQuery(query);
                }
            };
        });
    }
    bindChipButtons();

    // Bind Quick Skill Chips
    function bindSkillChips() {
        document.querySelectorAll('.ai-skill-chip').forEach(btn => {
            btn.onclick = function () {
                const cmd = this.getAttribute('data-cmd');
                if (cmd) {
                    sendQuery(cmd);
                }
            };
        });
    }
    bindSkillChips();

    // Submit Form
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const query = input.value.trim();
        if (query) {
            sendQuery(query);
            input.value = '';
        }
    });

    // Send Query to Backend AJAX
    function sendQuery(queryText) {
        // Append User Message
        const userMsgHtml = `
            <div style="display: flex; justify-content: flex-end; width: 100%;">
                <div class="ai-user-msg">${escapeHtml(queryText)}</div>
            </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', userMsgHtml);
        scrollToBottom();

        // Show Typing
        typingIndicator.classList.remove('d-none');
        scrollToBottom();

        // Perform Fetch API
        fetch("{{ route('ai-assistant.chat') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                message: queryText,
                current_path: window.location.pathname
            })
        })
        .then(response => response.json())
        .then(data => {
            typingIndicator.classList.add('d-none');
            renderAiResponse(data);
        })
        .catch(err => {
            console.error('AI Assistant Fetch Error:', err);
            typingIndicator.classList.add('d-none');
            renderAiResponse({
                status: 'error',
                reply: 'Gagal terhubung ke layanan asisten. Silakan periksa koneksi atau coba sesaat lagi.'
            });
        });
    }

    // Parse simple Markdown bold/italics
    function parseMarkdown(str) {
        if (!str) return '';
        let html = str;
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
        return html;
    }

    // Render AI Response
    function renderAiResponse(data) {
        // Update Active Skill Header & Chips
        const header = document.getElementById('ai-chat-header');
        const skillBadge = document.getElementById('ai-skill-badge');
        const subtitleText = document.getElementById('ai-subtitle-text');
        const allSkillChips = document.querySelectorAll('.ai-skill-chip');

        allSkillChips.forEach(chip => chip.classList.remove('active'));

        if (data.active_skill) {
            if (skillBadge) {
                skillBadge.classList.remove('d-none');
                skillBadge.textContent = '/' + data.active_skill;
            }
            if (subtitleText) {
                subtitleText.textContent = 'Mode: ' + (data.skill_title || data.active_skill);
            }
            if (input) {
                input.placeholder = `Tanya spesialis ${data.active_skill} (atau /general)...`;
            }

            const activeChip = document.querySelector(`.ai-skill-chip[data-cmd="/${data.active_skill}"]`);
            if (activeChip) activeChip.classList.add('active');

            if (header && data.skill_color) {
                header.style.backgroundColor = data.skill_color;
            }
        } else {
            if (skillBadge) skillBadge.classList.add('d-none');
            if (subtitleText) subtitleText.textContent = 'Pencarian Data & Panduan Prosedur';
            if (input) input.placeholder = 'Tanya owner, booking, obat, atau estimasi...';
            if (header) header.style.backgroundColor = '#2d8aba';
        }

        let resultsHtml = '';
        
        // Render DB Search result cards if available
        if (Array.isArray(data.results) && data.results.length > 0) {
            resultsHtml += `<div style="margin-top: 8px; display: flex; flex-direction: column; gap: 6px;">`;
            data.results.forEach(res => {
                resultsHtml += `
                    <div class="ai-result-card">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <strong style="color: #0f172a; font-size: 12.5px;">${e(res.title)}</strong>
                            <span class="badge ${res.badge_class}" style="font-size: 10px; padding: 2px 6px;">${e(res.badge)}</span>
                        </div>
                        <div style="color: #64748b; font-size: 11.5px; margin-top: 3px;">${res.subtitle}</div>
                        ${res.url ? `<a href="${res.url}" class="btn btn-sm btn-light border mt-2 py-0 px-2 text-primary fw-bold" style="font-size: 11px;">Lihat Detail</a>` : ''}
                    </div>
                `;
            });
            resultsHtml += `</div>`;
        }

        // Action URL Button
        if (data.action_url && data.action_label) {
            resultsHtml += `
                <div style="margin-top: 10px;">
                    <a href="${data.action_url}" class="btn btn-sm btn-primary w-100 fw-bold" style="font-size: 12px; border-radius: 8px; background-color: #2d8aba; border-color: #2d8aba;">
                        ${e(data.action_label)}
                    </a>
                </div>
            `;
        }

        // Suggestions Chips
        let suggestionsHtml = '';
        if (Array.isArray(data.suggestions) && data.suggestions.length > 0) {
            suggestionsHtml += `<div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px;">`;
            data.suggestions.forEach(sug => {
                suggestionsHtml += `<button type="button" class="ai-chip-btn" data-query="${e(sug)}">${e(sug)}</button>`;
            });
            suggestionsHtml += `</div>`;
        }

        const formattedReply = parseMarkdown(data.reply || '');

        const aiMsgGroupHtml = `
            <div class="ai-msg-group" style="display: flex; gap: 10px; max-width: 92%;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #ffffff; border: 1.5px solid #2d8aba; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px; box-shadow: 0 2px 6px rgba(45, 138, 186, 0.15);">
                    <svg width="22" height="22" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="8" y="10" width="32" height="28" rx="10" fill="#e0f2fe" stroke="#2d8aba" stroke-width="2.5"/>
                        <rect x="4" y="18" width="4" height="12" rx="2" fill="#2d8aba"/>
                        <rect x="40" y="18" width="4" height="12" rx="2" fill="#2d8aba"/>
                        <rect x="12" y="14" width="24" height="17" rx="6" fill="#0f172a"/>
                        <rect x="16" y="18.5" width="4.5" height="7" rx="2.2" fill="#38bdf8"/>
                        <rect x="27.5" y="18.5" width="4.5" height="7" rx="2.2" fill="#38bdf8"/>
                        <circle cx="15" cy="27" r="1.5" fill="#f472b6"/>
                        <circle cx="33" cy="27" r="1.5" fill="#f472b6"/>
                        <path d="M21.5 26.5C22.5 28 25.5 28 26.5 26.5" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round"/>
                        <rect x="22.5" y="40" width="3" height="5" rx="1" fill="#2d8aba"/>
                        <rect x="21.5" y="41" width="5" height="3" rx="1" fill="#2d8aba"/>
                    </svg>
                </div>
                <div style="flex: 1;">
                    <div class="ai-response-msg">
                        ${formattedReply}
                        ${resultsHtml}
                    </div>
                    ${suggestionsHtml}
                </div>
            </div>
        `;

        messagesContainer.insertAdjacentHTML('beforeend', aiMsgGroupHtml);
        bindChipButtons();
        scrollToBottom();
    }

    // Helper functions
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.innerText = text;
        return div.innerHTML;
    }

    function e(text) {
        return escapeHtml(text);
    }
});
</script>
