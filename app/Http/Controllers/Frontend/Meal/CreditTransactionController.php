<?php

namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CreditTransaction;
use Exception;

class CreditTransactionController extends Controller
{
    public function store(Request $request)
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

            // Save transaction
            CreditTransaction::create([
                'customer_id'    => $customerId,
                'type'           => 'credit',
                'method'         => $request->payment_method,
                'amount'         => $creditAmount,
                'balance_after'  => $newBalance,
                'transaction_id' => null,
                'currency'       => 'USD', // or local currency
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
