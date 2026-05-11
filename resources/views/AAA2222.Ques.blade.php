<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\MealOrder\NewMealOrderNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Helpers\DeliveryHelper;
use App\Models\Product;
use App\Models\MealType;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Models\ClientMealOrder;
use App\Models\MealShippingAddress;
use App\Models\MealDeliveryCharge;
use App\Models\CreditTransaction;
use App\Models\DeliveryChargeLedger;
use App\Models\MealDeliveryStatusHistory;  
use App\Models\User;
use Carbon\Carbon;
use Exception;

class MealOrderController extends Controller
{
    public function getMealOrderDetails($id)
    {
        try {
            $order = MealOrder::with([
                'items.mealType',
                'items.product.nutrient',
                'items.client:id,firstName,lastName',
                'mealShippingAddress.country',
                'mealShippingAddress.county',
                'mealShippingAddress.city',
                'deliveryChargeLedgers.mealType',
                'deliveryChargeLedgers.deliveryPerson:id,firstName,lastName',
                'deliveryChargeLedgers.statusHistories' => function ($query) {
                    $query->latest()->take(5);
                }
            ])->find($id);

            if (!$order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found.',
                ], 404);
            }

            $deliveryLedgers = $order->deliveryChargeLedgers
                ->keyBy(function ($ledger) {
                    return $ledger->client_id . '_' .
                           $ledger->meal_type_id . '_' .
                           Carbon::parse($ledger->delivery_date)->format('Y-m-d');
                });

            $groupedItems = $order->items->groupBy(function ($item) {
                return Carbon::parse($item->meal_date)->format('Y-m-d');
            })->map(function ($dayItems, $date) use ($deliveryLedgers) {

                return $dayItems->groupBy(function ($item) use ($date, $deliveryLedgers) {

                    $mealTypeName = $item->mealType->name ?? 'Other';

                    $ledgerKey = $item->client_id . '_' .
                                 $item->meal_type_id . '_' .
                                 $date;

                    $deliveryLedger = $deliveryLedgers->get($ledgerKey);

                    $item->delivery_info = $deliveryLedger ? [
                        'delivery_status' => $deliveryLedger->delivery_status,
                        'delivery_status_label' => DeliveryChargeLedger::STATUS_LABELS[$deliveryLedger->delivery_status] ?? 'Pending',
                        'delivery_person_name' => $deliveryLedger->deliveryPerson
                            ? $deliveryLedger->deliveryPerson->firstName . ' ' . $deliveryLedger->deliveryPerson->lastName
                            : 'Not Assigned',
                        'delivery_person_id' => $deliveryLedger->delivery_person_id,
                        'order_tracking' => $deliveryLedger->order_tracking,
                        'delivery_charge' => $deliveryLedger->delivery_charge,
                        'payment_status' => $deliveryLedger->payment_status
                    ] : [
                        'delivery_status' => 'pending',
                        'delivery_status_label' => 'Pending',
                        'delivery_person_name' => 'Not Assigned',
                        'delivery_person_id' => null,
                        'order_tracking' => null,
                        'delivery_charge' => 0,
                        'payment_status' => 'due'
                    ];

                    return $mealTypeName;
                });
            });

            $totalCalories = $order->items->sum(function ($item) {
                return ($item->product->nutrient->calories ?? 0) * $item->quantity;
            });

            $caloriesByMealType = $order->items->groupBy(function ($item) {
                return $item->mealType->name ?? 'Other';
            })->map(function ($group) {
                return $group->sum(function ($item) {
                    return ($item->product->nutrient->calories ?? 0) * $item->quantity;
                });
            });

            $summary = [
                'subtotal' => floatval($order->subtotal ?? 0),
                'tax' => floatval($order->tax ?? 0),
                'delivery_fee' => floatval($order->delivery_fee ?? 0),
                'total' => floatval($order->payable_amount ?? 0),
                'total_items' => $order->items->sum('quantity')
            ];

            $deliveryStatuses = DeliveryChargeLedger::STATUS_LABELS;
            return response()->json([
                'status' => 'success',
                'data' => [
                    'order' => $order,
                    'summary' => $summary,
                    'meal_cart' => $groupedItems,
                    'nutrition' => [
                        'total_calories' => $totalCalories,
                        'calories_by_meal_type' => $caloriesByMealType,
                    ],
                    'shipping_address' => $order->mealShippingAddress,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'meal_date' => $item->meal_date,
                            'meal_time' => $item->meal_time,
                            'meal_type' => $item->mealType ? [
                                'id' => $item->mealType->id,
                                'name' => $item->mealType->name
                            ] : null,
                            'product' => $item->product,
                            'client' => $item->client,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                            'delivery_info' => $item->delivery_info ?? null
                        ];
                    }),
                    'delivery_statuses' => $deliveryStatuses,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

from above methd response below view.blade.php is working fine. when click on Tracking: ${trackingNumber} in below function renderMealOrderItems() it will open a modal to show delivery_status from MealDeliveryStatusHistory model.order_tracking is showing in below code from DeliveryChargeLedger model which is connected with MealDeliveryStatusHistory model via delivery_charge_ledger_id.Status history will be same as earlier provided code.


<div class="container">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-silverware-fork-knife me-2"></i>Meal Order Details</h5>
            <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>
        <div class="card-body">
            <!-- Order Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="mb-1" id="mealPlanTitle">Order Details</h4>
                    <p class="mb-0 text-muted" id="orderNumberText"></p>
                </div>
            </div>

