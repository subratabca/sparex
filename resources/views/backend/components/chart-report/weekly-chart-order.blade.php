<div class="row">
  <div class="col-xl-12 col-12 mb-4">
    <div class="card">
      <div class="card-header header-elements">
        <h5 id="orderStatsTitle" class="card-title mb-0">Order Statistics</h5>
        <div class="card-action-element ms-auto py-0">
          <div class="dropdown">
            <button type="button" class="btn dropdown-toggle px-0" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="mdi mdi-calendar-month-outline"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" id="dateFilter">
              <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" data-filter="today">Today</a></li>
              <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" data-filter="yesterday">Yesterday</a></li>
              <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" data-filter="last_7_days">Last 7 Days</a></li>
              <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" data-filter="last_30_days">Last 30 Days</a></li>
              <li><hr class="dropdown-divider" /></li>
              <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" data-filter="current_month">Current Month</a></li>
              <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" data-filter="last_month">Last Month</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card-body">
        <canvas id="myChart" class="chartjs" data-height="400"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let chartInstance = null;

    async function fetchChartData(filter = "last_7_days") {
        try {
            const response = await axios.get(`/admin/chart/order/information?filter=${filter}`);
            const data = response.data.data;
            console.log('Chart Data:', data);

            if (!data.orders || data.orders.length === 0) {
                console.warn("No data available for selected range.");
                errorToast("No orders found for the selected range.");
                return;
            }

            updateChart(data.labels, data.orders);
        } catch (error) {
            handleError(error);
        }
    }

    function updateChart(labels, orders) {
        const chartCanvas = document.getElementById("myChart");

        if (chartInstance !== null) {
            chartInstance.destroy();
        }

        const ctx = chartCanvas.getContext("2d");

        chartInstance = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    label: "Orders",
                    data: orders,
                    backgroundColor: "rgba(54, 162, 235, 0.6)",
                    borderColor: "rgba(54, 162, 235, 1)",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function handleError(error) {
        console.error("Error Details:", error);

        if (error.response) {
            const status = error.response.status;
            const message = error.response.data.message || "An error occurred.";

            if (status === 500) {
                errorToast("Internal Server Error: " + message);
            } else if (status === 404) {
                errorToast("No orders found for the selected range.");
            } else {
                errorToast("Error fetching orders: " + message);
            }
        } else if (error.request) {
            errorToast("No response received from the server. Please try again.");
        } else {
            errorToast("Failed to load chart data. Please check your connection.");
        }
    }

    function updateTitle(filter) {
        const titleElement = document.getElementById("orderStatsTitle");
        const filterMapping = {
            today: "Order Statistics - Today",
            yesterday: "Order Statistics - Yesterday",
            last_7_days: "Order Statistics - Last 7 Days",
            last_30_days: "Order Statistics - Last 30 Days",
            current_month: "Order Statistics - Current Month",
            last_month: "Order Statistics - Last Month"
        };

        titleElement.textContent = filterMapping[filter] || "Order Statistics";
    }

    document.getElementById("dateFilter").addEventListener("click", function (event) {
        const filter = event.target.getAttribute("data-filter");
        if (filter) {
            updateTitle(filter);
            fetchChartData(filter);
        }
    });

    fetchChartData(); 
});
</script>
