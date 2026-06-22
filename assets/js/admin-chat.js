/**
 * assets/js/admin-chat.js
 * ─────────────────────────────────────────────────────────────
 * Logika halaman Chat Admin (Admin/chat-view.php):
 *   - Polling daftar percakapan aktif setiap 5 detik
 *   - Polling pesan baru setiap 3 detik
 *   - Ambil percakapan, kirim balasan, tutup percakapan
 * ─────────────────────────────────────────────────────────────
 */

// ── State ────────────────────────────────────────────────────
let selectedConvId   = null;
let lastMsgId        = 0;
let convPollTimer    = null;
let msgPollTimer     = null;
let allConversations = [];

// ── DOM Refs ─────────────────────────────────────────────────
const convList        = document.getElementById('conversationList');
const convListEmpty   = document.getElementById('convListEmpty');
const waitingBadge    = document.getElementById('waitingBadge');
const statusFilter    = document.getElementById('statusFilter');
const chatEmptyState  = document.getElementById('chatEmptyState');
const activeChatArea  = document.getElementById('activeChatArea');
const chatCustName    = document.getElementById('chatCustomerName');
const chatConvStatus  = document.getElementById('chatConvStatus');
const messageList     = document.getElementById('messageList');
const adminChatForm   = document.getElementById('adminChatForm');
const adminChatInput  = document.getElementById('adminChatInput');
const btnAssign       = document.getElementById('btnAssign');
const btnCloseConv    = document.getElementById('btnCloseConv');
const adminChatSubmit = document.getElementById('adminChatSubmit');

// ── Status label helper ───────────────────────────────────────
const STATUS_LABELS = {
    'ai_handling' : '🤖 Ditangani AI',
    'waiting_cs'  : '⏳ Menunggu Agen',
    'cs_handling' : '👤 Ditangani Agen',
    'closed'      : '✅ Selesai',
};

const STATUS_CLASSES = {
    'ai_handling' : 'ai_handling',
    'waiting_cs'  : 'waiting_cs',
    'cs_handling' : 'cs_handling',
    'closed'      : 'closed',
};

// ── Utility: Escape HTML ──────────────────────────────────────
function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ── Render: daftar percakapan ─────────────────────────────────
function renderConversationList(convs) {
    const filter   = statusFilter.value;
    const filtered = filter === 'all' ? convs : convs.filter(c => c.status === filter);

    convList.innerHTML = '';

    if (filtered.length === 0) {
        convListEmpty.hidden      = false;
        convListEmpty.textContent = 'Tidak ada percakapan.';
        convList.appendChild(convListEmpty);
        return;
    }

    convListEmpty.hidden = true;

    filtered.forEach(conv => {
        const isSelected = conv.id == selectedConvId;
        const card       = document.createElement('a');
        card.href        = '#';
        card.className   = 'conversation-card' + (isSelected ? ' selected' : '');
        card.dataset.convId = conv.id;

        const statusLabel = STATUS_LABELS[conv.status] || conv.status;
        const lastMsg     = conv.last_message
            ? conv.last_message.substring(0, 45) + (conv.last_message.length > 45 ? '...' : '')
            : 'Belum ada pesan';

        card.innerHTML = `
            <div class="conversation-phone">${escHtml(conv.customer_name || 'Tamu')}</div>
            <div class="conversation-meta">
                <span class="status-pill ${escHtml(conv.status)}">${statusLabel}</span>
                ${conv.status === 'waiting_cs' ? '<span class="red-dot"></span>' : ''}
            </div>
            <div class="conv-last-msg">${escHtml(lastMsg)}</div>
            <div class="conversation-footer">
                <span>${conv.message_count} pesan</span>
                <span>${conv.assigned_cs_name ? escHtml(conv.assigned_cs_name) : 'Unassigned'}</span>
            </div>
        `;

        card.addEventListener('click', e => {
            e.preventDefault();
            selectConversation(conv);
        });

        convList.appendChild(card);
    });
}

// ── Pilih percakapan ──────────────────────────────────────────
function selectConversation(conv) {
    selectedConvId = conv.id;
    lastMsgId      = 0;

    chatEmptyState.hidden    = true;
    activeChatArea.hidden    = false;
    chatCustName.textContent = conv.customer_name || 'Tamu';

    updateChatHeader(conv.status);
    messageList.innerHTML = '';

    // Highlight kartu yang dipilih
    document.querySelectorAll('.conversation-card').forEach(c => {
        c.classList.toggle('selected', c.dataset.convId == conv.id);
    });

    // Muat pesan & mulai polling pesan
    loadMessages();
    stopMsgPolling();
    msgPollTimer = setInterval(loadMessages, 3000);
}

// ── Update header chat ────────────────────────────────────────
function updateChatHeader(status) {
    const label              = STATUS_LABELS[status] || status;
    chatConvStatus.textContent = label;
    chatConvStatus.className   = 'conv-status-badge status-' + status;

    // Tampilkan tombol sesuai status
    btnAssign.hidden    = (status !== 'waiting_cs');
    btnCloseConv.hidden = (status === 'closed' || status === 'ai_handling');

    // Enable/disable form input admin berdasarkan status
    const canReply               = (status === 'cs_handling');
    adminChatInput.disabled      = !canReply;
    adminChatSubmit.disabled     = !canReply;
    adminChatForm.style.opacity  = canReply ? '1' : '0.45';
    adminChatInput.placeholder   = canReply
        ? 'Ketik pesan...'
        : (status === 'waiting_cs' ? 'Ambil percakapan untuk membalas...' :
           status === 'closed'     ? 'Percakapan sudah selesai.' :
                                     'Percakapan ditangani AI.');
}

