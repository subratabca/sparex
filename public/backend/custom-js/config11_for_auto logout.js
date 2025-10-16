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

function isTokenValid() {
    const token = getCookie('token');

    if (!token) {
        redirectToLogin();
        return false;
    }

    const decodedToken = decodeJwt(token);
    if (decodedToken && decodedToken.exp) {
        const currentTime = Math.floor(Date.now() / 1000);
        if (decodedToken.exp < currentTime) {
            removeTokenAndRedirect();
            return false;
        }
    }

    verifyTokenWithServer(token).then(isValid => {
        if (!isValid) {
            removeTokenAndRedirect();
        }
    });

    return true;
}

function getCookie(name) {
    const cookies = document.cookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i].trim();
        if (cookie.startsWith(name + '=')) {
            return cookie.substring((name + '=').length);
        }
    }
    return null;
}

function decodeJwt(token) {
    try {
        const payload = token.split('.')[1];
        return JSON.parse(atob(payload));
    } catch (error) {
        return null;
    }
}

async function verifyTokenWithServer(token) {
    try {
        const response = await fetch('/verify-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        
        const data = await response.json();
        if (data.status === 'unauthorized') {
            removeTokenAndRedirect();
            return false;
        }
        return true;
    } catch (error) {
        removeTokenAndRedirect();
        return false;
    }
}

function removeTokenAndRedirect() {
    document.cookie = 'token=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    redirectToLogin();
}

function redirectToLogin() {
    if (!window.location.pathname.includes('/user/login')) {
        window.location.href = '/user/login';
    }
}

// Check token validity every minute
setInterval(() => {
    isTokenValid();
}, 60000);

// Initial check when page loads
document.addEventListener('DOMContentLoaded', function() {
    isTokenValid();
});




