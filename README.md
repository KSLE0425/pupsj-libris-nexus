<div align="center">

# PUPSJ Libris Nexus

**A Digital Library Management and Inventory System for the PUP San Juan Campus Library**

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/License-Academic-blue?style=for-the-badge)](#license)

Built with Laravel 12 · Filament 5 · Tailwind CSS 4 · MySQL/MariaDB

</div>

---

## 📋 Table of Contents

- [Requirements](#-requirements)
- [Setup Instructions](#-setup-instructions)
- [Default Accounts](#-default-accounts)
- [Access Points](#-access-points)
- [Common Issues & Fixes](#-common-issues--fixes)
- [Useful Artisan Commands](#-useful-artisan-commands)
- [Tech Stack](#-tech-stack)
- [Project Team](#-project-team)
- [License](#-license)

---

## 🛠 Requirements

Make sure the following are installed before proceeding:

| Tool | Minimum Version | Notes |
|------|----------------|-------|
| **PHP** | 8.2+ | Enable `intl`, `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip` |
| **Composer** | Latest | https://getcomposer.org |
| **MySQL / MariaDB** | MySQL 8.0 / MariaDB 10.4 | |
| **XAMPP** | Latest | Required — provides Apache, MySQL, and PHP |
| **Git** | Latest | |

---

## 🚀 Setup Instructions

### Step 1 — Install XAMPP

Download and install **XAMPP** from https://www.apachefriends.org

After installation, start **Apache** and **MySQL** from the XAMPP Control Panel.

> The project must be placed inside the `htdocs` folder, which is usually located at:
> ```
> C:\xampp\htdocs
> ```

---

### Step 2 — Clone the Project Inside `htdocs`

Open a terminal (PowerShell or Git Bash) and navigate to your XAMPP `htdocs` folder:

```bash
cd C:\xampp\htdocs
```

Then clone the repository:

```bash
git clone <repository-url> library-api
cd library-api
```

Or extract the provided ZIP directly into `C:\xampp\htdocs\library-api`.

> ⚠️ **Important:** The project must live inside `htdocs` so XAMPP can serve it locally.

---

### Step 3 — Install PHP Dependencies

Inside the project folder, run:

```bash
composer install
```

---

### Step 4 — Create the `.env` File

Duplicate `.env.example` and rename the copy to `.env`:

**Windows (PowerShell):**
```powershell
Copy-Item .env.example .env
```

**Command Prompt (CMD):**
```cmd
copy .env.example .env
```

> **Tip:** You can also right-click `.env.example` → **Copy**, then **Paste**, then rename it to `.env`.

---

### Step 5 — Generate the Application Key

```bash
php artisan key:generate
```

---

### Step 6 — Create the Database

Open **phpMyAdmin** at http://localhost/phpmyadmin and create a new database named **`library_db`**:

```sql
CREATE DATABASE library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then update these values in your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=library_db
DB_USERNAME=root
DB_PASSWORD=
```

> XAMPP defaults: user `root`, empty password.

---

### Step 7 — Configure Basic `.env` Values

At minimum, verify these values in `.env`:

```env
APP_NAME="PUPSJ Libris Nexus"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=library_db
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
```

> **Note:** Mail configuration (SMTP) is optional for local development. Leave the default values unless you need to test email features.

---

### Step 8 — Run Database Migrations

```bash
php artisan migrate
```

This creates all required tables.

---

### Step 9 — Seed the Initial Data

```bash
php artisan db:seed
```

This runs the `PupsjAccountsSeeder` which creates:

- **1 Admin account**
- **1 Test Student account**
- **1 Test Faculty account**

> **Credentials:** The default emails and passwords are defined inside the seeder file at:
> ```
> database/seeders/PupsjAccountsSeeder.php
> ```
> Open that file to see the exact email and password. Change them after first login.

---

### Step 10 — Create the Storage Link

```bash
php artisan storage:link
```

This allows uploaded images (book covers, TOCs, COR files) to be served correctly.

---

### Step 11 — Start the Server

Open a new terminal inside the project folder and run:

```bash
php artisan serve
```

The system will be available at:

```
http://127.0.0.1:8000
```

---

## 🔑 Default Accounts

Initial accounts are created by the seeder in `database/seeders/PupsjAccountsSeeder.php`:

| Role | Purpose | Credentials |
|------|---------|-------------|
| **Admin** | Full access to the librarian/admin panel | See seeder file |
| **Test Student** | Used for "View as Student" impersonation | See seeder file |
| **Test Faculty** | Used for "View as Faculty" impersonation | See seeder file |

> ⚠️ **Important:** Change all default passwords immediately after first login, especially before deploying to production.

---

## 🌐 Access Points

| URL | Purpose |
|-----|---------|
| `http://127.0.0.1:8000` | Landing page / Guest catalog |
| `http://127.0.0.1:8000/login` | Student / Faculty login |
| `http://127.0.0.1:8000/admin` | Admin / Librarian panel |
| `http://127.0.0.1:8000/kiosk` | Kiosk self-service mode |

---

## 🐛 Common Issues & Fixes

### `php artisan migrate` fails with "Connection refused"
- Ensure MySQL is running from the XAMPP Control Panel
- Verify `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` in `.env`
- XAMPP defaults: user `root`, empty password

### `php artisan storage:link` fails
Delete the existing `public/storage` folder (if any), then re-run:
```bash
php artisan storage:link
```

### "419 Page Expired" error
Clear cache and try again:
```bash
php artisan cache:clear
php artisan config:clear
```

### Session timeouts / auto-logout
- Confirm `SESSION_DRIVER=database`
- Verify the `sessions` table exists (run `php artisan migrate`)

### QR scanner not working
- Use latest Chrome or Edge
- Allow camera access when prompted
- Ensure the device camera is not in use by another app

### Permission errors on macOS/Linux
```bash
chmod -R 775 storage bootstrap/cache
```

---

## ⚙️ Useful Artisan Commands

| Command | Description |
|---------|-------------|
| `php artisan migrate` | Run database migrations |
| `php artisan migrate:fresh --seed` | Reset database and re-seed |
| `php artisan db:seed` | Run seeders |
| `php artisan config:clear` | Clear cached config |
| `php artisan cache:clear` | Clear application cache |
| `php artisan route:list` | List all routes |
| `php artisan storage:link` | Create the storage symlink |
| `php artisan tinker` | Open an interactive REPL |

---

## 🧰 Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 12, PHP 8.2 |
| **Admin Panel** | Filament 5 |
| **Frontend** | Tailwind CSS 4 |
| **Database** | MySQL 8.0 / MariaDB 10.4 |
| **Authentication** | Laravel Sanctum |
| **PDF Generation** | Laravel DomPDF |
| **QR Code** | Simple QR Code |
| **AI Integration** | OpenAI PHP for Laravel |
| **Testing** | PHPUnit, Playwright |

---

## 👥 Project Team

**Developers:**
- Virgilio II Alvarez
- Marc Genesis L. Regis
- Giovanni Saavedra
- Alexis D. Ustare
- Trunkszisqa Mae O. Tamalla

**Adviser:** Elias A. Austria

Bachelor of Science in Information Technology  
Polytechnic University of the Philippines – San Juan City Campus  
Academic Year 2026–2027

---

## 📄 License

This project was developed as a partial fulfillment of the requirements for the degree of **Bachelor of Science in Information Technology** at the Polytechnic University of the Philippines – San Juan City Campus.

---

<div align="center">

**PUPSJ Libris Nexus** · Faster Access. Easier Management. Better Service.

</div>
