# Motorpool System – Security Implementation Plan

Laravel 12 · PHP 8.2+ · Livewire 3.6 · Breeze · Local now, Laravel Cloud later

This document defines **phased security work** for the Motorpool System. Use it as a living checklist.

---

## Phase 1 – Application Hardening (Local & Dev)

Focus: secure the **codebase itself** so that moving to production later is low risk.

### 1. Environment & Config (Dev Baseline)

- [x] Review `.env` and remove any secrets accidentally committed to git
- [x] Ensure different envs per machine (no sharing the same `.env` between dev/prod)
- [x] Set `APP_URL=http://localhost:8000` for local dev
- [x] Confirm `SESSION_DRIVER=database` (or `file` is acceptable for local only)
- [x] Confirm CSRF protection is enabled (default `VerifyCsrfToken` middleware kept)

### 2. Authentication & Authorization

- [x] Define roles/abilities needed (`admin`, `staff`, `user`) — added constants to `User` model
- [x] Create **authorization policies** for core models:
  - [x] `VehiclePolicy` (viewAny, view, create, update, delete, export)
  - [x] `MaintenanceRecordPolicy` (viewAny, view, create, update, delete, export)
  - [x] `TripTicketPolicy` (viewAny, view, create, update, delete)
  - [x] `UserPolicy` (viewAny, view, create, update, delete, manage)
- [x] Register policies in `AppServiceProvider` via `Gate::policy()`
- [x] Call `$this->authorize()` in:
  - [x] `Vehicles\Index` (mount, openCreateModal, edit, save, exportCsv)
  - [x] `Maintenance\Index` (mount, openCreateModal, save, exportCsv, exportPdf, exportExcel)
  - [x] `TripTickets\Index` (mount, openCreateModal, edit, save)
  - [x] `Account\UsersIndex` (mount, setRole, toggleStatus)
- [x] Pass `canCreate`, `canExport` abilities to views for conditional UI

### 3. Validation & Mass Assignment

- [x] Ensure **all** Livewire components validate user input using:
  - [x] `$this->validate([...])` in action methods (all components use `rules()` method)
- [x] For each model (`Vehicle`, `MaintenanceRecord`, `MaintenanceMaterial`, `TripTicket`, `User`):
  - [x] Define explicit `$fillable` arrays — all models have proper `$fillable`
  - [x] No `protected $guarded = [];` in any model
- [x] Review all `create()` / `update()` calls to ensure they only use validated data

### 4. Livewire-Specific Security

- [x] Identify **sensitive IDs** and mark as `#[Locked]` where appropriate:
  - [x] `Vehicles\Index::$editingId`
  - [x] `TripTickets\Index::$editingId`
- [x] Authorization checks prevent trusting client-modifiable state
- [x] For destructive actions (exports):
  - [x] Server-side authorization check added to all export methods
- [x] Verify no critical form fields are inside elements with `wire:ignore` — confirmed safe

### 5. File Uploads (if enabled)

- [x] List all upload points: `Vehicles\Index` (vehicle photo)
- [x] Add strict validation rules:
  - [x] `'photo' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp']`
- [x] Ensure storage path uses `storage/app/public/vehicles`
- [x] Use random filenames via `store()` method
- [x] Files served via `Storage::url()` through public disk

### 6. Output Escaping & XSS

- [x] Search for `{!!` in Blade views — **none found**, all output is escaped
- [x] Prefer `{{ $variable }}` (escaped) everywhere — confirmed
- [x] If rich text is ever added, plan to sanitize with a library before rendering

### 7. Local Tooling

- [x] Add a simple security checklist section to `README.md` (linking to this file)
- [ ] Optionally run a basic static analysis (e.g. Larastan or Psalm) to catch issues

> **Note:** IDE warnings about `auth()->user()` being "undefined" are false positives from Intelephense. The `auth()` helper returns `\Illuminate\Contracts\Auth\Factory` which has a valid `user()` method. This code works correctly at runtime.

---

## Phase 2 – Pre–Laravel Cloud Readiness

Focus: prepare configuration and behavior that will be required when the app runs on a public URL, even before actual deployment.

