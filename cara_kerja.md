# 📖 Cara Kerja Sistem Chatbot Help Desk — Disty Teknologi

> Dokumen ini menjelaskan secara menyeluruh bagaimana sistem chatbot Help Desk pada proyek ini bekerja, mulai dari percakapan pertama antara customer dengan AI, hingga proses penerusan ke agen manusia (admin/CS).

---

## 🗂️ Gambaran Umum Arsitektur

Sistem ini terdiri dari **tiga lapisan utama** yang saling berkomunikasi:

```
┌─────────────────────────────────────────────────────────┐
│                      CUSTOMER                           │
│              (Browser — index.php)                      │
└──────────────────────┬──────────────────────────────────┘
                       │ HTTP Request (fetch/AJAX)
                       ▼
┌─────────────────────────────────────────────────────────┐
│              BACKEND PHP (Apache / XAMPP)               │
│   api/start-conversation.php  api/send-message.php      │
│   api/request-human.php       api/get-messages.php      │
│   api/admin-*.php                                       │
└──────────┬──────────────────────────┬───────────────────┘
           │ PDO / MySQL              │ cURL (HTTP POST)
           ▼                         ▼
┌─────────────────┐      ┌───────────────────────────────┐
│  DATABASE MySQL │      │  Python FastAPI (Port 8000)   │
│  chatbot_ai     │      │  AI/project/main.py           │
│  - conversations│      │  Endpoint: /detect-handoff    │
│  - messages     │      │  RAG + LLM (Groq / LLaMA)    │
│  - users        │      └───────────────────────────────┘
└─────────────────┘
```

---

## 🔄 Alur Status Percakapan

Setiap percakapan memiliki **status** yang menentukan siapa yang menanganinya. Status dapat berubah seiring berjalannya percakapan:

```
  [START]
     │
     ▼
┌─────────────┐     AI tidak tahu jawabannya      ┌─────────────┐
│ ai_handling │ ─────────────────────────────────► │ waiting_cs  │
│  (default)  │     atau customer klik             │  (antrian)  │
└─────────────┘     "Hubungi Admin"                └──────┬──────┘
                                                          │ Admin klik
                                                          │ "Ambil Percakapan"
                                                          ▼
                                                   ┌─────────────┐
                                                   │ cs_handling │
                                                   │ (admin chat)│
                                                   └──────┬──────┘
                                                          │ Admin klik "Tutup"
                                                          ▼
                                                   ┌─────────────┐
                                                   │   closed    │
                                                   │  (selesai)  │
                                                   └─────────────┘
```

| Status | Arti | Siapa yang Menjawab |
|---|---|---|
| `ai_handling` | AI sedang menangani percakapan | Bot (AI otomatis) |
| `waiting_cs` | Menunggu agen manusia | — (tidak ada balasan otomatis) |
| `cs_handling` | Agen manusia sudah mengambil alih | Admin / CS |
| `closed` | Percakapan telah ditutup | — |

---

## 📋 Penjelasan Detail Per Tahap

### 1️⃣ Customer Membuka Help Desk

**File:** [`index.php`](index.php) → [`api/start-conversation.php`](api/start-conversation.php)

Ketika customer mengklik tombol **"Help Desk"** di halaman utama, JavaScript secara otomatis memanggil `api/start-conversation.php`.

**Proses yang terjadi:**
1. PHP memeriksa apakah session saat ini sudah memiliki `conv_id` (ID percakapan).
2. Jika belum ada, PHP mengecek database apakah `session_key` (ID PHP session) sudah memiliki percakapan aktif.
3. Jika belum ada sama sekali, sistem membuat **percakapan baru** di tabel `conversations` dengan:
   - `session_key` = ID session PHP customer
   - `customer_name` = `'Tamu'`
   - `status` = `'ai_handling'` ← percakapan dimulai dengan AI
4. Pesan sambutan awal dari AI (`"Halo! Ada yang bisa saya bantu?"`) langsung dimasukkan ke database.
5. `conversation_id` dikembalikan ke browser customer.

> ✅ Satu customer = Satu sesi = Satu percakapan aktif. Jika customer menutup dan membuka kembali browser (selama session masih hidup), percakapan lama akan dilanjutkan.

---

