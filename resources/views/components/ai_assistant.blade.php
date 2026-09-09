<!-- VETCLINIC OS - FLOATING CLINIC ASSISTANT WIDGET -->
<div id="ai-assistant-root" style="position: fixed; bottom: 24px; right: 24px; z-index: 99999; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;">
    
    <!-- 1. FLOATING LAUNCHER BUTTON -->
    <button id="ai-assistant-toggle-btn" type="button" 
            title="Buka Asisten Klinik" 
            aria-label="Buka Asisten Klinik Assyavet"
            style="width: 54px; height: 54px; border-radius: 50%; background-color: #2d8aba; color: #ffffff; border: none; box-shadow: 0 4px 14px rgba(45, 138, 186, 0.35); cursor: pointer; display: flex; align-items: center; justify-content: center; position: relative; transition: transform 0.2s ease, background-color 0.2s ease;">
        <i id="ai-assistant-icon-open" class="bi bi-chat-dots-fill" style="font-size: 22px; line-height: 1; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;"></i>
        <i id="ai-assistant-icon-close" class="bi bi-x-lg" style="font-size: 20px; line-height: 1; width: 20px; height: 20px; display: none; align-items: center; justify-content: center;"></i>
        
        <!-- Online Indicator (Subtle, no glow slop) -->
        <span style="position: absolute; top: 1px; right: 1px; width: 11px; height: 11px; background-color: #10b981; border: 2px solid #ffffff; border-radius: 50%;"></span>
    </button>

    <!-- 2. CHAT DRAWER / WINDOW MODAL -->
    <div id="ai-assistant-window" class="d-none" role="dialog" aria-modal="true" aria-labelledby="ai-window-title" style="position: fixed; bottom: 88px; right: 24px; width: 400px; max-width: calc(100vw - 32px); height: min(560px, calc(100vh - 110px)); background: #ffffff; border-radius: 16px; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15); border: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; animation: aiDrawerSlideUp 0.25s ease-out; z-index: 99998;">
        
        <!-- CHAT HEADER -->
        <div id="ai-chat-header" style="background-color: #2d8aba; padding: 14px 18px; color: #ffffff; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255, 255, 255, 0.15); transition: background-color 0.25s ease;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                    <i id="ai-header-icon" class="bi bi-chat-heart-fill" style="font-size: 18px; color: #ffffff; line-height: 1; display: flex; align-items: center; justify-content: center;"></i>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <h6 id="ai-window-title" style="margin: 0; font-size: 14px; font-weight: 700; color: #ffffff;">Asisten Klinik Assyavet</h6>
                        <span id="ai-skill-badge" class="badge d-none" style="font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 10px; background: rgba(255,255,255,0.25); border: 1px solid rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.3px;"></span>
                    </div>
                    <small id="ai-window-subtitle" style="font-size: 11px; color: rgba(255, 255, 255, 0.9); display: flex; align-items: center; gap: 4px;">
                        <span style="width: 7px; height: 7px; background-color: #34d399; border-radius: 50%; display: inline-block;"></span>
                        <span id="ai-subtitle-text">Pencarian Data &amp; Panduan Prosedur</span>
                    </small>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 6px;">
                <button type="button" id="ai-assistant-reset-btn" title="Bersihkan Chat / Mode Umum" aria-label="Bersihkan Riwayat Chat atau Reset Mode" style="background: transparent; border: none; color: rgba(255,255,255,0.85); cursor: pointer; padding: 4px 8px; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='transparent'">
                    <i class="bi bi-arrow-counterclockwise" style="font-size: 16px;"></i>
                </button>
                <button type="button" id="ai-assistant-close-window-btn" title="Tutup" aria-label="Tutup Asisten Klinik" style="background: transparent; border: none; color: rgba(255,255,255,0.85); cursor: pointer; padding: 4px 8px; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='transparent'">
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
                <div style="width: 30px; height: 30px; border-radius: 50%; background: #2d8aba; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 14px; margin-top: 2px;">
                    <i class="bi bi-chat-heart-fill"></i>
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
            <div style="width: 26px; height: 26px; border-radius: 50%; background: #2d8aba; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                <i class="bi bi-chat-heart-fill"></i>
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
        padding: 10px 14px;
        border-radius: 14px;
        border-top-right-radius: 2px;
        font-size: 13px;
        max-width: 88%;
        margin-left: auto;
        word-break: break-word;
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
</style>

<!-- JAVASCRIPT LOGIC FOR FLOATING CLINIC ASSISTANT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('ai-assistant-toggle-btn');
    const iconOpen = document.getElementById('ai-assistant-icon-open');
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
            iconOpen.style.display = 'none';
            iconClose.style.display = 'flex';
            setTimeout(() => input.focus(), 150);
            scrollToBottom();
        } else {
            chatWindow.classList.add('d-none');
            iconOpen.style.display = 'flex';
            iconClose.style.display = 'none';
            toggleBtn.focus();
        }
    }

    toggleBtn.addEventListener('click', toggleChat);
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
            <div style="margin-left: auto; max-width: 88%;">
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
                skillBadge.textContent = data.skill_title || data.active_skill;
            }
            if (subtitleText) {
                subtitleText.textContent = 'Mode Spesialis: ' + (data.skill_title || data.active_skill);
            }
            if (input) {
                input.placeholder = `Mode ${data.skill_title || data.active_skill}: Ketik pertanyaan atau /general...`;
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
                <div style="width: 30px; height: 30px; border-radius: 50%; background: #2d8aba; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 14px; margin-top: 2px;">
                    <i class="bi bi-chat-heart-fill"></i>
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
