# 🍽️ Drestro — Smart Restaurant Management & POS Ecosystem

<div align="center">

![Next.js](https://img.shields.io/badge/Next.js_14-black?style=for-the-badge&logo=next.js&logoColor=white)
![TypeScript](https://img.shields.io/badge/TypeScript-007ACC?style=for-the-badge&logo=typescript&logoColor=white)
![Prisma](https://img.shields.io/badge/Prisma-2D3748?style=for-the-badge&logo=prisma&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel_11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire_3-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)

**An all-in-one restaurant automation platform featuring multi-tenant SaaS management, online subscription billing, and real-time offline-capable Point of Sale (POS) terminals.**

[Live Portal](https://drestro.com) • [POS Terminal](https://portal.drestro.com) • [Documentation](https://drestro.com/faq)

---

</div>

## 🌟 Overview

**Drestro** is an enterprise-ready restaurant ecosystem designed to streamline food & beverage operations from dining tables and kitchen displays to cloud accounting and government e-billing.

The repository is organized into two primary applications:

1. **`drestro-web` (Root Next.js Application)**:
   - **Public Landing & Marketing Site**: Dynamic product showcases, pricing tiers, combo deals, hardware shop, and contact portals.
   - **SaaS Master Admin Panel**: Restaurant lifecycle management, subscription plans, client logos, dynamic ticketing, revenue analytics, and automated onboarding.
   - **Nepal E-Billing & Payment Gateways**: Integrated IRD-compliant Nepal e-billing API, automated QR invoices, Khalti / eSewa / Fonepay verification.
   - **Central License & SSO Verification**: Issues cryptographically bound license keys with machine-ID locking for local POS terminals.

2. **`DrestroPOS` (Laravel 11 + Livewire 3 Subsystem)**:
   - **Cashier & Billing Terminal**: High-speed offline-first POS billing with thermal network (ESC/POS) printing and browser print support.
   - **Kitchen Display System (KDS)**: Real-time ticket management for kitchen chefs with instant status syncing.
   - **Waiter & Mobile Ordering**: Mobile-responsive waiter ordering interface with table selection and kitchen dispatch.
   - **Bar Display & Hotel Room Service**: Dedicated modules for hotel room orders and bar counters.
   - **Table & Menu Management**: Visual table layouts, category assignments, item variations, and inventory alerts.
   - **License Manager**: Dynamic quota tracker for tables, staff accounts, menu items, and monthly invoices.

---

## 🏗️ Architecture & Project Structure

```
drestro/
├── src/                          # Next.js 14 App Router Source
│   ├── app/                      # Page routes & API endpoints
│   │   ├── (public)/             # Landing, Pricing, Products, Checkout
│   │   ├── admin/                # Master SaaS Admin Dashboard
│   │   ├── api/                  # REST endpoints (License, Auth, Billing)
│   │   └── dashboard/            # Tenant Client Billing Portal
│   ├── components/               # Reusable React UI Components
│   ├── context/                  # Client contexts (Cart, Site Settings)
│   └── lib/                      # Database client (Prisma), Auth, SMS, E-Billing
├── prisma/                       # Prisma Schema & Database Migrations
├── public/                       # Static public assets, icons, downloads
│
└── DrestroPOS/                   # Laravel 11 + Livewire POS Terminal
    ├── app/                      # Controllers, Livewire Components, Models
    │   ├── Http/Middleware/      # Tenant & License check middlewares
    │   ├── Livewire/Admin/       # POS Admin dashboards, Settings, License
    │   ├── Livewire/Staff/       # Cashier Panel, KDS, Waiter, Bar
    │   └── Services/             # Thermal Printer & License verification
    ├── database/                 # Migrations & SQLite database
    ├── resources/views/          # Blade templates & layout components
    └── routes/                   # Web & Staff route definitions
```

---

## 🚀 Quick Start

### 1. Next.js SaaS Web App (`drestro-web`)

```bash
# Install dependencies
npm install

# Setup environment variables
cp .env.example .env

# Generate Prisma Client & Sync Database
npx prisma generate
npx prisma db push

# Start development server
npm run dev
```

Visit `http://localhost:3000` in your browser.

---

### 2. Laravel POS Terminal (`DrestroPOS`)

```bash
cd DrestroPOS

# Install PHP dependencies
composer install

# Install frontend dependencies & build
npm install
npm run build

# Setup environment file
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Start Laravel development server
php artisan serve
```

Visit `http://localhost:8000` for POS administration and cashier terminals.

---

## 🔒 License Verification & Security

- POS instances communicate with `https://drestro.com/api/license/verify` to validate subscription status and feature quotas.
- Automatic offline grace period allows restaurants to continue billing smoothly during internet disruptions.
- Machine ID hashing prevents unauthorized license duplication across multiple unauthorized computers.

---

## 📦 Deployment

### Production Server (Deploy via Git)

```bash
# 1. Pull latest master branch from GitHub
git pull origin master

# 2. Update Next.js SaaS portal
npm install --production
npm run build
pm2 restart drestro

# 3. Update Laravel POS subsystem
cd DrestroPOS
php artisan optimize:clear
php artisan view:clear
```

---

## 📄 License

Proprietary Software — © **Drestro Inc.** All rights reserved.
