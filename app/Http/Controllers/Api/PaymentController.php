<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\Webinar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Create payment intent for course purchase
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            $request->validate([
                'webinar_id' => 'required|exists:webinars,id',
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'string|in:usd,eur,gbp'
            ]);

            $user = Auth::user();
            $webinar = Webinar::findOrFail($request->webinar_id);
            $amount = $request->amount;
            $currency = $request->currency ?? 'usd';

            // Check if user already has access to this course
            $existingPurchase = Sale::where('buyer_id', $user->id)
                ->where('webinar_id', $webinar->id)
                ->where('status', 'success')
                ->first();

            if ($existingPurchase) {
                return response()->json([
                    'error' => 'You already have access to this course'
                ], 400);
            }

            DB::beginTransaction();

            // Create sale record
            $sale = Sale::create([
                'buyer_id' => $user->id,
                'seller_id' => $webinar->teacher_id,
                'webinar_id' => $webinar->id,
                'type' => 'webinar',
                'payment_method' => 'stripe',
                'amount' => $amount,
                'total_amount' => $amount,
                'status' => 'pending',
            ]);

            // Create payment record
            $payment = Payment::create([
                'sale_id' => $sale->id,
                'user_id' => $user->id,
                'gateway' => 'stripe',
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
            ]);

            // Create Stripe payment intent
            $result = $this->stripeService->createPaymentIntent(
                $amount,
                $currency,
                [
                    'sale_id' => $sale->id,
                    'user_id' => $user->id,
                    'webinar_id' => $webinar->id,
                    'webinar_title' => $webinar->title,
                ]
            );

            if (!$result['success']) {
                DB::rollback();
                return response()->json([
                    'error' => $result['error']
                ], 400);
            }

            // Update payment with Stripe payment intent ID
            $payment->update([
                'gateway_payment_id' => $result['payment_intent_id']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'client_secret' => $result['client_secret'],
                'sale_id' => $sale->id,
                'payment_id' => $payment->id,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment Intent Creation Error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to create payment intent'
            ], 500);
        }
    }

    /**
     * Confirm payment and complete purchase
     */
    public function confirmPayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'sale_id' => 'required|exists:sales,id'
        ]);

        try {
            $sale = Sale::findOrFail($request->sale_id);
            $payment = Payment::where('sale_id', $sale->id)->first();

            if (!$payment) {
                return response()->json(['error' => 'Payment record not found'], 404);
            }

            // Verify payment intent with Stripe
            $paymentIntent = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);
            
            if ($paymentIntent->status === 'succeeded') {
                // Update records
                $sale->update([
                    'status' => 'success',
                    'reference_id' => $paymentIntent->id
                ]);

                $payment->update([
                    'status' => 'success',
                    'gateway_payment_id' => $paymentIntent->id,
                    'gateway_transaction_id' => $paymentIntent->charges->data[0]->id ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment confirmed successfully',
                    'sale' => $sale,
                ]);
            }

            return response()->json([
                'error' => 'Payment not completed',
                'status' => $paymentIntent->status
            ], 400);

        } catch (\Exception $e) {
            Log::error('Payment Confirmation Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to confirm payment'], 500);
        }
    }

    /**
     * Handle Stripe webhooks
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        $result = $this->stripeService->handleWebhook($payload, $signature);

        if ($result['status'] === 'success') {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Get user's purchase history
     */
    public function purchaseHistory(Request $request)
    {
        $user = Auth::user();
        
        $purchases = Sale::where('buyer_id', $user->id)
            ->with(['webinar:id,title,image_cover', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'purchases' => $purchases
        ]);
    }

    /**
     * Request refund
     */
    public function requestRefund(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'reason' => 'string|max:500'
        ]);

        try {
            $user = Auth::user();
            $sale = Sale::where('id', $request->sale_id)
                ->where('buyer_id', $user->id)
                ->where('status', 'success')
                ->first();

            if (!$sale) {
                return response()->json(['error' => 'Sale not found or not eligible for refund'], 404);
            }

            $payment = Payment::where('sale_id', $sale->id)->first();
            
            if (!$payment || !$payment->gateway_payment_id) {
                return response()->json(['error' => 'Payment not found'], 404);
            }

            // Process refund through Stripe
            $result = $this->stripeService->refundPayment($payment->gateway_payment_id);

            if ($result['success']) {
                $sale->update(['status' => 'refunded']);
                $payment->update(['status' => 'refunded']);

                return response()->json([
                    'success' => true,
                    'message' => 'Refund processed successfully',
                    'refund' => $result['refund']
                ]);
            }

            return response()->json([
                'error' => 'Failed to process refund: ' . $result['error']
            ], 400);

        } catch (\Exception $e) {
            Log::error('Refund Request Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to process refund'], 500);
        }
    }

    /**
     * Get available courses for purchase
     */
    public function availableCourses(Request $request)
    {
        $user = Auth::user();
        
        // Get courses user doesn't own
        $ownedCourseIds = Sale::where('buyer_id', $user->id)
            ->where('status', 'success')
            ->pluck('webinar_id');

        $courses = Webinar::where('status', 'active')
            ->whereNotIn('id', $ownedCourseIds)
            ->select('id', 'title', 'price', 'image_cover', 'description', 'teacher_id')
            ->with('teacher:id,full_name')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'courses' => $courses
        ]);
    }
}