            <div class="row">
                <!-- Order Items (Left) -->
                <div class="col-lg-8 mb-4">
                    <div class="accordion" id="mealOrderAccordion"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
let barChartInstance = null;
let mealTypeBreakdown = {};
let currentRange = '7days';
let deliveryStatuses = {};

document.addEventListener('DOMContentLoaded', async function() {
    await loadMealOrderDetails();
});

async function loadMealOrderDetails() {
    try {
        showLoader();
        const orderId = window.location.pathname.split('/').pop();
        const response = await axios.get(`/user/get/meal-order/details/${orderId}`);

        if (response.status === 200 && response.data.status === 'success') {
            const data = response.data.data;
            const mealCart = data.meal_cart;
            const summary = data.summary;
            const nutrition = data.nutrition;
            const shippingAddress = data.shipping_address;
            const order = data.order;
            const items = data.items;
            const deliveryStatuses = data.delivery_statuses || {};

            document.getElementById('mealPlanTitle').textContent = `Order #${order.order_number}`;
            document.getElementById('orderNumberText').textContent = `${summary.total_items} Items`;

            renderMealOrderItems(mealCart, items, deliveryStatuses);
        } else {
            document.getElementById('mealOrderAccordion').innerHTML = `<div class="alert alert-info">Order not found.</div>`;
        }
    } catch (error) {
        console.error('Error loading order details:', error);
        errorToast('Failed to load order details');
    } finally {
        hideLoader();
    }
}

function renderMealOrderItems(mealCart, allItems, deliveryStatuses) {
    const container = document.getElementById('mealOrderAccordion');
    container.innerHTML = '';

    const dates = Object.keys(mealCart);
    if (dates.length === 0) {
        container.innerHTML = `<div class="alert alert-info">No items found in this order.</div>`;
        return;
    }

    dates.forEach((date, index) => {
        const dayItems = mealCart[date];
        const collapseId = `mealDay${index}`;

        const formattedDate = new Date(date).toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'short',
            day: 'numeric'
        });

        const mealTypes = Object.keys(dayItems);
        let mealTypeHtml = '';

        mealTypes.forEach(type => {
            const typeTitle = toTitleCase(type);
            const items = dayItems[type];
            
            const mealTime = findMealTime(date, type, allItems);

            // Delivery status (group badge only)
            const firstDeliveryInfo = items[0]?.delivery_info || {
                delivery_status: 'pending',
                delivery_status_label: 'Pending'
            };

            const deliveryBadgeClass = getDeliveryBadgeClass(firstDeliveryInfo.delivery_status);
            const deliveryStatusLabel = firstDeliveryInfo.delivery_status_label || deliveryStatuses[firstDeliveryInfo.delivery_status] || 'Pending';

            mealTypeHtml += `
                <div class="meal-type-section mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-primary mb-0">${typeTitle} (${items.length} items)</h6>
                        <div class="d-flex align-items-center gap-2">
                            ${mealTime ? `<span class="badge bg-light text-dark"><i class="mdi mdi-clock-outline me-1"></i>${mealTime}</span>` : ''}
                            <span class="badge ${deliveryBadgeClass}">${deliveryStatusLabel}</span>
                        </div>
                    </div>
                    <ul class="list-group mb-3">
            `;

            items.forEach(item => {
                const productName = toTitleCase(item.product?.name || '');
                const img = item.product?.image ? `/upload/product/small/${item.product.image}` : '/upload/no_image.jpg';
                const clientName = item.client ? toTitleCase(`${item.client.firstName} ${item.client.lastName}`) : 'Unknown Provider';

                // ✅ CLIENT-SPECIFIC DELIVERY INFO
                const deliveryInfo = item.delivery_info || {};
                const deliveryPersonName = toTitleCase(deliveryInfo.delivery_person_name) || 'Not Assigned';
                const trackingNumber = deliveryInfo.order_tracking || null;

                mealTypeHtml += `
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3">
                                <img src="${img}" alt="${productName}" class="rounded" style="width:60px;height:60px;object-fit:cover;">
                                <div>
                                    <strong>${productName}</strong><br>
                                    <small class="text-muted">${formatCurrency(item.unit_price || 0)} each × ${item.quantity || 0}</small><br>
                                    <small class="text-info">Provider: ${clientName}</small><br>

                                    <!-- ✅ Delivery Person (Per Client) -->
                                    <small class="text-muted">
                                        <i class="mdi mdi-account-circle me-1"></i>
                                        Delivery Person: ${deliveryPersonName}
                                    </small><br>

                                    <!-- ✅ Tracking Number (Per Client) -->
                                    ${trackingNumber ? `
                                        <small class="text-muted">
                                            <i class="mdi mdi-truck-delivery me-1"></i>
                                            Tracking: ${trackingNumber}
                                        </small>
                                    ` : ''}
                                </div>
                            </div>
                            <div class="text-end">
                                <strong>${formatCurrency(item.total_price || 0)}</strong>
                            </div>
                        </div>
                    </li>
                `;
            });

            mealTypeHtml += `
                    </ul>
                </div>
            `;
        });

        const block = `
            <div class="accordion-item shadow-sm mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                        ${formattedDate}
                    </button>
                </h2>
                <div id="${collapseId}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" data-bs-parent="#mealOrderAccordion">
                    <div class="accordion-body">${mealTypeHtml}</div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', block);
    });
}

</script>
