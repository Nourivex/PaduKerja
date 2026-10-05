# Panduan Paket D: Akun & Keahlian

> **Penanggung Jawab:** Nabe'ela Ayu Ning Tyas Zahra (NIM: 23215052)  
> **Layanan Microservice:** Auth & Profile Service (Service 1)  
> **Mata Kuliah:** Pemrograman Web Service - Kelompok 4 UHN  

---

## 📋 Deskripsi Tugas Paket D

Paket D berfokus pada gerbang keamanan sistem (*authentication & authorization*), penerbitan token akses JWT, serta pengelolaan profil dan matriks keahlian pengguna (*skills matrix*):

1. **`POST /api/auth/login`** : Otentikasi kredensial pengguna (email & password) dan menerbitkan tanda pengenal digital stateless berstandar JSON Web Token (JWT).
2. **`PUT /api/profile/skills`** : Menyimpan atau memperbarui kumpulan data keahlian (*skills*) pada profil pengguna yang sedang terotentikasi.

---

## 📁 Berkas & Script Terkait

Bila Anda ingin mempelajari atau memodifikasi script Paket D, berikut file-file yang menyusunnya:

| Peran Berkas | Lokasi File | Fungsi Utama |
|---|---|---|
| **Controller Login** | [`app/Http/Controllers/Api/AuthController.php`](../../../../app/Http/Controllers/Api/AuthController.php) | Logika `login()`, `register()`, dan pengecekan profil `me()`. |
| **Controller Keahlian** | [`app/Http/Controllers/Api/ProfileSkillController.php`](../../../../app/Http/Controllers/Api/ProfileSkillController.php) | Logika `updateSkills()` untuk menyimpan/memperbarui array keahlian profil pengguna. |
| **Service JWT** | [`app/Services/JwtService.php`](../../../../app/Services/JwtService.php) | Pembuatan (*encode*) dan verifikasi (*decode*) token JWT dengan kunci rahasia HS256. |
| **Middleware Keamanan** | [`app/Http/Middleware/JwtAuth.php`](../../../../app/Http/Middleware/JwtAuth.php) | Memeriksa header `Authorization: Bearer <token>` dan otorisasi hak akses peran (*role*). |
| **Helper Response** | [`app/Traits/ApiResponse.php`](../../../../app/Traits/ApiResponse.php) | Standardisasi format JSON envelope (`status`, `message`, `data`, `meta`). |
| **Model** | [`app/Models/User.php`](../../../../app/Models/User.php) | Entitas data pengguna, role, casting `skills` ke array, dan enkripsi password. |
| **Migration** | [`database/migrations/2026_10_03_000001_add_profile_fields_to_users_table.php`](../../../../database/migrations/2026_10_03_000001_add_profile_fields_to_users_table.php) | Penambahan kolom role, keahlian (skills), telepon, dan bio pada tabel `users`. |
| **Routing** | [`routes/api.php`](../../../../routes/api.php) | Pendaftaran rute HTTP `POST /api/auth/login` dan `PUT /api/profile/skills`. |

---

## 🔐 Cara Kerja Autentikasi JWT (JSON Web Token)

```text
[ Client (Thunder Client / Web) ]                 [ Server (PaduKerja API) ]
              │                                                │
              │  1. POST /api/auth/login (email + password)   │
              ├───────────────────────────────────────────────▶│ Verifikasi hash sandi
              │                                                │ Buat token JWT (HS256)
              │  2. Return JSON { token: "eyJhbGci..." }       │
              │◀───────────────────────────────────────────────┤
              │                                                │
              │  3. PUT /api/profile/skills                    │
              │     Header: Authorization: Bearer <token>      │
              ├───────────────────────────────────────────────▶│ Middleware JwtAuth
              │                                                │ Verifikasi signature token
              │  4. Return JSON 200 OK (Data Terupdate)        │ Update array keahlian
              │◀───────────────────────────────────────────────┤
```

---

## 🚀 Panduan Uji Coba API (Testing)

### Akun Pengujian yang Tersedia:
- **Pelamar:** `budi@padukerja.id` (Password: `password123`)
- **Recruiter:** `recruiter@padukerja.id` (Password: `password123`)

