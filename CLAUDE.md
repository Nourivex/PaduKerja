# PaduKerja — Claude/Cursor Guidelines

> Platform Rekrutmen Terintegrasi Berbasis Microservice dengan Mesin Pemadanan Keahlian Otomatis.
> Mata Kuliah: Pemrograman Web Service (5537344 / 3 SKS) — Universitas Harkat Negeri — Kelompok 4.

---

## Project Context

PaduKerja is a microservice-based recruitment platform for a university Web Service Programming course. It consists of **4 independent Laravel services** with dedicated databases, communicating exclusively via REST API JSON.

### Services

| # | Service | Port | DB | Owner |
|---|---------|------|----|-------|
| 1 | Auth & Profile | `:8001` | `db_auth` | MUHAMMAD AFFIF |
| 2 | Job Catalog | `:8002` | `db_jobs` | MUHAMAD FAHREN ANDREAN RANGKUTI |
| 3 | Application & Matchmaking | `:8003` | `db_applications` | NABE'ELA AYU NING TYAZ ZAHRA |
| 4 | Recruitment Pipeline | `:8004` | `db_pipeline` | MUHAMMAD YASIR ILHAM NABIL |

### Core Features

1. **Automated Skill-Matchmaking Engine** — Weighted Jaccard Similarity scoring (required=3, important=2, nice_to_have=1).
2. **Multi-Stage Pipeline Tracker** — Screening → Assessment → Interview → Offering/Rejected.
3. **JWT Multi-Role Auth** — `applicant`, `recruiter`, `admin`.

---

## Critical Rules

### DO:
- Use the **JSON envelope** for ALL API responses: `{ "status", "message", "data", "meta" }`.
- Include `Content-Type: application/json`, `Accept: application/json` headers.
- Use proper HTTP status codes: 200, 201, 400, 401, 403, 404, 409, 422, 500.
- Use correct HTTP method semantics (GET=read, POST=create, PUT=replace, PATCH=partial, DELETE=remove).
- Use Laravel Form Requests for input validation.
- Use Laravel API Resources for response formatting.
- Write feature tests for every endpoint.
- Follow PSR-12 and standard Laravel conventions.
- Put API routes in `routes/api.php`.
- Return field-level `errors` object on 422 validation failures.

### DON'T:
- **Never** access another service's database directly — use REST API calls only.
- **Never** skip the JSON envelope format in responses.
- **Never** use session-based auth — JWT only.
- **Never** hardcode service URLs — use `.env` configuration.
- **Never** run migrations automatically in setup — they are manual (`./dev migrate`).

---

## Tech Stack

- **Framework:** Laravel 13.x (per service)
- **Runtime:** PHP 8.4+, Composer
- **Frontend:** Node.js 24+, Vite, Tailwind CSS
- **Database:** MariaDB (database-per-service)
- **Auth:** JWT (tymon/jwt-auth)
- **Dev Environment:** DDEV / Docker (preferred) or local PHP

---

## Development Commands

```sh
./dev --setup              # Full project setup
./dev --doctor             # Environment diagnostics
./dev migrate              # Run database migrations
./dev migrate:fresh --seed # Fresh DB with seeders
./dev make:controller Api/ExampleController --api
./dev make:model Example -m
./dev test                 # Run tests
./dev route:list           # List routes
```

---

## Key Documents — Read Before Making Changes

| File | Content |
|------|---------|
| `README.md` | Project overview, team, setup guide |
| `docs/ARCHITECTURE.md` | System diagrams, inter-service flows, matchmaking formula, data structures |
| `docs/API_CONTRACT_STANDARDS.md` | Envelope format, headers, error handling, mock endpoints |
| `docs/SPRINT_PLAN.md` | Sprint timeline, backlog, milestones |

Always consult `docs/API_CONTRACT_STANDARDS.md` before creating or modifying any API endpoint to ensure compliance with the agreed contract.

---

## Matchmaking Score Formula

```
Score = (Σ weight_i for matched skills) / (Σ weight_i for all required skills) × 100%
```

| Weight Level | Value | Meaning |
|-------------|-------|---------|
| Required | 3 | Must-have skill |
| Important | 2 | Significant contributor |
| Nice-to-have | 1 | Bonus, not mandatory |

Thresholds: `80-100%` Excellent (auto-advance), `50-79%` Good (manual review), `0-49%` Under-qualified.

---

## Pipeline Stage Transitions

```
[Screening] → [Assessment] → [Interview] → [Offering]
     ↓              ↓              ↓
  [Rejected]     [Rejected]     [Rejected]
```

Each transition is recorded with timestamps in the Pipeline Service. Stages only move forward — no rollback.
