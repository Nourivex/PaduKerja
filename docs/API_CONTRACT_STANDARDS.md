# Standar Kontrak API — PaduKerja

> Dokumen ini mendefinisikan standar global untuk seluruh request dan response payload JSON, header wajib, format error, dan contoh endpoint mock pada keempat microservice PaduKerja.

---

## 1. Standar Request/Response Payload JSON

### 1.1 Response Envelope

Seluruh API endpoint **wajib** mengembalikan response dalam format envelope berikut:

#### Success Response

```json
{
  "status": "success",
  "message": "Data retrieved successfully",
  "data": {
    "id": 1,
    "name": "Example"
  },
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0"
  }
}
```

#### Success Response (Paginated)

```json
{
  "status": "success",
  "message": "Jobs retrieved successfully",
  "data": [
    { "id": 1, "title": "Backend Developer" },
    { "id": 2, "title": "Frontend Developer" }
  ],
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0",
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 42,
      "last_page": 3
    }
  }
}
```

#### Error Response

```json
{
  "status": "error",
  "message": "Validation failed",
  "data": null,
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  },
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0"
  }
}
```

### 1.2 Request Payload

Semua request body **wajib** dikirim dalam format JSON:

```json
{
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "password": "securepassword123",
  "role": "applicant"
}
```

---

## 2. Header Wajib

### 2.1 Request Headers

| Header | Value | Wajib? | Keterangan |
|--------|-------|--------|------------|
| `Content-Type` | `application/json` | ✅ Ya (POST/PUT/PATCH) | Format body request |
| `Accept` | `application/json` | ✅ Ya (semua request) | Format response yang diharapkan |
| `Authorization` | `Bearer <token>` | ✅ Ya (endpoint terproteksi) | JWT token untuk autentikasi |

### 2.2 Response Headers

| Header | Value | Keterangan |
|--------|-------|------------|
| `Content-Type` | `application/json; charset=utf-8` | Format response |
| `X-Request-Id` | `uuid-v4` | ID unik per request untuk tracing |
| `X-RateLimit-Remaining` | `integer` | Sisa quota rate limit |

### 2.3 Contoh Request

```http
POST /api/auth/login HTTP/1.1
Host: localhost:8001
Content-Type: application/json
Accept: application/json

{
  "email": "budi@example.com",
  "password": "securepassword123"
}
```

```http
GET /api/jobs?page=1&per_page=15 HTTP/1.1
Host: localhost:8002
Accept: application/json
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
```

---

## 3. Format Penanganan Error

### 3.1 Validation Error — `422 Unprocessable Entity`

Dikembalikan ketika input tidak lolos validasi field.

```json
{
  "status": "error",
  "message": "Validation failed",
  "data": null,
  "errors": {
    "email": ["The email field is required.", "The email must be a valid email address."],
    "password": ["The password must be at least 8 characters."]
  },
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0"
  }
}
```

### 3.2 Conflict — `409 Conflict`

Dikembalikan ketika operasi menyebabkan konflik data (misalnya duplikasi).

```json
{
  "status": "error",
  "message": "Email already registered",
  "data": null,
  "errors": {
    "email": ["The email has already been taken."]
  },
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0"
  }
}
```

### 3.3 Unauthorized — `401 Unauthorized`

Dikembalikan ketika token tidak ada, invalid, atau expired.

```json
{
  "status": "error",
  "message": "Unauthenticated. Token is missing or expired.",
  "data": null,
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0"
  }
}
```

### 3.4 Forbidden — `403 Forbidden`

Dikembalikan ketika token valid tetapi user tidak memiliki akses (role tidak sesuai).

```json
{
  "status": "error",
  "message": "You do not have permission to access this resource.",
  "data": null,
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0"
  }
}
```

> **Perbedaan 401 vs 403:**
> - `401` → "Siapa kamu?" — Identitas tidak diketahui (token hilang/invalid).
> - `403` → "Kamu tidak boleh." — Identitas diketahui, tetapi hak akses tidak mencukupi.

### 3.5 Not Found — `404 Not Found`

```json
{
  "status": "error",
  "message": "Job not found",
  "data": null,
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0"
  }
}
```

### 3.6 Bad Request — `400 Bad Request`

```json
{
  "status": "error",
  "message": "Malformed JSON in request body",
  "data": null,
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0"
  }
}
```

### 3.7 Internal Server Error — `500 Internal Server Error`

```json
{
  "status": "error",
  "message": "An unexpected error occurred. Please try again later.",
  "data": null,
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0"
  }
}
```

---

## 4. Contoh Endpoint Mock per Service

### 4.1 Service 1: Auth & Profile Service

**Base URL:** `http://localhost:8001/api`

