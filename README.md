# Nourivex Laravel Template

<p align="center">
  <img src="public/logo.png" width="96" alt="Nourivex Logo">
</p>

<p align="center">
  <strong>Modern Laravel Engineering Template</strong>
</p>

<p align="center">
  A structured Laravel foundation for building maintainable web applications with a simple, cross-platform development workflow.
</p>

<p align="center">

![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![Node.js](https://img.shields.io/badge/Node.js-24.x-339933?style=flat-square&logo=node.js&logoColor=white)
![DDEV](https://img.shields.io/badge/DDEV-supported-0D1117?style=flat-square)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

</p>

---

## About

**Nourivex Laravel Template** is a reusable Laravel project foundation developed under **Nourivex**.

The template keeps Laravel's standard architecture while providing a more opinionated starting point for development, including:

- Modern Nourivex-branded welcome page
- Laravel 13 foundation
- Vite-based frontend workflow
- Cross-platform development helper
- DDEV-first development environment
- Local PHP fallback
- Automated project setup
- Environment diagnostics
- Consistent Linux and Windows commands
- Developer-oriented project documentation

The goal is simple:

> **Clone → Setup → Develop.**

---

## Technology Stack

| Technology | Purpose |
|---|---|
| Laravel 13 | Application framework |
| PHP 8.4+ | Backend runtime |
| Composer | PHP dependency management |
| Node.js 24+ | Frontend tooling |
| npm | JavaScript dependency management |
| Vite | Frontend asset bundling |
| DDEV | Local development environment |
| MariaDB | Development database |
| Tailwind CSS | UI styling |

---

## Requirements

### Recommended

For the DDEV workflow:

- Git
- DDEV
- Docker
- Node.js
- A code editor such as VS Code

DDEV provides the PHP, database, and web-server environment used by the project.

### Local PHP fallback

The development helper can also work without DDEV when the local environment provides:

- PHP 8.4+
- Composer
- Node.js
- npm

---

# Getting Started

## 1. Clone the repository

```bash
git clone https://github.com/Nourivex/nourivex-laravel-template.git
````

Enter the project:

```bash
cd nourivex-laravel-template
```

---

## 2. Run project setup

### Linux / macOS

```bash
./dev --setup
```

### Windows

```bat
dev.bat --setup
```

The setup helper will automatically detect the available development environment.

### With DDEV

The helper will:

1. Start DDEV
2. Install Composer dependencies
3. Create `.env` from `.env.example` when necessary
4. Generate the Laravel application key
5. Install Node dependencies
6. Build frontend assets

### Without DDEV

The helper falls back to the local PHP environment and uses:

```text
composer
php artisan
npm
```

when the required tools are available.

---

## Development Setup Flow

```text
Clone Repository
       │
       ▼
   ./dev --setup
       │
       ▼
Detect Environment
       │
   ┌───┴────┐
   │        │
 DDEV     Local PHP
   │        │
   ▼        ▼
Composer  Composer
   │        │
   ▼        ▼
 .env     .env
   │        │
   ▼        ▼
App Key  App Key
   │        │
   ▼        ▼
  npm      npm
   │        │
   ▼        ▼
Vite Build
       │
       ▼
     READY
```

Database migrations are intentionally **not executed automatically**.

This prevents the setup helper from making assumptions about the project's database state.

---

# Development Helper

The project includes two equivalent development helpers:

```text
Linux / macOS
./dev

Windows
dev.bat
```

Both provide the same basic interface.

---

## Help

Linux:

```bash
./dev --help
```

Windows:

```bat
dev.bat --help
```

---

## Version

```bash
./dev --version
```

or:

```bat
dev.bat --version
```

---

## Setup

Prepare a fresh development environment:

```bash
./dev --setup
```

Windows:

```bat
dev.bat --setup
```

---

## Environment Diagnostics

Check the development environment:

```bash
./dev --doctor
```

Example:

```text
✓ Laravel project      artisan found
✓ PHP                  8.4.x
✓ Composer             installed
✓ Node/npm             installed
✓ DDEV                 installed
✓ .ddev/config.yaml    found

Environment
────────────────────────────────────────
✓ Backend              DDEV
✓ Status               READY
```

---

## Environment Information

Display detected project and environment information:

```bash
./dev --env
```

This can show:

* Laravel version
* PHP version
* DDEV version
* Selected backend
* Detected development environment

---

# Artisan Commands

The helper acts as a shortcut for Laravel Artisan.

Instead of:

```bash
ddev artisan make:model User -m
```

you can use:

```bash
./dev make:model User -m
```

Instead of:

```bash
ddev artisan make:controller AuthController
```

use:

```bash
./dev make:controller AuthController
```

Examples:

```bash
./dev make:model User -m

./dev make:controller AuthController

./dev make:migration create_users_table

./dev migrate

./dev migrate:fresh --seed

./dev route:list

./dev test

./dev tinker
```

All arguments that are not recognized as helper options are passed directly to Laravel Artisan.

---

# Environment Priority

The helper uses the following priority:

```text
1. DDEV project
2. Local PHP
3. Error
```

If both DDEV and local PHP are available and the current directory contains:

```text
.ddev/config.yaml
```

the helper uses DDEV.

Otherwise, it falls back to the local PHP environment.

---

# Project Structure

The template follows Laravel's standard project structure.

```text
.
├── app/
├── bootstrap/
├── config/
├── database/
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

The template intentionally keeps Laravel's familiar structure so developers can work with standard Laravel tooling without learning a new application architecture.

---

# Frontend

Frontend assets are managed through Vite.

Install dependencies:

```bash
npm install
```

Build production assets:

```bash
npm run build
```

When using DDEV:

```bash
ddev npm install
ddev npm run build
```

The development helper performs these steps automatically during:

```bash
./dev --setup
```

---

# DDEV

When a DDEV project is available, the recommended workflow is:

```bash
ddev start
```

Check the project:

```bash
ddev describe
```

Run Artisan:

```bash
ddev artisan
```

Run Composer:

```bash
ddev composer install
```

Run npm:

```bash
ddev npm install
```

The `dev` helper provides shorter equivalents for common operations.

---

# Database

The template is designed to work with the database configuration provided by the Laravel application and DDEV environment.

Migrations are intentionally manual.

Run migrations with:

```bash
./dev migrate
```

or:

```bash
dev.bat migrate
```

For a fresh database:

```bash
./dev migrate:fresh
```

With seeders:

```bash
./dev migrate:fresh --seed
```

> **Warning:** `migrate:fresh` drops all tables before recreating them. Use it only when appropriate for the development environment.

---

# Design Philosophy

Nourivex Laravel Template follows several principles.

### 1. Keep Laravel Familiar

The template does not replace Laravel's architecture.

Developers should still be able to use standard Laravel documentation, Artisan commands, Composer packages, and Laravel conventions.

### 2. Reduce Setup Friction

A new developer should not need to remember a long list of environment commands.

Instead:

```bash
./dev --setup
```

should handle the common setup process.

### 3. Environment Aware

The helper detects the available environment instead of assuming that every developer uses the same setup.

```text
DDEV → preferred
PHP  → fallback
```

### 4. Explicit Database Operations

Project setup does not automatically run migrations or seed the database.

Database-changing operations remain explicit.

### 5. Cross-Platform Workflow

Linux and Windows provide equivalent helper commands:

```text
./dev
dev.bat
```

The goal is to make team development more consistent across operating systems.

---

# Nourivex

**Nourivex** is a technology and engineering initiative focused on building structured digital systems, developer tooling, and practical software solutions.

This Laravel template is maintained as a reusable engineering foundation for Nourivex projects and development workflows.

---

# Contributing

Contributions, improvements, and suggestions are welcome.

Before submitting changes:

1. Keep the existing Laravel structure intact.
2. Avoid unnecessary dependencies.
3. Keep Linux and Windows helper behavior consistent.
4. Test changes in the intended development environment.
5. Update the documentation when behavior changes.

---

# License

This project is open-sourced under the MIT License.

See the `LICENSE` file for details.

---

<p align="center">
  Built with Laravel · Maintained by Nourivex
</p>

