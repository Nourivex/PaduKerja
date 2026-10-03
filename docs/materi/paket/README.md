# Panduan Pembagian Tugas Praktikum REST API — PaduKerja

> Mata Kuliah: Pemrograman Web Service (5537344 / 3 SKS) — Universitas Harkat Negeri
> Dosen Pengampu: Zaenul Arif, S.Kom., M.Kom
> Kelompok 4: MUHAMMAD AFFIF, MUHAMAD FAHREN ANDREAN RANGKUTI, NABE'ELA AYU NING TYAZ ZAHRA, MUHAMMAD YASIR ILHAM NABIL

---

## 📌 Ringkasan 8 Endpoint Inti REST API

Sistem PaduKerja dirancang memiliki tepat **8 REST API Inti**, terbagi secara merata ke dalam 4 paket tugas individu untuk masing-masing anggota tim kolaborator:

| Paket | Penanggung Jawab | Microservice | Method & Endpoint | Deskripsi Tugas |
|:---:|---|---|---|---|
| **[Paket A](a/README.md)** | **MUHAMAD FAHREN ANDREAN RANGKUTI** | Job Catalog Service | `GET /api/jobs`<br>`POST /api/jobs` | 1. Menampilkan & filter lowongan kerja<br>2. Recruiter memposting lowongan baru |
| **[Paket B](b/README.md)** | **MUHAMMAD AFFIF** | Application & Matchmaking | `POST /api/applications`<br>`DELETE /api/applications/{id}` | 1. Mengirim lamaran + skoring otomatis<br>2. Membatalkan pengajuan lamaran |
| **[Paket C](c/README.md)** | **MUHAMMAD YASIR ILHAM NABIL** | Recruitment Pipeline | `PATCH /api/pipelines/{id}/status`<br>`GET /api/pipelines/{id}` | 1. Recruiter update tahap seleksi<br>2. Pelamar melihat riwayat linimasa |
| **[Paket D](d/README.md)** | **NABE'ELA AYU NING TYAZ ZAHRA** | Auth & Profile Service | `POST /api/auth/login`<br>`PUT /api/profile/skills` | 1. Login pengguna & token akses JWT<br>2. Simpan/update keahlian profil |

---

## 📂 Struktur Dokumentasi Per Paket

Masing-masing folder paket di bawah ini dapat langsung dibagikan ke anggota tim terkait untuk dipelajari, dijalankan, dan diuji secara mandiri:

1. **[`docs/materi/paket/a/README.md`](a/README.md)** — Panduan lengkap Paket A (Script, Controller, Model, Request/Response Payload, Uji Coba Thunder Client & curl).
2. **[`docs/materi/paket/b/README.md`](b/README.md)** — Panduan lengkap Paket B (Script, Controller, Rumus Skoring Weighted Jaccard, Request/Response Payload, Uji Coba).
3. **[`docs/materi/paket/c/README.md`](c/README.md)** — Panduan lengkap Paket C (Script, Controller, Tahapan Pipeline Screening $\to$ Offering, Request/Response Payload, Uji Coba).
4. **[`docs/materi/paket/d/README.md`](d/README.md)** — Panduan lengkap Paket D (Script, Controller, Middleware JWT, Request/Response Payload, Uji Coba).

---

## 🛠️ Standar Global Komunikasi API

Setiap endpoint pada seluruh paket wajib mengikuti kaidah berikut:

1. **Format JSON Envelope:**
   ```json
   {
     "status": "success | error",
     "message": "Pesan penjelasan untuk manusia",
     "data": { ... },
     "meta": {
       "timestamp": "ISO8601",
       "version": "1.0.0"
     }
   }
   ```
2. **Header Wajib:**
   - `Content-Type: application/json` (pada POST, PUT, PATCH)
   - `Accept: application/json` (pada seluruh request)
   - `Authorization: Bearer <token>` (pada endpoint terproteksi)
3. **Kode Status HTTP Standar:**
   - `200 OK` : Request berhasil diproses.
   - `201 Created` : Data baru berhasil disimpan (POST).
   - `400 Bad Request` : Input tidak valid atau lowongan sudah tutup.
   - `401 Unauthorized` : Token JWT tidak ada, salah, atau kedaluwarsa.
   - `403 Forbidden` : Hak akses peran (role) tidak mencukupi.
   - `404 Not Found` : ID data tidak ditemukan di database.
   - `409 Conflict` : Duplikasi data (misal: sudah pernah melamar).
   - `422 Unprocessable Entity` : Validasi field gagal.
