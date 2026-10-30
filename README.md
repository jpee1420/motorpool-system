## Quick Start

1. **Clone the repository**
   ```bash
   git clone [your-repo-url] motorpool-system
   cd motorpool-system
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set up database**
   - Create a MySQL database
   - Update `.env` with your database credentials:
     ```env
     DB_DATABASE=motorpool
     DB_USERNAME=your_username
     DB_PASSWORD=your_password
     ```

5. **Run migrations & seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Build assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

7. **Start the server**
   ```bash
   php artisan serve
   ```

## Key Features

- Vehicle management with maintenance tracking
- Trip ticket system
- Email notifications
- Role-based access control (Admin/Staff)

## Environment Variables

| Key               | Description       | Default |
|------------------|-------------------|---------|
| MOTORPOOL_LAYOUT | UI layout mode    | sidebar |
| MAIL_MAILER      | Mail driver       | smtp    |
| MAIL_HOST        | Mail server       | 127.0.0.1 |
| MAIL_PORT        | Mail port         | 1025 (Mailpit) |

## Seeded Users

- **Admin:** `admin@example.com` / `password`
- **Staff:** `staff@example.com` / `password`

## Testing

```bash
php artisan test
```

