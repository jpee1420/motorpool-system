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

## Quick Start (Beginner-Friendly Local Setup)

Welcome! If you are new to software development, don't worry. This guide will walk you through setting up the project on your computer step-by-step.

### Step 1: Install Required Tools

You will need to install a few tools to run the project.

1. **Laragon**: This is a magical tool that includes almost everything you need (PHP, MySQL Database, Composer, and a terminal).
   - Download **Laragon Full** from [laragon.org/download](https://laragon.org/download/) and install it.
   - Once installed, open Laragon. Click the **gear icon** (top right) to open Preferences. Go to the **Services & Ports** tab and make sure the following toggles are enabled: **Apache**, **MySQL**, and **Mail Catcher** (for testing emails).
   - After confirming, click **Start All** on the main Laragon window.
2. **Node.js**: Needed for the frontend (user interface) parts of the app.
   - Download the **LTS (Long Term Support)** version from [nodejs.org](https://nodejs.org/) and install it.
3. **Git**: Needed to download the project code.
   - Download from [git-scm.com/downloads](https://git-scm.com/downloads) and install it with default settings.

### Step 2: Download the Project

1. Click the **Terminal** button inside Laragon to open your command line.
2. Type the following command and press Enter (replace `<your-repo-url>` with the actual link):
   ```bash
   git clone <your-repo-url> motorpool-system
   ```
3. Move into the project folder you just downloaded:
   ```bash
   cd motorpool-system
   ```

### Step 3: Install Project Dependencies

While still inside the `motorpool-system` folder in your terminal, run these two commands to install the necessary background files:

1. Install PHP dependencies:
   ```bash
   composer install
   ```
2. Install Javascript dependencies:
   ```bash
   npm install
   ```

### Step 4: Configure the Project

1. **Create the settings file**: The project needs a settings file. We provide an example one. Copy it by running:
   ```bash
   cp .env.example .env
   ```
2. **Generate a security key**:
   ```bash
   php artisan key:generate
   ```
3. **Set up the Database**:
   - Click the **Database** button in Laragon to open HeidiSQL. 
   - Create a new database and name it `motorpool_db`.
4. **Prepare the database tables**:
   ```bash
   php artisan migrate
   ```

### Step 5: Start the Application!

To run the project, you just need a single terminal window. Make sure you are inside the `motorpool-system` folder and run:

```bash
composer run dev
```

This single command automatically starts the backend server, the frontend interface, and the background task queue all at once!

### Step 6: View the App

Open your web browser (Chrome, Edge, Safari, etc.) and go to:
**[http://localhost:8000](http://localhost:8000)**

Congratulations! You should now see the Motorpool System running on your computer.

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

## Security

This project implements several security measures:

- **Authorization Policies** – `VehiclePolicy`, `MaintenanceRecordPolicy`, `TripTicketPolicy`, `UserPolicy`
- **Role-based Access** – `admin`, `staff`, `user` roles with appropriate permissions
- **Livewire Security** – `#[Locked]` attributes on sensitive IDs, server-side authorization
- **Input Validation** – All Livewire components validate input before processing
- **File Upload Protection** – Strict MIME type validation, random filenames, secure storage
- **XSS Prevention** – All Blade output is escaped by default


