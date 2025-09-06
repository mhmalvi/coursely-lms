# 🚀 Quick Setup: Coursely LMS Database Migration

## Step 1: Execute Database Migration in Supabase

### 1. **Go to Supabase Dashboard**
https://supabase.com/dashboard/project/ltzsuubjuvpufkbvxtyg

### 2. **Navigate to SQL Editor**
- Click "SQL Editor" in the left sidebar
- Click "New Query"

### 3. **Copy & Execute This SQL** 
Copy the entire content below and paste it into the SQL editor, then click "Run":

```sql
-- Coursely LMS Database Migration for Supabase
-- Enable required extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Users table (core authentication)
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(128),
    role_name VARCHAR(64) NOT NULL,
    role_id INTEGER NOT NULL,
    avatar VARCHAR(64),
    mobile VARCHAR(255) UNIQUE,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    google_id VARCHAR(255),
    facebook_id VARCHAR(255),
    remember_token VARCHAR(100),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'pending', 'inactive')),
    currency VARCHAR(3) DEFAULT 'USD',
    timezone VARCHAR(50) DEFAULT 'UTC',
    stripe_customer_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Categories table (course categories)
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    title VARCHAR(64) NOT NULL,
    icon VARCHAR(255),
    parent_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    order_number INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Webinars table (courses/webinars)
CREATE TABLE IF NOT EXISTS webinars (
    id SERIAL PRIMARY KEY,
    teacher_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    creator_user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    title VARCHAR(64) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    start_date TIMESTAMP,
    end_date TIMESTAMP,
    image_cover VARCHAR(255),
    video_demo VARCHAR(255),
    capacity INTEGER DEFAULT 0,
    price DECIMAL(15,3) DEFAULT 0,
    description TEXT,
    short_description TEXT,
    support BOOLEAN DEFAULT FALSE,
    partner_instructor BOOLEAN DEFAULT FALSE,
    subscribe BOOLEAN DEFAULT FALSE,
    certificate BOOLEAN DEFAULT FALSE,
    private BOOLEAN DEFAULT FALSE,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('active', 'pending', 'is_draft', 'inactive')),
    type VARCHAR(20) DEFAULT 'webinar' CHECK (type IN ('webinar', 'course', 'text_lesson')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Sales table (orders/purchases)
CREATE TABLE IF NOT EXISTS sales (
    id SERIAL PRIMARY KEY,
    buyer_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    seller_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    webinar_id INTEGER REFERENCES webinars(id) ON DELETE CASCADE,
    type VARCHAR(50) DEFAULT 'webinar',
    payment_method VARCHAR(50),
    amount DECIMAL(15,3) NOT NULL,
    tax DECIMAL(15,3) DEFAULT 0,
    commission DECIMAL(15,3) DEFAULT 0,
    discount DECIMAL(15,3) DEFAULT 0,
    total_amount DECIMAL(15,3) NOT NULL,
    reference_id VARCHAR(255),
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'success', 'canceled', 'refunded')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Payments table (Stripe integration)
CREATE TABLE IF NOT EXISTS payments (
    id SERIAL PRIMARY KEY,
    sale_id INTEGER NOT NULL REFERENCES sales(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    gateway VARCHAR(50) NOT NULL DEFAULT 'stripe',
    gateway_payment_id VARCHAR(255),
    gateway_transaction_id VARCHAR(255),
    amount DECIMAL(15,3) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'success', 'failed', 'canceled', 'refunded')),
    data JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Files table (course content)
CREATE TABLE IF NOT EXISTS files (
    id SERIAL PRIMARY KEY,
    title VARCHAR(64) NOT NULL,
    accessibility VARCHAR(20) DEFAULT 'free' CHECK (accessibility IN ('free', 'paid')),
    downloadable BOOLEAN DEFAULT FALSE,
    storage VARCHAR(20) DEFAULT 'supabase' CHECK (storage IN ('local', 's3', 'supabase')),
    file_path VARCHAR(1000),
    file_type VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Comments table (course reviews)
CREATE TABLE IF NOT EXISTS comments (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    webinar_id INTEGER REFERENCES webinars(id) ON DELETE CASCADE,
    reply_id INTEGER REFERENCES comments(id) ON DELETE CASCADE,
    comment TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'pending')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Webinar reviews table
CREATE TABLE IF NOT EXISTS webinar_reviews (
    id SERIAL PRIMARY KEY,
    webinar_id INTEGER NOT NULL REFERENCES webinars(id) ON DELETE CASCADE,
    creator_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content_quality INTEGER CHECK (content_quality BETWEEN 1 AND 5),
    instructor_skills INTEGER CHECK (instructor_skills BETWEEN 1 AND 5),
    purchase_worth INTEGER CHECK (purchase_worth BETWEEN 1 AND 5),
    support_quality INTEGER CHECK (support_quality BETWEEN 1 AND 5),
    rates DECIMAL(3,2) DEFAULT 0,
    description TEXT,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'active')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_webinars_status ON webinars(status);
CREATE INDEX IF NOT EXISTS idx_sales_buyer ON sales(buyer_id);
CREATE INDEX IF NOT EXISTS idx_sales_status ON sales(status);
CREATE INDEX IF NOT EXISTS idx_payments_sale ON payments(sale_id);

-- Enable Row Level Security
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE webinars ENABLE ROW LEVEL SECURITY;
ALTER TABLE sales ENABLE ROW LEVEL SECURITY;
ALTER TABLE payments ENABLE ROW LEVEL SECURITY;

-- Basic RLS policies
CREATE POLICY "Users can view their own data" ON users
    FOR SELECT USING (auth.uid()::text = id::text);

CREATE POLICY "Public webinars are viewable" ON webinars
    FOR SELECT USING (status = 'active');

-- Insert default data
INSERT INTO categories (title, order_number) VALUES
    ('Programming', 1),
    ('Design', 2), 
    ('Marketing', 3),
    ('Business', 4),
    ('Personal Development', 5)
ON CONFLICT DO NOTHING;

-- Create a test admin user (password: 'password123')
INSERT INTO users (full_name, role_name, role_id, email, password, status) VALUES
    ('Admin User', 'admin', 1, 'admin@coursely.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active')
ON CONFLICT (email) DO NOTHING;

-- Create a sample course
INSERT INTO webinars (teacher_id, creator_user_id, category_id, title, slug, price, description, status, type) 
SELECT 
    1, 1, 1, 
    'Introduction to Web Development', 
    'intro-web-development', 
    99.99, 
    'Learn the basics of HTML, CSS, and JavaScript', 
    'active', 
    'course'
WHERE NOT EXISTS (SELECT 1 FROM webinars WHERE slug = 'intro-web-development');
```

