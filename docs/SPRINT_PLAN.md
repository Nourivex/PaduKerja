# Sprint Plan — PaduKerja

> Pemetaan milestone perkuliahan Semester 7 dan Product Backlog untuk Platform Rekrutmen Terintegrasi Berbasis Microservice.

---

## 1. Pemetaan Milestone Perkuliahan

### Semester 7 — Pemrograman Web Service (5537344 / 3 SKS)

| Minggu | Fase | Topik / Aktivitas | Deliverable |
|--------|------|-------------------|-------------|
| 1–2 | **Fondasi HTTP** | Memahami protokol HTTP, methods (GET/POST/PUT/PATCH/DELETE), status codes, headers, request-response cycle | Dokumen ringkasan fondasi HTTP, setup environment DDEV |
| 3–4 | **REST API Design** | Prinsip RESTful API, resource naming, JSON envelope standar, kontrak API, versioning | `docs/API_CONTRACT_STANDARDS.md`, endpoint design per service |
| 5–7 | **Core Implementation** | Implementasi 4 microservices, database schema, model & migration, CRUD endpoints | Service skeleton + working CRUD endpoints |
| 8 | **UTS** | Ujian Tengah Semester — Presentasi progress & demo API | Demo API via Postman/Insomnia, laporan progress |
| 9–10 | **Inter-Service Communication** | Komunikasi antar service via REST, matchmaking engine, pipeline integration | Working inter-service calls, match scoring |
| 11–12 | **Authentication & Authorization** | JWT implementation, role-based access control, middleware protection | Protected endpoints, multi-role access |
| 13–14 | **Testing & Documentation** | API testing, integration testing, dokumentasi Postman collection, refinement | Test suite, Postman collection, API docs |
| 15 | **Final Integration** | Full system integration, end-to-end testing, bug fixing | Fully integrated system |
| 16 | **UAS** | Ujian Akhir Semester — Presentasi final & demo lengkap | Final demo, laporan akhir, source code |

---

## 2. Sprint Breakdown

### Sprint 1: Foundation (Minggu 1–2)

**Goal:** Setup infrastruktur dan pemahaman fondasi.

| ID | Task | Assignee | Priority | Status |
|----|------|----------|----------|--------|
| S1-01 | Setup repository & DDEV environment | All | 🔴 High | ⬜ To Do |
| S1-02 | Konfigurasi 4 database instances (db_auth, db_jobs, db_applications, db_pipeline) | All | 🔴 High | ⬜ To Do |
| S1-03 | Dokumentasi README.md & project structure | MUHAMMAD AFFIF | 🟡 Medium | ⬜ To Do |
| S1-04 | Studi & ringkasan fondasi HTTP protocol | All | 🔴 High | ⬜ To Do |
| S1-05 | Setup development helper (`./dev`) untuk multi-service | MUHAMMAD AFFIF | 🟡 Medium | ⬜ To Do |

### Sprint 2: API Design (Minggu 3–4)

**Goal:** Finalisasi desain API dan kontrak antar service.

| ID | Task | Assignee | Priority | Status |
|----|------|----------|----------|--------|
| S2-01 | Definisi JSON envelope standar & error format | All | 🔴 High | ⬜ To Do |
| S2-02 | Desain endpoint Auth & Profile Service | MUHAMMAD AFFIF | 🔴 High | ⬜ To Do |
| S2-03 | Desain endpoint Job Catalog Service | MUHAMAD FAHREN A.R. | 🔴 High | ⬜ To Do |
| S2-04 | Desain endpoint Application & Matchmaking Service | NABE'ELA AYU N.T.Z. | 🔴 High | ⬜ To Do |
| S2-05 | Desain endpoint Recruitment Pipeline Service | MUHAMMAD YASIR I.N. | 🔴 High | ⬜ To Do |
| S2-06 | Buat docs/ARCHITECTURE.md & docs/API_CONTRACT_STANDARDS.md | All | 🟡 Medium | ⬜ To Do |
| S2-07 | Review & approval kontrak API antar service | All | 🔴 High | ⬜ To Do |

### Sprint 3: Core Implementation — Phase 1 (Minggu 5–6)

**Goal:** Implementasi skeleton service dan CRUD dasar.

