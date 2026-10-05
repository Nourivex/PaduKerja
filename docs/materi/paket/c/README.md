# Panduan Paket C: Seleksi Pelamar

> **Penanggung Jawab:** MUHAMMAD AFFIF (NIM: 24225046)  
> **Layanan Microservice:** Recruitment Pipeline Service (Service 4)  
> **Mata Kuliah:** Pemrograman Web Service - Kelompok 4 UHN  

---

## 📋 Deskripsi Tugas Paket C

Paket C berfokus pada pelacakan status seleksi bertahap (*multi-stage pipeline tracker*) secara transparan antara recruiter dan pelamar:

1. **`PATCH /api/pipelines/{applicationId}/status`** : Recruiter memperbarui status atau memajukan tahapan seleksi pelamar (misal: dari *Assessment* ke *Interview* lengkap dengan jadwal wawancara).
2. **`GET /api/pipelines/{applicationId}`** : Pelamar melihat seluruh jejak rekam linimasa seleksi secara kronologis beserta catatan hasil evaluasi.

---

## 📁 Berkas & Script Terkait

Bila Anda ingin mempelajari atau memodifikasi script Paket C, berikut file-file yang menyusunnya:

| Peran Berkas | Lokasi File | Fungsi Utama |
|---|---|---|
| **Controller** | [`app/Http/Controllers/Api/PipelineController.php`](../../../../app/Http/Controllers/Api/PipelineController.php) | Logika `updateStatus()` untuk transisi tahapan seleksi dan `showTimeline()` untuk memantau linimasa. |
| **Model** | [`app/Models/PipelineTimeline.php`](../../../../app/Models/PipelineTimeline.php) | Entitas data linimasa, casting atribut array JSON `stages`, dan relasi ke berkas lamaran. |
| **Model Terkait** | [`app/Models/Application.php`](../../../../app/Models/Application.php) | Sinkronisasi status lamaran saat tahapan pipeline dimajukan atau ditolak (*rejected*). |
| **Migration** | [`database/migrations/2026_10_03_000004_create_pipeline_timelines_table.php`](../../../../database/migrations/2026_10_03_000004_create_pipeline_timelines_table.php) | Skema tabel database `pipeline_timelines`. |
| **Routing** | [`routes/api.php`](../../../../routes/api.php) | Pendaftaran rute HTTP `PATCH /api/pipelines/{id}/status` dan `GET /api/pipelines/{id}`. |

---

## 🔄 Alur Transisi Tahapan Seleksi (Pipeline Sequence)

Proses rekrutmen mengikuti urutan linier berikut:

```text
[Screening]  ──▶  [Assessment]  ──▶  [Interview]  ──▶  [Offering]
     │                 │                  │
     ▼                 ▼                  ▼
 [Rejected]        [Rejected]         [Rejected]
```

- **Screening:** Verifikasi administrasi awal & penilaian otomatis skor kecocokan keahlian.
- **Assessment:** Penugasan teknis (*coding challenge* / tes studi kasus).
- **Interview:** Sesi wawancara tatap muka atau daring (*online meeting link*).
- **Offering:** Penerbitan penawaran kerja resmi jika kandidat memenuhi syarat.
- **Rejected:** Penolakan kandidat pada tahap tertentu disertai catatan evaluasi.

---

## 🚀 Panduan Uji Coba API (Testing)

### Persiapan Akun Pengujian
- **Akun Recruiter:** `recruiter@padukerja.id` (Password: `password123`)
- **Akun Pelamar (Budi):** `budi@padukerja.id` (Password: `password123`)
- **Lamaran Tersedia di Database:** Lamaran ID `1` milik Budi Santoso.

---

### Endpoint 1: Recruiter Update Status/Tahap Seleksi Pelamar

- **Method:** `PATCH` (Mengubah sebagian data resource)
- **URL:** `http://127.0.0.1:32768/api/pipelines/1/status`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `Authorization: Bearer <TOKEN_RECRUITER>`
- **Request Body (JSON):**
```json
{
  "stage": "interview",
  "status": "in_progress",
  "notes": "Kandidat lulus tahap assessment dengan nilai 92/100. Masuk sesi wawancara.",
  "interview_schedule": {
    "datetime": "2026-10-10T10:00:00+07:00",
    "type": "online",
    "meeting_url": "https://meet.google.com/padukerja-interview-budi",
    "interviewer": "Engineering Lead & HR Specialist"
  }
}
```

#### Contoh Uji Coba dengan curl:
```bash
# 1. Login Recruiter untuk mendapatkan token
RECRUITER_TOKEN=$(curl -s -X POST http://127.0.0.1:32768/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"recruiter@padukerja.id","password":"password123"}' | jq -r '.data.token')

# 2. Update status seleksi lamaran ID 1
curl -s -X PATCH http://127.0.0.1:32768/api/pipelines/1/status \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $RECRUITER_TOKEN" \
  -d '{
    "stage": "interview",
    "status": "in_progress",
    "notes": "Lolos assessment teknis. Menjadwalkan wawancara komprehensif.",
    "interview_schedule": {
      "datetime": "2026-10-10T10:00:00+07:00",
      "type": "online",
      "meeting_url": "https://meet.google.com/padukerja-sesi-interview",
      "interviewer": "Engineering Lead & HR Specialist"
    }
  }' | jq .
```

