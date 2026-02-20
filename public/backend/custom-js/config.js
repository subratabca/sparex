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





