<div class="container-xxl flex-grow-1 container-p-y px-0">

    {{-- ===== Header ===== --}}
    <div class="nd-hero text-white rounded-4 p-4 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="nd-hero-icon"><i class="mdi mdi-truck-fast-outline"></i></span>
            <div>
                <h5 class="fw-bold mb-1">Delivery Request</h5>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge bg-white text-dark" id="order-tracking">—</span>
                    <span id="status-badge"></span>
                </div>
            </div>
        </div>
        <a href="{{ route('delivery.notifications') }}" class="btn btn-light rounded-pill px-3">
            <i class="mdi mdi-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Loading placeholder --}}
    <div id="loading-placeholder" class="text-center py-5">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
        <p class="mt-2 text-muted">Loading delivery details...</p>
    </div>

    <div id="notification-details-container" class="d-none">

        {{-- ===== Accept CTA (delivery_person_id null) ===== --}}
        <div id="accept-order-section" class="d-none">
            <div class="nd-cta rounded-4 p-4 mb-4 text-center">
                <span class="nd-cta-bell mb-2"><i class="mdi mdi-bell-ring-outline"></i></span>
                <h5 class="fw-bold mb-1">This delivery is up for grabs</h5>
                <p class="text-muted mb-2">Accept before another rider claims it.</p>
                <p class="small text-muted mb-3" id="accept-deadline-wrap" style="display:none;">
                    <i class="mdi mdi-clock-alert-outline me-1 text-warning"></i>
                    Accept before <strong id="accept-deadline-display"></strong>
                </p>
                <button type="button" class="btn nd-accept-btn rounded-pill px-5 py-2" onclick="showAcceptConfirmation()">
                    <i class="mdi mdi-check-circle-outline me-1"></i> Accept Order
                </button>
            </div>
        </div>

        {{-- ===== Assigned banner (delivery_person_id set) ===== --}}
        <div id="delivery-person-section" class="d-none">
            <div class="alert nd-assigned rounded-4 d-flex align-items-center gap-3 mb-4">
                <span class="nd-assigned-icon"><i class="mdi mdi-account-check-outline"></i></span>
                <div>
                    <div class="fw-bold" id="delivery-person-name">Delivery Person Assigned</div>
                    <small class="text-muted">Rider ID: <span id="delivery-person-id"></span></small>
                </div>
                <span class="badge bg-success ms-auto"><i class="mdi mdi-shield-check me-1"></i>Assigned</span>
            </div>
        </div>

        {{-- ===== Stat tiles ===== --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="nd-stat nd-stat-distance h-100">
                    <span class="nd-stat-ico"><i class="mdi mdi-map-marker-distance"></i></span>
                    <div class="nd-stat-label">Distance</div>
                    <div class="nd-stat-value"><span id="stat-distance">0</span> km</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="nd-stat nd-stat-charge h-100">
                    <span class="nd-stat-ico"><i class="mdi mdi-cash"></i></span>
                    <div class="nd-stat-label">Delivery Charge</div>
                    <div class="nd-stat-value" id="stat-charge">£0.00</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="nd-stat nd-stat-meal h-100">
                    <span class="nd-stat-ico"><i class="mdi mdi-silverware-fork-knife"></i></span>
                    <div class="nd-stat-label">Meal Type</div>
                    <div class="nd-stat-value" id="stat-mealtype">—</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="nd-stat nd-stat-date h-100">
                    <span class="nd-stat-ico"><i class="mdi mdi-calendar-outline"></i></span>
                    <div class="nd-stat-label">Delivery Date</div>
                    <div class="nd-stat-value" id="stat-date">—</div>
                </div>
            </div>
        </div>

        {{-- ===== Delivery Actions (hidden when delivered) ===== --}}
        <div id="delivery-actions-card" class="card border-0 shadow-sm rounded-4 mb-4 nd-action-card d-none">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h6 class="fw-bold mb-0"><i class="mdi mdi-truck-check-outline me-2 text-primary"></i>Delivery Actions</h6>
                    <div id="action-buttons" class="d-flex gap-2 flex-wrap"></div>
                </div>
                <div id="notification-message-wrap" class="d-none">
                    <hr class="my-3">
                    <div class="d-flex flex-column gap-2 small">
                        <div>
                            <span class="text-muted"><i class="mdi mdi-pound text-primary me-1"></i>Order Tracking:</span>
                            <span class="nd-hl ms-1" id="ready-order-tracking">—</span>
                        </div>
                        <div>
                            <span class="text-muted"><i class="mdi mdi-clock-outline text-primary me-1"></i>Pickup Time:</span>
                            <span class="fw-semibold ms-1" id="ready-pickup-time">—</span>
                        </div>
                        <div>
                            <span class="text-muted"><i class="mdi mdi-message-text-outline text-primary me-1"></i>Message:</span>
                            <span id="notification-message" class="ms-1"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- ===== Pickup & Drop-off (stacked) ===== --}}
            <div class="col-lg-5 d-flex flex-column gap-3">
                <div class="nd-addr nd-addr-pickup rounded-4 p-3 flex-fill">
                    <div class="nd-addr-head text-danger"><i class="mdi mdi-storefront-outline"></i> Pickup — Restaurant</div>
                    <div class="fw-bold" id="client-name">—</div>
                    <div class="small text-muted mb-1"><i class="mdi mdi-phone-outline me-1"></i><span id="client-mobile">—</span></div>
                    <div class="small text-muted" id="client-address">—</div>
                </div>
                <div class="nd-addr nd-addr-drop rounded-4 p-3 flex-fill">
                    <div class="nd-addr-head text-success"><i class="mdi mdi-map-marker-radius-outline"></i> Drop-off — Customer</div>
                    <div class="fw-bold" id="customer-name">—</div>
                    <div class="small text-muted mb-1"><i class="mdi mdi-phone-outline me-1"></i><span id="customer-mobile">—</span></div>
                    <div class="small text-muted mb-1"><i class="mdi mdi-email-outline me-1"></i><span id="customer-email">—</span></div>
                    <div class="small text-muted" id="delivery-address">—</div>
                </div>
            </div>

            {{-- ===== Map ===== --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-3">
                        <h6 class="fw-bold mb-0"><i class="mdi mdi-map-outline me-2 text-primary"></i>Route Map</h6>
                        <a href="#" id="directions-btn" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill" style="display:none;">
                            <i class="mdi mdi-directions me-1"></i>Get Directions
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div id="locationMap" style="height:380px; width:100%;"></div>
                        <div id="map-empty" class="text-center text-muted py-5 d-none">
                            <i class="mdi mdi-map-marker-off-outline" style="font-size:2.5rem;"></i>
                            <p class="mb-0 mt-2">Map locations not available for this order.</p>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 d-flex justify-content-around small pb-3">
                        <span><i class="mdi mdi-storefront mdi-18px text-danger me-1"></i>Restaurant</span>
                        <span><i class="mdi mdi-home-map-marker mdi-18px text-success me-1"></i>Customer</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Order Items ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-3">
                <h6 class="fw-bold mb-0"><i class="mdi mdi-food-outline me-2 text-primary"></i>Order Items</h6>
                <span class="badge bg-label-primary rounded-pill">Total: <span id="total-items">0</span></span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Meal Time</th>
                            </tr>
                        </thead>
                        <tbody id="order-items-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', async function () {
    await loadNotificationDetails();
});