#### Contoh Response `200 OK`:
```json
{
  "status": "success",
  "message": "Tahapan proses seleksi pelamar berhasil diperbarui ke tahap 'interview'.",
  "data": {
    "application_id": 1,
    "job": {
      "title": "Senior Backend Engineer",
      "company": "PT Teknologi Maju"
    },
    "current_stage": "interview",
    "stage_status": "in_progress",
    "stages": [
      {
        "stage": "screening",
        "status": "passed",
        "entered_at": "2026-09-28T05:26:57+00:00",
        "completed_at": "2026-09-29T05:26:57+00:00",
        "notes": "Automated Matchmaking Score: 80%."
      },
      {
        "stage": "assessment",
        "status": "passed",
        "entered_at": "2026-09-29T05:26:57+00:00",
        "completed_at": "2026-10-01T05:26:57+00:00",
        "notes": "Tes teknis nilai 92/100."
      },
      {
        "stage": "interview",
        "status": "in_progress",
        "entered_at": "2026-10-01T05:26:57+00:00",
        "completed_at": null,
        "interview_schedule": {
          "datetime": "2026-10-10T10:00:00+07:00",
          "meeting_url": "https://meet.google.com/padukerja-sesi-interview"
        }
      }
    ],
    "updated_at": "2026-10-03T12:00:00+07:00"
  },
  "meta": {
    "timestamp": "2026-10-03T12:00:00+07:00",
    "version": "1.0.0"
  }
}
```

---

### Endpoint 2: Pelamar Melihat Riwayat Linimasa Proses Lamaran

- **Method:** `GET` (Safe, Read-Only)
- **URL:** `http://127.0.0.1:32768/api/pipelines/1`
- **Headers:**
  - `Accept: application/json`
  - `Authorization: Bearer <TOKEN_PELAMAR>`

#### Contoh Uji Coba dengan curl:
```bash
# 1. Login sebagai Pelamar Budi
BUDI_TOKEN=$(curl -s -X POST http://127.0.0.1:32768/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"budi@padukerja.id","password":"password123"}' | jq -r '.data.token')

# 2. Lihat linimasa proses lamaran
curl -s http://127.0.0.1:32768/api/pipelines/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $BUDI_TOKEN" | jq .
```

#### Contoh Response `200 OK`:
```json
{
  "status": "success",
  "message": "Riwayat linimasa proses lamaran berhasil dimuat.",
  "data": {
    "application_id": 1,
    "job": {
      "title": "Senior Backend Engineer",
      "company": "PT Teknologi Maju",
      "location": "Jakarta Selatan"
    },
    "applicant": {
      "name": "Budi Santoso",
      "match_score": 80,
      "match_category": "excellent"
    },
    "current_stage": "interview",
    "stages": [
      {
        "stage": "screening",
        "status": "passed",
        "entered_at": "2026-09-28T05:26:57+00:00",
        "completed_at": "2026-09-29T05:26:57+00:00"
      },
      {
        "stage": "assessment",
        "status": "passed",
        "entered_at": "2026-09-29T05:26:57+00:00",
        "completed_at": "2026-10-01T05:26:57+00:00"
      },
      {
        "stage": "interview",
        "status": "in_progress",
        "entered_at": "2026-10-01T05:26:57+00:00",
        "completed_at": null,
        "interview_schedule": {
          "datetime": "2026-10-10T10:00:00+07:00",
          "meeting_url": "https://meet.google.com/padukerja-sesi-interview"
        }
      }
    ],
    "timeline_summary": {
      "days_active": 5,
      "stages_completed": 2,
      "stages_total": 4
    }
  },
  "meta": {
    "timestamp": "2026-10-03T12:00:00+07:00",
    "version": "1.0.0"
  }
}
```

---

## 📝 Lembar Analisis HTTP Request-Response (Bahan Laporan)

| Komponen Analisis | Request 1 (`PATCH /api/pipelines/{id}/status`) | Request 2 (`GET /api/pipelines/{id}`) |
|---|---|---|
| **HTTP Method** | `PATCH` (Pembaruan sebagian field status/tahap) | `GET` (Safe, Idempotent, Read-Only) |
| **Request URI** | `/api/pipelines/1/status` | `/api/pipelines/1` |
| **Headers Penting** | `Content-Type: application/json`, `Authorization: Bearer <token_recruiter>` | `Accept: application/json`, `Authorization: Bearer <token_pelamar>` |
| **Status Code** | `200 OK` (atau `403 Forbidden` jika bukan recruiter) | `200 OK` (atau `403 Forbidden` jika pelamar lain mencoba intip data) |
| **Karakteristik Operasi** | Mengarsipkan waktu selesai tahap lama & mencatat tahap baru | Menyusun linimasa kronologis beserta summary durasi hari |
| **Envelope Response** | `{ status, message, data: { current_stage, stages }, meta }` | `{ status, message, data: { applicant, stages, timeline_summary }, meta }` |
