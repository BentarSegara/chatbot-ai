/**
 * help-desk-chat.js
 * ─────────────────────────────────────────────────────────────
 * Logika Help Desk chat untuk sisi user:
 *   - Inisiasi conversation via API
 *   - Kirim pesan via API (bukan langsung ke Python)
 *   - Polling pesan baru setiap 3 detik
 *   - Tombol "Hubungi Admin" untuk handoff manual
 * ─────────────────────────────────────────────────────────────
 */

/* ── Elemen DOM ─────────────────────────────────────────────── */
const trigger     = document.getElementById('helpDeskTrigger');
const popup       = document.getElementById('helpDeskPopup');
const backdrop    = document.getElementById('helpDeskBackdrop');
const closeButton = document.getElementById('closeHelpDesk');
const form        = document.getElementById('helpDeskForm');
const input       = document.getElementById('helpDeskInput');
const msgContainer = document.getElementById('helpDeskMessages');
const suggestions = document.getElementById('chatSuggestions');
const humanBtn    = document.getElementById('requestHumanBtn');

/* ── State ──────────────────────────────────────────────────── */
let convId          = null;   // ID percakapan dari DB
let lastMsgId       = 0;      // ID pesan terakhir yang sudah dirender
let pollTimer       = null;   // setInterval untuk polling
let convStatus      = 'ai_handling';
let isInitialized   = false;
let isSending       = false;  // Flag agar tidak poll saat sedang kirim pesan
let renderedMsgIds  = new Set(); // Set ID pesan dari DB yang sudah dirender (deduplikasi)

/* ── Helper: Render pesan ke chat ────────────────────────────── */
function renderMessage(role, content, senderName) {
    const isUser   = role === 'user';
    const isSystem = role === 'system';

    const wrap = document.createElement('div');
    wrap.className = isUser
        ? 'chat-message user'
        : isSystem
            ? 'chat-message system'
            : 'chat-message assistant';

    wrap.textContent = content;

    const ts = document.createElement('div');
    ts.className   = 'chat-timestamp';
    ts.textContent = isUser ? 'Anda' : (senderName || (role === 'ai' ? 'Bot Assistant' : 'Admin'));
    wrap.appendChild(ts);

    msgContainer.appendChild(wrap);
    msgContainer.scrollTop = msgContainer.scrollHeight;
}

/* ── Helper: Tampilkan loading indicator ─────────────────────── */
function showTyping() {
    const el = document.createElement('div');
    el.className = 'chat-message assistant typing-indicator';
    el.id        = 'typingIndicator';
    el.innerHTML = '<span></span><span></span><span></span>';
    msgContainer.appendChild(el);
    msgContainer.scrollTop = msgContainer.scrollHeight;
}

function hideTyping() {
    const el = document.getElementById('typingIndicator');
    if (el) el.remove();
}

/* ── Helper: Update UI berdasarkan status conversation ───────── */
function updateStatusUI(status) {
    convStatus = status;

    if (humanBtn) {
        humanBtn.style.display = (status === 'ai_handling') ? 'inline-flex' : 'none';
    }

    const statusEl = document.getElementById('chatStatusLabel');
    if (statusEl) {
        const labels = {
            'ai_handling' : '🤖 Dijawab AI',
            'waiting_cs'  : '⏳ Menunggu Agen',
            'cs_handling' : '👤 Terhubung ke Agen',
            'closed'      : '✅ Selesai',
        };
        statusEl.textContent = labels[status] || status;
        statusEl.className   = 'chat-status-label status-' + status;
    }

    // Nonaktifkan input jika closed
    if (input) input.disabled = (status === 'closed');
}

/* ── Inisiasi conversation ───────────────────────────────────── */
async function initConversation() {
    if (isInitialized) return;
    isInitialized = true;

    try {
        const res  = await fetch('api/start-conversation.php');
        const data = await res.json();
        convId = data.conversation_id;

        // Ambil pesan awal (sambutan dari DB)
        await pollMessages();

        // Mulai polling
        startPolling();
    } catch (err) {
        console.error('Gagal memulai percakapan:', err);
    }
}

/* ── Polling pesan baru ──────────────────────────────────────── */
async function pollMessages() {
    if (!convId) return;
    if (isSending) return; // Jangan poll saat sedang mengirim pesan

    try {
        const res  = await fetch(`api/get-messages.php?conv_id=${convId}&after=${lastMsgId}`);
        const data = await res.json();

        if (data.messages && data.messages.length > 0) {
            // Sembunyikan suggestion saat ada pesan masuk (gunakan hide, bukan remove)
            const suggWrapper = suggestions?.closest('.chat-suggestions-wrapper');
            if (suggWrapper) suggWrapper.hidden = true;

            data.messages.forEach(msg => {
                const msgId = parseInt(msg.id);
                // Deduplikasi: skip jika ID ini sudah pernah dirender
                if (!renderedMsgIds.has(msgId)) {
                    renderedMsgIds.add(msgId);
                    renderMessage(msg.sender_role, msg.content, msg.sender_name);
                }
                lastMsgId = Math.max(lastMsgId, msgId);
            });
        } else {
            // Tidak ada pesan sama sekali — tampilkan kembali suggestion box
            const suggWrapper = suggestions?.closest('.chat-suggestions-wrapper');
            if (suggWrapper) suggWrapper.hidden = false;
        }

        if (data.conv_status) {
            updateStatusUI(data.conv_status);
        }
    } catch (err) {
        console.error('Polling error:', err);
    }
}

