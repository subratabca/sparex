<!--Sidebar Page-->
<div class="sidebar-page-container">
    <!--Tabs Box-->
    <div class="auto-container">
        <div class="row clearfix">

            <!--Sidebar-->
            @include('frontend.components.dashboard.left-sidebar')
            <!--End Sidebar-->

            <!--Content Side-->
            <div class="content-side col-lg-9 col-md-8 col-sm-12 col-xs-12">
                <!--Single Service-->
                <section class="services-single">
                    <!--Default Text Block-->
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="default-text-block">
                                <div class="card bg-info" style="padding: 10px;">
                                    <p class="title" style="margin: 0px; padding: 0px; font-weight: bold;">Notification Details</p>

                                    <hr style="margin: 0px; padding: 0px; border: 0; border-top: 2px solid;">

                                    <p class="text" style="padding: 10px;"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                </section>
            </div>
            <!--End Content Side-->

        </div>
    </div>
</div>
<!--Sidebar Page end-->

<script>
async function NotificationsByType() {
    try {
        let pathArray = window.location.pathname.split('/');
        let id = pathArray[pathArray.length - 1]; 

        let res = await axios.get(`/user/notification/${id}`);

        if (res.status === 200) {
            let title = res.data.title;
            document.querySelector('.title').innerText = title;

            if (res.data.food && res.data.user) {
                if(title == 'Approve Food Request Notification'){
                    let content = `
                        <p>Dear <strong>${res.data.user.firstName}</strong>, your food request has been approved.Please collect your food. Food details are given below:</p>
                        <p>1. <strong>Food Name:</strong> ${res.data.food.name}</p>
                        <p>2. <strong>Food Gradients:</strong> ${res.data.food.gradients}</p>
                    `;
                    document.querySelector('.text').innerHTML = content;
                }else if(title == 'Food Delivery Notification'){
                    let content = `
                        <p>Dear <strong>${res.data.user.firstName}</strong>, your food request has been delivered successfully.Food details are given below:</p>
                        <p>1. <strong>Food Name:</strong> ${res.data.food.name}</p>
                        <p>2. <strong>Food Gradients:</strong> ${res.data.food.gradients}</p>
                    `;
                    document.querySelector('.text').innerHTML = content;
                }else if(title == 'Review Food Complain Notification'){
                    let content = `
                        <p>Dear <strong>${res.data.user.firstName}</strong>, your food complain is now under review.We will get back to you soon with feedback. Food details are given below:</p>
                        <p>1. <strong>Food Name:</strong> ${res.data.food.name}</p>
                        <p>2. <strong>Food Gradients:</strong> ${res.data.food.gradients}</p>
                    `;
                    document.querySelector('.text').innerHTML = content;
                }else{
                    let content = `
                        <p>Dear <strong>${res.data.user.firstName}</strong>, your food complain has been review.Your complain feedback details are given below:</p>
                        <p>1. <strong>Food Name:</strong> ${res.data.food.name}</p>
                        <p>2. <strong>Food Gradients:</strong> ${res.data.food.gradients}</p>
                    `;
                    document.querySelector('.text').innerHTML = content;
                }
            } else {
                document.querySelector('.text').innerHTML = 'Food details not available';

            }
        }
    }catch (error) {
        let errorMessage = 'An unexpected error occurred.';

        if (error.response) {
            const status = error.response.status;

            if (status === 400) {
                errorMessage = error.response.data.message || 'Food ID not found in the notification data.';
            } else if (status === 404) {
                const serverMessage = error.response.data.status;
                if (serverMessage === 'failed to fetch user') {
                    errorMessage = error.response.data.message || 'User not found.';
                } else if (serverMessage === 'failed to fetch notification') {
                    errorMessage = error.response.data.message || 'Notification not found.';
                } else if (serverMessage === 'failed to fetch food') {
                    errorMessage = error.response.data.message || 'Food details not found.';
                } else {
                    errorMessage = 'Resource not found.';
                }
            } else if (status === 500) {
                errorMessage = error.response.data.message || 'An unexpected error occurred.';
            }
        }

        // Display the error message to the user
        errorToast(errorMessage);
    }
}

// Call the function on page load or as needed
document.addEventListener('DOMContentLoaded', NotificationsByType);
</script>
