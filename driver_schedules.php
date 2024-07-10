<?php
// Include the database connection file
include_once 'db_config/db_conn.php';

// Start the session
session_start();

// Check if vehicle_id is set in the session
if (!isset($_SESSION['vehicle_id'])) {
    die('Vehicle ID not found in session.');
}

$vehicle_id = $_SESSION['vehicle_id'];
$current_date = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>View Schedules</title>
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
        }

        /* Fonts */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap");

        /* Body */
        .modal_container {
            padding: 20px;
            font-family: "Poppins", sans-serif;
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

        /* Modal */
        .modal {
            display: none;
            position: absolute;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            margin: 2% auto;
            border: 1px solid #888;
            max-width: 95%;
            max-height: 90%;
            width: auto;
            height: auto;
            background-color: #fff;
            border-radius: 5px;
            outline: 0;
            overflow: hidden;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            border-top-left-radius: 0.3rem;
            border-top-right-radius: 0.3rem;
        }

        .modal-title {
            margin: 0;
            font-size: 1.25rem;
            color: #666;
        }

        .close {
            font-size: 1.5rem;
            color: #000;
            opacity: 0.5;
            cursor: pointer;
        }

        .close-icon {
            background-color: transparent;
            border: 0;
            padding: 0;
        }

        /* Table */
        .table {
            width: 100%;
            height: 100%;
        }

        .table_header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px;
            background-color: rgb(240, 240, 240);
            margin: auto;
        }

        .table_header p {
            color: #000;
        }

        .table_section {
            max-height: 450px;
            overflow: auto;
        }

        table {
            width: 100%;
            table-layout: auto;
            border-collapse: collapse;
        }

        thead th {
            position: sticky;
            top: 0;
            background-color: #f6f9fc;
            color: #0298cf;
            font-size: 15px;
        }

        th,
        td,
        select {
            border-bottom: 1px solid #dddddd;
            padding: 3px 6px;
            word-break: break-all;
            text-align: center;
        }

        tr:hover td,
        select {
            color: #0298cf;
            background-color: #f6f9fc;
        }

        /*Added*/
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

        /*Added*/

        /* Buttons */
        button {
            outline: none;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            padding: 10px;
            color: #fff;
        }

        td button:nth-child(1) {
            background-color: #0298cf;
        }

        td button:nth-child(2) {
            background-color: #f80000;
        }

        /* Input */
        input {
            padding: 10px 20px;
            margin: 0 10px;
            outline: none;
            border: 1px solid #0298cf;
            border-radius: 6px;
            color: #0298cf;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            height: 5px;
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.3);
        }

        ::-webkit-scrollbar-thumb {
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.3);
        }

        /* Media Queries */
        @media (max-width: 584px) {
            .modal_container {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div id="modalDialog" class="modal" style="display: block;">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">View Schedules</h5>
                <button type="button" class="close close-icon">
                    <span class="material-icons-sharp">close</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="modal_container">
                <div class="table">
                    <div class="table_header">
                        <p>Schedule Details</p>
                        <div>
                            <input placeholder="Search" id="search" onkeyup="searchTable()" />
                        </div>
                    </div>
                    <div class="table_section">
                        <table id="schedulesTable">
                            <thead>
                                <tr>
                                    <th>Schedule ID</th>
                                    <th>Vehicle ID</th>
                                    <th>Date</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM maintenance_schedule WHERE vehicle_id = $vehicle_id AND schedule_date >= '$current_date' AND status != 'cancelled'";
                                $result = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>{$row['schedule_id']}</td>";
                                    echo "<td>{$row['vehicle_id']}</td>";
                                    echo "<td>{$row['schedule_date']}</td>";
                                    echo "<td>{$row['schedule_start_time']}</td>";
                                    echo "<td>{$row['schedule_end_time']}</td>";
                                    echo "<td>{$row['additional_info']}</td>";
                                    echo "<td>
                          <button onclick=\"rescheduleSchedule({$row['schedule_id']})\"><i class='fa-solid fa-edit'></i> Reschedule</button>
                          <button onclick=\"cancelSchedule({$row['schedule_id']})\"><i class='fa-solid fa-trash'></i> Cancel</button>
                        </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                    </div>
                </div>
            </div>
            <!-- End of Modal Body -->
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        function searchTable() {
            const input = document.getElementById("search");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("schedulesTable");
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName("td");
                let textValue = "";
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        textValue += td[j].textContent || td[j].innerText;
                    }
                }
                if (textValue.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }

        function rescheduleSchedule(scheduleId) {
    Swal.fire({
        title: 'Reschedule',
        html: `
        <style>
            .swal2-popup {
                width: 600px !important;
                height: auto !important;
            }
            .swal2-input {
                width: 100% !important;
            }
            .swal2-textarea {
                width: 100% !important;
            }
        </style>
        <input type="date" id="newDate" class="swal2-input" placeholder="New Date">
        <input type="text" id="task" class="swal2-input" placeholder="Task">`,
        showCancelButton: true,
        confirmButtonText: 'Next',
        preConfirm: () => {
            const newDate = document.getElementById('newDate').value;
            const task = document.getElementById('task').value;
            if (!newDate || !task) {
                Swal.showValidationMessage('Please enter all fields');
                return false;
            }
            return {
                newDate,
                task
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { newDate, task } = result.value;

            // Fetch available timeslots based on the selected date and task
            fetch('fetch_timeslots.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    date: newDate,
                    task: task
                })
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: 'Select Timeslot',
                    html: `
                    <style>
                        .swal2-popup {
                            width: 800px !important;
                            height: auto !important;
                        }
                        .timeslots {
                            display: flex;
                            flex-wrap: wrap;
                        }
                        .timeslot {
                            flex: 1 0 20%;
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
                    </style>
                    <div class="timeslots">
                        ${data}
                    </div>`,
                    showCancelButton: true,
                    confirmButtonText: 'Reschedule',
                    didOpen: () => {
                        // Add event listeners to each timeslot for selection
                        const timeslots = document.querySelectorAll('.timeslot.available');
                        timeslots.forEach(timeslot => {
                            timeslot.addEventListener('click', () => {
                                timeslots.forEach(ts => ts.classList.remove('selected'));
                                timeslot.classList.add('selected');
                            });
                        });
                    },
                    preConfirm: () => {
                        const selectedTimeslot = document.querySelector('.timeslot.selected');
                        if (!selectedTimeslot) {
                            Swal.showValidationMessage('Please select a timeslot');
                            return false;
                        }
                        return {
                            serviceCenterId: selectedTimeslot.dataset.serviceCenterId,
                            startTime: selectedTimeslot.dataset.startTime,
                            endTime: selectedTimeslot.dataset.endTime
                        };
                    }
                }).then((res) => {
                    if (res.isConfirmed) {
                        const { serviceCenterId, startTime, endTime } = res.value;
                        
                        // Make a request to update the schedule in the database
                        fetch('reschedule_schedule.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                schedule_id: scheduleId,
                                service_center_id: serviceCenterId,
                                schedule_date: newDate,
                                schedule_start_time: startTime,
                                schedule_end_time: endTime
                            })
                        })
                        .then(response => response.text())
                        .then(data => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Rescheduled',
                                text: 'The maintenance task has been successfully rescheduled.',
                            });
                        })
                        .catch(error => {
                            console.error('Error rescheduling task:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Failed to reschedule the task. Please try again.',
                            });
                        });
                    }
                });
            })
            .catch(error => {
                console.error('Error fetching timeslots:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Failed to fetch available timeslots. Please try again.',
                });
            });
        }
    });
}



        function cancelSchedule(scheduleId) {
            Swal.fire({
                title: 'Are you sure you want to cancel this schedule?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, cancel it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `cancel_schedule.php?schedule_id=${scheduleId}`;
                }
            });
        }
    </script>
</body>

</html>