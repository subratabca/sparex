@extends('frontend.components.dashboard.dashboard-master')

@section('dashboard-content')

<div class="container py-4 multi-checkout">

    <div class="d-flex align-items-center gap-2 mb-1">
        <i class="mdi mdi-map-marker-multiple-outline fs-3 text-primary"></i>
        <h3 class="fw-bold mb-0">Checkout — Multiple Locations</h3>
    </div>
    <p class="text-muted mb-4">Each meal type is delivered to its own location and time.</p>

    <div class="row g-4">
        {{-- ===== Left: grouped delivery plan ===== --}}
        <div class="col-lg-8">
            <div id="mc-days"></div>
        </div>

        {{-- ===== Right: summary + payment ===== --}}
        <div class="col-lg-4">
            <div class="mc-summary-card card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Order Summary</h5>
                    <div id="mc-summary"></div>

                    <hr class="my-3">

                    <label class="form-label fw-semibold">Payment Method</label>
                    <div class="mc-pay-options d-grid gap-2 mb-3" id="mc-pay">
                        <label class="mc-pay border rounded-3 p-2 d-flex align-items-center gap-2 m-0">
                            <input type="radio" name="payment_method" value="cash" checked>
                            <i class="mdi mdi-cash text-success fs-5"></i> <span>Cash on Delivery</span>
                        </label>
                        <label class="mc-pay border rounded-3 p-2 d-flex align-items-center gap-2 m-0">
                            <input type="radio" name="payment_method" value="credit">
                            <i class="mdi mdi-wallet-outline text-primary fs-5"></i> <span>My Credit</span>
                        </label>
                        <label class="mc-pay border rounded-3 p-2 d-flex align-items-center gap-2 m-0">
                            <input type="radio" name="payment_method" value="stripe">
                            <i class="mdi mdi-credit-card-outline text-info fs-5"></i> <span>Card (Stripe)</span>
                        </label>
                    </div>

                    <div id="mc-card-box" class="mb-3" style="display:none;">
                        <label class="form-label fw-semibold">Card Details</label>
                        <div id="mc-card-element" class="form-control" style="padding:12px;height:auto;"></div>
                        <div id="mc-card-errors" class="text-danger small mt-1"></div>
                    </div>

                    <button id="mc-place-order" class="btn btn-primary btn-lg w-100 rounded-pill">
                        <i class="mdi mdi-check-circle-outline me-1"></i> Place Order
                    </button>
                    <a href="{{ route('meal.cart') }}" class="btn btn-link w-100 mt-2 text-muted">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to cart
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.multi-checkout .mc-day-card { border:0; border-radius:1rem; overflow:hidden; }
.multi-checkout .mc-day-head { background:linear-gradient(135deg,#eef2ff,#f5f7ff); }
.multi-checkout .mc-group { border:1px solid rgba(0,0,0,.06); border-radius:.85rem; transition:box-shadow .2s ease; }
.multi-checkout .mc-group:hover { box-shadow:0 8px 24px rgba(0,0,0,.07); }
.multi-checkout .mc-badge { width:38px;height:38px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0; }
.multi-checkout .mc-loc { background:#f0f9f4; border:1px dashed #9fe1cb; border-radius:.7rem; }
.multi-checkout .mc-item img { width:52px;height:52px;object-fit:cover;border-radius:.55rem; }
.multi-checkout .mc-summary-card { position:sticky; top:1rem; }
.multi-checkout .mc-pay { cursor:pointer; transition:all .15s ease; }
.multi-checkout .mc-pay:has(input:checked) { border-color:#0d6efd !important; background:#f0f6ff; box-shadow:0 0 0 2px rgba(13,110,253,.15); }
@media (max-width:991px){ .multi-checkout .mc-summary-card{ position:static; } }
</style>
@endpush

@push('scripts')
<script>
let mcStripe = null, mcCard = null, mcSummaryTotal = 0;

document.addEventListener('DOMContentLoaded', () => {
    loadMultiCheckout();
    initMcStripe();
    document.getElementById('mc-pay').addEventListener('change', () => {
        const m = document.querySelector('input[name="payment_method"]:checked')?.value;
        document.getElementById('mc-card-box').style.display = (m === 'stripe') ? 'block' : 'none';
    });
});

function initMcStripe(){
    if (typeof Stripe === 'undefined') return;   // Stripe.js loaded in layout/app.blade.php
    mcStripe = Stripe('pk_test_51JHjNRSDYO1wlylS2t9Mdffvf6gXo7BhEkupXMj17tAoMteZHKKlP1ZooX6eaEZjOf6SHp8rJ2141rsuapAFLB3i00vysBKwyd');
    const els = mcStripe.elements();
    mcCard = els.create('card', { hidePostalCode: true });
    mcCard.mount('#mc-card-element');
    mcCard.on('change', e => { document.getElementById('mc-card-errors').textContent = e.error ? e.error.message : ''; });
}

function mcTitle(s){ return (s||'').toString().toLowerCase().replace(/\b\w/g,c=>c.toUpperCase()); }
function mcMoney(n){ return '$' + (parseFloat(n)||0).toFixed(2); }
function mcTime(t){
    if(!t) return 'Not set';
    const [h,m] = t.split(':'); const hr = parseInt(h);
    return `${hr%12||12}:${(m||'00').padStart(2,'0')} ${hr>=12?'PM':'AM'}`;
}

const MC_ICONS = {
    Breakfast:{i:'mdi-coffee-outline',c:'bg-warning'}, Lunch:{i:'mdi-food-outline',c:'bg-primary'},
    Snacks:{i:'mdi-food-apple-outline',c:'bg-success'}, Dinner:{i:'mdi-silverware-fork-knife',c:'bg-danger'}
};

async function loadMultiCheckout(){
    try{
        showLoader();
        const res = await axios.get('/get/multi-checkout-data');
        if(res.data.status === 'success'){
            renderDays(res.data.data.days);
            renderSummary(res.data.data.summary);
        }else{
            document.getElementById('mc-days').innerHTML =
                `<div class="alert alert-info rounded-4">${res.data.message || 'No items to check out.'}</div>`;
        }
    }catch(err){
        const msg = err.response?.data?.message || 'Failed to load checkout.';
        document.getElementById('mc-days').innerHTML = `<div class="alert alert-danger rounded-4">${msg}</div>`;
    }finally{ hideLoader(); }
}

function renderDays(days){
    const wrap = document.getElementById('mc-days');
    if(!days || !days.length){ wrap.innerHTML = `<div class="alert alert-info rounded-4">Your cart is empty.</div>`; return; }

    wrap.innerHTML = days.map(day=>{
        const dLabel = new Date(day.date+'T00:00:00').toLocaleDateString('en-US',{weekday:'long',month:'short',day:'numeric'});
        const groups = day.groups.map(g=>{
            const cfg = MC_ICONS[g.meal_type] || {i:'mdi-food',c:'bg-secondary'};
            const loc = g.location;
            const locHtml = loc ? `
                <div class="mc-loc p-2 px-3 mt-2 small">
                    <div class="fw-semibold text-success">
                        <i class="mdi mdi-map-marker me-1"></i>${mcTitle(loc.label)}${loc.city?' · '+mcTitle(loc.city):''}
                    </div>
                    <div class="text-muted">${mcTitle(loc.address1||'')}${loc.zip_code?', '+loc.zip_code:''}</div>
                    ${loc.phone?`<div class="text-muted"><i class="mdi mdi-phone-outline me-1"></i>${loc.phone}</div>`:''}
                </div>` : `<div class="text-danger small mt-2">No location set.</div>`;

            const items = g.items.map(it=>{
                const img = it.product?.image ? `/upload/product/small/${it.product.image}` : '/upload/no_image.jpg';
                return `
                    <div class="mc-item d-flex align-items-center gap-2 py-2 border-top">
                        <img src="${img}" alt="">
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">${mcTitle(it.product?.name||'')}</div>
                            ${it.client?`<small class="text-muted">Provided By: ${mcTitle(it.client.name)}</small>`:''}
                        </div>
                        <div class="text-end small">
                            <div class="text-muted">${it.quantity} × ${mcMoney(it.unit_price)}</div>
                            <div class="fw-semibold text-primary">${mcMoney(it.line_total)}</div>
                        </div>
                    </div>`;
            }).join('');

            return `
                <div class="mc-group p-3 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="mc-badge ${cfg.c} text-white"><i class="mdi ${cfg.i}"></i></span>
                        <div class="flex-grow-1">
                            <div class="fw-bold">${mcTitle(g.meal_type)}</div>
                            <small class="text-primary"><i class="mdi mdi-clock-outline me-1"></i>${mcTime(g.meal_time)}</small>
                        </div>
                        <span class="badge bg-light text-dark border">${g.items.length} item(s)</span>
                    </div>
                    ${locHtml}
                    <div class="mt-2">${items}</div>
                    <div class="d-flex justify-content-between mt-2 pt-2 border-top small">
                        <span class="text-muted">Subtotal ${mcMoney(g.subtotal)} · Delivery ${mcMoney(g.delivery_charge)}</span>
                        <span class="fw-bold">${mcMoney(g.subtotal + g.delivery_charge)}</span>
                    </div>
                </div>`;
        }).join('');

        return `
            <div class="mc-day-card card shadow-sm mb-4">
                <div class="mc-day-head card-header border-0 py-3">
                    <span class="fw-bold"><i class="mdi mdi-calendar me-2 text-primary"></i>${dLabel}</span>
                </div>
                <div class="card-body">${groups}</div>
            </div>`;
    }).join('');
}

function renderSummary(s){
    mcSummaryTotal = s.total;
    document.getElementById('mc-summary').innerHTML = `
        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Subtotal</span><span>${mcMoney(s.subtotal)}</span></div>
        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Tax (${Math.round(s.tax_rate*100)}%)</span><span>${mcMoney(s.tax)}</span></div>
        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Service Fee (${Math.round(s.service_fee_rate*100)}%)</span><span>${mcMoney(s.service_fee)}</span></div>
        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Delivery (all locations)</span><span>${mcMoney(s.delivery_total)}</span></div>
        <hr class="my-2">
        <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span class="text-primary">${mcMoney(s.total)}</span></div>
        <div class="text-end small text-muted mt-1">${s.total_items} item(s)</div>`;
}

document.getElementById('mc-place-order').addEventListener('click', placeMultiOrder);

async function placeMultiOrder(){
    const method = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
    const btn    = document.getElementById('mc-place-order');

    if(method === 'stripe'){ return placeStripeOrder(btn); }

    const urls = { cash:'/store/multi-meal-order/by/cash', credit:'/store/multi-meal-order/by/credit' };
    try{
        btn.disabled = true; showLoader();
        const res = await axios.post(urls[method], {});
        if((res.status === 201 || res.status === 200) && res.data.status === 'success'){
            successToast(res.data.message || 'Order placed successfully!');
            setTimeout(() => { window.location.href = '{{ route("meal.order") }}'; }, 1200);
        }else{
            errorToast(res.data.message || 'Failed to place order.');
            btn.disabled = false;
        }
    }catch(err){
        errorToast(err.response?.data?.message || 'Failed to place order.');
        btn.disabled = false;
    }finally{ hideLoader(); }
}

async function placeStripeOrder(btn){
    if(!mcStripe || !mcCard){ errorToast('Card payment is not ready. Please refresh.'); return; }
    if(!mcSummaryTotal || mcSummaryTotal <= 0){ errorToast('Nothing to pay.'); return; }
    try{
        btn.disabled = true; showLoader();

        // 1) create the payment intent (reuses the existing endpoint)
        const pi = await axios.post('/create-payment-intent', {
            amount:      Math.round(mcSummaryTotal * 100),
            currency:    'usd',
            description: 'Multi-location meal order',
            metadata:    { order_type: 'meal_multi_order' }
        });
        if(pi.data.status !== 'success') throw new Error(pi.data.message || 'Failed to start payment.');
        const d   = pi.data.data || pi.data;
        const cs  = d.client_secret;
        const pid = d.payment_intent_id || d.id;

        // 2) confirm the card payment
        const { error, paymentIntent } = await mcStripe.confirmCardPayment(cs, { payment_method: { card: mcCard } });
        if(error){ document.getElementById('mc-card-errors').textContent = error.message; throw new Error(error.message); }
        if(paymentIntent?.status !== 'succeeded') throw new Error('Payment not completed. Status: ' + (paymentIntent?.status || 'unknown'));

        // 3) place the order
        const res = await axios.post('/store/multi-meal-order/by/stripe', {
            payment_intent_id: pid,
            stripe_payment_id: paymentIntent.id
        });
        if((res.status === 201 || res.status === 200) && res.data.status === 'success'){
            successToast(res.data.message || 'Payment completed — order placed!');
            setTimeout(() => { window.location.href = '{{ route("meal.order") }}'; }, 1200);
        }else{
            throw new Error(res.data.message || 'Failed to place order after payment.');
        }
    }catch(err){
        errorToast(err.response?.data?.message || err.message || 'Card payment failed.');
        btn.disabled = false;
    }finally{ hideLoader(); }
}
</script>
@endpush
