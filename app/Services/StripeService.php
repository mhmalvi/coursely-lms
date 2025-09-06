<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class StripeService
{
    private $stripeSecretKey;
    private $webhookSecret;

    public function __construct()
    {
        $this->stripeSecretKey = env('STRIPE_SECRET_KEY');
        $this->webhookSecret = env('STRIPE_WEBHOOK_SECRET');
        
        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * Create a payment intent for course purchase
     */
    public function createPaymentIntent($amount, $currency = 'usd', $metadata = [])
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id
            ];
        } catch (\Exception $e) {
            Log::error('Stripe Payment Intent Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create or retrieve a Stripe customer
     */
    public function createOrGetCustomer($user)
    {
        try {
            // Check if user already has a Stripe customer ID
            if ($user->stripe_customer_id) {
                return Customer::retrieve($user->stripe_customer_id);
            }

            // Create new customer
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->full_name,
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);

            // Save customer ID to user
            $user->update(['stripe_customer_id' => $customer->id]);

            return $customer;
        } catch (\Exception $e) {
            Log::error('Stripe Customer Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a subscription for recurring payments
     */
    public function createSubscription($customerId, $priceId, $metadata = [])
    {
        try {
            $subscription = Subscription::create([
                'customer' => $customerId,
                'items' => [[
                    'price' => $priceId,
                ]],
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'subscription' => $subscription
            ];
        } catch (\Exception $e) {
            Log::error('Stripe Subscription Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function handleWebhook($payload, $signature)
    {
        try {
            $event = Webhook::constructEvent(
                $payload, 
                $signature, 
                $this->webhookSecret
            );

            Log::info('Stripe webhook received: ' . $event->type);

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;
                    
                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event->data->object);
                    break;
                    
                case 'customer.subscription.created':
                    $this->handleSubscriptionCreated($event->data->object);
                    break;
                    
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event->data->object);
                    break;
                    
                default:
                    Log::info('Unhandled webhook event type: ' . $event->type);
            }

            return ['status' => 'success'];

        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Invalid signature'];
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle successful payment intent
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        $metadata = $paymentIntent->metadata;
        
        if (isset($metadata->sale_id)) {
            $sale = Sale::find($metadata->sale_id);
            if ($sale) {
                // Update sale status
                $sale->update([
                    'status' => 'success',
                    'reference_id' => $paymentIntent->id
                ]);

                // Update payment record
                $payment = Payment::where('sale_id', $sale->id)->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'success',
                        'gateway_payment_id' => $paymentIntent->id,
                        'gateway_transaction_id' => $paymentIntent->charges->data[0]->id ?? null,
                    ]);
                }

                // Grant course access to user
                $this->grantCourseAccess($sale);
                
                Log::info("Payment successful for sale ID: {$sale->id}");
            }
        }
    }

    /**
     * Handle failed payment intent
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        $metadata = $paymentIntent->metadata;
        
        if (isset($metadata->sale_id)) {
            $sale = Sale::find($metadata->sale_id);
            if ($sale) {
                $sale->update(['status' => 'canceled']);
                
                $payment = Payment::where('sale_id', $sale->id)->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'failed',
                        'gateway_payment_id' => $paymentIntent->id,
                    ]);
                }
                
                Log::error("Payment failed for sale ID: {$sale->id}");
            }
        }
    }

    /**
     * Handle subscription created
     */
    private function handleSubscriptionCreated($subscription)
    {
        // Handle subscription creation logic
        Log::info("Subscription created: {$subscription->id}");
    }

    /**
     * Handle subscription deleted  
     */
    private function handleSubscriptionDeleted($subscription)
    {
        // Handle subscription cancellation logic
        Log::info("Subscription deleted: {$subscription->id}");
    }

    /**
     * Grant course access to user after successful payment
     */
    private function grantCourseAccess($sale)
    {
        // Create enrollment record or grant access
        // This would integrate with your existing course access logic
        
        if ($sale->webinar_id) {
            // Grant access to webinar/course
            Log::info("Granting access to webinar {$sale->webinar_id} for user {$sale->buyer_id}");
            
            // Your existing logic for granting course access would go here
            // For example: creating enrollment records, updating user permissions, etc.
        }
    }

    /**
     * Refund a payment
     */
    public function refundPayment($paymentIntentId, $amount = null)
    {
        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $paymentIntentId,
                'amount' => $amount ? $amount * 100 : null, // Convert to cents if specified
            ]);

            return [
                'success' => true,
                'refund' => $refund
            ];
        } catch (\Exception $e) {
            Log::error('Stripe Refund Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}