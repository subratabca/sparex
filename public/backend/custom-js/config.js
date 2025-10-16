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
        return false;
    }

    const decodedToken = decodeJwt(token);
    if (decodedToken && decodedToken.exp) {
        const currentTime = Math.floor(Date.now() / 1000); 
        if (decodedToken.exp < currentTime) {
            return false; 
        }
    }

    if (!verifyTokenWithServer(token)) {
        return false;
    }

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

function verifyTokenWithServer(token) {
    const isValid = fetch('/verify-token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'unauthorized') {
            return false; 
        }
        return true;
    })
    .catch(() => {
        return false; 
    });

    return isValid;
}



