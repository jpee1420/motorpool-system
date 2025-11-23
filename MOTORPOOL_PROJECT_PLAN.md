# Motorpool System – Laravel + Livewire + Alpine Project Plan

## 1. Vision & Goals

- **Modernize stack**: Migrate the existing Flutter + Firebase motorpool system to a Laravel 11 + Livewire 3 + Alpine.js web app.
- **Centralized, online system**: Primary deployment is cloud-hosted (DigitalOcean), accessed via browser from multiple PCs.
- **Offline tolerance (PC → cloud)**: Allow data entry on a local server and sync to cloud when online (later phase).
- **Maintenance intelligence**: Track vehicles, maintenance history, and notify users by email/SMS when:
  - Mileage thresholds are reached.
  - Maintenance is due based on time (e.g., 6 months).

---

## 2. Tech Stack & Key Dependencies

- **Backend Framework**: Laravel 11 (PHP 8.2+)
- **Frontend**: Blade + Livewire 3 + Alpine.js 3
- **Styling**: Tailwind CSS 3 + daisyUI
- **Database**: MySQL 8
  - Local: MySQL bundled with Laragon
  - Production: DigitalOcean Managed MySQL (or MySQL on Droplet for small setups)
- **Auth & Scaffolding**: Laravel Breeze (Livewire stack)
- **Queue & Jobs**:
  - Local: `database` queue driver
  - Production: `redis` (preferred) or `database`
- **Mail**:
  - Local: Mailpit (bundled with Laragon)
  - Production: SMTP (e.g., Mailgun, SendGrid, SES)
- **SMS**: Pluggable SMS provider (e.g., Twilio, Infobip) via a dedicated notification channel/service class.
- **Environment**:
  - Local server: Laragon on Windows
  - Production server: DigitalOcean Droplet (Ubuntu 22.04 LTS)

---

## 3. Local Development Environment Setup

### 3.1. Install Tools

- **Laragon** (full): PHP 8.2+, MySQL, Apache/Nginx, Mailpit
- **Composer** (latest)
- **Node.js** (LTS, e.g., 20.x) + npm
- **Git**
- **IDE**: VS Code / PhpStorm

### 3.2. Create Laravel Project

From Laragon terminal (or system terminal):

1. Navigate to projects folder:
   - `C:\Users\Pao\Projects\motorpool-system`
2. Create Laravel app (if not already created):
   - `composer create-project laravel/laravel .`
3. Generate app key:
   - `php artisan key:generate`

### 3.3. Configure `.env` (Local)

Key sections:

- **App & URL**
  - `APP_NAME="Motorpool System"`
  - `APP_ENV=local`
  - `APP_DEBUG=true`
  - `APP_URL=http://motorpool-system.test` (Laragon virtual host)

- **Database** (Laragon defaults)
  - `DB_CONNECTION=mysql`
  - `DB_HOST=127.0.0.1`
  - `DB_PORT=3306`
  - `DB_DATABASE=motorpool_db`
  - `DB_USERNAME=root`
  - `DB_PASSWORD=`

- **Mail (Mailpit via Laragon)**
  - `MAIL_MAILER=smtp`
  - `MAIL_HOST=127.0.0.1`
  - `MAIL_PORT=1025`
  - `MAIL_USERNAME=null`
  - `MAIL_PASSWORD=null`
  - `MAIL_ENCRYPTION=null`
  - `MAIL_FROM_ADDRESS="no-reply@motorpool.test"`
  - `MAIL_FROM_NAME="Motorpool System"`

- **Queues**
  - `QUEUE_CONNECTION=database`

### 3.4. Install Breeze + Livewire + Frontend

1. Require Breeze:
   - `composer require laravel/breeze --dev`
2. Install Breeze with Livewire stack:
   - `php artisan breeze:install livewire`
3. Install and build frontend assets:
   - `npm install`
   - `npm run dev` (for development)
4. Run initial migrations:
   - `php artisan migrate`

### 3.5. Mailpit Usage

- Start Mailpit from Laragon menu: `Mail → Mailpit`.
- UI available at: `http://localhost:1137`
- Password reset and other emails will appear here during local development.

---

## 4. Project Structure & Modules

### 4.1. High-Level Structure

- `app/Models`
  - `User.php`
  - `Vehicle.php`
  - `MaintenanceRecord.php`
  - `MaintenanceMaterial.php`
  - `NotificationLog.php`
- `app/Http/Livewire`
  - `Auth/` (Breeze generated)
  - `Layout/SidebarMenu.php`
  - `Layout/Topbar.php`
  - `Vehicles/Index.php`
  - `Vehicles/Form.php`
  - `Maintenance/Index.php`
  - `Maintenance/Show.php`
  - `TripTickets/Index.php`
  - `Calendar/Index.php`
  - `Account/Profile.php`
