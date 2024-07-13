$(document).ready(function() {
    // Function to load modal content dynamically
    function setupDynamicModal(clickEventListenerId, fileToLoad) {
        // Function to load the modal content
        function loadModalContent() {
            $.get(fileToLoad, function(data) {
                $('#modalPlaceholder').html(data);

                // Show the modal after loading content
                $('#modalDialog').show();
            });
        }

        // Event listener for opening the modal
        $('#' + clickEventListenerId).on('click', function() {
            loadModalContent();
        });

        // Event listener for closing the modal
        $('body').on('click', '.close, .modal', function(e) {
            if ($(e.target).hasClass('modal') || $(e.target).hasClass('close')) {
                $('#modalDialog').hide();
            }
        });

        // Event listener for closing the modal when clicking on the close icon inside the modal
        $('body').on('click', '.close-icon', function() {
            $('#modalDialog').hide();
        });

        // Prevent modal from closing when clicking inside it
        $('#modalDialog').on('click', function(e) {
            e.stopPropagation();
        });
    }

    // Function calls
    setupDynamicModal('addUser', 'create_new_user_form.php');
    setupDynamicModal('fleet_manager_change_password', 'change_password.php');
    setupDynamicModal('driver_change_password', 'change_password.php');
    setupDynamicModal('mechanic_change_password', 'change_password.php');
    setupDynamicModal('viewUsers', 'users.php');
    setupDynamicModal('addVehicle', 'add_vehicle.php');
    setupDynamicModal('viewVehicles', 'vehicles.php');
    setupDynamicModal('schedule_maintenance', 'book_schedule.php');
    setupDynamicModal('driver_schedules', 'driver_schedules.php');
    setupDynamicModal('fleet_manager_schedules', 'fleet_manager_schedules_view.php');
    setupDynamicModal('mechanic_schedules', 'mechanic_schedules.php');
    setupDynamicModal('checkout_vehicle_view', 'checkout_vehicle_view.php'); 
    setupDynamicModal('mechanic_view_service_history', 'view_service_history.php');
    setupDynamicModal('fleet_manager_view_service_history', 'view_service_history.php');
    setupDynamicModal('view_vehicle_service_history', 'view_vehicle_service_history.php');
    setupDynamicModal('request_breakdown_assist', 'request_breakdown_assist.php');
    setupDynamicModal('breakdown_requests', 'breakdown_requests.php');
    setupDynamicModal('view_notifications', 'view_notifications.php');

    // Add more calls to as needed for other elements and files 
});