let notificationData      = null;
let deliveryChargeLedgerId = null;
let currentDeliveryStatus = null;
let notificationId        = null;
let ndMap                 = null;

function fmtGBP(v) {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(parseFloat(v) || 0);
}

// Title Case for names (rider, meal type, product, customer, restaurant)
function toTitleCase(s) {
    if (s === null || s === undefined || s === '') return '';
    return String(s).toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
}

// e.g. "2026-06-05T18:00:00.000000Z" -> "05-June-2026 6:00 PM"
function fmtDateTimeFull(value) {
    if (!value) return 'N/A';
    const d = new Date(value);
    if (isNaN(d.getTime())) return value;
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const day   = String(d.getUTCDate()).padStart(2, '0');
    const month = months[d.getUTCMonth()];
    const year  = d.getUTCFullYear();
    let h = d.getUTCHours();
    const m = String(d.getUTCMinutes()).padStart(2, '0');
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${day}-${month}-${year} ${h}:${m} ${ampm}`;
}

// e.g. "2026-06-05T18:00:00.000000Z" -> "05-June-2026"
function fmtDateOnly(value) {
    if (!value) return 'N/A';
    const d = new Date(value);
    if (isNaN(d.getTime())) return value;
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    return `${String(d.getUTCDate()).padStart(2, '0')}-${months[d.getUTCMonth()]}-${d.getUTCFullYear()}`;
}

async function loadNotificationDetails() {
    showLoader();
    try {
        const segments = window.location.pathname.split('/');
        notificationId = segments[segments.length - 1];

        const response = await axios.get(`/delivery/get/notification/details/${notificationId}`);

        if (response.status === 200 && response.data.status === 'success') {
            notificationData = response.data.data;

            document.getElementById('loading-placeholder').classList.add('d-none');
            document.getElementById('notification-details-container').classList.remove('d-none');

            deliveryChargeLedgerId = notificationData.data?.delivery_charge_ledger_id;
            currentDeliveryStatus  = notificationData.data?.delivery_status || 'pending';

            populateNotificationDetails();
            setupActionButtons();
            showDeliveryPersonSection();
            renderMap();
        } else {
            errorToast(response.data.message || 'Failed to fetch notification details.');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function showAcceptConfirmation() {
    Swal.fire({
        title: 'Accept Delivery?',
        text: 'Are you sure you want to accept this delivery?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Accept It!',
        cancelButtonText: 'Cancel',
        customClass: { confirmButton: 'btn btn-success me-2', cancelButton: 'btn btn-danger' },
        buttonsStyling: false
    }).then((result) => { if (result.isConfirmed) acceptDelivery(); });
}

function showDeliveryPersonSection() {
    const acceptSection   = document.getElementById('accept-order-section');
    const assignedSection = document.getElementById('delivery-person-section');
    const deliveryPersonId = notificationData.data?.delivery_person_id;
    const acceptDeadline   = notificationData.data?.accept_deadline;

    if (deliveryPersonId === null || deliveryPersonId === undefined) {
        acceptSection.classList.remove('d-none');
        assignedSection.classList.add('d-none');
        if (acceptDeadline) {
            document.getElementById('accept-deadline-wrap').style.display = 'block';
            document.getElementById('accept-deadline-display').textContent =
                new Date(acceptDeadline).toLocaleString('en-GB', {
                    weekday: 'short', day: 'numeric', month: 'short',
                    hour: '2-digit', minute: '2-digit', hour12: true
                });
        }
    } else {
        acceptSection.classList.add('d-none');
        assignedSection.classList.remove('d-none');
        document.getElementById('delivery-person-name').textContent =
            toTitleCase(notificationData.data?.delivery_person?.name) || 'Delivery Person Assigned';
        document.getElementById('delivery-person-id').textContent = deliveryPersonId;
    }
}

async function acceptDelivery() {
    try {
        showLoader();
        const res = await axios.post('/delivery/accept/meal/delivery', {
            delivery_charge_ledger_id: deliveryChargeLedgerId
        });
        if (res.status === 200 && res.data.status === 'success') {
            Swal.fire({
                title: 'Success!', text: res.data.message || 'Delivery accepted successfully',
                icon: 'success', confirmButtonText: 'OK',
                customClass: { confirmButton: 'btn btn-success' }, buttonsStyling: false
            }).then(() => { setTimeout(() => window.location.reload(), 800); });
        } else {
            Swal.fire({ title: 'Error!', text: res.data.message || 'Failed to accept delivery', icon: 'error',
                confirmButtonText: 'OK', customClass: { confirmButton: 'btn btn-danger' }, buttonsStyling: false });
        }
    } catch (error) {
        const msg = error.response?.data?.message || 'This order may have already been accepted.';
        Swal.fire({ title: 'Error!', text: msg, icon: 'error', confirmButtonText: 'OK',
            customClass: { confirmButton: 'btn btn-danger' }, buttonsStyling: false });
    } finally { hideLoader(); }
}

function populateNotificationDetails() {
    if (!notificationData) return;
    const data            = notificationData.data || {};
    const deliveryDetails = data.delivery_details || {};
    const clientAddress   = data.client_address || {};
    const deliveryAddress = data.delivery_address || {};
    const customerDetails = data.customer_details || {};

    document.getElementById('order-tracking').textContent = data.order_number || 'N/A';

    // Stat tiles — Delivery Date uses the date + the actual delivery (meal) time
    document.getElementById('stat-mealtype').textContent = toTitleCase(deliveryDetails.meal_type) || 'N/A';
    document.getElementById('stat-date').innerHTML       =
        fmtDateOnly(deliveryDetails.delivery_date) +
        (deliveryDetails.meal_time ? ` <span class="nd-hl">${formatTime(deliveryDetails.meal_time)}</span>` : '');
    document.getElementById('stat-distance').textContent = deliveryDetails.distance_km ?? '0';
    document.getElementById('stat-charge').textContent   = fmtGBP(deliveryDetails.delivery_charge);

    // Status badge (hero)
    document.getElementById('status-badge').innerHTML  = statusBadge(data.delivery_status || 'pending');

    // Restaurant
    document.getElementById('client-name').textContent   = toTitleCase(data.client_name) || 'N/A';
    document.getElementById('client-mobile').textContent = data.client_mobile || 'N/A';
    document.getElementById('client-address').textContent = joinAddress(clientAddress);

    // Customer
    document.getElementById('customer-name').textContent    = toTitleCase(customerDetails.name) || 'N/A';
    document.getElementById('customer-mobile').textContent  = customerDetails.mobile || 'N/A';
    document.getElementById('customer-email').textContent   = customerDetails.email || 'N/A';
    document.getElementById('delivery-address').textContent = joinAddress(deliveryAddress);

    // Ready-for-pickup block: order tracking, client-set pickup time, message
    document.getElementById('ready-order-tracking').textContent = data.order_tracking || 'N/A';
    document.getElementById('ready-pickup-time').textContent    = data.pickup_time || 'Not set';
    document.getElementById('notification-message').textContent =
        data.client_name
            ? `New delivery order from ${toTitleCase(data.client_name)}`
            : (notificationData.message || '—');
    applyDeliveryActionsVisibility(data.delivery_status || 'pending');

    // Items
    const items = data.items || [];
    const body = document.getElementById('order-items-body');
    body.innerHTML = '';
    items.forEach((item, i) => {
        const img = item.product_image ? `/upload/product/small/${item.product_image}` : '/upload/no_image.jpg';
        body.insertAdjacentHTML('beforeend', `
            <tr>
                <td>${i + 1}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="${img}" class="rounded" style="width:42px;height:42px;object-fit:cover;border:1px solid #eef0f4;"
                             onerror="this.onerror=null;this.src='/upload/no_image.jpg';">
                        <span class="fw-semibold">${toTitleCase(item.product_name) || 'N/A'}</span>
                    </div>
                </td>
                <td><span class="badge bg-label-secondary">${item.quantity || 1}</span></td>
                <td>${item.meal_time ? formatTime(item.meal_time) : 'N/A'}</td>
            </tr>`);
    });
    document.getElementById('total-items').textContent = items.length;
}

// Show/hide the Delivery Actions card + its message based on delivery status
function applyDeliveryActionsVisibility(status) {
    const card    = document.getElementById('delivery-actions-card');
    const msgWrap = document.getElementById('notification-message-wrap');

    // Delivered → hide the whole section
    if (status === 'delivered') {
        card.classList.add('d-none');
        return;
    }
    card.classList.remove('d-none');

    // Message only shows when the order is ready for pickup
    msgWrap.classList.toggle('d-none', status !== 'ready_for_pickup');
}

function joinAddress(a) {
    return [a.address1, a.address2, a.city, a.county, a.country, a.zip_code]
        .filter(Boolean).join(', ') || 'Address not available';
}

function statusBadge(status) {
    const map = {
        pending:          'bg-secondary',      accept_order:    'bg-info',
        preparing:        'bg-primary',        ready_for_pickup:'bg-warning text-dark',
        picked_up:        'bg-warning text-dark', on_the_way:   'bg-primary',
        arrived:          'bg-info',           delivered:       'bg-success',
        cancelled:        'bg-danger',         due:             'bg-warning text-dark',
        paid:             'bg-success'
    };
    const label = status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    return `<span class="badge ${map[status] || 'bg-secondary'}">${label}</span>`;
}

/* ===== Leaflet map ===== */
function renderMap() {
    const data = notificationData.data || {};
    const c = data.client_address || {};
    const d = data.delivery_address || {};

    const cLat = parseFloat(c.latitude),  cLng = parseFloat(c.longitude);
    const dLat = parseFloat(d.latitude),  dLng = parseFloat(d.longitude);
    const hasC = !isNaN(cLat) && !isNaN(cLng);
    const hasD = !isNaN(dLat) && !isNaN(dLng);

    if (!hasC && !hasD) {
        document.getElementById('locationMap').classList.add('d-none');
        document.getElementById('map-empty').classList.remove('d-none');
        return;
    }

    ndMap = L.map('locationMap', { scrollWheelZoom: false });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap', maxZoom: 19
    }).addTo(ndMap);

    const pin = (color, icon) => L.divIcon({
        className: '',
        html: `<div style="background:${color};width:34px;height:34px;border-radius:50% 50% 50% 0;
               transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;
               box-shadow:0 2px 6px rgba(0,0,0,.35);">
               <i class="mdi ${icon}" style="transform:rotate(45deg);color:#fff;font-size:1.05rem;"></i></div>`,
        iconSize: [34, 34], iconAnchor: [17, 34], popupAnchor: [0, -34]
    });

    const bounds = [];
    if (hasC) {
        L.marker([cLat, cLng], { icon: pin('#ef4444', 'mdi-storefront') })
            .addTo(ndMap).bindPopup('<b>Restaurant</b><br>' + (data.client_name || ''));
        bounds.push([cLat, cLng]);
    }
    if (hasD) {
        L.marker([dLat, dLng], { icon: pin('#22c55e', 'mdi-home-map-marker') })
            .addTo(ndMap).bindPopup('<b>Customer</b><br>' + (data.customer_details?.name || ''));
        bounds.push([dLat, dLng]);
    }
    if (hasC && hasD) {
        L.polyline([[cLat, cLng], [dLat, dLng]], { color: '#6366f1', weight: 3, dashArray: '6,8' }).addTo(ndMap);
        ndMap.fitBounds(bounds, { padding: [40, 40] });

        const btn = document.getElementById('directions-btn');
        btn.href = `https://www.google.com/maps/dir/?api=1&origin=${cLat},${cLng}&destination=${dLat},${dLng}`;
        btn.style.display = 'inline-block';
    } else {
        ndMap.setView(bounds[0], 14);
    }

    // Ensure correct rendering inside the card
    setTimeout(() => ndMap.invalidateSize(), 300);
}

