// public/backend/custom-js/config.js

// Global loader management
let componentsToLoad = 0;
let componentsLoaded = 0;

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
    // Show loader immediately when page starts loading
    showLoader();
    
    // Track that we're loading the main page components
    componentStartedLoading(); // For app.blade.php
    componentStartedLoading(); // For header.blade.php
    
    // Initialize settings
    settingInfo().finally(() => {
        componentFinishedLoading(); // Mark app.blade.php as loaded
    });
});

// Track when a component starts loading
function componentStartedLoading() {
    componentsToLoad++;
    showLoader();
}

// Track when a component finishes loading
function componentFinishedLoading() {
    componentsLoaded++;
    if (componentsLoaded >= componentsToLoad) {
        // All components have loaded
        hideLoader();
    }
}

// Show loading spinner
function showLoader() {
    const loader = document.getElementById('bouncing-loader');
    if (loader) {
        loader.style.display = 'flex';
    }
}

// Hide loading spinner
function hideLoader() {
    const loader = document.getElementById('bouncing-loader');
    if (loader) {
        loader.style.display = 'none';
    }
}

// Global setting info function
async function settingInfo() {
    try {
        const response = await axios.get('/setting-list');
        if (response.status === 200) {
            const data = response.data.data;

            // Update header elements if they exist
            const logo = document.getElementById('logo');
            if (logo) {
                logo.src = data['logo'] ? "/upload/site-setting/" + data['logo'] : "/upload/no_image.jpg";
            }

            // Update footer elements if they exist
            const footerLogo = document.getElementById('footer-logo');
            if (footerLogo) {
                footerLogo.src = data['logo'] ? "/upload/site-setting/" + data['logo'] : "/upload/no_image.jpg";
            }

            const footerDescription = document.getElementById('footer-description');
            if (footerDescription) {
                footerDescription.innerHTML = data['description'];
            }

            const companyName = document.getElementById('footer-company-name');
            if (companyName) {
                companyName.innerText = data['name'];
            }

            const companyEmail = document.getElementById('footer-company-email');
            if (companyEmail) {
                companyEmail.innerText = data['email'];
            }

            const companyPhone = document.getElementById('footer-company-phone');
            if (companyPhone) {
                companyPhone.innerText = data['phone1'] || data['phone2'] || '';
            }
        }
    } catch (error) {
        handleError(error);
    }
}

// Global error handler
function handleError(error) {
    let message = "An unexpected error occurred.";
    
    if (error.response) {
        const status = error.response.status;
        message = error.response.data.message || message;
        
        if (status === 401) {
            // Handle unauthorized access
            window.location.href = '/login';
            return;
        }
    } else if (error.request) {
        message = "No response from server. Check your connection.";
    }
    
    errorToast(message);
}

// Toast notification functions
function successToast(message) {
    Toastify({
        text: message,
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: "#28a745",
    }).showToast();
}

function errorToast(message) {
    Toastify({
        text: message,
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: "#dc3545",
    }).showToast();
}

// Authentication check
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

// Make functions available globally
window.showLoader = showLoader;
window.hideLoader = hideLoader;
window.componentStartedLoading = componentStartedLoading;
window.componentFinishedLoading = componentFinishedLoading;
window.handleError = handleError;
window.successToast = successToast;
window.errorToast = errorToast;
window.isTokenValid = isTokenValid;