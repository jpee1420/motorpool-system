# Motorpool System

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **UI**: Blade + Livewire 3 + Alpine.js 3
- **Styling**: Tailwind CSS 3 + daisyUI
- **Database**: MySQL 8
- **Auth**: Laravel Breeze (Livewire stack)
- **Queues**: `database` driver (local)
- **Mail**: SMTP via Mailpit in local development

---

## Key Features (Current)

- **Vehicles**
  - CRUD management, search, status filters
  - Tracks current odometer, last maintenance, and next due by date/odometer
  - CSV export of vehicle list

- **Maintenance**
  - Maintenance records linked to vehicles
  - Materials/parts and cost breakdown
  - Automatic updates of vehicle odometer and next-due fields
  - CSV export of maintenance records

- **Notifications**
  - Email notifications when maintenance is due (date and/or odometer)
  - Logged to `notification_logs` with status and error messages
  - Retry failed notifications from the UI

- **Trip Tickets & Calendar**
  - Trip tickets for vehicle usage (CRUD, per-vehicle)
  - Calendar view showing maintenance events, due items, and trips

- **Dashboard & UX**
  - Dashboard summaries for vehicles and maintenance
  - Livewire-based modals, validation, and loading states

---

## Quick Start (Local Development)

### 1. Clone and install dependencies

```bash
git clone <your-repo-url> motorpool-system
cd motorpool-system

composer install
npm install
```

### 2. Configure environment

Copy the example environment and generate an app key:

```bash
cp .env.example .env
php artisan key:generate
```


### 3. Database and migrations

Create the database (`motorpool_db`) in MySQL, then run:

```bash
php artisan migrate
```

If the queue tables are not present yet:

```bash
php artisan queue:table
php artisan migrate
```

### 4. Frontend assets

For development (recommended while coding):

```bash
npm run dev
```

For a production-style build:

```bash
npm run build
```

### 5. Running the application

If you are not using Laragon’s virtual host, you can use:

```bash
php artisan serve
```

Then open the app at the URL shown by `php artisan serve` or your configured `APP_URL` (e.g., `http://motorpool-system.test`).

### 6. Queues and notifications (local)

To process queued jobs (maintenance notifications, etc.):

```bash
php artisan queue:work
```

Start Mailpit (via Laragon or manually) and open:

- Mailpit UI: `http://localhost:1137`

All outgoing emails (including maintenance notifications) will appear there in local development.

---

## Environment Variables

Some notable environment variables:

| Key               | Description            | Example / Default         |
|-------------------|------------------------|----------------------------|
| APP_URL           | Application URL        | `http://motorpool-system.test` |
| MOTORPOOL_LAYOUT  | UI layout mode         | `sidebar`                  |
| MAIL_MAILER       | Mail driver            | `smtp`                     |
| MAIL_HOST         | Mail server            | `127.0.0.1`                |
| MAIL_PORT         | Mail port (Mailpit)    | `1025`                     |
| QUEUE_CONNECTION  | Queue driver           | `database`                 |

---

## Testing

Run the automated test suite:

```bash
php artisan test
```

---

## Further Documentation

For a deeper look at modules, database schema, and deployment strategy, see:

- `MOTORPOOL_PROJECT_PLAN.md`

