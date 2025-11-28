<section class="section-py bg-body first-section-pt">
    <div class="container">
        <h3 class="mb-4">My Weekly Meal Plan</h3>

        <!-- Accordion Container -->
        <div class="accordion" id="mealCartAccordion"></div>

        <!-- Summary -->
        <div class="mt-4 border rounded p-3" id="meal-summary"></div>

        <div class="d-grid mt-4">
            <a href="{{ route('checkout') }}">
                <button class="btn btn-info btn-lg">Proceed to Checkout</button>
            </a>
        </div>
    </div>
</section>


<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadMealCart();
});

function toTitleCase(str) {
    if (!str) return "";
    return str
        .trim()
        .toLowerCase()
        .replace(/\b\w/g, char => char.toUpperCase());
}

async function loadMealCart() {
    try {
        showLoader();
        const response = await axios.get('/user/get/meal-cart');

        if (response.status === 200 && response.data.status === 'success') {
            renderMealCart(response.data.data.meal_cart);
            renderMealSummary(response.data.data.summary);
        }
    } catch (error) {
        console.error(error);
        errorToast('Failed to load meal cart');
    } finally {
        hideLoader();
    }
}

function renderMealCart(mealCart) {
    const container = document.getElementById('mealCartAccordion');
    container.innerHTML = "";

    const dates = Object.keys(mealCart);

    if (dates.length === 0) {
        container.innerHTML = `<div class="alert alert-info">Your meal cart is empty.</div>`;
        return;
    }

    dates.forEach((date, index) => {
        const dayItems = mealCart[date];
        const collapseId = `mealDay${index}`;

        // Format date: Monday, Jan 12
        const formattedDate = new Date(date).toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'short',
            day: 'numeric'
        });

        // Extract unique meal types
        const mealTypes = [...new Set(dayItems.map(i => i.meal_type?.name))].filter(Boolean);

        let mealTypeHtml = "";

        mealTypes.forEach(type => {
            const typeTitle = toTitleCase(type);

            const items = dayItems.filter(i => i.meal_type?.name === type);

            // Meal type block
            mealTypeHtml += `
                <h6 class="mt-3">${typeTitle}</h6>
                <ul class="list-group mb-3">
            `;

            items.forEach(item => {
                const productName = toTitleCase(item.product?.name || "");
                const img = item.product?.image
                    ? `/upload/product/small/${item.product.image}`
                    : "/upload/no_image.jpg";

                mealTypeHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <img src="${img}" alt="${productName}"
                                 class="rounded"
                                 style="width:60px;height:60px;object-fit:cover;">
                            
                            <div>
                                <strong>${productName}</strong><br>
                                $${parseFloat(item.unit_price).toFixed(2)} each
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <input type="number"
                                   class="form-control form-control-sm w-25"
                                   value="${item.quantity}"
                                   min="1"
                                   onchange="updateMealItem(${item.id}, this.value)">
                            
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="removeMealItem(${item.id})">&times;</button>
                        </div>
                    </li>
                `;
            });

            mealTypeHtml += `</ul>`;
        });

        // Accordion item for each date
        const block = `
            <div class="accordion-item shadow-sm mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#${collapseId}">
                        ${formattedDate}
                    </button>
                </h2>

                <div id="${collapseId}"
                     class="accordion-collapse collapse ${index === 0 ? 'show' : ''}"
                     data-bs-parent="#mealCartAccordion">
                    <div class="accordion-body">
                        ${mealTypeHtml}
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML("beforeend", block);
    });
}

function renderMealSummary(summary) {
    const container = document.getElementById('meal-summary');

    container.innerHTML = `
        <h5>Price Summary</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between">
                Subtotal: <span>$${summary.subtotal.toFixed(2)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                Tax: <span>$${summary.tax.toFixed(2)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between fw-bold">
                Total: <span>$${summary.total.toFixed(2)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                Total Items: <span>${summary.total_items}</span>
            </li>
        </ul>
    `;
}

async function updateMealItem(id, quantity) {
    if (quantity < 1) return errorToast('Quantity must be at least 1');

    try {
        await axios.post('/user/meal-cart/update', {
            meal_item_id: id,
            quantity
        });

        await loadMealCart();
    } catch (error) {
        console.error(error);
        errorToast('Failed to update item');
    }
}

async function removeMealItem(id) {
    try {
        await axios.post('/user/meal-cart/remove', {
            meal_item_id: id
        });

        await loadMealCart();
    } catch (error) {
        console.error(error);
        errorToast('Failed to remove item');
    }
}
</script>
