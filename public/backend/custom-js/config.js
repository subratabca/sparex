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




async function isTokenValid() {
    try {
        const token = getCookie('token'); // your method to get token
        if (!token) return false;

        const response = await axios.get('/user/validate-token', { headers: { Authorization: `Bearer ${token}` }});
        return response.data.valid; // true or false from backend
    } catch (error) {
        return false; // always return boolean
    }
}


// Get cookie value by name
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

// Decode JWT payload
function decodeJwt(token) {
    try {
        const payload = token.split('.')[1];
        return JSON.parse(atob(payload));
    } catch (error) {
        return null;
    }
}

// Verify token with server
async function verifyTokenWithServer(token) {
    try {
        const response = await axios.post('/verify-token', {}, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        return response.data.status === 'authorized';
    } catch (error) {
        return false;
    }
}


window.isTokenValid = isTokenValid;





