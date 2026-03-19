# Coursely LMS

> Full-featured Learning Management System — course creation, live classrooms, assessments, certifications, and multi-gateway payments with support for 30+ payment providers.

![Laravel](https://img.shields.io/badge/Laravel-7-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4-777BB4?logo=php&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-2-4FC08D?logo=vue.js&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Containerized-2496ED?logo=docker&logoColor=white)
![Stripe](https://img.shields.io/badge/Stripe-Payments-635BFF?logo=stripe&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-4-7952B3?logo=bootstrap&logoColor=white)

---

## Features

### Course Management
- **Course Builder** — Create courses with chapters, text lessons, video content, and file attachments
- **Course Bundles** — Package multiple courses into discounted bundles
- **Upcoming Courses** — Pre-launch pages with follower notifications and waitlists
- **Featured Courses** — Curated featured content with promotional special offers

### Live Learning
- **Live Classrooms** — Real-time sessions via BigBlueButton and Zoom integrations
- **Agora Video** — WebRTC-powered video conferencing with session history
- **Google Calendar Sync** — Schedule sync for live sessions

### Assessments & Certification
- **Quiz Engine** — Multiple question types with configurable passing scores
- **Assignments** — Submission and grading workflow with file attachments and messaging
- **Certificates** — Customizable certificate templates with public validation URLs
- **Badges** — Achievement badges awarded on course milestones

### User Management
- **Multi-Role System** — Admin, instructor, and student roles with panel-specific dashboards
- **Instructor Applications** — Self-service "Become an Instructor" workflow with admin approval
- **Social Authentication** — OAuth login via Laravel Socialite
- **Affiliate Program** — Referral tracking with affiliate codes and commission payouts

### Payments & Commerce
- **30+ Payment Gateways** — Stripe, PayPal, Razorpay, Braintree, Mollie, Klarna, iyzico, M-Pesa, JazzCash, Paytm, Paystack, MercadoPago, and more
- **Installment Plans** — Flexible payment installments with reminders
- **Shopping Cart** — Multi-item cart with discount codes and gift purchases
- **Accounting System** — Revenue tracking, payouts, and financial reporting
- **Subscription Plans** — Recurring subscription packages with usage tracking

### Content & Community
- **Blog System** — CMS with categories for content marketing
- **Course Forums** — Discussion boards with Q&A per course
- **Comments & Reviews** — Student reviews and comment moderation with reporting
- **Support Tickets** — Helpdesk with departments and threaded conversations

### Platform
- **Multi-Language** — Full i18n support via Laravel Translatable (including RTL/Arabic)
- **Advertising System** — Banner and modal ad placements
- **SEO-Friendly Slugs** — Eloquent Sluggable for clean URLs
- **File Management** — Laravel File Manager with S3/MinIO storage support
- **Docker Deployment** — Production-ready Dockerfile and docker-compose configuration
- **REST API** — Authenticated API with routes for guests, users, instructors, and auth

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 7, PHP 7.4 |
| Frontend | Vue.js 2, Bootstrap 4, jQuery, Laravel Mix / Webpack |
| Database | MySQL |
| Video | BigBlueButton, Zoom API, Agora RTC/RTM SDK |
| Payments | Stripe, PayPal, Razorpay, Braintree, Mollie, + 25 more |
| Storage | Local, AWS S3, MinIO |
| Auth | Laravel Auth, JWT (tymon/jwt-auth), Laravel Socialite |
| Deployment | Docker, Render, Vercel (serverless) |
| Testing | PHPUnit |

## Getting Started

### Prerequisites

- PHP 7.4+
- Composer
- Node.js 14+
- MySQL 5.7+

### Installation

```bash
# Clone the repository
git clone https://github.com/mhmalvi/coursely-lms.git
cd coursely-lms

# Install PHP dependencies
composer install

# Install frontend dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database in .env, then run migrations
php artisan migrate --seed

# Build frontend assets
npm run dev

# Start development server
php artisan serve
```

### Docker Deployment

```bash
# Build and start containers
docker-compose up -d

# Run migrations inside the container
docker exec -it coursely-app php artisan migrate --seed
```

## Project Structure

```
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/        # Admin panel controllers
│   │   ├── Panel/        # Instructor panel controllers
│   │   ├── Web/          # Public-facing controllers
│   │   └── Api/          # REST API controllers
│   ├── Models/           # 169 Eloquent models
│   └── ...
├── routes/
│   ├── web.php           # Public routes
│   ├── admin.php         # Admin panel routes
│   ├── panel.php         # Instructor panel routes
│   └── api/              # API routes (auth, guest, user, instructor)
├── resources/
│   └── views/            # Blade templates (admin, web, vendor)
├── database/
│   └── migrations/       # Schema migrations
├── api/                  # API entry point and webhooks
├── config/               # Laravel configuration
├── docker/               # Docker configuration files
├── Dockerfile            # Production container definition
└── docker-compose.yml    # Multi-service orchestration
```

## API Routes

The REST API is organized by role:

- `api/auth` — Registration, login, JWT token management
- `api/guest` — Public endpoints (course listing, categories)
- `api/user` — Student actions (enrollment, purchases, progress)
- `api/instructor` — Course management, content uploads, analytics

## License

MIT License. See [composer.json](composer.json) for details.
