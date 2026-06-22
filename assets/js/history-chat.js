/**
 * assets/js/history-chat.js
 * ─────────────────────────────────────────────────────────────
 * Logika halaman Histori Percakapan (Admin/history-view.php):
 *   - Memuat daftar percakapan yang sudah ditutup (closed)
 *   - Pagination & pencarian real-time
 *   - Menampilkan detail pesan tiap percakapan
 * ─────────────────────────────────────────────────────────────
 */

// ── State ────────────────────────────────────────────────────
let currentPage       = 1;
let totalPages        = 1;
let allHistories      = [];
let searchTimeout     = null;
let selectedHistoryId = null;

// ── DOM Refs ─────────────────────────────────────────────────
const historyList    = document.getElementById('historyList');
const paginationBar  = document.getElementById('paginationBar');
const prevBtn        = document.getElementById('prevBtn');
const nextBtn        = document.getElementById('nextBtn');
const pageInfo       = document.getElementById('pageInfo');
const searchInput    = document.getElementById('searchInput');
const detailEmpty    = document.getElementById('detailEmpty');
const detailContent  = document.getElementById('detailContent');
const detailCustName = document.getElementById('detailCustomerName');
const detailMeta     = document.getElementById('detailMeta');
const detailMessages = document.getElementById('detailMessages');

// ── Utility ───────────────────────────────────────────────────
function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatDate(dt) {
    if (!dt) return '—';
    const d = new Date(dt);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

// ── Render daftar histori ─────────────────────────────────────
function renderHistoryList(items) {
    historyList.innerHTML = '';

    const query    = searchInput.value.trim().toLowerCase();
    const filtered = query
        ? items.filter(c => (c.customer_name || '').toLowerCase().includes(query))
        : items;

    if (filtered.length === 0) {
        historyList.innerHTML = `
            <div class="empty-history">
                <div class="icon">🔍</div>
                <p>Tidak ada histori percakapan.</p>
            </div>`;
        return;
    }

    filtered.forEach(conv => {
        const card = document.createElement('div');
        card.className      = 'history-card' + (conv.id == selectedHistoryId ? ' selected' : '');
        card.dataset.convId = conv.id;

        const lastMsg = conv.last_message
            ? conv.last_message.substring(0, 55) + (conv.last_message.length > 55 ? '...' : '')
            : 'Tidak ada pesan';

        card.innerHTML = `
            <div class="history-card-name">${escHtml(conv.customer_name || 'Tamu')}</div>
            <div class="history-card-meta">
                <span class="history-badge-closed">✅ Selesai</span>
                <span>${escHtml(conv.assigned_cs_name || 'Unassigned')}</span>
                <span>·</span>
                <span>${escHtml(String(conv.message_count))} pesan</span>
            </div>
            <div class="history-card-preview">${escHtml(lastMsg)}</div>
            <div class="history-card-date">${formatDate(conv.updated_at)}</div>
        `;

        card.addEventListener('click', () => loadDetail(conv));
        historyList.appendChild(card);
    });
}

// ── Muat daftar histori dari API ──────────────────────────────
async function loadHistory(page = 1) {
    currentPage = page;
    historyList.innerHTML = `
        <div class="empty-history">
            <div class="icon">🕐</div>
            <p>Memuat histori...</p>
        </div>`;

    try {
        const res  = await fetch(`../api/admin-get-history.php?page=${page}&per_page=20`);
        const data = await res.json();

        if (!data.conversations) {
            historyList.innerHTML = `<div class="empty-history"><p>Gagal memuat data.</p></div>`;
            return;
        }

        allHistories = data.conversations;
        totalPages   = data.pagination.total_pages || 1;

        // Update pagination
        pageInfo.textContent  = `Halaman ${data.pagination.current_page} / ${Math.max(1, totalPages)} (${data.pagination.total_rows} percakapan)`;
        prevBtn.disabled      = (currentPage <= 1);
        nextBtn.disabled      = (currentPage >= totalPages);
        paginationBar.style.display = data.pagination.total_rows > 0 ? 'flex' : 'none';

        renderHistoryList(allHistories);
    } catch (err) {
        console.error('Load history error:', err);
        historyList.innerHTML = `<div class="empty-history"><p>Gagal memuat data.</p></div>`;
    }
}

// ── Muat detail percakapan ────────────────────────────────────
async function loadDetail(conv) {
    selectedHistoryId = conv.id;

    // Highlight card terpilih
    document.querySelectorAll('.history-card').forEach(c => {
        c.classList.toggle('selected', c.dataset.convId == conv.id);
    });

    // Tampilkan loading
    detailEmpty.style.display   = 'none';
    detailContent.style.display = 'flex';
    detailMessages.innerHTML    = '<p class="detail-loading">Memuat pesan...</p>';
    detailCustName.textContent  = conv.customer_name || 'Tamu';
    detailMeta.textContent      = `Ditangani oleh: ${conv.assigned_cs_name || 'Tidak ada agen'} · Ditutup: ${formatDate(conv.updated_at)}`;

    try {
        const res  = await fetch(`../api/admin-get-history.php?conv_id=${conv.id}`);
        const data = await res.json();

        if (!data.messages) {
            detailMessages.innerHTML = '<p class="detail-loading">Gagal memuat pesan.</p>';
            return;
        }

        detailMessages.innerHTML = '';
        if (data.messages.length === 0) {
            detailMessages.innerHTML = '<p class="detail-loading">Tidak ada pesan dalam percakapan ini.</p>';
            return;
        }

        data.messages.forEach(msg => renderDetailMessage(msg));
        detailMessages.scrollTop = detailMessages.scrollHeight;
    } catch (err) {
        console.error('Load detail error:', err);
        detailMessages.innerHTML = '<p class="detail-loading">Gagal memuat pesan.</p>';
    }
}

// ── Render satu pesan di detail ───────────────────────────────
function renderDetailMessage(msg) {
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

    detailMessages.appendChild(row);
}

// ── Event: Pagination ─────────────────────────────────────────
prevBtn.addEventListener('click', () => {
    if (currentPage > 1) loadHistory(currentPage - 1);
});

nextBtn.addEventListener('click', () => {
    if (currentPage < totalPages) loadHistory(currentPage + 1);
});

// ── Event: Search (debounced 300ms) ──────────────────────────
searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        renderHistoryList(allHistories);
    }, 300);
});

// ── Init ──────────────────────────────────────────────────────
loadHistory(1);
