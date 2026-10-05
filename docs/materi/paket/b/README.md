# Panduan Paket B: Lamaran & Scoring

> **Penanggung Jawab:** Muhammad Yasir Ilham Nabil (NIM: 23215040)  
> **Layanan Microservice:** Application & Matchmaking Service (Service 3)  
> **Mata Kuliah:** Pemrograman Web Service - Kelompok 4 UHN  

---

## 📋 Deskripsi Tugas Paket B

Paket B berfokus pada alur pengajuan berkas lamaran kerja oleh pelamar, pemadanan otomatis kompetensi keahlian menggunakan algoritma *Weighted Jaccard Similarity*, serta pembatalan berkas lamaran:

1. **`POST /api/applications`** : Mengirim berkas lamaran kerja baru + memicu kalkulasi skor kecocokan (*match score*) secara otomatis antara keahlian pelamar dan kualifikasi lowongan.
2. **`DELETE /api/applications/{id}`** : Pelamar membatalkan pengajuan lamaran kerjanya (dengan pemeriksaan hak kepemilikan berkas).

---

## 📁 Berkas & Script Terkait

Bila Anda ingin mempelajari atau memodifikasi script Paket B, berikut file-file yang menyusunnya:

| Peran Berkas | Lokasi File | Fungsi Utama |
|---|---|---|
| **Controller** | [`app/Http/Controllers/Api/ApplicationController.php`](../../../../app/Http/Controllers/Api/ApplicationController.php) | Logika `store()` untuk kirim lamaran & skoring, serta `destroy()` untuk pembatalan. |
| **Service Engine** | [`app/Services/MatchmakingService.php`](../../../../app/Services/MatchmakingService.php) | Algoritma matematis *Weighted Jaccard Similarity* untuk menghitung persentase kecocokan keahlian. |
| **Model** | [`app/Models/Application.php`](../../../../app/Models/Application.php) | Entitas data lamaran, skor kecocokan, daftar keahlian yang cocok, dan status lamaran. |
| **Model Terkait** | [`app/Models/PipelineTimeline.php`](../../../../app/Models/PipelineTimeline.php) | Inisialisasi otomatis tahapan awal *screening* saat lamaran pertama kali dibuat. |
| **Migration** | [`database/migrations/2026_10_03_000003_create_applications_table.php`](../../../../database/migrations/2026_10_03_000003_create_applications_table.php) | Skema tabel database `applications`. |
| **Routing** | [`routes/api.php`](../../../../routes/api.php) | Pendaftaran rute HTTP `POST /api/applications` dan `DELETE /api/applications/{id}`. |

---

## 🧮 Logika & Rumus Matchmaking Engine

Ketika pelamar mengirim lamaran, sistem secara otomatis mengeksekusi perhitungan:

$$\text{Match Score} = \frac{\sum (\text{bobot keahlian pelamar yang cocok})}{\sum (\text{total bobot seluruh syarat lowongan})} \times 100\%$$

**Bobot Tingkatan Kualifikasi:**
- `required` : Bobot **3**
- `important` : Bobot **2**
- `nice_to_have` : Bobot **1**

**Kategori Hasil:**
- $\ge 80\%$ : `excellent` (Otomatis lolos tahap screening)
- $50\% - 79\%$ : `good` (Masuk tahap review manual oleh recruiter)
- $< 50\%$ : `under_qualified` (Belum memenuhi kriteria minimal)

---

## 🚀 Panduan Uji Coba API (Testing)

### Persiapan: Dapatkan Token Pelamar
Gunakan akun pelamar Siti Nurhaliza atau Budi Santoso untuk menguji endpoint:

```bash
# Login sebagai Pelamar (Siti)
curl -s -X POST http://127.0.0.1:32768/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "siti@padukerja.id", "password": "password123"}' | jq -r '.data.token'
```
*Salin token yang dihasilkan untuk dimasukkan pada header `Authorization: Bearer <token>`.*

---

### Endpoint 1: Mengirim Lamaran + Otomatis Hitung Skor

