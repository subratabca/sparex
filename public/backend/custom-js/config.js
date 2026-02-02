function showLoader() {
    document.getElementById('bouncing-loader').style.display = 'flex';
}
function hideLoader() {
    document.getElementById('bouncing-loader').style.display = 'none';  
}

function successToast(msg) {
    Toastify({
        gravity: "top", 
        position: "right",
        text: msg,
        className: "mb-5",
        style: {
            background: "green",
        }
    }).showToast();
}

function errorToast(msg) {
    Toastify({
        gravity: "top", 
        position: "right",
        text: msg,
        className: "mb-5",
        style: {
            background: "red",
        }
    }).showToast();
}

async function updateMealCartCount() {
    try {
        const res = await axios.get('/user/meal-cart/count');
        if (res.status === 200) {
            const count = res.data.count;
            const cartCountElem = document.getElementById('mealCartCount');
            const cartItemElem = document.getElementById('mealCartItem');
            
            if (count > 0) {
                cartItemElem.style.display = 'block';
                cartCountElem.innerText = count;
                cartCountElem.style.display = 'inline-block';
            } else {
                cartItemElem.style.display = 'none';
                cartCountElem.innerText = '0'; 
            }
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}





