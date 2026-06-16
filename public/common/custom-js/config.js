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
        const res = await axios.get('/meal-cart/count');
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
// String() comparison keeps pre-selection working whether the API returns
// ids as numbers or strings. dropdownId lets any view reuse these (default
// ids: country / county / city).
async function loadCountries(selectedId = '', dropdownId = 'country') {
    try {
        const dropdown = document.getElementById(dropdownId);
        if (!dropdown) return;
        dropdown.innerHTML = '<option value="">Select Country</option>';
        const response = await axios.get('/countries');
        response.data.data.forEach(country => {
            const option = new Option(country.name, country.id);
            option.selected = String(country.id) === String(selectedId);
            dropdown.add(option);
        });
    } catch (error) {
        handleError(error);
    }
}

async function loadCounties(countryId, selectedId = '', dropdownId = 'county') {
    try {
        const dropdown = document.getElementById(dropdownId);
        if (!dropdown) return;
        dropdown.innerHTML = '<option value="">Select County</option>';
        if (!countryId) return;
        const response = await axios.get(`/counties/${countryId}`);
        response.data.data.forEach(county => {
            const option = new Option(county.name, county.id);
            option.selected = String(county.id) === String(selectedId);
            dropdown.add(option);
        });
    } catch (error) {
        handleError(error);
    }
}

async function loadCities(countyId, selectedId = '', dropdownId = 'city') {
    try {
        const dropdown = document.getElementById(dropdownId);
        if (!dropdown) return;
        dropdown.innerHTML = '<option value="">Select City</option>';
        if (!countyId) return;
        const response = await axios.get(`/cities/${countyId}`);
        response.data.data.forEach(city => {
            const option = new Option(city.name, city.id);
            option.selected = String(city.id) === String(selectedId);
            dropdown.add(option);
        });
    } catch (error) {
        handleError(error);
    }
}

/**
 * Wire up a country → county → city cascade (with optional pre-selected values)
 * in one call. Reusable across any form.
 *
 * await initLocationCascade({
 *     countryId: 'countrySelect', countyId: 'countySelect', cityId: 'citySelect',
 *     country: userData.country_id, county: userData.county_id, city: userData.city_id
 * });
 */
async function initLocationCascade(opts = {}) {
    const cfg = {
        countryId: 'country', countyId: 'county', cityId: 'city',
        country: '', county: '', city: '',
        ...opts,
    };

    const countryEl = document.getElementById(cfg.countryId);
    if (!countryEl) return;
    const countyEl = document.getElementById(cfg.countyId);
    const cityEl   = document.getElementById(cfg.cityId);

    // Initial load + pre-selection
    await loadCountries(cfg.country, cfg.countryId);
    await loadCounties(cfg.country, cfg.county, cfg.countyId);
    await loadCities(cfg.county, cfg.city, cfg.cityId);

    // Reset downstream selects when a parent changes
    countryEl.addEventListener('change', async function () {
        await loadCounties(this.value, '', cfg.countyId);
        if (cityEl) cityEl.innerHTML = '<option value="">Select City</option>';
    });
    if (countyEl) {
        countyEl.addEventListener('change', async function () {
            await loadCities(this.value, '', cfg.cityId);
        });
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
                setTimeout(() => window.location.href = (data?.redirect || '/login'), 2000);
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





