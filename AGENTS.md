# PaduKerja — Agent Guidelines

> Platform Rekrutmen Terintegrasi Berbasis Microservice dengan Mesin Pemadanan Keahlian Otomatis.
> Mata Kuliah: Pemrograman Web Service (5537344 / 3 SKS) — Universitas Harkat Negeri — Kelompok 4.

---

## Project Overview

PaduKerja is a recruitment platform built with **4 independent Laravel microservices** communicating via **REST API JSON**. Each service has its own database (database-per-service pattern).

### Microservices

| # | Service | Port | Database | Owner |
|---|---------|------|----------|-------|
| 1 | **Auth & Profile Service** | `:8001` | `db_auth` | MUHAMMAD AFFIF |
| 2 | **Job Catalog Service** | `:8002` | `db_jobs` | MUHAMAD FAHREN ANDREAN RANGKUTI |
| 3 | **Application & Matchmaking Service** | `:8003` | `db_applications` | NABE'ELA AYU NING TYAZ ZAHRA |
| 4 | **Recruitment Pipeline Service** | `:8004` | `db_pipeline` | MUHAMMAD YASIR ILHAM NABIL |

---

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, install using the appropriate command for the user's OS:

- **macOS:** `/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"`
- **Linux:** `/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"`
- **Windows:** `Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))`

After installation, ask the user to restart their terminal.

---

## Development Environment

- **Runtime:** PHP 8.4+, Composer, Node.js 24+
- **Framework:** Laravel 13.x per service
- **Database:** MariaDB (one instance per service)
- **Local Environment:** DDEV (preferred) or local PHP fallback
- **CLI Helper:** `./dev --setup`, `./dev migrate`, `./dev <artisan-command>`

---

## Architecture & Coding Conventions

### REST API Standards

All endpoints MUST follow these conventions:

1. **JSON envelope** — Every response uses this structure:
   ```json
   {
     "status": "success | error",
     "message": "Human-readable message",
     "data": {},
     "meta": { "timestamp": "ISO8601", "version": "1.0.0" }
   }
   ```

2. **Headers** — All requests must include:
   - `Content-Type: application/json` (POST/PUT/PATCH)
   - `Accept: application/json` (all requests)
   - `Authorization: Bearer <token>` (protected endpoints)

3. **HTTP Methods** — Use correct semantics:
   - `GET` → read (idempotent)
   - `POST` → create
   - `PUT` → full replace
   - `PATCH` → partial update
   - `DELETE` → remove

4. **Status Codes** — Use appropriate codes:
   - `200` OK, `201` Created
   - `400` Bad Request, `401` Unauthorized, `403` Forbidden
   - `404` Not Found, `409` Conflict, `422` Validation Error
   - `500` Internal Server Error

5. **Validation errors** (422) must return field-level errors in an `errors` object.

### Authentication

- JWT-based authentication via Auth & Profile Service.
- Three roles: `applicant`, `recruiter`, `admin`.
- Inter-service calls use service-to-service tokens configured via `.env`.

### Matchmaking Engine

The Application & Matchmaking Service calculates match scores using **Weighted Jaccard Similarity**:

```
Match Score = (Σ weight of matched skills) / (Σ weight of all required skills) × 100%
```

Skill weights: `required = 3`, `important = 2`, `nice_to_have = 1`.

### Pipeline Stages

Recruitment pipeline follows this fixed sequence:

```
Screening → Assessment → Interview → Offering / Rejected
```

### Database Rules

- **No cross-database queries.** Services access other services' data only through REST API calls.
- Each service manages its own migrations independently.
- Use Laravel Form Requests for all input validation.

### Code Style

- Follow standard Laravel conventions (PSR-12, Eloquent models, Resource controllers).
- API routes go in `routes/api.php`.
- Use Laravel API Resources for response transformation.
- Use Form Request classes for validation.
- Write feature tests for every endpoint.

---

## Key Documentation

| Document | Purpose |
|----------|---------|
| `README.md` | Project overview, setup instructions, team info |
| `docs/ARCHITECTURE.md` | System topology, inter-service communication, matchmaking formula, data structures |
| `docs/API_CONTRACT_STANDARDS.md` | JSON envelope, headers, error formats, mock endpoints per service |
| `docs/SPRINT_PLAN.md` | Sprint breakdown, product backlog, milestone mapping |

**Always consult these documents before making architectural decisions or designing new endpoints.**

---

## Common Tasks

### Setting up the project
```sh
./dev --setup
```

### Running migrations
```sh
./dev migrate
```

### Creating a new controller
```sh
./dev make:controller Api/JobController --api
```

### Running tests
```sh
./dev test
```

### Environment diagnostics
```sh
./dev --doctor
```
