<div class="container-xxl flex-grow-1 container-p-y px-0">

    {{-- ===== Hero ===== --}}
    <div class="mo-hero text-white rounded-4 p-4 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="mo-hero-icon"><i class="mdi mdi-truck-fast-outline"></i></span>
            <div>
                <h5 class="fw-bold mb-1">Delivery Order Details</h5>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge bg-white text-dark" id="tracking-number">—</span>
                    <span id="delivery-status-badge" class="badge bg-light text-dark">—</span>
                </div>
            </div>
        </div>
        <a href="{{ route('delivery.meal.orders') }}" class="btn btn-light rounded-pill px-3">
            <i class="mdi mdi-arrow-left me-1"></i> Back to Orders
        </a>
    </div>

    {{-- ===== Action banner (status update) ===== --}}
    <div id="delivery-actions-row" class="mb-4" style="display:none;">
        <div class="mo-action rounded-4 p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="mo-action-icon"><i class="mdi mdi-progress-clock"></i></span>
                <div>
                    <div class="fw-bold text-success">Delivery Action Required</div>
                    <small class="text-muted" id="delivery-action-hint"></small>
                </div>
            </div>
            <button type="button" class="btn btn-success rounded-pill px-4" id="update-delivery-status-btn" onclick="handleUpdateDeliveryStatus()">
                <i class="mdi mdi-refresh me-1"></i> Update Status
            </button>
        </div>
    </div>

    {{-- ===== Stat tiles ===== --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="mo-stat mo-stat-distance h-100">
                <span class="mo-stat-ico"><i class="mdi mdi-map-marker-distance"></i></span>
                <div class="mo-stat-label">Distance</div>
                <div class="mo-stat-value" id="stat-distance">—</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="mo-stat mo-stat-charge h-100">
                <span class="mo-stat-ico"><i class="mdi mdi-cash"></i></span>
                <div class="mo-stat-label">Delivery Charge</div>
                <div class="mo-stat-value" id="delivery-charge">£0.00</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="mo-stat mo-stat-meal h-100">
                <span class="mo-stat-ico"><i class="mdi mdi-silverware-fork-knife"></i></span>
                <div class="mo-stat-label">Meal Type</div>
                <div class="mo-stat-value" id="meal-type">—</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="mo-stat mo-stat-date h-100">
                <span class="mo-stat-ico"><i class="mdi mdi-calendar-outline"></i></span>
                <div class="mo-stat-label">Delivery Date</div>
                <div class="mo-stat-value" id="delivery-date">—</div>
            </div>
        </div>
    </div>

    {{-- ===== Addresses (stacked) + map ===== --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-5 d-flex flex-column gap-3">
            <div class="mo-addr mo-addr-pickup rounded-4 p-3 flex-fill">
                <div class="mo-addr-head text-danger"><i class="mdi mdi-storefront-outline"></i> Pickup — Restaurant</div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <img id="restaurant-image" src="" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;">
                    <span class="fw-bold" id="restaurant-name">—</span>
                </div>
                <div class="small text-muted"><i class="mdi mdi-phone-outline me-1"></i><span id="restaurant-phone">—</span></div>
                <div class="small text-muted"><i class="mdi mdi-email-outline me-1"></i><span id="restaurant-email">—</span></div>
                <div class="small text-muted mt-1" id="restaurant-address">—</div>
            </div>
            <div class="mo-addr mo-addr-drop rounded-4 p-3 flex-fill">
                <div class="mo-addr-head text-success"><i class="mdi mdi-map-marker-radius-outline"></i> Drop-off — Customer</div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <img id="customer-image" src="" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;">
                    <span class="fw-bold" id="customer-name">—</span>
                </div>
                <div class="small text-muted"><i class="mdi mdi-phone-outline me-1"></i><span id="customer-phone">—</span></div>
                <div class="small text-muted"><i class="mdi mdi-email-outline me-1"></i><span id="customer-email">—</span></div>
                <div class="small text-muted mt-1" id="shipping-address">—</div>
                <div class="small text-muted">Recipient: <span id="shipping-name">—</span></div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-3">
                    <h6 class="fw-bold mb-0"><i class="mdi mdi-map-outline me-2 text-primary"></i>Route Map</h6>
                    <a href="#" id="directions-btn" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill" style="display:none;">
                        <i class="mdi mdi-directions me-1"></i>Get Directions
                    </a>
                </div>
                <div class="card-body p-0">
                    <div id="locationMap" style="height:400px;width:100%;"></div>
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

    {{-- ===== Items + history ===== --}}
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-3">
                    <h6 class="fw-bold mb-0"><i class="mdi mdi-food-outline me-2 text-primary"></i>Order Items</h6>
                    <span class="badge bg-label-primary rounded-pill">Total: <span id="total-items">0</span></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Product</th><th>Qty</th><th>Unit</th><th>Total</th><th>Meal Time</th></tr>
                            </thead>
                            <tbody id="order-items-table"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="fw-bold mb-0"><i class="mdi mdi-history me-2 text-primary"></i>Status History</h6>
                </div>
                <div class="card-body">
                    <div class="timeline" id="status-history">
                        <div class="text-center text-muted py-3">No status history available</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", loadDeliveryOrderDetails);

let deliveryData = null;
let moMap = null;

function toTitleCase(s) { return s ? s.toLowerCase().replace(/\b\w/g, c => c.toUpperCase()) : ''; }
function gbp(v) { return '£' + (v ?? '0.00'); }

// e.g. "2026-06-14" -> "14-June-2026"
function fmtDateOnly(value) {
    if (!value) return '—';
    const d = new Date(value);
    if (isNaN(d.getTime())) return value;
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    return `${String(d.getUTCDate()).padStart(2, '0')}-${months[d.getUTCMonth()]}-${d.getUTCFullYear()}`;
}
// e.g. "08:00:00" -> "8:00 AM"
function formatTime(t) {
    if (!t) return '';
    const p = String(t).split(':');
    if (p.length < 2) return t;
    const h = parseInt(p[0], 10), m = p[1];
    const ampm = h >= 12 ? 'PM' : 'AM';
    return `${h % 12 || 12}:${m} ${ampm}`;
}
function getImageUrl(type, file) {
    if (!file) return '/upload/no_image.jpg';
    return ({ restaurant: '/upload/client-profile/medium/', customer: '/upload/customer-profile/medium/', product: '/upload/product/small/' }[type]) + file;
}
function joinAddr(a) {
    return [a.address1, a.address2, a.city, a.county, a.country, a.zip_code].filter(Boolean).join(', ') || 'Address not available';
}

async function loadDeliveryOrderDetails() {
    try {
        showLoader();
        const segs = window.location.pathname.split('/');
        const response = await axios.get(`/delivery/get/meal-order/details/${segs[segs.length - 1]}`);

        if (response.status === 200 && response.data.status === 'success') {
            deliveryData = response.data.data;
            const di = deliveryData.delivery_info;
            const os = deliveryData.order_summary;

            document.getElementById('tracking-number').textContent = os.order_number || '—';

            // Delivery date & time come from the order item (meal_date / meal_time); time is highlighted
            const item0 = (deliveryData.order_items && deliveryData.order_items[0]) || null;
            const mealDate = item0 ? item0.meal_date : di.delivery_date;
            const mealTime = item0 ? item0.meal_time : null;
            document.getElementById('delivery-date').innerHTML =
                fmtDateOnly(mealDate) + (mealTime ? ` <span class="mo-hl">${formatTime(mealTime)}</span>` : '');

            const badge = document.getElementById('delivery-status-badge');
            badge.textContent = di.delivery_status_label || '—';
            badge.className = 'badge ' + getDeliveryBadgeClass(di.delivery_status);

            document.getElementById('delivery-charge').textContent = gbp(di.delivery_charge);
            document.getElementById('stat-distance').textContent = di.distance_km ? `${di.distance_km} km` : '—';
            document.getElementById('meal-type').textContent = toTitleCase(os.meal_type) || '—';

            // Restaurant
            const r = deliveryData.restaurant_details;
            if (r) {
                document.getElementById('restaurant-image').src = getImageUrl('restaurant', r.image);
                document.getElementById('restaurant-name').textContent = toTitleCase(r.name) || '—';
                document.getElementById('restaurant-email').textContent = r.email || '—';
                document.getElementById('restaurant-phone').textContent = r.mobile || '—';
                document.getElementById('restaurant-address').textContent = joinAddr(r);
            }

            // Customer
            const c = deliveryData.customer_details;
            if (c) {
                document.getElementById('customer-image').src = getImageUrl('customer', c.image);
                document.getElementById('customer-name').textContent = toTitleCase(c.name) || '—';
                document.getElementById('customer-email').textContent = c.email || '—';
                document.getElementById('customer-phone').textContent = c.mobile || '—';
                document.getElementById('shipping-name').textContent = toTitleCase(c.shipping_name) || '—';
                document.getElementById('shipping-address').textContent = joinAddr(c);
            }

            // Items
            const tbody = document.getElementById('order-items-table');
            tbody.innerHTML = '';
            const items = deliveryData.order_items || [];
            if (items.length) {
                items.forEach(item => {
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="${getImageUrl('product', item.product_image)}" class="rounded me-2" style="width:42px;height:42px;object-fit:cover;">
                                    <span class="fw-semibold">${toTitleCase(item.product_name) || '—'}</span>
                                </div>
                            </td>
                            <td><span class="badge bg-label-secondary">${item.quantity}</span></td>
                            <td>${gbp(item.unit_price)}</td>
                            <td class="fw-semibold">${gbp(item.total_price)}</td>
                            <td>${item.meal_time || '—'}</td>
                        </tr>`);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No items found</td></tr>';
            }
            document.getElementById('total-items').textContent = items.length;

            // History
            const hist = document.getElementById('status-history');
            hist.innerHTML = '';
            const hs = deliveryData.status_history || [];
            if (hs.length) {
                hs.forEach(h => {
                    hist.insertAdjacentHTML('beforeend', `
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content ms-3">
                                    <small class="text-muted d-block">${h.created_at}</small>
                                    <span class="badge ${getDeliveryBadgeClass(h.delivery_status)} mt-1">${h.status_label}</span>
                                    ${h.notes ? `<small class="d-block text-muted mt-1">${h.notes}</small>` : ''}
                                </div>
                            </div>
                        </div>`);
                });
            } else {
                hist.innerHTML = '<div class="text-center text-muted py-3">No status history available</div>';
            }

            setupDeliveryActionsCard(deliveryData);
            renderMap();
        } else {
            errorToast('Failed to load order details');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

/* ===== Map ===== */
function renderMap() {
    const r = deliveryData.restaurant_details || {};
    const c = deliveryData.customer_details || {};
    const rLat = parseFloat(r.latitude), rLng = parseFloat(r.longitude);
    const cLat = parseFloat(c.latitude), cLng = parseFloat(c.longitude);
    const hasR = !isNaN(rLat) && !isNaN(rLng);
    const hasC = !isNaN(cLat) && !isNaN(cLng);

    if (!hasR && !hasC) {
        document.getElementById('locationMap').classList.add('d-none');
        document.getElementById('map-empty').classList.remove('d-none');
        return;
    }

    moMap = L.map('locationMap', { scrollWheelZoom: false });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(moMap);

    const pin = (color, icon) => L.divIcon({
        className: '',
        html: `<div style="background:${color};width:34px;height:34px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.35);"><i class="mdi ${icon}" style="transform:rotate(45deg);color:#fff;font-size:1.05rem;"></i></div>`,
        iconSize: [34, 34], iconAnchor: [17, 34], popupAnchor: [0, -34]
    });

    const bounds = [];
    if (hasR) { L.marker([rLat, rLng], { icon: pin('#ef4444', 'mdi-storefront') }).addTo(moMap).bindPopup('<b>Restaurant</b><br>' + (r.name || '')); bounds.push([rLat, rLng]); }
    if (hasC) { L.marker([cLat, cLng], { icon: pin('#22c55e', 'mdi-home-map-marker') }).addTo(moMap).bindPopup('<b>Customer</b><br>' + (c.name || '')); bounds.push([cLat, cLng]); }

    if (hasR && hasC) {
        L.polyline([[rLat, rLng], [cLat, cLng]], { color: '#6366f1', weight: 3, dashArray: '6,8' }).addTo(moMap);
        moMap.fitBounds(bounds, { padding: [40, 40] });
        const btn = document.getElementById('directions-btn');
        btn.href = `https://www.google.com/maps/dir/?api=1&origin=${rLat},${rLng}&destination=${cLat},${cLng}`;
        btn.style.display = 'inline-block';
    } else {
        moMap.setView(bounds[0], 14);
    }
    setTimeout(() => moMap.invalidateSize(), 300);
}

/* ===== Status actions (unchanged) ===== */
function setupDeliveryActionsCard(data) {
    const row = document.getElementById('delivery-actions-row');
    const btn = document.getElementById('update-delivery-status-btn');
    const hint = document.getElementById('delivery-action-hint');
    const dp = data.delivery_person;
    const cur = data.delivery_info.delivery_status;
    const allowed = ['ready_for_pickup', 'picked_up', 'on_the_way', 'arrived'];

    if (dp && dp.id && allowed.includes(cur)) {
        const next = getNextDeliveryStatus(cur);
        btn.innerHTML = `<i class="mdi mdi-progress-check me-1"></i> ${getStatusButtonLabel(cur)}`;
        btn.dataset.nextStatus = next;
        btn.dataset.ledgerId = data.delivery_info.id;
        hint.textContent = `Current: ${data.delivery_info.delivery_status_label} → Next: ${getStatusLabel(next)}`;
        row.style.display = 'block';
    } else {
        row.style.display = 'none';
    }
}
function getNextDeliveryStatus(s) { return ({ ready_for_pickup: 'picked_up', picked_up: 'on_the_way', on_the_way: 'arrived', arrived: 'delivered' })[s] || null; }
function getStatusLabel(s) { return ({ pending: 'Pending', accept_order: 'Order Accepted', accept_delivery: 'Delivery Accepted', preparing: 'Preparing', ready_for_pickup: 'Ready for Pickup', picked_up: 'Picked Up', on_the_way: 'On the Way', arrived: 'Arrived', delivered: 'Delivered', cancelled: 'Cancelled' })[s] || s; }
function getStatusButtonLabel(s) { return ({ ready_for_pickup: 'Mark as Picked Up', picked_up: 'Mark as On the Way', on_the_way: 'Mark as Arrived', arrived: 'Mark as Delivered' })[s] || 'Update Status'; }

async function handleUpdateDeliveryStatus() {
    const btn = document.getElementById('update-delivery-status-btn');
    const ledgerId = btn.dataset.ledgerId, nextStatus = btn.dataset.nextStatus;
    if (!ledgerId || !nextStatus) { errorToast('Cannot determine next status.'); return; }

    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `Mark this delivery as "${getStatusLabel(nextStatus)}".`,
        icon: 'question', showCancelButton: true,
        confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, proceed!', cancelButtonText: 'Cancel'
    });
    if (!result.isConfirmed) return;

    showLoader();
    try {
        const res = await axios.post(`/delivery/update/delivery-status/${ledgerId}`, { delivery_status: nextStatus, notes: '' });
        if (res.data.status === 'success') {
            Swal.fire({ title: 'Updated!', text: 'Delivery status updated successfully.', icon: 'success', timer: 1500, showConfirmButton: false });
            setTimeout(() => window.location.reload(), 1500);
        } else {
            errorToast(res.data.message || 'Failed to update status.');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function getDeliveryBadgeClass(status) {
    return ({
        delivered: 'bg-success', arrived: 'bg-info', on_the_way: 'bg-primary',
        picked_up: 'bg-warning text-dark', ready_for_pickup: 'bg-warning text-dark',
        preparing: 'bg-primary', accept_delivery: 'bg-primary', accept_order: 'bg-info', pending: 'bg-secondary', cancelled: 'bg-danger'
    })[status] || 'bg-secondary';
}

// showLoader / hideLoader / successToast / errorToast / handleError are provided globally by config.js
</script>

<style>
.mo-hero { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 55%, #ec4899 100%); }
.mo-hero-icon { width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;font-size:1.7rem; }
.mo-action { background:#ecfdf5; border:1px solid #d1fae5; }
.mo-action-icon { width:46px;height:46px;border-radius:12px;background:#22c55e;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem; }
.mo-stat { background:#fff;border:1px solid #eef0f4;border-radius:16px;padding:1rem 1.1rem;box-shadow:0 2px 10px rgba(0,0,0,.04);position:relative; }
.mo-stat-ico { position:absolute;top:1rem;right:1rem;width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.2rem; }
.mo-stat-label { font-size:.74rem;color:#64748b;font-weight:600; }
.mo-stat-value { font-size:1.3rem;font-weight:800;color:#0f172a;line-height:1.2; }
.mo-stat-distance .mo-stat-ico { background:#eef2ff;color:#6366f1; }
.mo-stat-charge   .mo-stat-ico { background:#ecfdf5;color:#16a34a; }
.mo-stat-meal     .mo-stat-ico { background:#fff7ed;color:#ea580c; }
.mo-stat-date     .mo-stat-ico { background:#fdf2f8;color:#db2777; }
.mo-addr { background:#fff;border:1px solid #eef0f4;box-shadow:0 2px 10px rgba(0,0,0,.04); }
.mo-addr-pickup { border-left:4px solid #ef4444; }
.mo-addr-drop   { border-left:4px solid #22c55e; }
.mo-addr-head { font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em;margin-bottom:.5rem; }
.timeline { position:relative; padding-left:20px; }
.timeline::before { content:'';position:absolute;left:0;top:0;bottom:0;width:2px;background:#e9ecef; }
.timeline-item { position:relative; }
.timeline-marker { position:absolute;left:-24px;top:6px;width:10px;height:10px;border-radius:50%;background:#6366f1;border:2px solid #fff;box-shadow:0 0 0 3px rgba(99,102,241,.15); }
#locationMap { z-index:1; }
.leaflet-container { font-family:inherit; }
/* Highlight chip for the delivery time */
.mo-hl { background:#eef2ff; color:#4f46e5; font-weight:700; padding:.05rem .4rem; border-radius:5px; }
</style>