- `app/Services`
  - `Maintenance/NextMaintenanceCalculator.php`
  - `Notifications/MaintenanceNotifier.php`
  - `Notifications/SmsGateway.php` (interface)
  - `Notifications/TwilioSmsGateway.php` (implementation)
- `app/Notifications`
  - `MaintenanceDueNotification.php`
  - `MileageThresholdNotification.php`
- `app/Console/Commands`
  - `CheckVehicleMaintenanceDue.php`

### 4.2. Core Functional Modules

1. **Authentication & Authorization**
   - Breeze (Livewire) for login, registration, email verification, password reset.
   - `users` table extended with `role` and `status` fields.
   - Use policies/gates for access control (e.g., only admins can manage vehicles, maintenance records, and accounts).

2. **Vehicle Management**
   - CRUD for vehicles.
   - Fields: plate number, make, model, year, current odometer, last maintenance info, next maintenance due by date/odometer.
   - Vehicle list with filters, search, and status chips (Operational, Under Maintenance, Non-operational).

3. **Maintenance Log**
   - Records of maintenance against vehicles.
   - Fields include:
     - `vehicle_id`
     - `performed_by_user_id`
     - `performed_at`
     - `odometer_reading`
     - `description_of_work`
     - `personnel_labor_cost`
     - `materials_cost_total`
     - `total_cost`
     - `next_maintenance_due_at`
     - `next_maintenance_due_odometer`
   - Detail view showing maintenance materials used.

4. **Maintenance Materials**
   - Link materials/parts to a maintenance record.
   - Fields: name, description, quantity, unit, unit cost, total cost.

5. **Notifications & Alerts**
   - Automatic email/SMS notifications when maintenance is due by time or mileage.
   - Logging of all sent notifications.

6. **Trip Tickets** (later phase)
   - Manage trip tickets for vehicle usage.
   - Associate trip tickets with vehicles and drivers.

7. **Calendar & Dashboard**
   - Calendar view of upcoming and past maintenance tasks.
   - Dashboard summaries: number of vehicles per status, upcoming maintenance, overdue maintenance.

8. **Account Management**
   - Manage users, roles, and statuses.
   - Profile management for logged-in user.

---

## 5. Database Schema Overview

### 5.1. Users (`users`)

- `id`
- `name`
- `email` (unique)
- `email_verified_at`
- `password`
- `role` (e.g., admin, staff, driver)
- `status` (e.g., active, pending, disabled)
- `remember_token`
- Timestamps

### 5.2. Vehicles (`vehicles`)

- `id`
- `plate_number` (unique)
- `make`
- `model`
- `year` (small integer)
- `current_odometer`
- `last_maintenance_at`
- `last_maintenance_odometer`
- `next_maintenance_due_at`
- `next_maintenance_due_odometer`
- Timestamps

### 5.3. Maintenance Records (`maintenance_records`)

- `id`
- `vehicle_id` (FK → vehicles)
- `performed_by_user_id` (nullable FK → users)
- `performed_at`
- `odometer_reading`
- `description_of_work`
- `personnel_labor_cost`
- `materials_cost_total`
- `total_cost`
- `next_maintenance_due_at`
- `next_maintenance_due_odometer`
- `notes`
- Timestamps

### 5.4. Maintenance Materials (`maintenance_materials`)

- `id`
- `maintenance_record_id` (FK → maintenance_records)
- `name`
- `description`
- `quantity`
- `unit`
- `unit_cost`
- `total_cost`
- Timestamps

### 5.5. Notification Logs (`notification_logs`)

- `id`
- `vehicle_id` (FK → vehicles)
- `maintenance_record_id` (nullable FK → maintenance_records)
- `channel` (email, sms)
- `type` (maintenance_due, mileage_threshold, etc.)
- `recipient_name`
- `recipient_contact` (email or phone)
- `sent_at`
- `status` (sent, failed)
- `error_message`
- Timestamps

### 5.6. Password Reset Tokens (`password_reset_tokens`)

- `email` (primary key)
- `token`
- `created_at`

---

## 6. Livewire Components & UI Plan

### 6.1. Layout

- **Main layout**: `resources/views/layouts/app.blade.php`
  - Top navigation bar: app name, user menu, notifications icon.
  - Left sidebar: navigation for Vehicle Management, Maintenance Log, Trip Tickets, Calendar, Account Management.
  - Uses Tailwind + daisyUI for a clean, modern look.

- **Livewire Layout Components**:
  - `Layout/SidebarMenu` – handles menu items, active states, and collapse behavior.
  - `Layout/Topbar` – search, notifications, user profile dropdown.

### 6.2. Vehicle Management

- `Vehicles/Index`
  - Paginated table with filters (status, make, year) and search.
  - Column set similar to the old Flutter UI but with improved design.
  - Actions: view, edit, mark as under maintenance, mark as operational.