---

### Endpoint 1: Login Akun Pengguna & Generate Token JWT

- **Method:** `POST`
- **URL:** `http://127.0.0.1:32768/api/auth/login`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Request Body (JSON):**
```json
{
  "email": "budi@padukerja.id",
  "password": "password123"
}
```

#### Contoh Uji Coba dengan curl:
```bash
curl -s -X POST http://127.0.0.1:32768/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "budi@padukerja.id", "password": "password123"}' | jq .
```

#### Contoh Response `200 OK`:
```json
{
  "status": "success",
  "message": "Login berhasil. Token otentikasi JWT berhasil diterbitkan.",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL3BhZHVrZXJqYS5kZGV2LnNpdGUiLCJzdWIiOjIsIm5hbWUiOiJCdWRpIFNhbnRvc28iLCJlbWFpbCI6ImJ1ZGlAcGFkdWtlcmphLmlkIiwicm9sZSI6ImFwcGxpY2FudCIsImlhdCI6MTc5MTAwNTIyNiwiZXhwIjoxNzkxNjEwMDI2fQ...",
    "token_type": "Bearer",
    "expires_in": 604800,
    "user": {
      "id": 2,
      "name": "Budi Santoso",
      "email": "budi@padukerja.id",
      "role": "applicant",
      "location": "Bandung",
      "skills": ["PHP", "Laravel", "MySQL", "Git"]
    }
  },
  "meta": {
    "timestamp": "2026-10-03T12:00:00+07:00",
    "version": "1.0.0"
  }
}
```

---

### Endpoint 2: Menyimpan / Update Data Keahlian di Profil

- **Method:** `PUT` (Mengganti/memperbarui kumpulan resource data)
- **URL:** `http://127.0.0.1:32768/api/profile/skills`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `Authorization: Bearer <TOKEN_PELAMAR>`
- **Request Body (JSON):**
```json
{
  "skills": ["PHP", "Laravel", "MySQL", "Git", "Redis", "Docker"]
}
```

#### Contoh Uji Coba dengan curl:
```bash
# 1. Login untuk mendapatkan token
TOKEN=$(curl -s -X POST http://127.0.0.1:32768/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "budi@padukerja.id", "password": "password123"}' | jq -r '.data.token')

# 2. Update daftar keahlian
curl -s -X PUT http://127.0.0.1:32768/api/profile/skills \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "skills": ["PHP", "Laravel", "MySQL", "Git", "Redis", "Docker"]
  }' | jq .
```

#### Contoh Response `200 OK`:
```json
{
  "status": "success",
  "message": "Data keahlian (skills) pada profil pengguna berhasil diperbarui.",
  "data": {
    "user_id": 2,
    "name": "Budi Santoso",
    "email": "budi@padukerja.id",
    "skills": [
      "PHP",
      "Laravel",
      "MySQL",
      "Git",
      "Redis",
      "Docker"
    ],
    "skills_count": 6,
    "updated_at": "2026-10-03T12:05:00+07:00"
  },
  "meta": {
    "timestamp": "2026-10-03T12:05:00+07:00",
    "version": "1.0.0"
  }
}
```

---

## 📝 Lembar Analisis HTTP Request-Response (Bahan Laporan)

| Komponen Analisis | Request 1 (`POST /api/auth/login`) | Request 2 (`PUT /api/profile/skills`) |
|---|---|---|
| **HTTP Method** | `POST` (Autentikasi & Pembentukan Sesi) | `PUT` (Pembaruan Penuh Array Data Keahlian) |
| **Request URI** | `/api/auth/login` | `/api/profile/skills` |
| **Headers Penting** | `Content-Type: application/json` | `Content-Type: application/json`, `Authorization: Bearer <token>` |
| **Status Code** | `200 OK` (atau `401 Unauthorized` jika password keliru) | `200 OK` (atau `422 Unprocessable Entity` jika array kosong) |
| **Mekanisme Keamanan** | Pengecekan Bcrypt Hash & Generasi JWT | Validasi JWT oleh Middleware `JwtAuth` |
| **Envelope Response** | `{ status, message, data: { token, user }, meta }` | `{ status, message, data: { skills, skills_count }, meta }` |