### 2️⃣ Customer Mengirim Pesan → AI Menjawab

**File:** [`api/send-message.php`](api/send-message.php) → [`AI/project/main.py`](AI/project/main.py) → [`AI/project/ai.py`](AI/project/ai.py)

Ketika customer mengetik pesan dan menekan tombol **"Kirim"**:

#### a. PHP Menerima Pesan
PHP membaca `conv_id` dan isi pesan, lalu **menyimpan pesan customer** ke tabel `messages` dengan `sender_role = 'user'`.

#### b. Cek Status Percakapan
PHP membaca status percakapan dari database dan masuk ke logika `switch`:

**Jika status = `ai_handling`:**

1. PHP mengirim pertanyaan customer ke **Python FastAPI** via cURL:
   ```
   POST http://127.0.0.1:8000/detect-handoff
   Body: { "question": "..." }
   ```
2. Python meneruskan pertanyaan ke **RAG Chain** (Retrieval-Augmented Generation):
   - **Retriever** mencari dokumen yang relevan dari **ChromaDB** (vector database).
   - Dokumen yang relevan diambil dari dataset QnA (`Dataset_QnA_Disty.csv`) yang sudah di-*embed* sebelumnya menggunakan model `sentence-transformers/all-MiniLM-L6-v2`.
   - Konteks + pertanyaan dikirim ke **LLM Groq (LLaMA 3.3 70B)** untuk menghasilkan jawaban.
3. Python juga memeriksa apakah jawaban AI mengandung frasa ketidaktahuan seperti:
   - `"tidak mengerti"`, `"tidak tahu"`, `"maaf saya tidak"`, `"tidak dapat menjawab"`, dll.
4. Python mengembalikan respons ke PHP:
   ```json
   {
     "answer": "...",
     "needs_human": true/false
   }
   ```

#### c. PHP Menyimpan & Memutuskan
- Jawaban AI disimpan ke tabel `messages` dengan `sender_role = 'ai'`.
- **Jika `needs_human = false`:** Jawaban AI dikirim balik ke browser customer → selesai.
- **Jika `needs_human = true`:** Status percakapan diubah ke `waiting_cs` + pesan sistem dikirim → lanjut ke Tahap 3.

**Jika status = `waiting_cs`:**
PHP hanya membalas dengan pesan informasi: *"Pesan Anda sudah tercatat. Agen kami akan segera merespons..."* — AI **tidak** aktif menjawab.

**Jika status = `cs_handling`:**
PHP menyimpan pesan customer ke database dan memberi sinyal ke frontend. Admin/CS akan membalas secara manual.

---

### 3️⃣ Bagaimana AI Bisa Menjawab Pertanyaan? (Mekanisme RAG)

**File:** [`AI/project/ai.py`](AI/project/ai.py) · [`AI/project/embedding.py`](AI/project/embedding.py)

Sistem AI menggunakan teknik **RAG (Retrieval-Augmented Generation)** — AI tidak mengandalkan pengetahuan umum, melainkan hanya menjawab berdasarkan **dokumen yang sudah disiapkan**.

#### Tahap Persiapan (dijalankan sekali oleh developer)
1. File dataset QnA (`Dataset_QnA_Disty.csv`) yang berisi pasangan pertanyaan-jawaban dibaca oleh `embedding.py`.
2. Setiap baris di-*embed* (dikonversi menjadi vektor numerik) menggunakan model `sentence-transformers/all-MiniLM-L6-v2` dari HuggingFace.
3. Vektor-vektor tersebut disimpan secara lokal di **ChromaDB** (`AI/project/chroma_db/`).

#### Tahap Menjawab Pertanyaan (setiap kali ada pesan masuk)
```
Pertanyaan Customer
       │
       ▼
  [ Embedding ]  ←── Model: all-MiniLM-L6-v2
       │
       ▼
  [ ChromaDB Retriever ]  ←── Cari 2 dokumen paling relevan (k=2)
       │
       ▼
  [ Prompt Template ]
  "Anda adalah asisten Customer Support...
   Konteks: {dokumen_relevan}
   Pertanyaan: {pertanyaan_customer}"
       │
       ▼
  [ LLM Groq — LLaMA 3.3 70B ]
       │
       ▼
     Jawaban
```

