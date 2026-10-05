# Panduan Pengujian API - PaduKerja (Thunder Client & Postman)

> Panduan praktikum & testing REST API PaduKerja untuk **Paket A, B, C, dan D**.
> Sesuai standar mata kuliah Pemrograman Web Service (5537344 / 3 SKS) - Kelompok 4 UHN.

---

## 1. Lingkungan Pengujian (Environment)

Saat DDEV berjalan, API dapat diakses melalui:

| Tipe URL | URL Base API | Keterangan |
|----------|--------------|------------|
| **Local Port (Rekomendasi)** | `http://127.0.0.1:32768/api` | Akses langsung tanpa DNS resolver |
| **DDEV Domain (HTTPS)** | `https://padukerja.ddev.site/api` | Memerlukan SSL trust & Traefik router |

> **Health Check Endpoint:**
> `GET http://127.0.0.1:32768/api/health`

---

## 2. Akun Percobaan (Pre-Seeded Accounts)

Database telah disiapkan (*seeded*) dengan akun berikut:

| Peran (Role) | Email | Password | Keterangan |
|--------------|-------|----------|------------|
| **Recruiter** | `recruiter@padukerja.id` | `password123` | HR / Recruiter PT Teknologi Maju |
| **Pelamar (Budi)** | `budi@padukerja.id` | `password123` | Skills: `PHP`, `Laravel`, `MySQL`, `Git` |
| **Pelamar (Siti)** | `siti@padukerja.id` | `password123` | Skills: `Python`, `Django`, `PostgreSQL`, `Docker` |

---

## 3. Cara Import Koleksi ke API Client

### Opsi A: Thunder Client (VS Code)
1. Buka tab **Thunder Client** di VS Code.
2. Klik tab **Collections** (ikon folder).
3. Klik ikon menu titik tiga (`...`) di pojok atas -> pilih **Import**.
4. Pilih file: [`docs/thunder-collection_padukerja.json`](thunder-collection_padukerja.json) (atau `docs/PaduKerja.postman_collection.json`).
5. Semua folder Paket A, B, C, dan D akan langsung muncul dan siap dijalankan!

### Opsi B: Postman Desktop / Web
1. Buka aplikasi **Postman**.
2. Klik tombol **Import** di kiri atas.
3. Drag & drop file: [`docs/PaduKerja.postman_collection.json`](PaduKerja.postman_collection.json).
4. Variabel `base_url` sudah otomatis terset ke `http://127.0.0.1:32768/api`.

---

## 4. Rincian Pengujian Tiap Paket

### 📌 Paket D: Akun & Keahlian (Auth & Profile Service)
*Penanggung Jawab: MUHAMMAD AFFIF*

#### 1. POST: Login & Generate Token JWT
- **Endpoint:** `POST /api/auth/login`
- **Headers:** `Content-Type: application/json`, `Accept: application/json`
- **Body:**
```json
{
  "email": "budi@padukerja.id",
  "password": "password123"
}
```
- **Response `200 OK`:** Menghasilkan string JWT pada `data.token`. Copy token ini untuk request terproteksi berikutnya.

#### 2. PUT: Menyimpan/Update Data Keahlian (Skills) di Profil
- **Endpoint:** `PUT /api/profile/skills`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `Authorization: Bearer <TOKEN_PELAMAR>`
- **Body:**
```json
{
  "skills": ["PHP", "Laravel", "MySQL", "Git", "Redis", "Docker"]
}
```
- **Response `200 OK`:** Data array `skills` pengguna langsung terupdate di database.

---

### 📌 Paket A: Lowongan Kerja (Job Catalog Service)
*Penanggung Jawab: MUHAMAD FAHREN ANDREAN RANGKUTI*

#### 1. GET: Menampilkan & Filter Daftar Lowongan Kerja
- **Endpoint:** `GET /api/jobs`
- **Filter Query Params yang didukung:**
  - `search=backend` (mencari pada judul, perusahaan, atau deskripsi)
  - `location=Jakarta` (filter kota)
  - `skill=Laravel` (mencari lowongan yang mensyaratkan skill tertentu)
  - `per_page=10` (pagination)
- **Contoh Request:** `GET /api/jobs?skill=Laravel&location=Jakarta`
- **Response `200 OK`:** Menampilkan daftar lowongan beserta `pagination` meta.