| Method | Endpoint | Deskripsi | Auth |
|--------|----------|-----------|------|
| `POST` | `/auth/register` | Registrasi user baru | ❌ |
| `POST` | `/auth/login` | Login dan dapatkan JWT token | ❌ |
| `POST` | `/auth/logout` | Invalidasi token | ✅ |
| `GET` | `/auth/me` | Profil user yang sedang login | ✅ |
| `GET` | `/users/{id}` | Detail profil user | ✅ |
| `PUT` | `/users/{id}` | Update profil user | ✅ |
| `GET` | `/users/{id}/skills` | Daftar skill user | ✅ |
| `POST` | `/users/{id}/skills` | Tambah skill ke profil | ✅ |
| `PATCH` | `/users/{id}/skills/{skillId}` | Update detail skill | ✅ |
| `DELETE` | `/users/{id}/skills/{skillId}` | Hapus skill dari profil | ✅ |

#### Mock: `POST /auth/register`

**Request:**
```json
{
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "password": "securepassword123",
  "password_confirmation": "securepassword123",
  "role": "applicant"
}
```

**Response `201 Created`:**
```json
{
  "status": "success",
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "role": "applicant",
    "token": "eyJhbGciOiJIUzI1NiIs..."
  },
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0"
  }
}
```

#### Mock: `GET /users/{id}/skills`

**Response `200 OK`:**
```json
{
  "status": "success",
  "message": "User skills retrieved successfully",
  "data": [
    { "id": 1, "name": "PHP", "level": "advanced", "years_exp": 3 },
    { "id": 2, "name": "Laravel", "level": "advanced", "years_exp": 2 },
    { "id": 3, "name": "MySQL", "level": "intermediate", "years_exp": 3 }
  ],
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0"
  }
}
```

---

### 4.2 Service 2: Job Catalog Service

**Base URL:** `http://localhost:8002/api`

| Method | Endpoint | Deskripsi | Auth | Role |
|--------|----------|-----------|------|------|
| `GET` | `/jobs` | Daftar semua lowongan (paginated) | ✅ | All |
| `GET` | `/jobs/{id}` | Detail lowongan | ✅ | All |
| `POST` | `/jobs` | Buat lowongan baru | ✅ | Recruiter |
| `PUT` | `/jobs/{id}` | Update lowongan | ✅ | Recruiter |
| `DELETE` | `/jobs/{id}` | Hapus lowongan | ✅ | Recruiter |
| `GET` | `/jobs/{id}/requirements` | Daftar skill requirements | ✅ | All |
| `POST` | `/jobs/{id}/requirements` | Tambah skill requirement | ✅ | Recruiter |
| `PATCH` | `/jobs/{id}/requirements/{reqId}` | Update weight/level requirement | ✅ | Recruiter |

#### Mock: `POST /jobs`

**Request:**
```json
{
  "title": "Backend Developer",
  "description": "Membangun REST API microservices",
  "location": "Jakarta",
  "employment_type": "full-time",
  "salary_min": 8000000,
  "salary_max": 15000000,
  "quota": 3,
  "requirements": [
    { "skill": "PHP", "weight": 3, "level": "required" },
    { "skill": "Laravel", "weight": 3, "level": "required" },
    { "skill": "MySQL", "weight": 2, "level": "important" }
  ]
}
```

**Response `201 Created`:**
```json
{
  "status": "success",
  "message": "Job created successfully",
  "data": {
    "id": 1,
    "title": "Backend Developer",
    "status": "open",
    "quota": 3,
    "filled": 0,
    "requirements_count": 3
  },
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0"
  }
}
```

---

### 4.3 Service 3: Application & Matchmaking Service

**Base URL:** `http://localhost:8003/api`

| Method | Endpoint | Deskripsi | Auth | Role |
|--------|----------|-----------|------|------|
| `POST` | `/applications` | Submit lamaran baru (trigger matchmaking) | ✅ | Applicant |
| `GET` | `/applications` | Daftar lamaran user | ✅ | Applicant |
| `GET` | `/applications/{id}` | Detail lamaran + match score | ✅ | All |
| `PATCH` | `/applications/{id}/status` | Update status lamaran | ✅ | Recruiter |
| `GET` | `/jobs/{jobId}/applications` | Semua lamaran untuk 1 lowongan | ✅ | Recruiter |
| `GET` | `/jobs/{jobId}/applications/ranked` | Lamaran terurut by match score | ✅ | Recruiter |
| `POST` | `/applications/{id}/cv` | Upload CV document | ✅ | Applicant |

#### Mock: `POST /applications`

**Request:**
```json
{
  "job_id": 1,
  "cover_letter": "Saya tertarik dengan posisi ini karena..."
}
```

**Response `201 Created`:**
```json
{
  "status": "success",
  "message": "Application submitted. Match score calculated.",
  "data": {
    "id": 1,
    "job_id": 1,
    "user_id": 1,
    "match_score": 80.0,
    "match_category": "excellent",
    "matched_skills": ["PHP", "Laravel", "MySQL"],
    "missing_skills": ["Redis", "Docker"],
    "status": "submitted",
    "applied_at": "2026-10-01T14:00:00+07:00"
  },
  "meta": {
    "timestamp": "2026-10-01T14:00:05+07:00",
    "version": "1.0.0"
  }
}
```

