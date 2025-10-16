<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <a href="{{ route('clients') }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="me-1">
                            <p class="text-heading mb-2">Total Clients</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-2 me-1 display-6" id="total-clients">0</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-primary rounded">
                                <div class="mdi mdi-account-outline mdi-24px"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <a href="{{ route('customers') }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="me-1">
                            <p class="text-heading mb-2">Total Customers</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-2 me-1 display-6" id="total-customers">0</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-danger rounded">
                                <div class="mdi mdi-account-plus-outline mdi-24px scaleX-n1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <a href="{{ route('products') }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="me-1">
                            <p class="text-heading mb-2">Total Items</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-2 me-1 display-6" id="total-items">0</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-success rounded">
                                <div class="mdi mdi-account-check-outline mdi-24px"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <a href="{{ route('admin.orders') }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="me-1">
                            <p class="text-heading mb-2">Total Orders</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-2 me-1 display-6" id="total-orders">0</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-warning rounded">
                                <div class="mdi mdi-account-search mdi-24px"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <a href="{{ route('admin.complaints') }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="me-1">
                            <p class="text-heading mb-2">Total Complaints</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-2 me-1 display-6" id="total-complaints">0</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-warning rounded">
                                <div class="mdi mdi-account-search mdi-24px"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        getList();
    });

    async function getList() {
        showLoader();
        try {
            let res = await axios.get("/admin/total/information");
            if (res.status === 200 && res.data.status === 'success') {
                document.getElementById('total-clients').innerText = res.data.totalClients;
                document.getElementById('total-customers').innerText = res.data.totalCustomers;
                document.getElementById('total-items').innerText = res.data.totalProducts;
                document.getElementById('total-orders').innerText = res.data.totalOrders;
                document.getElementById('total-complaints').innerText = res.data.totalComplaints;
            } else {
                errorToast(res.data.message || "Failed to fetch information count.");
            }
        } catch (error) {
            handleError(error);
        }finally {
            hideLoader();
        }
    }


    function handleError(error) {
        if (error.response) {
            if (error.response.status === 500) {
                errorToast(error.response.data.error || "An internal server error occurred.");
            } else {
                errorToast("Request failed!");
            }
        } else {
            errorToast("Request failed!");
        }
    }

</script>