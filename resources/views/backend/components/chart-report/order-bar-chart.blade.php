<div class="row">
  <div class="col-xl-12 col-12 mb-4">
    <div class="card">
      <div class="card-header header-elements">
        <h5 id="chart-title" class="card-title mb-0">Order Statistics</h5>
        <div class="card-action-element ms-auto py-0">
          <div class="dropdown">
            <button type="button" class="btn dropdown-toggle px-0" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="mdi mdi-calendar-month-outline"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a href="javascript:void(0);" class="dropdown-item filter-option" data-filter="today">Today</a></li>
              <li><a href="javascript:void(0);" class="dropdown-item filter-option" data-filter="yesterday">Yesterday</a></li>
              <li><a href="javascript:void(0);" class="dropdown-item filter-option" data-filter="last_7_days">Last 7 Days</a></li>
              <li><a href="javascript:void(0);" class="dropdown-item filter-option" data-filter="last_30_days">Last 30 Days</a></li>
              <li><hr class="dropdown-divider" /></li>
              <li><a href="javascript:void(0);" class="dropdown-item filter-option" data-filter="current_month">Current Month</a></li>
              <li><a href="javascript:void(0);" class="dropdown-item filter-option" data-filter="last_month">Last Month</a></li>
              <li><a href="javascript:void(0);" class="dropdown-item filter-option" data-filter="weekly">Weekly</a></li> <!-- Added weekly filter -->
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

    async function fetchChartData(filter = 'last_7_days') {
        try {
            const response = await axios.get('/admin/bar/chart/order/information', {
                params: { filter: filter }
            });
            const data = response.data.data;
            console.log('Chart Data:', data);

            if (!data.orders || data.orders.length === 0) {
                console.warn("No data available.");
                errorToast("No orders found for the selected period.");
                return;
            }
            updateChartTitle(filter);
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

    function updateChartTitle(filter) {
        const titleElement = document.getElementById("chart-title");

        switch (filter) {
            case 'today':
                titleElement.textContent = "Today's Order Statistics";
                break;
            case 'yesterday':
                titleElement.textContent = "Yesterday's Order Statistics";
                break;
            case 'last_7_days':
                titleElement.textContent = "Last 7 Days Order Statistics";
                break;
            case 'last_30_days':
                titleElement.textContent = "Last 30 Days Order Statistics";
                break;
            case 'current_month':
                titleElement.textContent = "Current Month's Order Statistics";
                break;
            case 'last_month':
                titleElement.textContent = "Last Month's Order Statistics";
                break;
            case 'weekly':
                titleElement.textContent = "Weekly Order Statistics";
                break;
            default:
                titleElement.textContent = "Order Statistics";
        }
    }


    function handleError(error) {
        console.error("Error Details:", error);

        if (error.response) {
            const status = error.response.status;
            const message = error.response.data.message || "An error occurred.";

            if (status === 500) {
                errorToast("Internal Server Error: " + message);
            } else if (status === 404) {
                errorToast("No orders found for the selected period.");
            } else {
                errorToast("Error fetching data: " + message);
            }
        } else if (error.request) {
            errorToast("No response received. Please try again.");
        } else {
            errorToast("Failed to load data. Check your connection.");
        }
    }

    document.querySelectorAll('.filter-option').forEach(item => {
        item.addEventListener('click', function () {
            const filterType = this.getAttribute('data-filter');
            fetchChartData(filterType);
        });
    });

    fetchChartData(); // Load default data (Last 7 Days)
});
</script>


