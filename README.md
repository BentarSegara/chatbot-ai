# 🤖 Disty Teknologi — Help Desk Hybrid AI + Admin

> Sistem **Help Desk** berbasis web yang menggabungkan **AI Chatbot** (RAG) dengan kemampuan **handoff ke agen manusia** secara real-time.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![Python](https://img.shields.io/badge/Python-3.12-3776AB?style=flat-square&logo=python&logoColor=white)
![FastAPI](https://img.shields.io/badge/FastAPI-0.x-009688?style=flat-square&logo=fastapi&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql&logoColor=white)
![LangChain](https://img.shields.io/badge/LangChain-RAG-1C3C3C?style=flat-square)

---

## 📖 Tentang Proyek

Proyek ini adalah platform **Disty Teknologi** yang dilengkapi sistem **Help Desk Hybrid**. Pengguna dapat:

1. **Bertanya ke AI chatbot** — AI menjawab menggunakan sistem RAG (Retrieval-Augmented Generation) berbasis dataset perusahaan.
2. **Dialihkan ke agen manusia** — Jika AI tidak bisa menjawab (atau pengguna meminta), percakapan secara otomatis diteruskan ke admin/CS.
3. **Chat real-time dengan admin** — Admin menerima dan membalas pesan secara live melalui panel admin.

### Alur Sistem

```
User buka Help Desk
        │
        ▼
AI menjawab (RAG: LangChain + ChromaDB + Groq)
        │
   AI tidak tahu? ──── YA ──► Status: "Menunggu Agen" ──► Admin ambil alih
        │                                                          │
        NO                                                         ▼
        │                                               Admin ↔ User chat real-time
        ▼
AI terus menjawab                                        Status: "Selesai" (closed)
```

---

## 🗂️ Struktur Proyek

```
chatbot-ai/
├── AI/
│   └── project/
│       ├── ai.py              # Logika RAG (LangChain + ChromaDB + Groq)
│       ├── embedding.py       # Script embedding dataset ke ChromaDB
│       ├── main.py            # FastAPI server
│       ├── requirements.txt   # Dependensi Python
│       ├── .env               # API Key (tidak di-commit)
│       └── chroma_db/         # Vector database lokal
├── Admin/
│   ├── chat-view.php          # UI chat admin (real-time)
│   ├── dashboard-view.php     # Dashboard admin
│   ├── staff-view.php         # Manajemen staff
│   └── settings-view.php      # Pengaturan
├── api/
│   ├── start-conversation.php # Inisiasi percakapan baru
│   ├── send-message.php       # Kirim pesan + panggil AI + deteksi handoff
│   ├── get-messages.php       # Polling pesan baru
│   ├── request-human.php      # User minta bantuan manusia
│   ├── admin-get-conversations.php
│   ├── admin-send-message.php
│   ├── admin-assign.php
│   └── admin-close-conversation.php
├── assets/
│   ├── css/
│   │   ├── style.css          # CSS halaman utama
│   │   ├── admin.css          # CSS panel admin
│   │   └── login.css          # CSS halaman login
│   └── js/
│       └── help-desk-chat.js  # JS chat widget (polling + API)
├── includes/
│   ├── auth.php               # Autentikasi via database
│   └── db.php                 # Koneksi PDO ke MySQL
├── index.php                  # Halaman utama + Help Desk widget
├── login.php                  # Halaman login
├── logout.php                 # Logout handler
├── database.sql               # Skema database (import ke phpMyAdmin)
└── seed.php                   # Seeder akun admin (hapus setelah dijalankan)
```

---

## ⚙️ Prasyarat (Prerequisites)

Pastikan semua software berikut sudah terinstal:

| Software | Versi | Fungsi |
|---|---|---|
| **XAMPP** | ≥ 8.x | PHP + Apache + MySQL |
| **Python** | ≥ 3.10 | Menjalankan FastAPI + AI |
| **pip** | terbaru | Mengelola package Python |
| **Browser** | Chrome / Firefox | Mengakses aplikasi |

---

## 🚀 Cara Instalasi & Menjalankan

### Langkah 1 — Clone / Download Proyek

Letakkan folder proyek di direktori XAMPP:

```
C:\xampp\htdocs\chatbot-ai\
```

### Langkah 2 — Konfigurasi Database

**2a. Buka XAMPP Control Panel** → Start **Apache** dan **MySQL**.

**2b. Buka phpMyAdmin:**
```
http://localhost/phpmyadmin
```

**2c. Buat database** bernama `chatbot_ai` (jika belum ada):
- Klik **New** di panel kiri
- Nama database: `chatbot_ai`
- Collation: `utf8mb4_unicode_ci`
- Klik **Create**

**2d. Import skema database:**
- Pilih database `chatbot_ai`
- Klik tab **Import**
- Pilih file `database.sql` dari root proyek
- Klik **Go**

### Langkah 3 — Konfigurasi Koneksi Database

Buka file `includes/db.php` dan sesuaikan dengan konfigurasi MySQL Anda:

```php
$host   = 'localhost';
$dbname = 'chatbot_ai';
$user   = 'root';         // ← Ganti jika berbeda
$pass   = 'password';     // ← Ganti dengan password MySQL Anda
```

### Langkah 4 — Buat Akun Admin

Buka browser dan akses URL berikut **sekali saja**:

```
http://localhost/chatbot-ai/seed.php
```

Jika berhasil, akan muncul pesan:

```
✅ Seeder selesai!

Akun yang berhasil dibuat:
  - [admin] username: admin | password: admin123
  - [cs]    username: cs1   | password: cs123

⚠️ HAPUS file seed.php ini setelah selesai!
```

> ⚠️ **Penting:** Hapus file `seed.php` setelah akun berhasil dibuat agar tidak bisa diakses orang lain.

### Langkah 5 — Setup Python (AI Chatbot)

**5a. Buka terminal** dan masuk ke folder AI:

```bash
cd C:\xampp\htdocs\chatbot-ai\AI\project
```

**5b. Aktifkan virtual environment** (jika ada):

```bash
# Windows (PowerShell)
..\.venv\Scripts\Activate.ps1

# Windows (Command Prompt)
..\.venv\Scripts\activate.bat
```

**5c. Install dependensi** (jika belum):

```bash
pip install -r requirements.txt
```

**5d. Buat file `.env`** di folder `AI/project/` dan isi dengan API Key Groq Anda:

```env
GROQ_API_KEY = gsk_xxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

> Daftar akun dan dapatkan API Key gratis di: https://console.groq.com

**5e. Jalankan FastAPI server:**

```bash
uvicorn main:app --reload
```

Jika berhasil, terminal akan menampilkan:
```
INFO:     Uvicorn running on http://127.0.0.1:8000 (Press CTRL+C to quit)
INFO:     Started reloader process
```

### Langkah 6 — Buka Aplikasi

Buka browser dan akses:

```
http://localhost/chatbot-ai/
```

---

## 🧪 Cara Testing (Satu Perangkat)

Karena menggunakan session PHP, Anda perlu **dua session browser terpisah** untuk mencoba sebagai customer dan admin sekaligus.

### Opsi A — Dua Browser Berbeda (Direkomendasikan)

| Browser | Peran | URL |
|---|---|---|
| Chrome | 👤 Customer | `http://localhost/chatbot-ai/` |
| Firefox / Edge | 👑 Admin | `http://localhost/chatbot-ai/login.php` |

### Opsi B — Normal + Incognito

| Window | Peran | Cara Buka |
|---|---|---|
| Normal | 👑 Admin | Login biasa |
| Incognito | 👤 Customer | `Ctrl+Shift+N` (Chrome) |

---

## 🎯 Panduan Penggunaan

### Sebagai Customer (Pengguna Biasa)

1. Buka `http://localhost/chatbot-ai/`
2. Klik tombol **Help Desk** di pojok kanan bawah
3. Kirim pertanyaan — AI akan menjawab secara otomatis
4. Jika ingin berbicara dengan manusia, klik tombol **"🙋 Hubungi Admin"**
5. Tunggu agen bergabung — status akan berubah secara otomatis

### Sebagai Admin

1. Login di `http://localhost/chatbot-ai/login.php`
   - Username: `admin`
   - Password: `admin123`
2. Pergi ke menu **💬 Chat**
3. Percakapan yang perlu ditangani akan muncul di sidebar (ditandai titik merah 🔴)
4. Klik percakapan → klik **"🙋 Ambil Percakapan"**
5. Balas pesan customer melalui form di bawah
6. Setelah selesai, klik **"✅ Tutup"** untuk menutup percakapan

---

## 📊 Status Percakapan

| Status | Arti | Aksi Selanjutnya |
|---|---|---|
| 🤖 `ai_handling` | AI sedang menjawab | AI otomatis merespons |
| ⏳ `waiting_cs` | Menunggu agen manusia | Admin klik "Ambil Percakapan" |
| 👤 `cs_handling` | Admin/CS sedang membalas | Admin balas via chat |
| ✅ `closed` | Percakapan selesai | — |

---

## 🔑 Akun Default

| Role | Username | Password |
|---|---|---|
| Administrator | `admin` | `admin123` |
| Customer Service | `cs1` | `cs123` |

> ⚠️ Ganti password setelah pertama kali login di lingkungan produksi.

---

## 🛠️ Teknologi yang Digunakan

| Layer | Teknologi |
|---|---|
| Frontend | HTML5, Vanilla CSS, JavaScript (Fetch API + Polling) |
| Backend PHP | PHP 8, PDO (MySQL), XAMPP |
| Backend AI | Python 3, FastAPI, Uvicorn |
| AI / RAG | LangChain, ChromaDB, HuggingFace Embeddings |
| LLM | Groq API (LLaMA 3.3 70B) |
| Database | MySQL (via XAMPP) |
| Font | Outfit (Google Fonts) |

---

## ❓ Troubleshooting

| Masalah | Solusi |
|---|---|
| Halaman error / putih | Pastikan Apache & MySQL aktif di XAMPP |
| Login gagal setelah import DB | Jalankan `seed.php` untuk membuat akun |
| AI tidak merespons | Pastikan `uvicorn main:app --reload` berjalan di terminal |
| Pesan tidak muncul di admin | Cek apakah URL menggunakan `localhost` (bukan `127.0.0.1`) |
| Error koneksi database | Sesuaikan kredensial di `includes/db.php` |
| `GROQ_API_KEY` error | Pastikan file `.env` ada di folder `AI/project/` dan key valid |

---

## 📝 Lisensi

Proyek ini dibuat sebagai demonstrasi sistem Help Desk Hybrid untuk **Disty Teknologi**.

---

<div align="center">
  <strong>Dibuat dengan ❤️ untuk Disty Teknologi</strong><br>
  <sub>AI Chatbot + Human Agent = Layanan Pelanggan Terbaik</sub>
</div>