- **Method:** `POST`
- **URL:** `http://127.0.0.1:32768/api/applications`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `Authorization: Bearer <TOKEN_PELAMAR>`
- **Request Body (JSON):**
```json
{
  "job_id": 2,
  "cover_letter": "Saya sangat tertarik dengan posisi Frontend React Specialist.",
  "candidate_skills": ["React", "JavaScript", "Tailwind CSS", "Git"]
}
```

#### Contoh Uji Coba dengan curl:
```bash
APPLICANT_TOKEN="<PASTE_TOKEN_PELAMAR_DISINI>"

curl -s -X POST http://127.0.0.1:32768/api/applications \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $APPLICANT_TOKEN" \
  -d '{
    "job_id": 2,
    "cover_letter": "Saya tertarik mengisi posisi Frontend React Specialist.",
    "candidate_skills": ["React", "JavaScript", "Tailwind CSS", "Git"]
  }' | jq .
```

#### Contoh Response `201 Created`:
```json
{
  "status": "success",
  "message": "Berkas lamaran berhasil dikirim. Skor pemadanan kompetensi dihitung secara otomatis.",
  "data": {
    "application_id": 2,
    "job": {
      "id": 2,
      "title": "Frontend React Specialist",
      "company": "PT Digital Solusindo"
    },
    "applicant": {
      "id": 3,
      "name": "Siti Nurhaliza",
      "skills_used": ["React", "JavaScript", "Tailwind CSS", "Git"]
    },
    "matchmaking_result": {
      "match_score": 88.89,
      "match_category": "excellent",
      "matched_skills": ["React", "JavaScript", "Tailwind CSS"],
      "missing_skills": ["TypeScript"],
      "calculation_details": {
        "matched_weight": 8,
        "total_weight": 9,
        "formula": "Skor = (Σ Bobot Cocok / Σ Total Bobot Syarat) x 100%"
      }
    },
    "pipeline_status": {
      "current_stage": "screening",
      "stage_status": "passed"
    },
    "applied_at": "2026-10-03T12:00:00+07:00"
  },
  "meta": {
    "timestamp": "2026-10-03T12:00:00+07:00",
    "version": "1.0.0"
  }
}
```

---

### Endpoint 2: Membatalkan Pengajuan Lamaran

- **Method:** `DELETE`
- **URL:** `http://127.0.0.1:32768/api/applications/{id}`
- **Headers:**
  - `Accept: application/json`
  - `Authorization: Bearer <TOKEN_PELAMAR>`

#### Contoh Uji Coba dengan curl:
```bash
# Batalkan lamaran ID 2 yang baru saja dibuat
curl -s -X DELETE http://127.0.0.1:32768/api/applications/2 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $APPLICANT_TOKEN" | jq .
```

#### Contoh Response `200 OK`:
```json
{
  "status": "success",
  "message": "Pengajuan berkas lamaran untuk posisi Frontend React Specialist berhasil dibatalkan.",
  "data": {
    "application_id": 2,
    "job_title": "Frontend React Specialist",
    "company": "PT Digital Solusindo",
    "status": "canceled",
    "canceled_at": "2026-10-03T12:05:00+07:00"
  },
  "meta": {
    "timestamp": "2026-10-03T12:05:00+07:00",
    "version": "1.0.0"
  }
}
```

---

## 📝 Lembar Analisis HTTP Request-Response (Bahan Laporan)

| Komponen Analisis | Request 1 (`POST /api/applications`) | Request 2 (`DELETE /api/applications/{id}`) |
|---|---|---|
| **HTTP Method** | `POST` (Pembuatan lamaran baru) | `DELETE` (Menghapus/Membatalkan resource) |
| **Request URI** | `/api/applications` | `/api/applications/2` |
| **Headers Penting** | `Content-Type: application/json`, `Authorization: Bearer <token>` | `Accept: application/json`, `Authorization: Bearer <token>` |
| **Status Code** | `201 Created` (atau `409 Conflict` jika sudah pernah melamar) | `200 OK` (atau `403 Forbidden` jika bukan pemilik berkas) |
| **Integritas Data** | Dikelola via `DB::transaction` (Atomic) | Melakukan update status status menjadi `canceled` pada tabel lamaran & pipeline |
| **Envelope Response** | `{ status, message, data: { match_score, ... }, meta }` | `{ status, message, data: { status: "canceled" }, meta }` |
