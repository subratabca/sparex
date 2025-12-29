<?php

namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CreditTransaction;
use App\Models\User;
use Exception;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class CreditTransactionController extends Controller
{
    public function storeCreditByStripe(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'payment_method' => 'required|in:cash,stripe,paypal',
                'amount' => 'required|numeric|min:1',
                'stripe_payment_id' => 'required|string',
                'stripe_payment_method' => 'required|string',
                'payment_intent_id' => 'required|string'
            ]);

            $customerId = $request->header('id');
            if (!$customerId) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized user'
                ], 401);
            }

            // Verify customer exists
            $customer = User::find($customerId);
            if (!$customer) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Customer not found'
                ], 404);
            }

            // Get the secret key from config
            $stripeSecretKey = config('services.stripe.secret');
            
            // Check if secret key is set
            if (!$stripeSecretKey) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Stripe configuration error'
                ], 500);
            }
            
            // Set Stripe secret key
            Stripe::setApiKey($stripeSecretKey);

            try {
                // Verify the payment intent with Stripe
                $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);
                
                // Check if payment was successful
                if ($paymentIntent->status !== 'succeeded') {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Payment not completed successfully. Status: ' . $paymentIntent->status
                    ], 400);
                }

                // Verify payment amount matches
                $stripeAmount = $paymentIntent->amount / 100; // Convert from cents to dollars
                $requestedAmount = floatval($request->amount);
                
                if (abs($stripeAmount - $requestedAmount) > 0.01) { // Allow small floating point differences
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Payment amount mismatch. Stripe: $' . $stripeAmount . ', Requested: $' . $requestedAmount
                    ], 400);
                }

                // Check if metadata exists before accessing it
                $metadata = $paymentIntent->metadata ?: (object)[];
                if (isset($metadata->customer_email) && $metadata->customer_email !== $customer->email) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Payment does not belong to this customer'
                    ], 400);
                }

                // Check if this payment intent has already been processed
                $existingTransaction = CreditTransaction::where('transaction_id', $paymentIntent->id)
                    ->orWhere('transaction_id', $request->stripe_payment_id)
                    ->first();
                    
                if ($existingTransaction) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'This payment has already been processed',
                        'transaction_id' => $existingTransaction->id
                    ], 400);
                }

            } catch (ApiErrorException $e) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Stripe verification failed: ' . $e->getMessage()
                ], 400);
            }

            // Get last balance
            $lastTransaction = CreditTransaction::where('customer_id', $customerId)
                ->orderBy('id', 'DESC')
                ->first();

            $previousBalance = $lastTransaction ? $lastTransaction->balance_after : 0;
            $creditAmount = floatval($request->amount);
            
            // New balance
            $newBalance = $previousBalance + $creditAmount;

            // Save transaction
            $transaction = CreditTransaction::create([
                'customer_id'    => $customerId,
                'type'           => 'credit',
                'payment_method' => 'stripe', // Note: Your migration uses 'method' not 'payment_method'
                'amount'         => $creditAmount,
                'balance_after'  => $newBalance,
                'transaction_id' => $paymentIntent->id, 
                'currency'       => strtoupper($paymentIntent->currency),
                'description'    => 'Credit added using Stripe. Payment Method: ' . $request->stripe_payment_method,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Credit added successfully!',
                'balance_after' => $newBalance,
                'transaction_id' => $transaction->id,
                'stripe_payment_intent' => $paymentIntent->id
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to add credit via Stripe',
                'error'   => env('APP_DEBUG') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function storeCreditByCash(Request $request)
    {
        try {
            // Validate payment method
            $request->validate([
                'payment_method' => 'required|in:cash,stripe,paypal',
                'amount' => 'nullable|numeric|min:1'
            ]);

            $customerId = $request->header('id');
            if (!$customerId) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized user'
                ], 401);
            }

            // Get last balance
            $lastTransaction = CreditTransaction::where('customer_id', $customerId)
                ->orderBy('id', 'DESC')
                ->first();

            $previousBalance = $lastTransaction ? $lastTransaction->balance_after : 0;

            // For cash payment, user enters amount manually
            $creditAmount = $request->payment_method === 'cash'
                            ? floatval($request->amount)
                            : 0;

            // New balance
            $newBalance = $previousBalance + $creditAmount;

            // Generate transaction ID for cash payment
            $transactionId = 'CASH-' . time() . '-' . strtoupper(uniqid());

            // Save transaction
            CreditTransaction::create([
                'customer_id'    => $customerId,
                'type'           => 'credit',
                'payment_method' => $request->payment_method,
                'amount'         => $creditAmount,
                'balance_after'  => $newBalance,
                'transaction_id' => $transactionId,
                'currency'       => 'USD',
                'description'    => 'Credit added using ' . $request->payment_method,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Credit added successfully!',
                'balance_after' => $newBalance
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to add credit',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        return view('frontend.pages.my-credit.index');
    }

    public function getCreditInfo(Request $request)
    {
        try {
            $customer_id = $request->header('id');

            if (!$customer_id) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized user'
                ], 401);
            }

            $creditInfo = CreditTransaction::where('customer_id', $customer_id)
                ->orderBy('id', 'DESC')
                ->get(['id', 'type', 'amount', 'balance_after']);

            return response()->json([
                'status' => 'success',
                'data' => $creditInfo
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getBalance(Request $request)
    {
        try {
            $customer_id = $request->header('id');

            if (!$customer_id) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized user'
                ], 401);
            }

            $credit = CreditTransaction::where('customer_id', $customer_id)
                ->latest()
                ->first(['balance_after']);

            $balance = $credit ? $credit->balance_after : 0;

            return response()->json([
                'status' => 'success',
                'balance' => $balance
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
