<div class="container-fluid py-3">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="mb-0">Meal Details For - <span id="mealDate"></span></h5>
            <a href="{{ route('client.meal.orders') }}" class="btn btn-light btn-sm">Back To Meal Orders</a>
        </div>

        <div class="card-body">
            <div class="mb-3">
                <h6>Customer: <span id="customerName">Loading...</span></h6>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle" id="mealOrderDetailsTable">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Sl</th>
                            <th>Meal Type</th>
                            <th>Meal Name</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody id="mealOrderDetailsBody">
                        <tr>
                            <td colspan="6" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                <p><strong>Subtotal:</strong> $<span id="subtotal">0.00</span></p>
                <p><strong>Tax:</strong> $<span id="tax">0.00</span></p>
                <p><strong>Total:</strong> $<span id="total">0.00</span></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    fetchOrderDetails();
});

async function fetchOrderDetails() {
    let url = window.location.pathname;
    let segments = url.split('/');
    let orderId = segments[segments.length - 1];

    showLoader();
    try {
        let res = await axios.get("/client/get/meal-order/details/" + orderId);
        const tbody = document.getElementById("mealOrderDetailsBody");
        const mealDateSpan = document.getElementById("mealDate");
        const customerNameSpan = document.getElementById("customerName");

        tbody.innerHTML = "";

        if(res.status === 200 && res.data.status === 'success') {
            const data = res.data.data;
            mealDateSpan.textContent = data.meal_date;
            customerNameSpan.textContent = data.customer_name || 'N/A';

            let index = 1;
            data.meals.forEach(meal => {

                let mealTypeName = meal.meal_type_name
                    ? meal.meal_type_name.charAt(0).toUpperCase() + meal.meal_type_name.slice(1)
                    : 'N/A';
                    
                meal.products.forEach(product => {

                    let productName = (product.name ?? '').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');

                    let row = `
                        <tr>
                            <td>${index}</td>
                            <td>${mealTypeName}</td>
                            <td class="text-start">
                                <img src="${product.image ?? '/upload/no_image.jpg'}" alt="${product.name}" class="img-thumbnail" style="width:50px; height:50px; object-fit:cover; margin-right:10px;">
                                ${productName}
                            </td>
                            <td>${product.quantity ?? 1}</td>
                            <td>$${parseFloat(product.unit_price ?? 0).toFixed(2)}</td>
                            <td>$${parseFloat(product.total_price ?? 0).toFixed(2)}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                    index++;
                });
            });

            // Payment summary
            document.getElementById('subtotal').textContent = parseFloat(data.subtotal ?? 0).toFixed(2);
            document.getElementById('tax').textContent = parseFloat(data.tax ?? 0).toFixed(2);
            document.getElementById('total').textContent = parseFloat(data.payable_amount ?? 0).toFixed(2);

        } else {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${res.data.message || 'No details found.'}</td></tr>`;
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
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
</script>

<style>
#mealOrderDetailsTable tbody tr:hover {
    background-color: #f1f1f1;
    transition: background-color 0.3s ease;
}

#mealOrderDetailsTable th {
    text-align: center;
}

#mealOrderDetailsTable td {
    vertical-align: middle;
    text-align: center;
}

#mealOrderDetailsTable td.text-start {
    text-align: left;
    display: flex;
    align-items: center;
}
</style>