/* ===== Action buttons (status updates) ===== */
function setupActionButtons() {
    const box = document.getElementById('action-buttons');
    const data = notificationData.data || {};
    const status = data.delivery_status || 'pending';
    const assigned = data.delivery_person_id !== null && data.delivery_person_id !== undefined;

    let html = '';
    if (assigned) {
        if (['ready_for_pickup', 'picked_up', 'on_the_way', 'arrived'].includes(status)) {
            html = `<button type="button" class="btn btn-primary btn-lg rounded-pill px-4" onclick="handleUpdateDeliveryStatus()">
                        <i class="mdi mdi-progress-check me-1"></i> ${getStatusButtonLabel(status)}
                    </button>`;
        } else if (status === 'delivered') {
            html = `<button class="btn btn-success btn-lg rounded-pill px-4" disabled><i class="mdi mdi-check-circle me-1"></i>Delivered</button>`;
        } else if (status === 'cancelled') {
            html = `<button class="btn btn-danger btn-lg rounded-pill px-4" disabled><i class="mdi mdi-close-circle me-1"></i>Cancelled</button>`;
        } else if (status === 'accept_order') {
            html = `<button class="btn btn-info btn-lg rounded-pill px-4" disabled><i class="mdi mdi-account-check me-1"></i>Awaiting Preparation</button>`;
        } else if (status === 'preparing') {
            html = `<button class="btn btn-primary btn-lg rounded-pill px-4" disabled><i class="mdi mdi-chef-hat me-1"></i>Being Prepared</button>`;
        } else {
            html = '<p class="text-muted mb-0">No actions available for the current status.</p>';
        }
    } else {
        html = '<p class="text-muted mb-0">Accept the order to see available actions.</p>';
    }
    box.innerHTML = html;
}

