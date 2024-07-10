<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Maintenance Booking</title>
    <link rel="stylesheet" href="assets/css/message_box.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div id="modalDialog" class="modal" style="display: block">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">Vehicle Maintenance Booking</h5>
                <button type="button" class="close close-icon">
                    <span class="material-icons-sharp">close</span>
                </button>
            </div>
            <div class="modal_container">
                <div id="message-container"></div>
                <form action="" method="POST" id="maintenanceBookingForm">
                    <div class="details">
                        <div class="input-box">
                            <span class="details">Date</span>
                            <input type="date" name="date" id="date" required />
                        </div>
                        <div class="input-box">
                            <span class="details">Maintenance Task</span>
                            <input type="text" name="maintenance_task" id="maintenance_task" placeholder="Enter maintenance task" required />
                        </div>
                        <div class="input-box full-width">
                            <span class="details">Additional Information</span>
                            <textarea name="additional_info" id="additional_info" placeholder="Enter additional information" required></textarea>
                        </div>
                        <input type="hidden" name="service_center_id" id="selected_service_center_id" required />
                        <input type="hidden" name="start_time" id="selected_start_time" required />
                        <input type="hidden" name="end_time" id="selected_end_time" required />
                    </div>
                    <div class="service-centers">
                        <h5><b>Available timeslots</b></h5>
                        <div id="service-center-container"></div>
                    </div>
                    <div class="button">
                        <input type="submit" value="Book Maintenance" />
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function fetchAvailableTimeslots() {
                const date = $('#date').val();
                const task = $('#maintenance_task').val();

                if (date && task) {
                    $.ajax({
                        url: 'fetch_timeslots.php',
                        type: 'POST',
                        data: {
                            date: date,
                            task: task
                        },
                        success: function(data) {
                            $('#service-center-container').html(data);
                        }
                    });
                }
            }

            $('#date, #maintenance_task').change(fetchAvailableTimeslots);

            $('#service-center-container').on('click', '.timeslot.available', function() {
                $('.timeslot').removeClass('selected'); // Clear previous selection
                $(this).addClass('selected'); // Mark current selection

                $('#selected_service_center_id').val($(this).data('service-center-id'));
                $('#selected_start_time').val($(this).data('start-time'));
                $('#selected_end_time').val($(this).data('end-time'));
            });

            $('#maintenanceBookingForm').submit(function(e) {
                e.preventDefault();

                const formData = $(this).serialize();

                $.ajax({
                    url: 'book_maintenance.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#message-container').html(response);
                        fetchAvailableTimeslots();
                    }
                });
            });
        });
    </script>

</body>

</html>