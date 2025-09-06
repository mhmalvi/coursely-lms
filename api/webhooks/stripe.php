<?php

// Stripe Webhook Handler for Vercel Serverless Functions
// This file handles Stripe webhook events for payment processing

require_once __DIR__ . '/../../vendor/autoload.php';

// Set up Laravel application context
$_SERVER['SCRIPT_NAME'] = '/api/webhooks/stripe.php';

// Import Laravel app
$app = require_once __DIR__ . '/../../bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

// Set CORS headers for webhook
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Stripe-Signature');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get webhook secret from environment
    $webhookSecret = env('STRIPE_WEBHOOK_SECRET');
    if (!$webhookSecret) {
        throw new Exception('Stripe webhook secret not configured');
    }

    // Get the request body and signature
    $payload = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

    if (empty($payload) || empty($signature)) {
        throw new Exception('Missing payload or signature');
    }

    // Verify webhook signature
    try {
        $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
    } catch (SignatureVerificationException $e) {
        error_log('Stripe webhook signature verification failed: ' . $e->getMessage());
        http_response_code(400);
        echo json_encode(['error' => 'Invalid signature']);
        exit();
    }

    // Log the event
    error_log('Stripe webhook received: ' . $event->type);

    // Handle different event types
    switch ($event->type) {
        case 'payment_intent.succeeded':
            handlePaymentIntentSucceeded($event->data->object);
            break;
            
        case 'payment_intent.payment_failed':
            handlePaymentIntentFailed($event->data->object);
            break;
            
        case 'customer.subscription.created':
            handleSubscriptionCreated($event->data->object);
            break;
            
        case 'customer.subscription.deleted':
            handleSubscriptionDeleted($event->data->object);
            break;
            
        case 'invoice.payment_succeeded':
            handleInvoicePaymentSucceeded($event->data->object);
            break;
            
        default:
            error_log('Unhandled webhook event type: ' . $event->type);
    }

    // Return success response
    http_response_code(200);
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    error_log('Stripe webhook error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Handle successful payment intent
 */
function handlePaymentIntentSucceeded($paymentIntent)
{
    $metadata = $paymentIntent->metadata;
    
    if (isset($metadata->sale_id)) {
        try {
            // Update sale status in Supabase
            $supabase = createSupabaseClient();
            
            // Update sale record
            $supabase->from('sales')
                ->update([
                    'status' => 'success',
                    'reference_id' => $paymentIntent->id,
                    'updated_at' => date('Y-m-d H:i:s')
                ])
                ->eq('id', $metadata->sale_id)
                ->execute();

            // Update payment record
            $supabase->from('payments')
                ->update([
                    'status' => 'success',
                    'gateway_payment_id' => $paymentIntent->id,
                    'gateway_transaction_id' => $paymentIntent->charges->data[0]->id ?? null,
                    'updated_at' => date('Y-m-d H:i:s')
                ])
                ->eq('sale_id', $metadata->sale_id)
                ->execute();

            // Grant course access
            if (isset($metadata->webinar_id)) {
                grantCourseAccess($metadata->user_id, $metadata->webinar_id);
            }
            
            error_log("Payment successful for sale ID: {$metadata->sale_id}");
            
        } catch (Exception $e) {
            error_log("Error updating payment success: " . $e->getMessage());
        }
    }
}

/**
 * Handle failed payment intent
 */
function handlePaymentIntentFailed($paymentIntent)
{
    $metadata = $paymentIntent->metadata;
    
    if (isset($metadata->sale_id)) {
        try {
            $supabase = createSupabaseClient();
            
            // Update sale status
            $supabase->from('sales')
                ->update([
                    'status' => 'canceled',
                    'updated_at' => date('Y-m-d H:i:s')
                ])
                ->eq('id', $metadata->sale_id)
                ->execute();

            // Update payment record
            $supabase->from('payments')
                ->update([
                    'status' => 'failed',
                    'gateway_payment_id' => $paymentIntent->id,
                    'updated_at' => date('Y-m-d H:i:s')
                ])
                ->eq('sale_id', $metadata->sale_id)
                ->execute();
            
            error_log("Payment failed for sale ID: {$metadata->sale_id}");
            
        } catch (Exception $e) {
            error_log("Error updating payment failure: " . $e->getMessage());
        }
    }
}

/**
 * Handle subscription created
 */
function handleSubscriptionCreated($subscription)
{
    try {
        $supabase = createSupabaseClient();
        
        // Handle subscription creation logic
        // This would create subscription records in your database
        
        error_log("Subscription created: {$subscription->id}");
        
    } catch (Exception $e) {
        error_log("Error handling subscription creation: " . $e->getMessage());
    }
}

/**
 * Handle subscription deleted
 */
function handleSubscriptionDeleted($subscription)
{
    try {
        $supabase = createSupabaseClient();
        
        // Handle subscription cancellation logic
        // This would update subscription status and revoke access if needed
        
        error_log("Subscription deleted: {$subscription->id}");
        
    } catch (Exception $e) {
        error_log("Error handling subscription deletion: " . $e->getMessage());
    }
}

/**
 * Handle invoice payment succeeded (for subscriptions)
 */
function handleInvoicePaymentSucceeded($invoice)
{
    try {
        // Handle recurring payment success
        error_log("Invoice payment succeeded: {$invoice->id}");
        
    } catch (Exception $e) {
        error_log("Error handling invoice payment: " . $e->getMessage());
    }
}

/**
 * Grant course access to user
 */
function grantCourseAccess($userId, $webinarId)
{
    try {
        $supabase = createSupabaseClient();
        
        // Create enrollment record or update user permissions
        // This is where you would implement your course access logic
        
        error_log("Granting access to webinar {$webinarId} for user {$userId}");
        
    } catch (Exception $e) {
        error_log("Error granting course access: " . $e->getMessage());
    }
}

/**
 * Create Supabase client for database operations
 */
function createSupabaseClient()
{
    // This would use a Supabase PHP client library
    // For now, return a simple HTTP client setup
    
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_SERVICE_KEY'); // Use service key for server-side operations
    
    if (!$supabaseUrl || !$supabaseKey) {
        throw new Exception('Supabase credentials not configured');
    }
    
    // Return a configured HTTP client or Supabase client
    // This would typically use a library like supabase-php
    return new SupabaseClient($supabaseUrl, $supabaseKey);
}

/**
 * Simple Supabase HTTP client for database operations
 */
class SupabaseClient
{
    private $baseUrl;
    private $apiKey;
    
    public function __construct($url, $key)
    {
        $this->baseUrl = rtrim($url, '/') . '/rest/v1';
        $this->apiKey = $key;
    }
    
    public function from($table)
    {
        return new SupabaseQuery($this->baseUrl, $this->apiKey, $table);
    }
}

/**
 * Simple query builder for Supabase operations
 */
class SupabaseQuery
{
    private $baseUrl;
    private $apiKey;
    private $table;
    private $conditions = [];
    
    public function __construct($baseUrl, $apiKey, $table)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->table = $table;
    }
    
    public function update($data)
    {
        $this->data = $data;
        return $this;
    }
    
    public function eq($column, $value)
    {
        $this->conditions[] = urlencode($column) . '=eq.' . urlencode($value);
        return $this;
    }
    
    public function execute()
    {
        $url = $this->baseUrl . '/' . $this->table;
        
        if (!empty($this->conditions)) {
            $url .= '?' . implode('&', $this->conditions);
        }
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey,
            'Prefer: return=minimal'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => json_encode($this->data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('CURL Error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception("HTTP Error {$httpCode}: " . $response);
        }
        
        return json_decode($response, true);
    }
}