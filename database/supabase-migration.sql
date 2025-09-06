-- Coursely LMS Database Migration for Supabase (PostgreSQL)
-- Project: ltzsuubjuvpufkbvxtyg
-- Migration from MySQL to PostgreSQL for Vercel deployment

-- Enable required extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- =============================================
-- Core Tables for Coursely LMS
-- =============================================

-- Users table (authentication and profiles)
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
    logged_count INTEGER DEFAULT 0,
    access_content BOOLEAN DEFAULT TRUE,
    create_store BOOLEAN DEFAULT FALSE,
    enable_registration_bonus BOOLEAN DEFAULT FALSE,
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
    url VARCHAR(255),
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
    video_demo_source VARCHAR(50),
    capacity INTEGER DEFAULT 0,
    price DECIMAL(15,3) DEFAULT 0,
    organization_price DECIMAL(15,3) DEFAULT 0,
    description TEXT,
    short_description TEXT,
    support BOOLEAN DEFAULT FALSE,
    partner_instructor BOOLEAN DEFAULT FALSE,
    subscribe BOOLEAN DEFAULT FALSE,
    forum BOOLEAN DEFAULT FALSE,
    certificate BOOLEAN DEFAULT FALSE,
    enable_waitlist BOOLEAN DEFAULT FALSE,
    private BOOLEAN DEFAULT FALSE,
    access_time INTEGER DEFAULT 0, -- days
    message_for_reviewer TEXT,
    type VARCHAR(20) DEFAULT 'webinar' CHECK (type IN ('webinar', 'course', 'text_lesson')),
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('active', 'pending', 'is_draft', 'inactive')),
    points INTEGER DEFAULT 0,
    timezone VARCHAR(50) DEFAULT 'UTC',
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
    bundle_id INTEGER NULL, -- for bundles
    product_id INTEGER NULL, -- for products
    type VARCHAR(50) DEFAULT 'webinar',
    payment_method VARCHAR(50),
    amount DECIMAL(15,3) NOT NULL,
    tax DECIMAL(15,3) DEFAULT 0,
    commission DECIMAL(15,3) DEFAULT 0,
    discount DECIMAL(15,3) DEFAULT 0,
    total_amount DECIMAL(15,3) NOT NULL,
    product_delivery_fee DECIMAL(15,3) DEFAULT 0,
    reference_id VARCHAR(255),
    manual_added BOOLEAN DEFAULT FALSE,
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