#### 2. POST: Recruiter Memposting Lowongan Baru
- **Endpoint:** `POST /api/jobs`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `Authorization: Bearer <TOKEN_RECRUITER>`
- **Body:**
```json
{
  "title": "Cloud & DevOps Engineer",
  "company": "PT Solusi Cloud Nusantara",
  "description": "Mengelola deployment infrastruktur berbasis container dan CI/CD pipeline.",
  "location": "Jakarta Pusat",
  "employment_type": "full-time",
  "salary_min": 15000000,
  "salary_max": 22000000,
  "quota": 2,
  "requirements": [
    {"skill": "Docker", "level": "required", "weight": 3},
    {"skill": "Kubernetes", "level": "required", "weight": 3},
    {"skill": "Linux", "level": "important", "weight": 2},
    {"skill": "CI/CD", "level": "important", "weight": 2}
  ]
}
```
- **Response `201 Created`:** Lowongan tersimpan dengan ID baru dan status `open`.

---

### 📌 Paket B: Lamaran & Scoring (Application & Matchmaking Service)
*Penanggung Jawab: NABE'ELA AYU NING TYAZ ZAHRA*

#### 1. POST: Mengirim Lamaran + Otomatis Hitung Skor Kecocokan
- **Endpoint:** `POST /api/applications`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `Authorization: Bearer <TOKEN_PELAMAR>`
- **Body:**
```json
{
  "job_id": 2,
  "cover_letter": "Saya sangat tertarik dengan lowongan ini karena sesuai keahlian teknis saya.",
  "candidate_skills": ["React", "JavaScript", "Tailwind CSS", "Git"]
}
```
- **Response `201 Created`:**
  - Sistem otomatis menjalankan formula **Weighted Jaccard Similarity**:
    $$\text{Match Score} = \frac{\sum \text{weight skill yang cocok}}{\sum \text{weight seluruh skill lowongan}} \times 100\%$$
  - Memberikan kategori `excellent` ($\ge 80\%$), `good` ($50\% - 79\%$), atau `under_qualified` ($< 50\%$).
  - Otomatis membuat entri linimasa pipeline awal (`screening`).

#### 2. DELETE: Membatalkan Pengajuan Lamaran
- **Endpoint:** `DELETE /api/applications/{id}`
- **Headers:**
  - `Accept: application/json`
  - `Authorization: Bearer <TOKEN_PELAMAR>`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Pengajuan lamaran untuk posisi Frontend React Specialist berhasil dibatalkan.",
  "data": {
    "application_id": 2,
    "status": "canceled"
  }
}
```

---

### 📌 Paket C: Seleksi Pelamar (Recruitment Pipeline Service)
*Penanggung Jawab: MUHAMMAD YASIR ILHAM NABIL*

#### 1. PATCH: Recruiter Update Status/Tahap Seleksi Pelamar
- **Endpoint:** `PATCH /api/pipelines/{applicationId}/status`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `Authorization: Bearer <TOKEN_RECRUITER>`
- **Pilihan Stage:** `screening` $\to$ `assessment` $\to$ `interview` $\to$ `offering` $\to$ `rejected`
- **Body (Contoh Jadwal Interview):**
```json
{
  "stage": "interview",
  "status": "in_progress",
  "notes": "Lolos assessment teknis dengan nilai 92/100. Masuk sesi wawancara tim teknis.",
  "interview_schedule": {
    "datetime": "2026-10-10T10:00:00+07:00",
    "type": "online",
    "meeting_url": "https://meet.google.com/padukerja-interview",
    "interviewer": "Engineering Lead & HR Manager"
  }
}
```
- **Response `200 OK`:** Tahap seleksi bergerak maju, mencatat waktu transisi secara akurat.

#### 2. GET: Pelamar Melihat Riwayat Linimasa Lamaran
- **Endpoint:** `GET /api/pipelines/{applicationId}`
- **Headers:**
  - `Accept: application/json`
  - `Authorization: Bearer <TOKEN_PELAMAR>`
- **Response `200 OK`:** Menampilkan seluruh riwayat tahapan seleksi secara kronologis, catatan recruiter, status per tahap, tautan interview, dan ringkasan durasi proses.

---

## 5. Menjalankan via Terminal / Podman / DDEV

Jika ingin menjalankan test langsung dari command line (CLI):

```bash
# Cek status kesehatan API
curl -s http://127.0.0.1:32768/api/health | jq .

# Dapatkan Token Login Pelamar
BUDI_TOKEN=$(curl -s -X POST http://127.0.0.1:32768/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"budi@padukerja.id","password":"password123"}' | jq -r '.data.token')

# Lihat Riwayat Linimasa
curl -s http://127.0.0.1:32768/api/pipelines/1 \
  -H "Authorization: Bearer $BUDI_TOKEN" | jq .
```