> ⚠️ Jika pertanyaan tidak relevan dengan konteks yang tersedia, AI diperintahkan untuk menjawab dengan frasa seperti *"Maaf saya tidak mengerti dengan pertanyaan anda"* — yang kemudian memicu **handoff** ke manusia.

---

### 4️⃣ Customer Minta Bantuan Admin Secara Manual

**File:** [`api/request-human.php`](api/request-human.php)

Selain handoff otomatis oleh AI, customer juga bisa menekan tombol **"🙋 Hubungi Admin"** secara manual.

**Proses yang terjadi:**
1. Browser customer mengirim `POST` ke `api/request-human.php`.
2. PHP hanya mengubah status percakapan dari `ai_handling` → `waiting_cs` (tidak bisa digunakan jika sudah dalam status lain).
3. Pesan sistem ditambahkan ke percakapan: *"Pengguna meminta bantuan agen manusia. Mohon tunggu, agen kami akan segera bergabung..."*
4. Browser customer menampilkan pesan tunggu; tombol "Hubungi Admin" dinonaktifkan.

---

### 5️⃣ Admin Melihat & Menangani Percakapan

**File:** [`Admin/chat-view.php`](Admin/chat-view.php) · [`api/admin-get-conversations.php`](api/admin-get-conversations.php)

Admin mengakses halaman **Chat** di panel admin. JavaScript melakukan **short polling setiap 5 detik** ke `api/admin-get-conversations.php` untuk memperbarui daftar percakapan aktif.

**Urutan tampilan percakapan di panel admin:**
1. 🔴 `waiting_cs` — Menunggu agen (prioritas tertinggi, ada indikator merah)
2. 🟡 `cs_handling` — Sedang ditangani agen
3. 🤖 `ai_handling` — Masih ditangani AI

**Fitur filter:** Admin bisa memfilter percakapan berdasarkan statusnya.

---

### 6️⃣ Admin Mengambil Alih Percakapan

**File:** [`api/admin-assign.php`](api/admin-assign.php)

Ketika admin mengklik tombol **"🙋 Ambil Percakapan"** pada percakapan berstatus `waiting_cs`:

1. Browser admin mengirim `POST` ke `api/admin-assign.php` dengan `conv_id`.
2. PHP memverifikasi bahwa pengguna yang login memiliki role `admin` atau `cs`.
3. Status percakapan diubah ke `cs_handling` dan `assigned_cs_id` diisi dengan ID admin yang mengambil alih.
4. Pesan sistem dikirim ke percakapan: *"Agen [Nama Admin] telah bergabung. Anda sekarang terhubung ke agen manusia."*
5. Customer di browser-nya akan melihat pesan sistem ini pada polling berikutnya (setiap ~3 detik).

---

### 7️⃣ Admin Membalas Pesan Customer

**File:** [`api/admin-send-message.php`](api/admin-send-message.php)

Saat admin mengetik pesan dan menekan "Kirim" di panel chat:

1. Browser admin mengirim `POST` ke `api/admin-send-message.php`.
2. PHP menyimpan pesan dengan `sender_role = 'cs'` dan nama admin yang sedang login.
3. Jika status masih `waiting_cs` atau `ai_handling`, PHP otomatis mengubahnya ke `cs_handling`.
4. Browser customer menerima pesan admin ini melalui mekanisme polling pada `api/get-messages.php`.

---

### 8️⃣ Mekanisme Real-time (Polling)

Sistem ini tidak menggunakan WebSocket. Sebagai gantinya, digunakan **short polling** dari sisi JavaScript:

| Sisi | Interval | Endpoint | Tujuan |
|---|---|---|---|
| Customer | ~3 detik | `api/get-messages.php` | Mendapatkan balasan AI atau admin |
| Admin | ~5 detik | `api/admin-get-conversations.php` | Memperbarui daftar percakapan |
| Admin | ~3 detik | `api/get-messages.php` | Mendapatkan pesan baru dalam percakapan yang dibuka |

`api/get-messages.php` hanya mengambil pesan dengan `id > lastMsgId` sehingga **tidak ada duplikat** dan transfer data tetap efisien.

---

### 9️⃣ Admin Menutup Percakapan

**File:** [`api/admin-close-conversation.php`](api/admin-close-conversation.php)

