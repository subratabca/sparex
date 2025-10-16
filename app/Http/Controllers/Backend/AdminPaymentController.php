<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Notifications\ProductComplaint\ForwardProductComplaintNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\ClientOrderAction;
use App\Models\ClientOrder;
use App\Models\Order;
use Exception;

class AdminPaymentController extends Controller
{
    public function customerPaymentPage()
    {
        return view('backend.pages.payment-history.customer.customer-payment-list');
    }

    public function getCustomerPaymentList(Request $request)
    {
        try {
            $refundData = ClientOrderAction::with([
                'order:id,invoice_no,created_at,payment_type,customer_id',
                'order.customer:id,firstName,lastName',
                'clientOrder:id,payable_amount,paid_amount',
                'client:id,firstName,lastName'
            ])
            ->latest()
            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $refundData
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving customer payments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function customerPaymentDetailsPage(Request $request)
    {
      return view('backend.pages.payment-history.customer.customer-payment-details');
    }

    public function getCustomerPaymentDetailsInfo($client_order_action_id)
    {
        try {
            $refundData = ClientOrderAction::with([
                'order:id,order_number,created_at,payment_type,customer_id',
                'order.customer:id,firstName,lastName',
                'clientOrder:id,payable_amount,paid_amount',
                'orderItem:id,product_id,unit_price',
                'orderItem.product:id,name,image',
                'orderItem.variant:id,color,size',
                'client:id,firstName,lastName',
                'actionBy:id,firstName,lastName'
            ])->findOrFail($client_order_action_id);

            return response()->json([
                'status' => 'success',
                'data' => $refundData
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Customer payment details not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving customer payment details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markCustomerPaymentAsPaid($id)
    {
        try {
            $payment = ClientOrderAction::findOrFail($id);

            if ($payment->payment_status === 'paid') {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'This payment is already marked as paid.'
                ], 400);
            }

            $payment->payment_status = 'paid';
            $payment->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment status updated to paid successfully.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Payment record not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while updating payment status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Client Payment Section

    public function clientPaymentPage()
    {
        return view('backend.pages.payment-history.client.client-payment-list');
    }

    public function getClientPaymentList(Request $request)
    {
        try {
            $clientOrders = ClientOrder::with([
                'order:id,invoice_no,created_at,customer_id',
                'order.customer:id,firstName,lastName',
                'client:id,firstName,lastName'
            ])
            ->latest()
            ->get();

            // Format data for response
            $data = $clientOrders->map(function ($item) {
                $order = $item->order;
                $client = $item->client;

                return [
                    'id' => $item->id,
                    'order_id' => $item->order_id,
                    'client_id' => $item->client_id,
                    'order_date' => $order ? $order->created_at->format('d M Y') : '-',
                    'invoice_no' => $order ? $order->invoice_no : '-',
                    'client_name' => $client ? ($client->firstName . ' ' . ($client->lastName ?? '')) : '-',
                    'payable_amount' => isset($item->payable_amount) ? '£' . number_format($item->payable_amount, 2) : '£0.00',
                    'payment_status' => $item->payment_status ? ucfirst($item->payment_status) : 'Unknown'
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving customer payments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function clientPaymentDetailsPage($client_id, $order_id)
    {
        return view('backend.pages.payment-history.client.client-payment-details', compact('client_id', 'order_id'));
    }

    public function getClientPaymentDetailsInfo($client_id, $order_id)
    {
        try {
            
            $order = Order::with([
                'customer',
                'orderItems' => function($query) use ($client_id) {
                    $query->where('client_id', $client_id);
                },
                'orderItems.client',
                'orderItems.product',
                'orderItems.variant',
                'orderItems.product.category',
                'orderItems.product.brand',
                'shippingAddress.country',
                'shippingAddress.county',
                'shippingAddress.city',
                'clientOrders' => function($query) use ($client_id,$order_id) {
                    $query->where('client_id', $client_id)->where('order_id', $order_id);
                },
                'clientOrders.client' 
            ])->where('id', $order_id)->first();

            if (!$order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Order details not found',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'order' => $order,
                    'order_items' => $order->orderItems,
                    'client_orders' => $order->clientOrders 
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error retrieving order information',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function markClientPaymentAsPaid($client_id, $order_id)
    {
        try {
            $payment = ClientOrder::where('client_id', $client_id)
                ->where('order_id', $order_id)
                ->first();

            if (!$payment) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Payment record not found.'
                ], 404);
            }

            if ($payment->payment_status === 'paid') {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'This payment is already marked as paid.'
                ], 400);
            }

            $payment->payment_status = 'paid';
            $payment->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment status updated to paid successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Payment record not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while updating payment status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}