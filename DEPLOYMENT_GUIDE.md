# Coursely LMS Stripe + Supabase + Vercel Deployment Guide

## 🎯 Complete Implementation Overview

Your Coursely LMS is now configured for modern serverless deployment with:
- **Supabase** (PostgreSQL database + Authentication + Storage)
- **Stripe** (Single payment gateway replacing 30+ integrations)
- **Vercel** (Serverless hosting with global CDN)

## ✅ What's Been Implemented

### 1. **Supabase Database Setup** ✅
- **Project Created**: `ltzsuubjuvpufkbvxtyg`
- **Database Schema**: Complete PostgreSQL migration from MySQL
- **File**: `database/supabase-migration.sql` (ready to execute)

### 2. **Stripe Payment Integration** ✅
- **Service Class**: `app/Services/StripeService.php`
- **API Controller**: `app/Http/Controllers/Api/PaymentController.php` 
- **Webhook Handler**: `api/webhooks/stripe.php`
- **Features**: Payment intents, subscriptions, refunds, webhooks

### 3. **Vercel Configuration** ✅
- **Runtime**: PHP 8.3 with vercel-php@0.7.4
- **Serverless Functions**: Laravel API + Stripe webhooks
- **Environment Variables**: Configured for production

## 🚀 Deployment Steps

### Step 1: Set up Supabase Database

1. **Go to**: https://supabase.com/dashboard/project/ltzsuubjuvpufkbvxtyg
2. **Navigate to**: SQL Editor
3. **Execute**: Copy and paste `database/supabase-migration.sql`
4. **Run**: Click "Run" to create all tables and indexes

**Your Database Credentials:**
```bash
SUPABASE_URL=https://ltzsuubjuvpufkbvxtyg.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imx0enN1dWJqdXZwdWZrYnZ4dHlnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTcxMzYwNTAsImV4cCI6MjA3MjcxMjA1MH0.nAs6A9Jl9kYD5vlL3rrcCvTI_zQrBoUU1JMxrVp2jtQ
SUPABASE_SERVICE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imx0enN1dWJqdXZwdWZrYnZ4dHlnIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1NzEzNjA1MCwiZXhwIjoyMDcyNzEyMDUwfQ.Ce-IhNfMJUHmc7aki9TiGoceDEMq_LC22MPIeKPBYlk
```

### Step 2: Create Stripe Account

1. **Sign up**: https://dashboard.stripe.com/register
2. **Get API keys**: Dashboard → Developers → API keys
3. **Create webhook endpoint**: Dashboard → Webhooks → Add endpoint
   - **URL**: `https://your-vercel-domain.vercel.app/api/webhooks/stripe`
   - **Events**: Select all payment events

### Step 3: Configure Vercel Environment Variables

```bash
# Set environment variables
vercel env add SUPABASE_URL production
vercel env add SUPABASE_ANON_KEY production  
vercel env add SUPABASE_SERVICE_KEY production
vercel env add STRIPE_PUBLISHABLE_KEY production
vercel env add STRIPE_SECRET_KEY production
vercel env add STRIPE_WEBHOOK_SECRET production
vercel env add APP_URL production  # Your Vercel domain
```

### Step 4: Deploy to Vercel

```bash
# Commit changes
git add -A
git commit -m "Implement Stripe + Supabase integration for Vercel deployment"
git push origin master

# Deploy to production
vercel --prod --archive=tgz
```

## 📋 Environment Variables Reference

Create these in Vercel dashboard:

| Variable | Description | Example Value |
|----------|-------------|---------------|
| `SUPABASE_URL` | Your Supabase project URL | `https://ltzsuubjuvpufkbvxtyg.supabase.co` |
| `SUPABASE_ANON_KEY` | Public anon key | `eyJhbG...` |
| `SUPABASE_SERVICE_KEY` | Private service key | `eyJhbG...` |
| `STRIPE_PUBLISHABLE_KEY` | Stripe public key | `pk_live_...` |
| `STRIPE_SECRET_KEY` | Stripe secret key | `sk_live_...` |
| `STRIPE_WEBHOOK_SECRET` | Webhook endpoint secret | `whsec_...` |
| `APP_URL` | Your Vercel domain | `https://coursely-lms.vercel.app` |

## 🔧 API Endpoints

### Payment Endpoints
```
POST /api/payment/create-intent     # Create payment intent
POST /api/payment/confirm          # Confirm payment
POST /api/webhooks/stripe          # Stripe webhook handler
GET  /api/payment/history          # Purchase history
POST /api/payment/refund           # Request refund
GET  /api/payment/courses          # Available courses
```

### Example Frontend Integration

```javascript
// Create payment intent
const response = await fetch('/api/payment/create-intent', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer ' + userToken
  },
  body: JSON.stringify({
    webinar_id: 123,
    amount: 99.99,
    currency: 'usd'
  })
});

const { client_secret } = await response.json();

// Process payment with Stripe Elements
const stripe = Stripe('pk_test_...');
const result = await stripe.confirmPayment({
  elements,
  clientSecret: client_secret,
  confirmParams: {
    return_url: 'https://your-domain.com/success',
  },
});
```

## 🎨 Frontend Integration

### Stripe Elements Setup
```html
<script src="https://js.stripe.com/v3/"></script>
<div id="payment-element"></div>

<script>
const stripe = Stripe('pk_live_...');
const elements = stripe.elements({
  clientSecret: 'pi_..._secret_...'
});

const paymentElement = elements.create('payment');
paymentElement.mount('#payment-element');
</script>
```

## 🔄 Migration Checklist

- [ ] Execute Supabase SQL migration
- [ ] Configure Stripe account and webhooks
- [ ] Set all Vercel environment variables
- [ ] Deploy to Vercel production
- [ ] Test payment flow end-to-end
- [ ] Configure domain and SSL
- [ ] Set up monitoring and alerts

## 📊 Cost Breakdown

| Service | Free Tier | Production Cost |
|---------|-----------|-----------------|
| **Supabase** | 500MB DB + 1GB storage | $0/month (current) |
| **Vercel** | 100GB bandwidth | $0/month (hobby) |
| **Stripe** | No monthly fee | 2.9% + 30¢ per transaction |
| **Total** | **$0/month** | **Only pay per transaction** |

## 🚨 Important Notes

1. **Database Migration**: Execute the SQL file in Supabase to create all tables
2. **Webhook URL**: Update Stripe webhook endpoint after deployment
3. **Environment Variables**: Use Vercel dashboard, not vercel.json for secrets
4. **File Uploads**: Configure Supabase Storage for file uploads
5. **Authentication**: Integrate Supabase Auth for user management

## 🔍 Testing

### Test Payment Flow:
1. **Create test account** in your deployed app
2. **Browse courses** at `/api/payment/courses`  
3. **Make test purchase** using Stripe test cards
4. **Verify webhook** processing in Vercel logs
5. **Confirm course access** granted to user

### Stripe Test Cards:
- **Success**: `4242424242424242`
- **Decline**: `4000000000000002`
- **Authentication**: `4000002500003155`

## 🎯 Next Steps

After successful deployment:
1. **Configure custom domain** in Vercel
2. **Set up monitoring** with Vercel Analytics  
3. **Enable real-time features** with Supabase Realtime
4. **Implement file uploads** with Supabase Storage
5. **Add email notifications** with Supabase Edge Functions

Your Coursely LMS is now ready for modern, scalable deployment! 🚀