Ketika masalah customer telah selesai, admin menekan tombol **"✅ Tutup"**:

1. Status percakapan diubah ke `closed`.
2. Pesan sistem dikirim: *"Percakapan ini telah ditutup oleh [Nama Admin]. Terima kasih."*
3. Percakapan yang sudah `closed` **tidak ditampilkan** lagi di daftar percakapan aktif admin.

---

## 🔐 Sistem Autentikasi Admin

**File:** [`includes/auth.php`](includes/auth.php) · [`login.php`](login.php)

- Admin dan CS login melalui halaman `login.php` menggunakan **username** dan **password** yang tersimpan di tabel `users`.
- Password diverifikasi menggunakan `password_verify()` PHP (bcrypt-compatible).
- Setelah login, data pengguna (id, username, role, nama) disimpan di **PHP Session**.
- Semua API endpoint untuk admin (`api/admin-*.php`) memeriksa session dan **menolak akses** dengan HTTP 401 jika tidak terautentikasi.
- Role yang diizinkan: `admin` dan `cs`.

---

## 🗄️ Struktur Database

**File:** [`database.sql`](database.sql)

```sql
-- Tabel pengguna admin & CS
users (id, username, password, full_name, role, created_at)

-- Tabel percakapan (satu baris = satu sesi customer)
conversations (id, session_key, customer_name, customer_email,
               status, assigned_cs_id, created_at, updated_at)

-- Tabel semua pesan dalam percakapan
messages (id, conversation_id, sender_role, sender_name,
          content, created_at)
```

**`sender_role`** pada tabel `messages` bisa berisi:
- `user` — Pesan dari customer
- `ai` — Balasan dari AI bot
- `cs` — Balasan dari admin/CS
- `system` — Notifikasi otomatis dari sistem (handoff, bergabung, dsb.)

---

## 📂 Peta File Proyek

```
chatbot-ai/
├── index.php                    # Halaman utama + widget Help Desk (customer)
├── login.php                    # Halaman login admin/CS
├── logout.php                   # Hapus session & redirect
├── database.sql                 # Skema database MySQL
│
├── includes/
│   ├── db.php                   # Fungsi koneksi PDO ke MySQL
│   └── auth.php                 # Logic autentikasi login
│
├── api/
│   ├── start-conversation.php   # Memulai/melanjutkan percakapan customer
│   ├── send-message.php         # Kirim pesan customer → AI / waiting
│   ├── request-human.php        # Trigger handoff manual ke CS
│   ├── get-messages.php         # Polling pesan baru (customer & admin)
│   ├── admin-get-conversations.php  # Daftar percakapan aktif (admin)
│   ├── admin-assign.php         # Admin ambil alih percakapan
│   ├── admin-send-message.php   # Admin kirim balasan ke customer
│   └── admin-close-conversation.php # Admin tutup percakapan
│
├── Admin/
│   ├── dashboard-view.php       # Halaman dashboard admin
│   ├── chat-view.php            # Halaman chat admin (real-time polling)
│   ├── staff-view.php           # Halaman manajemen staf
│   └── settings-view.php        # Halaman pengaturan
│
└── AI/
    └── project/
        ├── main.py              # FastAPI server (endpoint /detect-handoff)
        ├── ai.py                # RAG chain — retriever + LLM Groq
        ├── embedding.py         # Script embedding dataset ke ChromaDB
        ├── chroma_db/           # Vector database lokal (hasil embedding)
        └── dataset/
            └── Dataset_QnA_Disty.csv  # Data QnA untuk pengetahuan AI
```

---

## ⚙️ Teknologi yang Digunakan

| Komponen | Teknologi |
|---|---|
| Frontend | HTML, CSS, JavaScript (Vanilla) |
| Backend Web | PHP 8+, Apache (XAMPP) |
| Database | MySQL (PDO) |
| AI Backend | Python, FastAPI |
| LLM | Groq API — LLaMA 3.3 70B Versatile |
| Embedding Model | HuggingFace `sentence-transformers/all-MiniLM-L6-v2` |
| Vector Database | ChromaDB (lokal) |
| Framework AI | LangChain (RAG, prompt template, output parser) |
| Komunikasi AI-PHP | cURL (HTTP POST ke `localhost:8000`) |