| ID | Task | Assignee | Priority | Status |
|----|------|----------|----------|--------|
| S3-01 | Model, Migration, Seeder — `users`, `user_skills`, `roles` | MUHAMMAD AFFIF | 🔴 High | ⬜ To Do |
| S3-02 | Model, Migration, Seeder — `jobs`, `job_requirements`, `skill_tags` | MUHAMAD FAHREN A.R. | 🔴 High | ⬜ To Do |
| S3-03 | Model, Migration, Seeder — `applications`, `match_scores`, `cv_documents` | NABE'ELA AYU N.T.Z. | 🔴 High | ⬜ To Do |
| S3-04 | Model, Migration, Seeder — `pipeline_stages`, `stage_transitions`, `interview_schedules` | MUHAMMAD YASIR I.N. | 🔴 High | ⬜ To Do |
| S3-05 | CRUD Controller — Auth & Profile (register, login, profile, skills) | MUHAMMAD AFFIF | 🔴 High | ⬜ To Do |
| S3-06 | CRUD Controller — Job Catalog (jobs, requirements) | MUHAMAD FAHREN A.R. | 🔴 High | ⬜ To Do |
| S3-07 | CRUD Controller — Applications (submit, list, detail) | NABE'ELA AYU N.T.Z. | 🔴 High | ⬜ To Do |
| S3-08 | CRUD Controller — Pipeline (init, advance, reject, board) | MUHAMMAD YASIR I.N. | 🔴 High | ⬜ To Do |

### Sprint 4: Core Implementation — Phase 2 (Minggu 7)

**Goal:** Business logic dan validasi.

| ID | Task | Assignee | Priority | Status |
|----|------|----------|----------|--------|
| S4-01 | Implementasi JWT Authentication middleware | MUHAMMAD AFFIF | 🔴 High | ⬜ To Do |
| S4-02 | Implementasi role-based authorization (applicant, recruiter, admin) | MUHAMMAD AFFIF | 🔴 High | ⬜ To Do |
| S4-03 | Job quota management & status toggle (open/closed) | MUHAMAD FAHREN A.R. | 🟡 Medium | ⬜ To Do |
| S4-04 | Skill tagging system & search/filter by skill | MUHAMAD FAHREN A.R. | 🟡 Medium | ⬜ To Do |
| S4-05 | CV upload & file management | NABE'ELA AYU N.T.Z. | 🟡 Medium | ⬜ To Do |
| S4-06 | Kanban board view (group by stage) | MUHAMMAD YASIR I.N. | 🟡 Medium | ⬜ To Do |
| S4-07 | Form Request validation di semua endpoint | All | 🔴 High | ⬜ To Do |

### Sprint 5: UTS Preparation (Minggu 8)

**Goal:** Persiapan demo dan presentasi UTS.

| ID | Task | Assignee | Priority | Status |
|----|------|----------|----------|--------|
| S5-01 | Persiapan Postman collection untuk demo | All | 🔴 High | ⬜ To Do |
| S5-02 | Laporan progress UTS | All | 🔴 High | ⬜ To Do |
| S5-03 | Bug fixing & stabilisasi CRUD endpoints | All | 🔴 High | ⬜ To Do |
| S5-04 | Rehearsal presentasi | All | 🟡 Medium | ⬜ To Do |

### Sprint 6: Inter-Service Integration (Minggu 9–10)

**Goal:** Komunikasi antar service dan matchmaking engine.

| ID | Task | Assignee | Priority | Status |
|----|------|----------|----------|--------|
| S6-01 | HTTP Client setup untuk inter-service calls | All | 🔴 High | ⬜ To Do |
| S6-02 | Implementasi Skill-Matchmaking Engine (Weighted Jaccard) | NABE'ELA AYU N.T.Z. | 🔴 High | ⬜ To Do |
| S6-03 | Integrasi Application Service ↔ Auth Service (fetch skills) | NABE'ELA AYU N.T.Z. + MUHAMMAD AFFIF | 🔴 High | ⬜ To Do |
| S6-04 | Integrasi Application Service ↔ Job Catalog (fetch requirements) | NABE'ELA AYU N.T.Z. + MUHAMAD FAHREN A.R. | 🔴 High | ⬜ To Do |
| S6-05 | Integrasi Pipeline Service ↔ Application Service | MUHAMMAD YASIR I.N. + NABE'ELA AYU N.T.Z. | 🔴 High | ⬜ To Do |
| S6-06 | Pipeline auto-initialization on application submit | MUHAMMAD YASIR I.N. | 🟡 Medium | ⬜ To Do |
| S6-07 | Interview scheduling implementation | MUHAMMAD YASIR I.N. | 🟡 Medium | ⬜ To Do |

### Sprint 7: Auth & Security Hardening (Minggu 11–12)

**Goal:** Keamanan dan access control.

| ID | Task | Assignee | Priority | Status |
|----|------|----------|----------|--------|
| S7-01 | JWT token refresh mechanism | MUHAMMAD AFFIF | 🟡 Medium | ⬜ To Do |
| S7-02 | Rate limiting per endpoint | MUHAMMAD AFFIF | 🟢 Low | ⬜ To Do |
| S7-03 | Inter-service authentication (service-to-service token) | MUHAMMAD AFFIF | 🟡 Medium | ⬜ To Do |
| S7-04 | Input sanitization & XSS prevention | All | 🟡 Medium | ⬜ To Do |
| S7-05 | Error handling standarisasi (sesuai API_CONTRACT_STANDARDS) | All | 🔴 High | ⬜ To Do |

