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



<!--css-->
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
    }

    body {
        display: flex;
        height: 100vh;
        justify-content: center;
        align-items: center;
        padding: 10px;
        background-color: #f5f5f5;
    }

    .modal_container {
        padding: 20px;
    }

    ::-webkit-input-placeholder {
        color: #969494;
    }

    ::-moz-placeholder {
        color: #969494;
    }

    :-ms-input-placeholder {
        color: #969494;
    }

    :-moz-placeholder {
        color: #969494;
    }

    .animate-top {
        position: relative;
        animation: animatetop 0.4s;
    }

    @keyframes animatetop {
        from {
            top: -300px;
            opacity: 0;
        }

        to {
            top: 0;
            opacity: 1;
        }
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(5px);
    }

    .modal-content {
        margin: 8% auto;
        border: 1px solid #888;
        max-width: 65%;
        width: auto;
        background-color: #fff;
        border: 1px solid rgba(0, 0, 0, .2);
        border-radius: 10px;
        outline: 0;
    }

    .modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        border-top-left-radius: 0.3rem;
        border-top-right-radius: 0.3rem;
    }

    .modal-title {
        margin: 0;
        line-height: 1.5;
        font-size: 1.25rem;
        color: #666;
    }

    .close {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        opacity: 0.5;
        background-color: transparent;
        border: none;
        cursor: pointer;
    }

    .modal-body {
        flex: 1 1 auto;
        padding: 1rem;
    }

    .modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 1rem;
        border-top: 1px solid #e9ecef;
    }

    .modal_container form .details {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    form .details .input-box {
        margin-bottom: 15px;
        width: calc(50% - 10px);
    }

    .details .input-box.full-width {
        width: 100%;
    }

    .details .input-box .details {
        display: block;
        font-weight: 500;
        margin-bottom: 5px;
    }

    h5 {
        padding-bottom: 8px;
    }

    form .details .input-box input,
    .details .input-box textarea,
    .details .input-box select {
        height: 45px;
        width: 100%;
        outline: none;
        border-radius: 5px;
        border: 1px solid #ccc;
        padding-left: 15px;
        font-size: 16px;
        border-bottom-width: 2px;
        transition: all 0.3s ease;
    }

    .details .input-box textarea {
        height: auto;
        padding-top: 10px;
        resize: vertical;
    }

    .details .input-box input:focus,
    .details .input-box textarea:focus,
    .details .input-box select:focus,
    .details .input-box input:valid,
    .details .input-box textarea:valid,
    .details .input-box select:valid {
        border-color: #6C9BCF;
    }

    .service-centers {
        margin: 15px 0;
        border: 1px solid #ccc;
        padding: 5px;
    }

    .service-center {
        margin-bottom: 15px;
        padding: 10px;
        border-radius: 5px;
    }

    .service-center h6 {
        margin-bottom: 10px;
        color: #333;
        font-weight: bold;
        font-size: 0.95rem;
    }

    .timeslots {
        display: flex;
        flex-wrap: wrap;
    }

    .timeslot {
        padding: 12px;
        margin: 5px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .timeslot.available {
        background-color: #28a745;
        color: white;
    }

    .timeslot.unavailable {
        background-color: #ccc;
        color: #666;
        cursor: not-allowed;
    }

    .timeslot.selected {
        background-color: #6C9BCF;
    }

    form .button {
        height: 45px;
        width: 90%;
        margin: 0 auto;
        padding: 2px;
    }

    form .button input {
        height: 100%;
        width: 100%;
        outline: none;
        color: #fff;
        border: none;
        font-size: 18px;
        font-weight: 500;
        border-radius: 5px;
        letter-spacing: 1px;
        background: #6C9BCF;
    }

    form .button input:hover {
        background: linear-gradient(-135deg, #71b7e6, #6C9BCF);
    }

    @media (max-width: 584px) {
        .modal_container {
            max-width: 100%;
        }

        .modal_container form .details {
            max-height: 300px;
            overflow-y: scroll;
        }

        .details::-webkit-scrollbar {
            width: 0;
        }

        form .details .input-box {
            width: 100%;
        }
    }
</style>
<!--css-->