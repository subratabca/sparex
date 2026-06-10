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

// ===== Global Location Loaders =====
async function loadCountries(selectedId = '') {
    try {
        const response = await axios.get('/countries');
        const dropdown = document.getElementById('country');
        if (!dropdown) return;
        dropdown.innerHTML = '<option value="">Select Country</option>';
        response.data.data.forEach(country => {
            const option = new Option(country.name, country.id);
            option.selected = parseInt(country.id) === parseInt(selectedId);
            dropdown.add(option);
        });
    } catch (error) {
        handleError(error);
    }
}

async function loadCounties(countryId, selectedId = '') {
    try {
        const dropdown = document.getElementById('county');
        if (!dropdown) return;
        dropdown.innerHTML = '<option value="">Select County</option>';
        if (!countryId) return;
        const response = await axios.get(`/counties/${countryId}`);
        response.data.data.forEach(county => {
            const option = new Option(county.name, county.id);
            option.selected = parseInt(county.id) === parseInt(selectedId);
            dropdown.add(option);
        });
    } catch (error) {
        handleError(error);
    }
}

async function loadCities(countyId, selectedId = '') {
    try {
        const dropdown = document.getElementById('city');
        if (!dropdown) return;
        dropdown.innerHTML = '<option value="">Select City</option>';
        if (!countyId) return;
        const response = await axios.get(`/cities/${countyId}`);
        response.data.data.forEach(city => {
            const option = new Option(city.name, city.id);
            option.selected = parseInt(city.id) === parseInt(selectedId);
            dropdown.add(option);
        });
    } catch (error) {
        handleError(error);
    }
}




function handleError(error) {
    if (error.response) {
        const status = error.response.status;
        const data   = error.response.data;
        const message = data?.message || data?.error || 'An unexpected error occurred';

        switch (status) {
            case 400:
                errorToast(message || 'Bad Request');
                break;
            case 401:
                errorToast(message || 'Unauthorized. Please login.');
                setTimeout(() => window.location.href = '/user/login', 2000);
                break;
            case 403:
                errorToast(message || 'Forbidden. You do not have permission.');
                break;
            case 404:
                errorToast(message || 'Resource not found.');
                break;
            case 409:
                errorToast(message || 'Conflict. Duplicate entry.');
                break;
            case 422:
                if (data?.errors) {
                    Object.values(data.errors).forEach(errs => {
                        errs.forEach(err => errorToast(err));
                    });
                } else {
                    errorToast(message || 'Validation failed.');
                }
                break;
            case 500:
                errorToast(message || 'Server error. Please try again later.');
                break;
            default:
                errorToast(message || 'Something went wrong.');
        }
    } else if (error.request) {
        errorToast('No response from server. Check your internet connection.');
    } else {
        errorToast(error.message || 'An unexpected error occurred.');
    }
}



window.getCountryCode = function(countryName) {
    const countryCodes = {
        'United States': 'US',
        'United Kingdom': 'GB',
        'Canada': 'CA',
        'Australia': 'AU',
        'Germany': 'DE',
        'France': 'FR',
        'Spain': 'ES',
        'Italy': 'IT',
        'Japan': 'JP',
        'China': 'CN',
        'India': 'IN',
        'Brazil': 'BR',
        'Mexico': 'MX',
        'Russia': 'RU',
        'South Korea': 'KR',
        'Netherlands': 'NL',
        'Switzerland': 'CH',
        'Sweden': 'SE',
        'Norway': 'NO',
        'Denmark': 'DK',
        'Finland': 'FI',
        'Ireland': 'IE',
        'Poland': 'PL',
        'Portugal': 'PT',
        'Austria': 'AT',
        'Belgium': 'BE',
        'Greece': 'GR',
        'Turkey': 'TR',
        'Saudi Arabia': 'SA',
        'United Arab Emirates': 'AE',
        'South Africa': 'ZA',
        'Egypt': 'EG',
        'Nigeria': 'NG',
        'Kenya': 'KE',
        'Argentina': 'AR',
        'Chile': 'CL',
        'Colombia': 'CO',
        'Peru': 'PE',
        'Venezuela': 'VE',
        'New Zealand': 'NZ',
        'Singapore': 'SG',
        'Malaysia': 'MY',
        'Thailand': 'TH',
        'Vietnam': 'VN',
        'Philippines': 'PH',
        'Indonesia': 'ID',
        'Pakistan': 'PK',
        'Bangladesh': 'BD',
        'Sri Lanka': 'LK',
        'Israel': 'IL',
        'Iran': 'IR',
        'Iraq': 'IQ',
        'Afghanistan': 'AF',
        'England': 'GB', // England is part of United Kingdom
        'Scotland': 'GB', // Scotland is part of United Kingdom
        'Wales': 'GB', // Wales is part of United Kingdom
        'Northern Ireland': 'GB' // Northern Ireland is part of United Kingdom
    };
    return countryCodes[countryName] || '';
};