#### Mock: `GET /jobs/{jobId}/applications/ranked`

**Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Applications ranked by match score",
  "data": [
    { "id": 1, "user_name": "Budi Santoso", "match_score": 80.0, "match_category": "excellent" },
    { "id": 3, "user_name": "Dewi Lestari", "match_score": 65.5, "match_category": "good" },
    { "id": 2, "user_name": "Andi Prasetyo", "match_score": 40.0, "match_category": "under_qualified" }
  ],
  "meta": {
    "timestamp": "2026-10-02T15:00:00+07:00",
    "version": "1.0.0",
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 3,
      "last_page": 1
    }
  }
}
```

---

### 4.4 Service 4: Recruitment Pipeline Service

**Base URL:** `http://localhost:8004/api`

| Method | Endpoint | Deskripsi | Auth | Role |
|--------|----------|-----------|------|------|
| `POST` | `/pipelines` | Inisialisasi pipeline untuk application | ✅ | System/Recruiter |
| `GET` | `/pipelines/{applicationId}` | Timeline pipeline application | ✅ | All |
| `PATCH` | `/pipelines/{applicationId}/advance` | Majukan ke stage berikutnya | ✅ | Recruiter |
| `PATCH` | `/pipelines/{applicationId}/reject` | Reject pada stage saat ini | ✅ | Recruiter |
| `GET` | `/pipelines/board` | Kanban board semua pipeline aktif | ✅ | Recruiter |
| `POST` | `/pipelines/{applicationId}/interviews` | Jadwalkan interview | ✅ | Recruiter |
| `GET` | `/pipelines/{applicationId}/interviews` | Daftar jadwal interview | ✅ | All |
| `PATCH` | `/pipelines/{applicationId}/interviews/{id}` | Update jadwal interview | ✅ | Recruiter |

#### Mock: `GET /pipelines/{applicationId}`

**Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Pipeline timeline retrieved successfully",
  "data": {
    "application_id": 1,
    "current_stage": "interview",
    "stages": [
      {
        "stage": "screening",
        "status": "passed",
        "entered_at": "2026-10-01T14:00:05+07:00",
        "completed_at": "2026-10-03T10:00:00+07:00"
      },
      {
        "stage": "assessment",
        "status": "passed",
        "entered_at": "2026-10-03T10:00:00+07:00",
        "completed_at": "2026-10-07T16:00:00+07:00"
      },
      {
        "stage": "interview",
        "status": "in_progress",
        "entered_at": "2026-10-07T16:00:00+07:00",
        "completed_at": null
      }
    ],
    "timeline_summary": {
      "total_days": 6,
      "stages_completed": 2,
      "stages_total": 4
    }
  },
  "meta": {
    "timestamp": "2026-10-08T09:00:00+07:00",
    "version": "1.0.0"
  }
}
```

#### Mock: `POST /pipelines/{applicationId}/interviews`

**Request:**
```json
{
  "datetime": "2026-10-10T10:00:00+07:00",
  "type": "online",
  "meeting_url": "https://meet.example.com/interview-123",
  "interviewer": "HR Manager",
  "notes": "Please prepare your portfolio"
}
```

**Response `201 Created`:**
```json
{
  "status": "success",
  "message": "Interview scheduled successfully",
  "data": {
    "id": 1,
    "application_id": 1,
    "datetime": "2026-10-10T10:00:00+07:00",
    "type": "online",
    "meeting_url": "https://meet.example.com/interview-123",
    "interviewer": "HR Manager",
    "status": "scheduled"
  },
  "meta": {
    "timestamp": "2026-10-08T09:00:00+07:00",
    "version": "1.0.0"
  }
}
```

---

## 5. Ringkasan Status Code Matrix

| Endpoint Pattern | `200` | `201` | `400` | `401` | `403` | `404` | `409` | `422` | `500` |
|-----------------|-------|-------|-------|-------|-------|-------|-------|-------|-------|
| `GET /resource` | ✅ | | ✅ | ✅ | ✅ | ✅ | | | ✅ |
| `GET /resource/{id}` | ✅ | | ✅ | ✅ | ✅ | ✅ | | | ✅ |
| `POST /resource` | | ✅ | ✅ | ✅ | ✅ | | ✅ | ✅ | ✅ |
| `PUT /resource/{id}` | ✅ | | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `PATCH /resource/{id}` | ✅ | | ✅ | ✅ | ✅ | ✅ | | ✅ | ✅ |
| `DELETE /resource/{id}` | ✅ | | ✅ | ✅ | ✅ | ✅ | | | ✅ |

---

<p align="center">
  <strong>PaduKerja</strong> · API Contract Standards · Kelompok 4 UHN
</p>
