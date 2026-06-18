<?php
/**
 * Admin/history-view.php
 * ─────────────────────────────────────────────────────────────
 * Halaman Histori Percakapan — menampilkan semua percakapan
 * yang sudah selesai (status = closed).
 * ─────────────────────────────────────────────────────────────
 */
session_start();
if (empty($_SESSION['auth']) || $_SESSION['auth']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
$adminName = htmlspecialchars($_SESSION['auth']['name']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Histori Percakapan – Disty Teknologi">
    <title>Histori Percakapan – Disty Teknologi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        /* ── Histori-specific styles ── */
        .history-layout {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 0;
            height: calc(100vh - 56px);
            overflow: hidden;
        }

        .history-panel {
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .history-filter {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: var(--surface);
        }

        .history-filter h2 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .history-search {
            display: flex;
            gap: 8px;
        }

        .history-search input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.875rem;
        }

        .history-search input:focus {
            outline: none;
            border-color: var(--accent);
        }

        .history-list {
            flex: 1;
            overflow-y: auto;
            padding: 8px;
        }

        .history-card {
            display: block;
            padding: 14px;
            border-radius: 10px;
            text-decoration: none;
            color: inherit;
            transition: background 0.15s;
            margin-bottom: 4px;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .history-card:hover {
            background: var(--surface-hover, rgba(255,255,255,0.05));
        }

        .history-card.selected {
            background: rgba(99, 102, 241, 0.12);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .history-card-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .history-card-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }

        .history-card-preview {
            font-size: 0.82rem;
            color: var(--text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .history-badge-closed {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            font-size: 0.72rem;
            font-weight: 600;
        }

        /* ── Detail panel ── */
        .history-detail {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--bg);
        }

        .history-detail-empty {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            gap: 12px;
        }

        .history-detail-empty .icon {
            font-size: 3rem;
            opacity: 0.4;
        }

        .history-detail-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .history-detail-header h2 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }

        .history-detail-header .meta-info {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .history-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Pagination */
        .pagination-bar {
            padding: 12px 16px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--surface);
        }

        .page-btn {
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.15s;
        }

        .page-btn:hover:not(:disabled) {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .page-info {
            font-size: 0.82rem;
            color: var(--text-secondary);
        }

        /* Empty state */
        .empty-history {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            gap: 10px;
            color: var(--text-secondary);
        }

        .empty-history .icon {
            font-size: 2.5rem;
            opacity: 0.35;
        }
    </style>
</head>
<body>

<div class="app-shell">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">DT</div>
            <span class="sidebar-brand">Disty Teknologi</span>
        </div>
        <nav class="side-nav">
            <a href="dashboard-view.php">📊 Dashboard</a>
            <a href="chat-view.php">💬 Chat <span class="nav-badge" id="waitingBadge" hidden>!</span></a>
            <a href="history-view.php" class="active">📋 Histori</a>
            <a href="staff-view.php">👥 Staff</a>
        </nav>
        <div class="side-bottom">
            <a href="../logout.php">🚪 Logout</a>
        </div>
    </aside>

    <!-- Main -->
    <main class="main-content">

        <!-- Top Bar -->
        <header class="topbar">
            <div class="top-left">
                <a href="../index.php" class="breadcrumb-link">Beranda</a>
                <span class="breadcrumb-sep">/</span>
                <span class="page-label">Histori Percakapan</span>
            </div>
            <div class="topbar-right">
                <span class="admin-info">👑 <?= $adminName ?></span>
                <a href="../logout.php" class="topbar-logout">Logout</a>
            </div>
        </header>

        <!-- Page Body -->
        <section class="page-body" style="padding:0;">
            <div class="history-layout">

                <!-- List Panel -->
                <aside class="history-panel">
                    <div class="history-filter">
                        <h2>📋 Histori Percakapan</h2>
                        <div class="history-search">
                            <input type="text" id="searchInput" placeholder="Cari nama customer..." autocomplete="off">
                        </div>
                    </div>
                    <div class="history-list" id="historyList">
                        <div class="empty-history">
                            <div class="icon">🕐</div>
                            <p>Memuat histori...</p>
                        </div>
                    </div>
                    <div class="pagination-bar" id="paginationBar" style="display:none;">
                        <button class="page-btn" id="prevBtn" disabled>← Sebelumnya</button>
                        <span class="page-info" id="pageInfo">–</span>
                        <button class="page-btn" id="nextBtn" disabled>Berikutnya →</button>
                    </div>
                </aside>

                <!-- Detail Panel -->
                <section class="history-detail" id="historyDetail">
                    <div class="history-detail-empty" id="detailEmpty">
                        <div class="icon">💬</div>
                        <p>Pilih percakapan untuk melihat detail percakapan.</p>
                    </div>

                    <div id="detailContent" style="display:none; flex-direction:column; height:100%; overflow:hidden;">
                        <div class="history-detail-header">
                            <div>
                                <h2 id="detailCustomerName">—</h2>
                                <div class="meta-info" id="detailMeta">—</div>
                            </div>
                            <span class="history-badge-closed">✅ Selesai</span>
                        </div>
                        <div class="history-messages" id="detailMessages"></div>
                    </div>
                </section>

            </div>
        </section>

    </main>
</div>

<script>
/* ══════════════════════════════════════════════════════════════
   Histori Percakapan — JavaScript
   ══════════════════════════════════════════════════════════════ */

// ── State ────────────────────────────────────────────────────
let currentPage   = 1;
let totalPages    = 1;
let allHistories  = [];
let searchTimeout = null;
let selectedHistoryId = null;

// ── DOM Refs ─────────────────────────────────────────────────
const historyList      = document.getElementById('historyList');
const paginationBar    = document.getElementById('paginationBar');
const prevBtn          = document.getElementById('prevBtn');
const nextBtn          = document.getElementById('nextBtn');
const pageInfo         = document.getElementById('pageInfo');
const searchInput      = document.getElementById('searchInput');
const detailEmpty      = document.getElementById('detailEmpty');
const detailContent    = document.getElementById('detailContent');
const detailCustName   = document.getElementById('detailCustomerName');
const detailMeta       = document.getElementById('detailMeta');
const detailMessages   = document.getElementById('detailMessages');

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

    const query = searchInput.value.trim().toLowerCase();
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
        card.className = 'history-card' + (conv.id == selectedHistoryId ? ' selected' : '');
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
            <div style="font-size:0.75rem;color:var(--text-secondary);margin-top:4px;">
                ${formatDate(conv.updated_at)}
            </div>
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
        pageInfo.textContent = `Halaman ${data.pagination.current_page} / ${Math.max(1, totalPages)} (${data.pagination.total_rows} percakapan)`;
        prevBtn.disabled = (currentPage <= 1);
        nextBtn.disabled = (currentPage >= totalPages);
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
    detailEmpty.style.display = 'none';
    detailContent.style.display = 'flex';
    detailMessages.innerHTML = '<p style="color:var(--text-secondary);text-align:center;padding:20px;">Memuat pesan...</p>';
    detailCustName.textContent = conv.customer_name || 'Tamu';
    detailMeta.textContent = `Ditangani oleh: ${conv.assigned_cs_name || 'Tidak ada agen'} · Ditutup: ${formatDate(conv.updated_at)}`;

    try {
        const res  = await fetch(`../api/admin-get-history.php?conv_id=${conv.id}`);
        const data = await res.json();

        if (!data.messages) {
            detailMessages.innerHTML = '<p style="color:var(--text-secondary);text-align:center;padding:20px;">Gagal memuat pesan.</p>';
            return;
        }

        detailMessages.innerHTML = '';
        if (data.messages.length === 0) {
            detailMessages.innerHTML = '<p style="color:var(--text-secondary);text-align:center;padding:20px;">Tidak ada pesan dalam percakapan ini.</p>';
            return;
        }

        data.messages.forEach(msg => renderDetailMessage(msg));
        detailMessages.scrollTop = detailMessages.scrollHeight;
    } catch (err) {
        console.error('Load detail error:', err);
        detailMessages.innerHTML = '<p style="color:var(--text-secondary);text-align:center;padding:20px;">Gagal memuat pesan.</p>';
    }
}

// ── Render satu pesan di detail ───────────────────────────────
function renderDetailMessage(msg) {
    const isCs     = msg.sender_role === 'cs' || msg.sender_role === 'ai';
    const isSystem = msg.sender_role === 'system';

    const row = document.createElement('div');
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

// ── Event: Search ─────────────────────────────────────────────
searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        renderHistoryList(allHistories);
    }, 300);
});

// ── Init ──────────────────────────────────────────────────────
loadHistory(1);
</script>

</body>
</html>
