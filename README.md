# Motorpool Management System

A web application for managing a vehicle fleet and its day-to-day operations. It brings vehicle information, maintenance and repair history, trip planning, notifications, and user workflows together in one place.

## Features

### Fleet and vehicle management

- Maintain vehicle and equipment records, including make, model, plate number, status, odometer, and photos.
- Search, sort, and filter the fleet.
- Assign vehicles to users and track each vehicle's maintenance schedule.
- Export vehicle records to CSV or Excel.

### Maintenance tracking

- Create maintenance records linked to vehicles, including service dates, odometer readings, checklist results, materials, quantities, and costs.
- Keep a history of work performed and the people involved.
- Track the next maintenance due date and odometer reading.
- Export maintenance records to CSV or Excel.

### Repair tracking

- Record vehicle repairs, odometer readings, materials, and costs.
- Review repair history and individual repair details.
- Export repair records to CSV or Excel.

### Trip tickets and calendar

- Create and manage trip tickets for vehicle use.
- Browse scheduled trips and fleet maintenance events in a calendar, with month navigation.
- View trip ticket details and relevant vehicle information.

### Maintenance notifications

- Generate email reminders for maintenance coming due by date or odometer.
- Review notification history and delivery status.
- Retry failed notifications, archive entries, and export notification records.
- View unread notifications from the in-app notification menu.

### Dashboard and activity

- See fleet totals, maintenance counts, and upcoming or overdue maintenance on the dashboard.
- Review recent maintenance and, where available, recent notification activity.
- Browse activity history with search and filters.

### Accounts and approvals

- Support administrator, staff, and general user roles with role-appropriate access to system areas.
- Let users register and track account approval status.
- Manage users, roles, and account status.
- Review and track approval requests and their outcomes.

## Technology

- **Backend:** Laravel 12, PHP 8.2+
- **UI:** Blade and Livewire 3
- **Frontend assets:** Tailwind CSS 3 and Vite
- **Database:** SQLite by default; MySQL and MariaDB are also supported through Laravel configuration
- **Queue:** Database queue by default
- **Mail:** Laravel mail configuration; local defaults write mail to the log

## Local development

### Requirements

- PHP 8.2 or newer with the required Laravel extensions
- Composer
- Node.js and npm
- SQLite, MySQL, or MariaDB

### Setup

1. Clone the repository and enter the project directory:

   ```bash
   git clone <repository-url> motorpool-system
   cd motorpool-system
   ```

2. Install dependencies:

   ```bash
   composer install
   npm install
   ```

3. Create and configure the environment file:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   The example configuration uses SQLite. To use MySQL or MariaDB, update the `DB_*` values in `.env` and create the database before migrating.

4. Create the database tables and build frontend assets:

   ```bash
   php artisan migrate
   npm run build
   ```

5. Start the local application:

   ```bash
   composer run dev
   ```

   This starts the Laravel server, queue worker, and Vite development server. Open [http://localhost:8000](http://localhost:8000) in your browser.

### Maintenance notifications

The application defines scheduled maintenance-notification tasks. In an environment where scheduled reminders should run automatically, configure Laravel's scheduler according to the deployment platform. Mail delivery also requires a working mail transport configured through `.env`.

## Configuration

Common environment settings include:

| Variable | Purpose | Default |
| --- | --- | --- |
| `APP_URL` | Base URL used by the application | `http://localhost:8000` |
| `DB_CONNECTION` | Database connection | `sqlite` |
| `QUEUE_CONNECTION` | Queue backend | `database` |
| `MAIL_MAILER` | Mail transport | `log` |
| `MOTORPOOL_LAYOUT` | Navigation layout (`topnav` or `sidebar`) | `topnav` |
| `MOTORPOOL_MAINTENANCE_MONTHS_INTERVAL` | Default maintenance interval in months | `6` |
| `MOTORPOOL_MAINTENANCE_KM_INTERVAL` | Default maintenance interval in kilometers | `5000` |

See `.env.example` and the files in `config/` for the full set of application options.

## Tests

Run the automated test suite with:

```bash
php artisan test
```
