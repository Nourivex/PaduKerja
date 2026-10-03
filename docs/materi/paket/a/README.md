# Panduan Paket A: Lowongan Kerja

> **Penanggung Jawab:** MUHAMAD FAHREN ANDREAN RANGKUTI  
> **Layanan Microservice:** Job Catalog Service (Service 2)  
> **Mata Kuliah:** Pemrograman Web Service — Kelompok 4 UHN  

---

## 📋 Deskripsi Tugas Paket A

Paket A berfokus pada pengelolaan katalog lowongan kerja dan penentuan kriteria keahlian (*skill requirements*) beserta pembobotannya:

1. **`GET /api/jobs`** : Menampilkan dan memfilter daftar lowongan kerja yang tersedia (pencarian judul, lokasi, keahlian, tipe pekerjaan, dan pagination).
2. **`POST /api/jobs`** : Recruiter memposting lowongan baru lengkap dengan bobot kualifikasi keahlian (`required = 3`, `important = 2`, `nice_to_have = 1`).

---

## 📁 Berkas & Script Terkait

Bila Anda ingin mempelajari atau memodifikasi script Paket A, berikut file-file yang menyusunnya:

| Peran Berkas | Lokasi File | Fungsi Utama |
|---|---|---|
| **Controller** | [`app/Http/Controllers/Api/JobVacancyController.php`](../../../../app/Http/Controllers/Api/JobVacancyController.php) | Logika `index()` untuk filter/paginasi dan `store()` untuk pembuatan lowongan baru. |
| **Model** | [`app/Models/JobVacancy.php`](../../../../app/Models/JobVacancy.php) | Definisi entitas data lowongan, casting atribut JSON `requirements`, dan relasi ke User Recruiter. |
| **Migration** | [`database/migrations/2026_10_03_000002_create_job_vacancies_table.php`](../../../../database/migrations/2026_10_03_000002_create_job_vacancies_table.php) | Skema tabel database `job_vacancies`. |
| **Routing** | [`routes/api.php`](../../../../routes/api.php) | Pendaftaran rute HTTP `GET /api/jobs` dan `POST /api/jobs`. |

---

## 🚀 Panduan Uji Coba API (Testing)

### Persiapan: Dapatkan Token Recruiter
Untuk menjalankan endpoint `POST /api/jobs`, diperlukan token akses dengan peran `recruiter`.

```bash
# Login sebagai Recruiter
curl -s -X POST http://127.0.0.1:32768/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "recruiter@padukerja.id", "password": "password123"}' | jq -r '.data.token'
```
*Salin (copy) token yang dihasilkan untuk dimasukkan pada header `Authorization: Bearer <token>`.*

---

### Endpoint 1: Menampilkan & Filter Daftar Lowongan Kerja

- **Method:** `GET`
- **URL:** `http://127.0.0.1:32768/api/jobs`
- **Headers:**
  - `Accept: application/json`
- **Query Parameters yang Didukung:**
  - `skill=Laravel` (mencari lowongan yang mensyaratkan keahlian Laravel)
  - `location=Jakarta` (filter lowongan di Jakarta)
  - `search=engineer` (kata kunci pada judul/perusahaan/deskripsi)
  - `per_page=5` (jumlah per halaman)

#### Contoh Uji Coba dengan curl:
```bash
# Ambil lowongan dengan filter keahlian Laravel dan lokasi Jakarta
curl -s "http://127.0.0.1:32768/api/jobs?skill=Laravel&location=Jakarta" \
  -H "Accept: application/json" | jq .
```

#### Contoh Response `200 OK`:
```json
{
  "status": "success",
  "message": "Daftar lowongan kerja berhasil diambil.",
  "data": [
    {
      "id": 1,
      "title": "Senior Backend Engineer",
      "company": "PT Teknologi Maju",
      "location": "Jakarta Selatan",
      "employment_type": "full-time",
      "salary_min": 12000000,
      "salary_max": 18000000,
      "quota": 3,
      "requirements": [
        { "skill": "PHP", "level": "required", "weight": 3 },
        { "skill": "Laravel", "level": "required", "weight": 3 },
        { "skill": "MySQL", "level": "important", "weight": 2 }
      ],
      "status": "open"
    }
  ],
  "meta": {
    "timestamp": "2026-10-03T12:00:00+07:00",
    "version": "1.0.0",
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 1,
      "last_page": 1
    }
  }
}
```

---

### Endpoint 2: Recruiter Memposting Lowongan Baru

- **Method:** `POST`
- **URL:** `http://127.0.0.1:32768/api/jobs`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `Authorization: Bearer <TOKEN_RECRUITER>`
- **Request Body (JSON):**
```json
{
  "title": "DevOps & Cloud Engineer",
  "company": "PT Solusi Cloud Nusantara",
  "description": "Mengelola deployment infrastruktur berbasis container dan CI/CD pipeline.",
  "location": "Jakarta Pusat",
  "employment_type": "full-time",
  "salary_min": 15000000,
  "salary_max": 22000000,
  "quota": 2,
  "requirements": [
    { "skill": "Docker", "level": "required", "weight": 3 },
    { "skill": "Kubernetes", "level": "required", "weight": 3 },
    { "skill": "Linux", "level": "important", "weight": 2 },
    { "skill": "CI/CD", "level": "important", "weight": 2 }
  ]
}
```

#### Contoh Uji Coba dengan curl:
```bash
RECRUITER_TOKEN="<PASTE_TOKEN_RECRUITER_DISINI>"

curl -s -X POST http://127.0.0.1:32768/api/jobs \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $RECRUITER_TOKEN" \
  -d '{
    "title": "DevOps & Cloud Engineer",
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
  }' | jq .
```

#### Contoh Response `201 Created`:
```json
{
  "status": "success",
  "message": "Lowongan kerja baru berhasil diposting.",
  "data": {
    "id": 3,
    "title": "DevOps & Cloud Engineer",
    "company": "PT Solusi Cloud Nusantara",
    "location": "Jakarta Pusat",
    "quota": 2,
    "requirements_count": 4,
    "status": "open",
    "created_at": "2026-10-03T12:00:00+07:00"
  },
  "meta": {
    "timestamp": "2026-10-03T12:00:00+07:00",
    "version": "1.0.0"
  }
}
```

---

## 📝 Lembar Analisis HTTP Request-Response (Bahan Laporan)

Sesuai format tugas individu modul kuliah Minggu 2:

| Komponen Analisis | Request 1 (`GET /api/jobs`) | Request 2 (`POST /api/jobs`) |
|---|---|---|
| **HTTP Method** | `GET` (Safe, Idempotent, Read-only) | `POST` (Non-idempotent, Resource Creation) |
| **Request URI** | `/api/jobs?skill=Laravel&location=Jakarta` | `/api/jobs` |
| **Headers Penting** | `Accept: application/json` | `Content-Type: application/json`, `Authorization: Bearer <token>` |
| **Status Code** | `200 OK` | `201 Created` (atau `422` jika validasi field gagal) |
| **Format Payload** | Tanpa Request Body | JSON Object berisi data lowongan & array kriteria keahlian |
| **Envelope Response** | `{ status, message, data, meta: { pagination } }` | `{ status, message, data: { id, title, ... }, meta }` |