### 4. **Verify Tables Created**
After running the SQL, you should see:
- ✅ Tables created successfully
- ✅ Indexes created  
- ✅ Sample data inserted

## Step 2: Configure Vercel Environment Variables

Run these commands in your terminal:

```bash
# Set Supabase credentials
vercel env add SUPABASE_URL
# Enter: https://ltzsuubjuvpufkbvxtyg.supabase.co

vercel env add SUPABASE_ANON_KEY  
# Enter: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imx0enN1dWJqdXZwdWZrYnZ4dHlnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTcxMzYwNTAsImV4cCI6MjA3MjcxMjA1MH0.nAs6A9Jl9kYD5vlL3rrcCvTI_zQrBoUU1JMxrVp2jtQ

vercel env add SUPABASE_SERVICE_KEY
# Enter: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imx0enN1dWJqdXZwdWZrYnZ4dHlnIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1NzEzNjA1MCwiZXhwIjoyMDcyNzEyMDUwfQ.Ce-IhNfMJUHmc7aki9TiGoceDEMq_LC22MPIeKPBYlk

# Add placeholder Stripe keys (get real ones from Stripe dashboard)
vercel env add STRIPE_PUBLISHABLE_KEY
# Enter: pk_test_placeholder

vercel env add STRIPE_SECRET_KEY  
# Enter: sk_test_placeholder

vercel env add STRIPE_WEBHOOK_SECRET
# Enter: whsec_placeholder

vercel env add APP_URL
# Enter: https://coursely-lms.vercel.app (or your domain)
```

## Step 3: Deploy to Vercel

```bash
# Commit the changes
git add -A
git commit -m "Add Stripe + Supabase integration for serverless deployment"
git push origin master

# Deploy to production
vercel --prod --archive=tgz
```

## Step 4: Set up Stripe (After Deployment)

1. **Create Stripe Account**: https://dashboard.stripe.com/register
2. **Get API Keys**: Dashboard → Developers → API keys
3. **Update Vercel env vars** with real Stripe keys
4. **Create Webhook**: Dashboard → Webhooks → Add endpoint
   - URL: `https://your-vercel-url.vercel.app/api/webhooks/stripe`
   - Events: Select `payment_intent.succeeded`, `payment_intent.payment_failed`

## 🎯 Test Your Setup

1. **Database**: Check tables at https://supabase.com/dashboard/project/ltzsuubjuvpufkbvxtyg/editor
2. **API**: Test at `https://your-vercel-url.vercel.app/api/payment/courses`
3. **Payments**: Use Stripe test cards for transactions

Your Coursely LMS is ready for modern serverless deployment! 🚀