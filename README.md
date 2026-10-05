<p align="center">
  <img src="public/logo.png" width="96" alt="PaduKerja Logo">
</p>

<h1 align="center">PaduKerja</h1>

<p align="center">
  <strong>Platform Rekrutmen Terintegrasi Berbasis Microservice</strong><br>
  <em>Automated Skill-Matchmaking Engine & Multi-Stage Pipeline Tracker</em>
</p>

<p align="center">

![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-Microservices-0D1117?style=flat-square)
![API](https://img.shields.io/badge/API-REST%20JSON-blue?style=flat-square)
![DDEV](https://img.shields.io/badge/DDEV-supported-0D1117?style=flat-square)
![Platform](https://img.shields.io/badge/Platform-Fedora%20%2F%20Linux-294172?style=flat-square&logo=fedora&logoColor=white)
![Team](https://img.shields.io/badge/Team-Kelompok%204%20UHN-orange?style=flat-square)

</p>

---

## Informasi Akademik

| | |
|---|---|
| **Mata Kuliah** | Pemrograman Web Service (5537344 / 3 SKS) |
| **Universitas** | Universitas Harkat Negeri |
| **Dosen Pengampu** | Zaenul Arif, S.Kom., M.Kom |
| **Kelompok** | 4 |
| **Semester** | 7 (Ganjil) |

---

## Executive Summary

**PaduKerja** adalah platform rekrutmen terintegrasi yang dibangun dengan arsitektur **microservice** dan **REST API** berstandar industri. Sistem ini dirancang untuk menyelesaikan dua masalah fundamental dalam proses rekrutmen digital:

1. **Bagi Pencari Kerja** - fenomena *"black-hole resume"* di mana lamaran dikirim namun tidak pernah ada kejelasan status atau feedback.
2. **Bagi Recruiter** - tumpukan ratusan CV PDF yang tidak relevan, tanpa mekanisme filter otomatis berbasis keahlian.

---

## Problem Statement

Proses rekrutmen konvensional memiliki inefisiensi kritis:

- **Pencari kerja** mengirim lamaran ke puluhan lowongan tanpa mengetahui seberapa cocok profil mereka dengan kualifikasi yang diminta, dan tidak memiliki visibilitas terhadap tahapan seleksi.
- **Recruiter** harus menyaring ratusan CV secara manual, menghabiskan waktu untuk profil yang tidak memenuhi kualifikasi, dan kesulitan mengelola pipeline seleksi multi-tahap.

---

## Value Proposition

PaduKerja menghadirkan tiga solusi utama:

### 1. 🎯 Automated Skill-Matchmaking Engine
Kalkulasi persentase kecocokan skill profil pelamar versus kualifikasi lowongan secara **real-time** melalui API. Recruiter langsung mendapatkan daftar pelamar terurut berdasarkan skor kecocokan.

### 2. 📊 Multi-Stage Transparent Pipeline Tracker
Status seleksi bertahap yang transparan bagi kedua pihak:

```
Screening → Assessment → Interview → Offering / Rejected
```

Pelamar dapat memantau posisi mereka di pipeline secara real-time.

### 3. 🏗️ Desain Microservice Terdistribusi
4 service independen dengan pola **database-per-service**, berkomunikasi melalui kontrak **REST API JSON** yang ketat sesuai kaidah fondasi HTTP:
- Status codes `2xx` / `4xx` / `5xx`
- Semantik methods `GET` / `POST` / `PUT` / `PATCH` / `DELETE`
- Headers `Content-Type` & `Accept: application/json`

---

## Arsitektur Microservices & Pembagian Tugas Tim

| # | Service | Deskripsi | Penanggung Jawab |
|---|---------|-----------|------------------|
| 1 | **Auth & Profile Service** | Autentikasi JWT multi-role (`applicant`, `recruiter`, `admin`), manajemen profil pengguna, dan skill matrix profile | **MUHAMMAD AFFIF** |
| 2 | **Job Catalog Service** | Manajemen lowongan pekerjaan, kuota posisi, requirements & skill tagging per lowongan | **MUHAMAD FAHREN ANDREAN RANGKUTI** |
| 3 | **Application & Matchmaking Service** | Upload CV, pengajuan lamaran, dan scoring engine untuk kalkulasi match percentage | **NABE'ELA AYU NING TYAZ ZAHRA** |
| 4 | **Recruitment Pipeline Service** | Kanban stage timeline, transisi tahapan seleksi, dan interview scheduling | **MUHAMMAD YASIR ILHAM NABIL** |

> Setiap anggota bertanggung jawab penuh atas **desain API**, **implementasi**, **testing**, dan **dokumentasi** service masing-masing.

---

## Konvensi Komunikasi REST API

### JSON Envelope Standar

Seluruh response API mengikuti format envelope yang konsisten:

```json
{
  "status": "success | error",
  "message": "Human-readable message",
  "data": { },
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0"
  }
}
```

### HTTP Status Codes

| Code | Makna | Penggunaan |
|------|-------|------------|
| `200` | OK | Request berhasil, data dikembalikan |
| `201` | Created | Resource baru berhasil dibuat |
| `400` | Bad Request | Request tidak valid / malformed |
| `401` | Unauthorized | Token tidak ada atau expired |
| `403` | Forbidden | Token valid, tapi role tidak memiliki akses |
| `404` | Not Found | Resource tidak ditemukan |
| `409` | Conflict | Konflik data (e.g. email sudah terdaftar) |
| `422` | Unprocessable Entity | Validation error pada field tertentu |
| `500` | Internal Server Error | Kesalahan tidak terduga di server |

### Semantik HTTP Methods

| Method | Semantik | Contoh |
|--------|----------|--------|
| `GET` | Mengambil data (read-only, idempotent) | `GET /api/jobs` |
| `POST` | Membuat resource baru | `POST /api/jobs` |
| `PUT` | Mengganti seluruh resource | `PUT /api/jobs/{id}` |
| `PATCH` | Memperbarui sebagian resource | `PATCH /api/users/{id}/skills` |
| `DELETE` | Menghapus resource | `DELETE /api/jobs/{id}` |

> 📄 Dokumentasi lengkap kontrak API tersedia di [`docs/API_CONTRACT_STANDARDS.md`](docs/API_CONTRACT_STANDARDS.md)

---

## Dokumentasi Proyek

| Dokumen | Deskripsi |
|---------|-----------|
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | Arsitektur sistem, diagram topologi, komunikasi inter-service, dan logika matchmaking engine |
| [`docs/API_CONTRACT_STANDARDS.md`](docs/API_CONTRACT_STANDARDS.md) | Standar request/response payload, header wajib, format error, dan contoh endpoint |
| [`docs/SPRINT_PLAN.md`](docs/SPRINT_PLAN.md) | Pemetaan milestone perkuliahan, sprint plan, dan product backlog |

---

## Technology Stack

| Technology | Purpose |
|---|---|
| Laravel 13 | Application framework (per-service) |
| PHP 8.4+ | Backend runtime |
| Composer | PHP dependency management |
| Node.js 24+ | Frontend tooling |
| npm | JavaScript dependency management |
| Vite | Frontend asset bundling |
| DDEV | Local development environment |
| MariaDB | Database (per-service instance) |
| Tailwind CSS | UI styling |
| JWT (tymon/jwt-auth) | API authentication |

---

# Local Development & Infrastructure Setup

## Requirements

### Recommended (DDEV Workflow)

- Git
- DDEV
- Docker
- Node.js
- A code editor such as VS Code

DDEV provides the PHP, database, and web-server environment used by the project.

### Local PHP Fallback

The development helper can also work without DDEV when the local environment provides:

- PHP 8.4+
- Composer
- Node.js
- npm

---

## Getting Started

### 1. Clone the repository

```bash
git clone <repository-url>
cd PaduKerja
```

### 2. Run project setup

**Linux / macOS:**

```bash
./dev --setup
```

**Windows:**

```bat
dev.bat --setup
```

The setup helper will automatically detect the available development environment.

#### With DDEV

The helper will:

1. Start DDEV
2. Install Composer dependencies
3. Create `.env` from `.env.example` when necessary
4. Generate the Laravel application key
5. Install Node dependencies
6. Build frontend assets

#### Without DDEV

The helper falls back to the local PHP environment and uses `composer`, `php artisan`, and `npm` when the required tools are available.

---

## Development Helper

The project includes two equivalent development helpers:

```text
Linux / macOS:  ./dev
Windows:        dev.bat
```

### Common Commands

```bash
# Setup & diagnostics
./dev --setup          # Full project setup
./dev --doctor         # Environment diagnostics
./dev --env            # Environment information
./dev --help           # Show help
./dev --version        # Show version

# Database
./dev migrate                 # Run migrations
./dev migrate:fresh --seed    # Fresh database with seeders

# Artisan shortcuts
./dev make:model User -m
./dev make:controller AuthController
./dev route:list
./dev test
./dev tinker
```

All arguments that are not recognized as helper options are passed directly to Laravel Artisan.

---

## Environment Priority

```text
1. DDEV project    → preferred
2. Local PHP       → fallback
3. Error
```

If both DDEV and local PHP are available and `.ddev/config.yaml` exists, the helper uses DDEV. Otherwise, it falls back to the local PHP environment.

---

## Frontend

Frontend assets are managed through Vite.

```bash
npm install        # Install dependencies
npm run build      # Build production assets
```

When using DDEV:

```bash
ddev npm install
ddev npm run build
```

The development helper performs these steps automatically during `./dev --setup`.

---

## Database

Migrations are intentionally manual to prevent assumptions about database state.

```bash
./dev migrate              # Run migrations
./dev migrate:fresh        # Fresh database
./dev migrate:fresh --seed # With seeders
```

> **Warning:** `migrate:fresh` drops all tables before recreating them. Use it only in development.

---

## Project Structure

```text
.
├── app/
├── bootstrap/
├── config/
├── database/
├── docs/
│   ├── ARCHITECTURE.md
│   ├── API_CONTRACT_STANDARDS.md
│   └── SPRINT_PLAN.md
├── public/
│   ├── build/
│   ├── favicon.ico
│   ├── logo.png
│   └── index.php
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
├── storage/
├── tests/
│
├── .ddev/
├── artisan
├── composer.json
├── package.json
├── dev
├── dev.bat
└── README.md
```

---

## Contributing

Kontribusi dan saran perbaikan sangat diterima.

Sebelum submit perubahan:

1. Pertahankan struktur Laravel standar.
2. Hindari dependency yang tidak perlu.
3. Jaga konsistensi behavior helper Linux dan Windows.
4. Test perubahan di environment yang sesuai.
5. Update dokumentasi ketika behavior berubah.
6. Ikuti konvensi REST API yang telah ditetapkan.

---

## License

This project is open-sourced under the MIT License.

See the `LICENSE` file for details.

---

<p align="center">
  <strong>PaduKerja</strong> · Kelompok 4 · Pemrograman Web Service · Universitas Harkat Negeri
</p>