-- Payment channels table (Stripe configuration)
CREATE TABLE IF NOT EXISTS payment_channels (
    id SERIAL PRIMARY KEY,
    title VARCHAR(64) NOT NULL,
    class_name VARCHAR(64) NOT NULL,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    image VARCHAR(255),
    settings JSONB,
    currencies TEXT, -- JSON array as text
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Currencies table (multi-currency support)
CREATE TABLE IF NOT EXISTS currencies (
    id SERIAL PRIMARY KEY,
    currency VARCHAR(3) NOT NULL UNIQUE,
    currency_position VARCHAR(20) DEFAULT 'left',
    currency_separator VARCHAR(10) DEFAULT ',',
    currency_decimal VARCHAR(10) DEFAULT '.',
    exchange_rate DECIMAL(15,8) DEFAULT 1.0,
    order_number INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Files table (Supabase Storage integration)
CREATE TABLE IF NOT EXISTS files (
    id SERIAL PRIMARY KEY,
    title VARCHAR(64) NOT NULL,
    accessibility VARCHAR(20) DEFAULT 'free' CHECK (accessibility IN ('free', 'paid')),
    downloadable BOOLEAN DEFAULT FALSE,
    storage VARCHAR(20) DEFAULT 'supabase' CHECK (storage IN ('local', 's3', 'supabase')),
    file_path VARCHAR(1000),
    volume VARCHAR(64),
    file_type VARCHAR(64),
    interactive_type VARCHAR(64),
    interactive_file_name VARCHAR(255),
    online_viewer BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Webinar chapters (course structure)
CREATE TABLE IF NOT EXISTS webinar_chapters (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    webinar_id INTEGER NOT NULL REFERENCES webinars(id) ON DELETE CASCADE,
    title VARCHAR(64) NOT NULL,
    order_number INTEGER DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Webinar chapter items (lessons, quizzes, assignments)
CREATE TABLE IF NOT EXISTS webinar_chapter_items (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    chapter_id INTEGER NOT NULL REFERENCES webinar_chapters(id) ON DELETE CASCADE,
    item_id INTEGER NOT NULL, -- references files, sessions, quizzes, assignments
    type VARCHAR(20) NOT NULL CHECK (type IN ('file', 'session', 'quiz', 'assignment')),
    order_number INTEGER DEFAULT 0,
    check_previous_parts BOOLEAN DEFAULT FALSE,
    access_after_day INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Quizzes table
CREATE TABLE IF NOT EXISTS quizzes (
    id SERIAL PRIMARY KEY,
    webinar_id INTEGER REFERENCES webinars(id) ON DELETE CASCADE,
    creator_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(64) NOT NULL,
    time INTEGER DEFAULT 0, -- minutes
    attempt INTEGER DEFAULT 0,
    pass_mark INTEGER DEFAULT 50,
    certificate BOOLEAN DEFAULT FALSE,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    total_mark INTEGER DEFAULT 0,
    display_limited_questions BOOLEAN DEFAULT FALSE,
    display_number_of_questions INTEGER DEFAULT 10,
    display_questions_randomly BOOLEAN DEFAULT FALSE,
    expiry_days INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Sessions table (live sessions)
CREATE TABLE IF NOT EXISTS sessions (
    id SERIAL PRIMARY KEY,
    creator_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    webinar_id INTEGER NOT NULL REFERENCES webinars(id) ON DELETE CASCADE,
    title VARCHAR(64) NOT NULL,
    date TIMESTAMP NOT NULL,
    duration INTEGER NOT NULL, -- minutes
    link VARCHAR(255),
    zoom_start_link VARCHAR(1000),
    extra_time_to_join INTEGER DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Comments table (course reviews and comments)
CREATE TABLE IF NOT EXISTS comments (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    webinar_id INTEGER REFERENCES webinars(id) ON DELETE CASCADE,
    product_id INTEGER NULL, -- for products
    bundle_id INTEGER NULL, -- for bundles  
    blog_id INTEGER NULL, -- for blogs
    reply_id INTEGER REFERENCES comments(id) ON DELETE CASCADE,
    comment TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'pending')),
    viewed BOOLEAN DEFAULT FALSE,
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

-- =============================================
-- Indexes for Performance
-- =============================================

-- Users indexes
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role_name);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);

-- Webinars indexes
CREATE INDEX IF NOT EXISTS idx_webinars_teacher ON webinars(teacher_id);
CREATE INDEX IF NOT EXISTS idx_webinars_category ON webinars(category_id);
CREATE INDEX IF NOT EXISTS idx_webinars_status ON webinars(status);
CREATE INDEX IF NOT EXISTS idx_webinars_slug ON webinars(slug);

-- Sales indexes
CREATE INDEX IF NOT EXISTS idx_sales_buyer ON sales(buyer_id);
CREATE INDEX IF NOT EXISTS idx_sales_webinar ON sales(webinar_id);
CREATE INDEX IF NOT EXISTS idx_sales_status ON sales(status);
CREATE INDEX IF NOT EXISTS idx_sales_created ON sales(created_at);

-- Payments indexes
CREATE INDEX IF NOT EXISTS idx_payments_sale ON payments(sale_id);
CREATE INDEX IF NOT EXISTS idx_payments_gateway ON payments(gateway);
CREATE INDEX IF NOT EXISTS idx_payments_status ON payments(status);

-- =============================================
-- Row Level Security (RLS) Setup
-- =============================================

-- Enable RLS
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE webinars ENABLE ROW LEVEL SECURITY;
ALTER TABLE sales ENABLE ROW LEVEL SECURITY;
ALTER TABLE payments ENABLE ROW LEVEL SECURITY;

-- Basic RLS Policies (will be refined based on business logic)
CREATE POLICY "Users can view their own data" ON users
    FOR SELECT USING (auth.uid()::text = id::text);

CREATE POLICY "Webinars are viewable by everyone" ON webinars
    FOR SELECT USING (status = 'active');

CREATE POLICY "Users can view their own sales" ON sales
    FOR SELECT USING (auth.uid()::text = buyer_id::text);

-- =============================================
-- Insert Default Data
-- =============================================

-- Insert default currency (USD)
INSERT INTO currencies (currency, currency_position, exchange_rate, order_number) 
VALUES ('USD', 'left', 1.0, 1) ON CONFLICT (currency) DO NOTHING;

-- Insert default payment channel (Stripe)
INSERT INTO payment_channels (title, class_name, status, settings)
VALUES ('Stripe', 'Stripe', 'active', '{"publishable_key": "", "secret_key": "", "webhook_secret": ""}') 
ON CONFLICT DO NOTHING;

-- Insert default categories
INSERT INTO categories (title, order_number) VALUES
    ('Programming', 1),
    ('Design', 2),
    ('Marketing', 3),
    ('Business', 4),
    ('Personal Development', 5)
ON CONFLICT DO NOTHING;

COMMIT;