function startPolling() {
    if (pollTimer) return;
    pollTimer = setInterval(pollMessages, 3000);
}

function stopPolling() {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
}

/* ── Buka & tutup popup ──────────────────────────────────────── */
function openPopup() {
    if (!popup || !backdrop) return;
    popup.hidden    = false;
    backdrop.hidden = false;
    document.body.classList.add('chat-open');
    if (input) input.focus();
    initConversation();
}

function closePopup() {
    if (!popup || !backdrop) return;
    popup.hidden    = true;
    backdrop.hidden = true;
    document.body.classList.remove('chat-open');
    stopPolling();

    // Reset semua state agar percakapan yang sudah closed tidak menghambat sesi baru
    convId         = null;
    lastMsgId      = 0;
    convStatus     = 'ai_handling';
    isInitialized  = false;
    isSending      = false;
    renderedMsgIds = new Set(); // Reset deduplikasi

    // Bersihkan tampilan pesan
    if (msgContainer) msgContainer.innerHTML = '';
    if (input) {
        input.value    = '';
        input.disabled = false;
    }

    // Tampilkan kembali suggestion box untuk sesi baru
    const suggWrapper = suggestions?.closest('.chat-suggestions-wrapper');
    if (suggWrapper) suggWrapper.hidden = false;
}

/* ── Kirim pesan ────────────────────────────────────────────── */
async function sendMessage(text) {
    if (!convId || !text) return;

    // Render pesan user secara optimistic (tidak punya DB ID, tampilkan langsung)
    renderMessage('user', text);

    if (input) {
        input.value    = '';
        input.disabled = true;
    }

    isSending = true; // Blokir polling saat mengirim
    showTyping();

    try {
        const res  = await fetch('api/send-message.php', {
            method  : 'POST',
            headers : { 'Content-Type': 'application/json' },
            body    : JSON.stringify({ conv_id: convId, message: text }),
        });
        const data = await res.json();

        // ── FIX DUPE MSG: Tandai semua pesan yang baru diinsert ke DB
        // sebagai "sudah dirender" agar polling tidak render ulang.
        // Server menyimpan: pesan user (ID=N), pesan AI (ID=N+1),
        // dan mungkin pesan system (ID=N+2). last_message_id = ID terakhir.
        if (data.last_message_id) {
            const newLastId  = parseInt(data.last_message_id);
            const prevLastId = lastMsgId; // ID sebelum pengiriman ini
            // Tandai seluruh rentang ID yang baru diinsert server
            for (let id = prevLastId + 1; id <= newLastId; id++) {
                renderedMsgIds.add(id);
            }
            lastMsgId = Math.max(lastMsgId, newLastId);
        }

        hideTyping();

        if (data.answer) {
            renderMessage('ai', data.answer, 'Bot Assistant');
        }
        if (data.system_msg) {
            renderMessage('system', data.system_msg);
        }
        if (data.status) {
            updateStatusUI(data.status);
        }
    } catch (err) {
        hideTyping();
        renderMessage('system', 'Pesan tidak dapat dikirim. Periksa koneksi Anda.');
        console.error('Send error:', err);
    } finally {
        isSending = false;
        if (input && convStatus !== 'closed') {
            input.disabled = false;
            input.focus();
        }
    }
}


/* ── Event: Tombol "Hubungi Admin" ───────────────────────────── */
if (humanBtn) {
    humanBtn.addEventListener('click', async () => {
        if (!convId) return;
        humanBtn.disabled = true;

        try {
            await fetch('api/request-human.php', {
                method  : 'POST',
                headers : { 'Content-Type': 'application/json' },
                body    : JSON.stringify({ conv_id: convId }),
            });
            updateStatusUI('waiting_cs');
            renderMessage('system', 'Permintaan Anda sudah dikirim. Agen kami akan segera merespons...');
        } catch (err) {
            console.error('Request human error:', err);
            humanBtn.disabled = false;
        }
    });
}

/* ── Event: Tombol trigger ───────────────────────────────────── */
if (trigger) {
    trigger.addEventListener('click', openPopup);
}

/* ── Event: Tombol tutup ─────────────────────────────────────── */
if (closeButton) {
    closeButton.addEventListener('click', (e) => {
        e.preventDefault();
        closePopup();
    });
}

/* ── Event: Klik backdrop ────────────────────────────────────── */
if (backdrop) {
    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) closePopup();
    });
}

/* ── Event: Tekan Escape ─────────────────────────────────────── */
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && popup && !popup.hidden) closePopup();
});

/* ── Event: Submit form ──────────────────────────────────────── */
if (form) {
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = input?.value.trim();
        if (text) sendMessage(text);
    });
}

/* ── Event: Klik suggestion ──────────────────────────────────── */
if (suggestions) {
    suggestions.addEventListener('click', (e) => {
        const target = e.target;
        if (target.matches('.suggestion-box')) {
            const text = target.textContent.trim();
            if (input) input.value = text;
            sendMessage(text);
        }
    });
}