### 1. Production-Oriented Config Template

- [ ] Create `.env.production.example` with sane defaults:
  - [ ] `APP_ENV=production`
  - [ ] `APP_DEBUG=false`
  - [ ] `APP_URL=https://your-domain` (placeholder for Laravel Cloud URL)
  - [ ] `SESSION_DRIVER=database`
  - [ ] `QUEUE_CONNECTION=database` (or `redis` when available)
  - [ ] Proper mail settings (SMTP)
- [ ] Document which env vars must be set in Laravel Cloud (DB, Redis, mail, etc.)

### 2. Sessions & Cookies

- [ ] In `config/session.php` prepare production settings:
  - [ ] `secure => true` (only over HTTPS)
  - [ ] `http_only => true`
  - [ ] `same_site => 'lax'` (or `'strict'` if UX allows)
  - [ ] `lifetime` reasonable (e.g. 120 minutes)
- [ ] Ensure `password_timeout` in `config/auth.php` is acceptable for sensitive actions

### 3. Queues, Jobs & Scheduling

- [ ] Confirm all email notifications and heavy processes run via queues
- [ ] Ensure jobs fail gracefully and avoid leaking sensitive data in exceptions
- [ ] Verify `motorpool:*` console commands are **CLI-only** (not exposed via web)
- [ ] Prepare documentation for Laravel Cloud scheduler configuration (e.g. `schedule:run` every minute)

### 4. Access Control Review

- [ ] Manually test authorization paths:
  - [ ] Non-admin user attempting to access admin-only sections
  - [ ] Attempt to modify other users’ vehicles / maintenance / trip tickets via URL/ID changes
- [ ] Add automated tests for critical policies (feature tests using Laravel’s testing tools)
 
### 5. Data Imports (Optional)

- [ ] If you use import commands (e.g. `motorpool:import-vehicles`, `motorpool:import-maintenance`), first run them against a **copy** of production-like data
- [ ] Verify imported data permissions and visibility align with authorization rules

---

## Phase 3 – Laravel Cloud Production Deployment

Focus: hardening once the app is **publicly reachable**.

### 1. Laravel Cloud Environment Setup

- [ ] Configure app in Laravel Cloud with:
  - [ ] `APP_ENV=production`
  - [ ] `APP_DEBUG=false`
  - [ ] Correct `APP_URL` for the Cloud URL or custom domain
- [ ] Configure database and cache services (MySQL/Postgres, Redis if used)
- [ ] Configure mail (SMTP provider)

### 2. HTTPS & Domain Security

- [ ] Attach custom domain to Laravel Cloud app
- [ ] Enable HTTPS and automatic certificate management
- [ ] Enforce HTTPS redirects (via Cloud or Laravel middleware)
- [ ] Confirm cookies are marked `secure` and `http_only`

### 3. Queues & Scheduler in Laravel Cloud

- [ ] Configure queue worker(s) for `QUEUE_CONNECTION`
- [ ] Configure the scheduler to run `php artisan schedule:run` every minute
- [ ] Verify maintenance notification jobs run as expected in production

### 4. Monitoring & Logging

- [ ] Confirm Laravel logs are stored and rotated by Laravel Cloud
- [ ] Set up alerts for application errors (through Cloud UI or external service)
- [ ] Add uptime monitoring for key endpoints (`/`, `/login`, `/maintenance`)

### 5. Backups & Recovery

- [ ] Enable automated database backups in Laravel Cloud
- [ ] Document restore procedures
- [ ] Perform at least one test restore to a staging environment

### 6. Ongoing Security Practices

- [ ] Keep all Composer dependencies up to date (`composer update` plan)
- [ ] Regularly review Laravel and package security advisories
- [ ] Periodically review access logs for suspicious activity
- [ ] Re-run tests after any security-related change

---

## Usage

- Treat this file as a **living roadmap**; update checkboxes and add details as you implement.
- When starting a new security-focused session, pick the next unchecked items in the current phase.
- Before going live on Laravel Cloud, ensure **Phase 1 and Phase 2** are fully checked.