### Sprint 8: Testing & Documentation (Minggu 13–14)

**Goal:** Test coverage dan dokumentasi final.

| ID | Task | Assignee | Priority | Status |
|----|------|----------|----------|--------|
| S8-01 | Unit test — Auth & Profile Service | MUHAMMAD AFFIF | 🔴 High | ⬜ To Do |
| S8-02 | Unit test — Job Catalog Service | MUHAMAD FAHREN A.R. | 🔴 High | ⬜ To Do |
| S8-03 | Unit test — Application & Matchmaking Service | NABE'ELA AYU N.T.Z. | 🔴 High | ⬜ To Do |
| S8-04 | Unit test — Recruitment Pipeline Service | MUHAMMAD YASIR I.N. | 🔴 High | ⬜ To Do |
| S8-05 | Integration test — inter-service communication | All | 🔴 High | ⬜ To Do |
| S8-06 | Finalisasi Postman collection (semua endpoint) | All | 🟡 Medium | ⬜ To Do |
| S8-07 | Update dokumentasi (README, ARCHITECTURE, API_CONTRACT) | All | 🟡 Medium | ⬜ To Do |

### Sprint 9: Final Integration & UAS (Minggu 15–16)

**Goal:** Integrasi akhir, demo final.

| ID | Task | Assignee | Priority | Status |
|----|------|----------|----------|--------|
| S9-01 | End-to-end testing full flow | All | 🔴 High | ⬜ To Do |
| S9-02 | Bug fixing & performance optimization | All | 🔴 High | ⬜ To Do |
| S9-03 | Persiapan demo UAS | All | 🔴 High | ⬜ To Do |
| S9-04 | Laporan akhir & dokumentasi final | All | 🔴 High | ⬜ To Do |
| S9-05 | Code review & cleanup | All | 🟡 Medium | ⬜ To Do |
| S9-06 | Rehearsal presentasi UAS | All | 🟡 Medium | ⬜ To Do |

---

## 3. Product Backlog Summary

### Per Service

| Service | Penanggung Jawab | Backlog Items | Critical | High | Medium | Low |
|---------|------------------|---------------|----------|------|--------|-----|
| Auth & Profile | MUHAMMAD AFFIF | 14 | 0 | 9 | 4 | 1 |
| Job Catalog | MUHAMAD FAHREN A.R. | 10 | 0 | 7 | 3 | 0 |
| Application & Matchmaking | NABE'ELA AYU N.T.Z. | 11 | 0 | 8 | 2 | 1 |
| Recruitment Pipeline | MUHAMMAD YASIR I.N. | 10 | 0 | 7 | 3 | 0 |
| Cross-team | All | 17 | 0 | 12 | 5 | 0 |

### Per Sprint (Velocity Estimate)

| Sprint | Minggu | Items | Focus Area |
|--------|--------|-------|------------|
| Sprint 1 | 1–2 | 5 | Foundation & Setup |
| Sprint 2 | 3–4 | 7 | API Design & Contract |
| Sprint 3 | 5–6 | 8 | Core CRUD Implementation |
| Sprint 4 | 7 | 7 | Business Logic & Validation |
| Sprint 5 | 8 | 4 | UTS Preparation |
| Sprint 6 | 9–10 | 7 | Inter-Service Integration |
| Sprint 7 | 11–12 | 5 | Security & Auth Hardening |
| Sprint 8 | 13–14 | 7 | Testing & Documentation |
| Sprint 9 | 15–16 | 6 | Final Integration & UAS |

---

## 4. Definition of Done (DoD)

Sebuah task dianggap **Done** jika memenuhi:

- [x] Code telah di-push ke branch feature masing-masing
- [x] Endpoint mengikuti standar JSON envelope yang disepakati
- [x] Endpoint mengembalikan status code yang sesuai
- [x] Request validation terimplementasi
- [x] Minimal 1 unit test per endpoint
- [x] Dokumentasi Postman collection terupdate
- [x] Code review oleh minimal 1 anggota tim lain
- [x] Tidak ada regression pada service lain

---

## 5. Risiko & Mitigasi

| Risiko | Dampak | Probabilitas | Mitigasi |
|--------|--------|-------------|----------|
| Koordinasi antar service gagal sinkron | 🔴 High | Medium | Weekly sync meeting, shared Postman workspace |
| Database schema conflict | 🟡 Medium | Low | Database-per-service pattern, independent migration |
| Skill mismatch dalam implementasi | 🟡 Medium | Medium | Pair programming, knowledge sharing session |
| Deadline mepet karena load kuliah lain | 🔴 High | High | Buffer time di setiap sprint, prioritasi backlog |
| Environment inconsistency antar anggota | 🟡 Medium | Medium | DDEV sebagai standard environment |

---

<p align="center">
  <strong>PaduKerja</strong> · Sprint Plan · Kelompok 4 UHN
</p>
