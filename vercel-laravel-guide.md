# Laravel on Vercel Deployment Guide (2024-2025)

## Why Our Current Deployment Failed

1. **Complex Dependencies**: Coursely LMS has 30+ payment gateway packages that aren't compatible with serverless
2. **File System**: Laravel needs persistent storage for sessions, cache, uploads
3. **Database**: No MySQL/PostgreSQL hosting on Vercel
4. **Background Jobs**: Queue workers need persistent processes

## Working Laravel on Vercel Setup

### Step 1: Create API Structure
```
api/
├── index.php          # Main entry point
├── [...slug].php      # Catch-all routes (optional)
└── auth/
    └── login.php      # Specific endpoints
```

### Step 2: Updated vercel.json
```json
{
  "version": 2,
  "functions": {
    "api/index.php": {
      "runtime": "vercel-php@0.7.4"
    },
    "api/[...slug].php": {
      "runtime": "vercel-php@0.7.4"
    }
  },
  "routes": [
    {
      "src": "/(css|js|images|fonts)/(.*)",
      "dest": "public/$1/$2"
    },
    {
      "src": "/api/(.*)",
      "dest": "api/index.php"
    },
    {
      "src": "/(.*)",
      "dest": "api/index.php"
    }
  ],
  "env": {
    "APP_ENV": "production",
    "APP_DEBUG": "false",
    "VIEW_COMPILED_PATH": "/tmp/views",
    "CACHE_DRIVER": "array",
    "SESSION_DRIVER": "cookie",
    "FILESYSTEM_DISK": "local",
    "LOG_CHANNEL": "errorlog"
  }
}
```

### Step 3: API Entry Point (api/index.php)
```php
<?php

// For Vercel serverless
$_SERVER['SCRIPT_NAME'] = '/api/index.php';

// Boot Laravel
require __DIR__ . '/../public/index.php';
```

### Step 4: Laravel Configuration Changes

**config/view.php:**
```php
'compiled' => env('VIEW_COMPILED_PATH', realpath(storage_path('framework/views'))),
```

**config/app.php:**
```php
'asset_url' => env('ASSET_URL', null),
```

## Current Limitations for Coursely LMS

❌ **Won't Work On Vercel:**
- Payment gateway webhooks (need persistent endpoints)
- File uploads (no persistent storage)
- Session-based authentication
- Background email sending
- Video streaming/processing
- Complex database operations

## Alternative Solutions

### 1. DigitalOcean App Platform ⭐ **RECOMMENDED**
```yaml
# .do/app.yaml
name: coursely-lms
services:
  - name: web
    source_dir: /
    github:
      repo: mhmalvi/coursely-lms
      branch: master
    run_command: "php artisan serve --host=0.0.0.0 --port=$PORT"
    environment_slug: php
    instance_count: 1
    instance_size_slug: basic-xxs
    routes:
      - path: /
databases:
  - name: coursely-db
    engine: MYSQL
    version: "8"
```

### 2. Railway.app
```toml
# railway.toml
[build]
builder = "NIXPACKS"

[deploy]
healthcheckPath = "/"
healthcheckTimeout = 100
restartPolicyType = "ON_FAILURE"

[[services]]
name = "web"
```

### 3. Render.com
```yaml
# render.yaml
services:
  - type: web
    name: coursely-lms
    runtime: php
    buildCommand: composer install --no-dev && php artisan config:cache
    startCommand: php artisan serve --host=0.0.0.0 --port=$PORT
```

### 4. Traditional VPS/Shared Hosting
- **Hostinger** - Laravel optimized hosting
- **SiteGround** - PHP 8.3 support
- **A2 Hosting** - High-performance PHP hosting

## Hybrid Approach: Frontend on Vercel + Backend Elsewhere

1. **Vercel**: Host static frontend/marketing pages
2. **DigitalOcean/Railway**: Host Laravel API
3. **Configure CORS** for cross-origin requests

## Cost Comparison

| Platform | Free Tier | Paid Plans | Laravel Support |
|----------|-----------|------------|-----------------|
| Vercel | ✅ Limited | $20/mo | ⚠️ Serverless only |
| DigitalOcean | ❌ None | $5/mo | ✅ Full support |
| Railway | ✅ $5 credit | $5/mo | ✅ Full support |
| Render | ✅ 750hrs/mo | $7/mo | ✅ Full support |

## Recommendation for Coursely LMS

**Best Solution:** Deploy to **DigitalOcean App Platform**
- Full Laravel support
- MySQL database included
- File storage capability
- Background job processing
- $5/month total cost
- Easy GitHub integration