- `Vehicles/Form`
  - Create/edit vehicle modal or separate page.
  - Live validation via `wire:model` and Laravel validation.

### 6.3. Maintenance

- `Maintenance/Index`
  - List of maintenance records with filters by vehicle, date range, and status.

- `Maintenance/Show`
  - Detailed view for a maintenance record including materials used and costs.

### 6.4. Dashboard & Calendar

- `Dashboard/Index`
  - KPIs: total vehicles, operational, under maintenance, non-operational.
  - Cards for upcoming and overdue maintenance.

- `Calendar/Index`
  - Calendar view (e.g., simple custom calendar or JS library) with maintenance events.

### 6.5. Account Management

- `Account/UsersIndex` – manage users, assign roles, activate/deactivate.
- `Account/Profile` – profile and password updates for current user.

---

## 7. Background Jobs & Notifications

### 7.1. Queue Setup

- Local:
  - `QUEUE_CONNECTION=database`
  - Run: `php artisan queue:table` + `php artisan migrate`
  - Start worker: `php artisan queue:work`

- Production:
  - Prefer Redis for performance.
  - Supervisor (or systemd) to keep `queue:work` running.

### 7.2. Scheduled Tasks

- Create command `CheckVehicleMaintenanceDue`:
  - Check all vehicles against:
    - `next_maintenance_due_at` <= today
    - `next_maintenance_due_odometer` <= current odometer
  - For due items, dispatch jobs to send notifications and store records in `notification_logs`.

- Register command in `app/Console/Kernel.php` schedule:
  - e.g., `->dailyAt('08:00')`

### 7.3. Notification Channels

- **Email**: standard Laravel notifications.
- **SMS**:
  - `SmsGateway` interface.
  - `TwilioSmsGateway` implementation using Twilio REST API.
  - Notification classes call the gateway via the service container.

---

## 8. Deployment Plan (DigitalOcean)

### 8.1. Infrastructure

- **Option A – Droplet + Managed MySQL**
  - 1–2 GB RAM Droplet for app server.
  - DO Managed MySQL instance.

- **Option B – App Platform + Managed DB** (simpler ops, higher cost)

### 8.2. Droplet Setup (Option A)

1. Provision Ubuntu Droplet.
2. Install stack:
   - Nginx
   - PHP-FPM (matching local version)
   - Composer
   - Node.js (for build step, or build locally and deploy public assets)
3. Clone project from Git.
4. Set folder permissions for `storage` and `bootstrap/cache`.
5. Copy `.env` and set production values (DB, mail, queue, app URL).
6. Run:
   - `composer install --no-dev --optimize-autoloader`
   - `php artisan key:generate` (if not already set)
   - `php artisan migrate --force`
   - `php artisan storage:link`
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
7. Build assets:
   - `npm ci`
   - `npm run build`
8. Configure Nginx server block for `server_name` and point to `public/index.php`.
9. Setup HTTPS via Let[200~'s Encrypt (Certbot).

### 8.3. Scheduler & Queue in Production

- Add cron entry for scheduler:
  - `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`
- Configure Supervisor (or systemd) to run `php artisan queue:work` continuously.

---

## 9. Migration Strategy from Flutter + Firebase

1. **Schema Mapping**
   - Map Firestore collections (vehicles, maintenance, users) to the new MySQL schema.
2. **Data Export**
   - Export Firestore collections as JSON/CSV.
3. **Import Scripts**
   - Create Laravel console commands to import data into MySQL with proper foreign keys.
4. **Verification**
   - Spot-check records between old and new systems.
5. **Cutover Plan**
   - Freeze writes to the old system.
   - Run final import.
   - Switch users to the new Laravel app.

---

## 10. Phased Implementation Roadmap

1. **Phase 1 – Foundation & Auth**
   - Laravel project setup, `.env`, Breeze + Livewire.
   - Migrations for `users`, `vehicles`, `maintenance_records`, `maintenance_materials`, `notification_logs`, `password_reset_tokens`.
   - Basic layout and navigation.

2. **Phase 2 – Vehicle & Maintenance Core**
   - Vehicle CRUD and list UI.
   - Maintenance records + materials.
   - Dashboard summaries.

3. **Phase 3 – Notifications & Scheduling**
   - Queue + database driver locally.
   - Maintenance due checker command and scheduled job.
   - Email and SMS notifications + log.

4. **Phase 4 – Calendar, Trip Tickets, and UX polish**
   - Calendar module.
   - Trip ticket module.
   - Advanced filtering, exports (PDF, Excel), and responsive layout.

5. **Phase 5 – Data Migration & Go-Live**
   - Firestore export/import.
   - Production deployment to DigitalOcean.
   - Monitoring, logging, and optimization.