// ── Render pesan ──────────────────────────────────────────────
function renderMessage(msg) {
    const isCs     = msg.sender_role === 'cs' || msg.sender_role === 'ai';
    const isSystem = msg.sender_role === 'system';

    const row     = document.createElement('div');
    row.className = 'message-row ' + (isSystem ? 'system-msg' : isCs ? 'outgoing' : 'incoming');

    const time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

    if (isSystem) {
        row.innerHTML = `<div class="message-system">${escHtml(msg.content)}</div>`;
    } else {
        row.innerHTML = `
            <div class="message-bubble">
                <p>${escHtml(msg.content)}</p>
                <small>${escHtml(msg.sender_name || 'Unknown')} &middot; ${time}</small>
            </div>
        `;
    }

    messageList.appendChild(row);
    messageList.scrollTop = messageList.scrollHeight;
}

// ── Muat pesan dari API ───────────────────────────────────────
async function loadMessages() {
    if (!selectedConvId) return;

    try {
        const res  = await fetch(`../api/get-messages.php?conv_id=${selectedConvId}&after=${lastMsgId}`);
        const data = await res.json();

        if (data.messages && data.messages.length > 0) {
            data.messages.forEach(msg => {
                renderMessage(msg);
                lastMsgId = Math.max(lastMsgId, parseInt(msg.id));
            });
        }

        if (data.conv_status) {
            updateChatHeader(data.conv_status);
        }
    } catch (err) {
        console.error('Load messages error:', err);
    }
}

// ── Muat daftar percakapan ────────────────────────────────────
async function loadConversations() {
    try {
        const res  = await fetch('../api/admin-get-conversations.php');
        const data = await res.json();

        if (!data.conversations) return;

        allConversations = data.conversations;

        // Badge notifikasi waiting_cs
        const waitingCount       = allConversations.filter(c => c.status === 'waiting_cs').length;
        waitingBadge.hidden      = waitingCount === 0;
        waitingBadge.textContent = waitingCount;

        renderConversationList(allConversations);

        // Update header jika sedang di percakapan yang aktif
        if (selectedConvId) {
            const curr = allConversations.find(c => c.id == selectedConvId);
            if (curr) updateChatHeader(curr.status);
        }
    } catch (err) {
        console.error('Load conversations error:', err);
    }
}

// ── Stop polling pesan ────────────────────────────────────────
function stopMsgPolling() {
    if (msgPollTimer) { clearInterval(msgPollTimer); msgPollTimer = null; }
}

// ── Event: Filter percakapan ──────────────────────────────────
statusFilter.addEventListener('change', () => {
    renderConversationList(allConversations);
});

// ── Event: Kirim pesan admin ──────────────────────────────────
adminChatForm.addEventListener('submit', async e => {
    e.preventDefault();
    const text = adminChatInput.value.trim();
    if (!text || !selectedConvId) return;

    adminChatInput.value    = '';
    adminChatInput.disabled = true;

    try {
        await fetch('../api/admin-send-message.php', {
            method  : 'POST',
            headers : { 'Content-Type': 'application/json' },
            body    : JSON.stringify({ conv_id: selectedConvId, message: text }),
        });

        await loadMessages();
        await loadConversations();
    } catch (err) {
        console.error('Send error:', err);
    } finally {
        adminChatInput.disabled = false;
        adminChatInput.focus();
    }
});

// ── Event: Tombol "Ambil Percakapan" ──────────────────────────
btnAssign.addEventListener('click', async () => {
    if (!selectedConvId) return;
    btnAssign.disabled = true;

    try {
        const res  = await fetch('../api/admin-assign.php', {
            method  : 'POST',
            headers : { 'Content-Type': 'application/json' },
            body    : JSON.stringify({ conv_id: selectedConvId }),
        });
        const data = await res.json();

        if (data.success || data.error === undefined) {
            // Langsung update UI tanpa menunggu polling
            updateChatHeader('cs_handling');
            adminChatInput.disabled  = false;
            adminChatSubmit.disabled = false;
            adminChatInput.focus();
        }

        await loadMessages();
        await loadConversations();
    } catch (err) {
        console.error('Assign error:', err);
        btnAssign.disabled = false;
    }
});

// ── Event: Tombol "Tutup Percakapan" ─────────────────────────
btnCloseConv.addEventListener('click', async () => {
    if (!selectedConvId || !confirm('Tutup percakapan ini?')) return;

    try {
        await fetch('../api/admin-close-conversation.php', {
            method  : 'POST',
            headers : { 'Content-Type': 'application/json' },
            body    : JSON.stringify({ conv_id: selectedConvId }),
        });

        selectedConvId        = null;
        activeChatArea.hidden = true;
        chatEmptyState.hidden = false;
        stopMsgPolling();
        await loadConversations();
    } catch (err) {
        console.error('Close conv error:', err);
    }
});

// ── Init ──────────────────────────────────────────────────────
loadConversations();
convPollTimer = setInterval(loadConversations, 5000);