function getNextDeliveryStatus(s) {
    return ({ ready_for_pickup: 'picked_up', picked_up: 'on_the_way', on_the_way: 'arrived', arrived: 'delivered' })[s] || null;
}
function getStatusButtonLabel(s) {
    return ({ ready_for_pickup: 'Mark as Picked Up', picked_up: 'Mark as On the Way',
              on_the_way: 'Mark as Arrived', arrived: 'Mark as Delivered' })[s] || 'Update Status';
}

async function handleUpdateDeliveryStatus() {
    try {
        const checkRes = await axios.post(`/delivery/check/delivery-status/${deliveryChargeLedgerId}`);
        if (checkRes.data.status !== 'success') { errorToast(checkRes.data.message || 'Failed to check delivery status.'); return; }

        const currentStatus = checkRes.data.data.delivery_status;
        const nextStatus = getNextDeliveryStatus(currentStatus);
        if (!nextStatus) { errorToast('No further status updates allowed.'); return; }

        const result = await Swal.fire({
            title: 'Are you sure?',
            text: `Update delivery status to "${getStatusButtonLabel(currentStatus)}".`,
            icon: 'question', showCancelButton: true,
            confirmButtonText: 'Yes, proceed!', cancelButtonText: 'Cancel'
        });
        if (!result.isConfirmed) return;

        showLoader();
        const updateRes = await axios.post(`/delivery/update/delivery-status/${deliveryChargeLedgerId}`, {
            delivery_status: nextStatus, notes: ''
        });
        if (updateRes.data.status === 'success') {
            successToast(updateRes.data.message);
            setTimeout(() => window.location.reload(), 1500);
        } else {
            errorToast(updateRes.data.message || 'Failed to update delivery status.');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function formatTime(t) {
    if (!t) return 'N/A';
    const p = t.split(':');
    if (p.length < 2) return t;
    const h = parseInt(p[0], 10), m = p[1];
    const ampm = h >= 12 ? 'PM' : 'AM';
    return `${h % 12 || 12}:${m} ${ampm}`;
}

function handleError(error) {
    let message = 'An unexpected error occurred';
    if (error.response) {
        message = error.response.data?.message || message;
    } else if (error.request) {
        message = 'No response received from the server.';
    } else {
        message = error.message || message;
    }
    errorToast(message);
}
</script>

<style>
.nd-hero {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 55%, #ec4899 100%);
}
.nd-hero-icon {
    width: 52px; height: 52px; border-radius: 14px; background: rgba(255,255,255,.18);
    display: flex; align-items: center; justify-content: center; font-size: 1.7rem;
}
.nd-cta { background: linear-gradient(135deg,#f5f3ff,#fdf2f8); border: 1px solid #ede9fe; }
.nd-cta-bell {
    width: 56px; height: 56px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; font-size: 1.7rem;
    animation: ndRing 1.6s ease-in-out infinite;
}
@keyframes ndRing { 0%,100%{transform:rotate(0)} 20%{transform:rotate(12deg)} 40%{transform:rotate(-10deg)} 60%{transform:rotate(6deg)} 80%{transform:rotate(-4deg)} }
.nd-accept-btn { background: linear-gradient(135deg,#16a34a,#22c55e); color:#fff; border:none; font-weight:700; }
.nd-accept-btn:hover { filter: brightness(.95); color:#fff; }
.nd-assigned { background:#ecfdf5; border:1px solid #d1fae5; }
.nd-assigned-icon { width:46px;height:46px;border-radius:12px;background:#22c55e;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem; }

.nd-stat { background:#fff; border:1px solid #eef0f4; border-radius:16px; padding:1rem 1.1rem; box-shadow:0 2px 10px rgba(0,0,0,.04); position:relative; }
.nd-stat-ico { position:absolute; top:1rem; right:1rem; width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.2rem; }
.nd-stat-label { font-size:.74rem; color:#64748b; font-weight:600; }
.nd-stat-value { font-size:1.4rem; font-weight:800; color:#0f172a; line-height:1.2; }
.nd-stat-distance .nd-stat-ico { background:#eef2ff; color:#6366f1; }
.nd-stat-charge   .nd-stat-ico { background:#ecfdf5; color:#16a34a; }
.nd-stat-meal     .nd-stat-ico { background:#fff7ed; color:#ea580c; }
.nd-stat-date     .nd-stat-ico { background:#fdf2f8; color:#db2777; }
/* Delivery Date is longer than the other stats — shrink, wrap, and keep clear of the icon */
.nd-stat-date .nd-stat-value { font-size:1.02rem; line-height:1.3; white-space:normal; padding-right:2.4rem; }

.nd-addr { background:#fff; border:1px solid #eef0f4; box-shadow:0 2px 10px rgba(0,0,0,.04); }
.nd-addr-pickup { border-left:4px solid #ef4444; }
.nd-addr-drop   { border-left:4px solid #22c55e; }
.nd-addr-head { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.03em; margin-bottom:.4rem; }

#locationMap { border-radius:0 0 0 0; z-index:1; }
.leaflet-container { font-family:inherit; }

.nd-action-card { border-left:4px solid #6366f1; background:linear-gradient(180deg,#fbfbff,#ffffff); }
.nd-action-card hr { border-color:#eef0f4; opacity:1; }

/* Highlight chip for emphasised values (delivery time, tracking number) */
.nd-hl { background:#eef2ff; color:#4f46e5; font-weight:700; padding:.05rem .4rem; border-radius:5px; letter-spacing:.02em; }
</style>