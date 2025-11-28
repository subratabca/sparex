@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')

<div class="container">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-silverware-fork-knife me-2"></i>Meal Order Details</h5>
            <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <div>
                    <p class="mb-0 text-muted">Meal Date: <span id="mealDateText"></span></p>
                </div>
                <div>
                    <span class="badge bg-success" id="orderStatusText"></span>
                </div>
            </div>

            <div class="container-xxl flex-grow-1 container-p-y">
              <div class="row">
                <div class="col-xl-12 col-12 mb-4">
                  <div class="card">
                    <div class="card-header header-elements">
                      <h5 class="card-title mb-0">Calories Statistics</h5>
                      <div class="card-action-element ms-auto py-0">
                        <div class="dropdown">
                          <button
                            type="button"
                            class="btn dropdown-toggle px-0"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="mdi mdi-calendar-month-outline"></i>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                              <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">Today</a>
                            </li>
                            <li>
                              <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center"
                                >Yesterday</a
                              >
                            </li>
                            <li>
                              <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center"
                                >Last 7 Days</a
                              >
                            </li>
                            <li>
                              <hr class="dropdown-divider" />
                            </li>
                            <li>
                              <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center"
                                >Current Month</a
                              >
                            </li>
                            <li>
                              <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center"
                                >Last Month</a
                              >
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                    <div class="card-body">
                      <canvas id="barChart" style="width:100%; height:200px;"></canvas>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div id="mealGroupsContainer"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let barChartInstance = null;
let mealTypeBreakdown = {};


document.addEventListener("DOMContentLoaded", function() {
    const orderId = window.location.pathname.split("/").pop();

    loadMealOrderDetails(orderId);
    loadDailyCalories("7days");
});

$(document).on("click", ".dropdown-item", function () {
    let label = $(this).text().trim().toLowerCase();

    let range = "";

    switch (label) {
        case "today":
            range = "today";
            break;
        case "yesterday":
            range = "yesterday";
            break;
        case "last 7 days":
            range = "7days";
            break;
        case "current month":
            range = "current_month";
            break;
        case "last month":
            range = "last_month";
            break;
        default:
            range = "7days";
    }

    loadDailyCalories(range);
});


async function loadMealOrderDetails(orderId) {
    showLoader();
    try {
        const res = await axios.get(`/user/get/meal-order/details/${orderId}`);
        if (res.status === 200 && res.data.status === 'success') {
            const order = res.data.order;
            const groupedItems = groupByMealType(order.items);

            document.getElementById('mealDateText').textContent = order.meal_date;
            document.getElementById('orderStatusText').textContent = order.status;

            renderGroupedMealItems(groupedItems);
        } else {
            errorToast(res.data.message || "Unable to load meal order details.");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function groupByMealType(items) {
    return items.reduce((acc, item) => {
        const mealType = item.meal_type?.name || 'Other';
        if (!acc[mealType]) acc[mealType] = [];
        acc[mealType].push(item);
        return acc;
    }, {});
}

function renderGroupedMealItems(groups) {
    const container = document.getElementById('mealGroupsContainer');
    container.innerHTML = '';

    Object.entries(groups).forEach(([mealType, items]) => {
        const groupHTML = `
            <div class="mb-4">
                <h5 class="text-primary mb-3 text-capitalize border-bottom pb-2">
                    <i class="mdi mdi-food-variant me-1"></i> ${mealType}
                </h5>
                <div class="row g-3">
                    ${items.map(item => {
                        // Title Case variable
                        let productName = (item.product?.name || 'Unnamed Product')
                            .split(' ')
                            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                            .join(' ');

                        return `
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 hover-card">
                                <div class="card-body d-flex flex-column">
                                    <div class="text-center mb-3">
                                        <img src="/upload/product/medium/${item.product?.image || 'no_image.jpg'}"
                                             alt="${productName}"
                                             class="img-fluid rounded"
                                             style="max-height: 150px; object-fit: cover;">
                                    </div>
                                    <h6 class="fw-bold text-dark text-center">${productName}</h6>
                                    <p class="text-muted text-center mb-2">Qty: ${item.quantity}</p>
                                    <div class="d-flex justify-content-center mt-auto">
                                        <button class="btn btn-sm btn-outline-danger deleteBtn"
                                                data-id="${item.id}">
                                            <i class="mdi mdi-trash-can-outline me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', groupHTML);
    });

    attachEventListeners();
}

function attachEventListeners() {
    $('.deleteBtn').on('click', function () {
        let id = $(this).data('id');
        $("#deleteMealItemID").val(id);
        $("#delete-meal-item-modal").modal('show');
    });
}

function handleError(error) {
    let message = "An unexpected error occurred.";
    if (error.response) {
        const { status, data } = error.response;
        switch (status) {
            case 500:
                message = data?.error || "Internal server error. Please try again later.";
                break;
            case 404:
                message = data?.message || "Data not found.";
                break;
            default:
                message = data?.message || "Something went wrong.";
        }
    } else if (error.request) {
        message = "No response from the server. Please check your internet connection.";
    } else {
        message = error.message;
    }
    errorToast(message);
}


async function loadDailyCalories(range) {
    try {
        showLoader();
        const res = await axios.get(`/user/get/calories/history?range=${range}`);

        if (res.data.status === "success") {
            mealTypeBreakdown = { ...res.data.meal_type_breakdown };
            renderBarChart(res.data.dates, res.data.calories, res.data.calories_unit, res.data.total_calories_sum);

        } else {
            errorToast(res.data.message);
        }
    } catch (err) {
        handleError(err);
    } finally {
        hideLoader();
    }
}



function renderBarChart(labels, data, unit, totalCalories) {
    // 💥 FULL RESET (Fix for Yesterday / Today showing extra days)
    mealTypeBreakdown = mealTypeBreakdown || {};

    const ctx = document.getElementById("barChart").getContext("2d");

    // Destroy previous chart completely
    if (barChartInstance) {
        barChartInstance.destroy();
        barChartInstance = null;
    }

    barChartInstance = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [{
                label: `Total Calories: ${totalCalories} (${unit})`,
                data: data,
                backgroundColor: "rgba(54, 162, 235, 0.6)",
                borderColor: "rgba(54, 162, 235, 1)",
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {

                            let date = context.label; 
                            let mealTypes = mealTypeBreakdown[date] || {};
                            let lines = [];

                            // Total calories
                            lines.push(`Total: ${context.parsed.y} ${unit}`);

                            // Meal type details
                            Object.entries(mealTypes).forEach(([type, cal]) => {
                                lines.push(`${type}: ${cal} ${unit}`);
                            });

                            return lines;
                        }
                    }
                }
            }
        }
    });
}



</script>

<style>
.hover-card {
    transition: all 0.3s ease;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}
</style>

@endsection