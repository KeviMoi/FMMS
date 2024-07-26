<?php
// Include the database connection file
include_once 'db_config/db_conn.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>View Service Centers</title>
</head>

<body>
    <div id="modalDialog" class="modal" style="display: none">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">View Service Centers</h5>
                <button type="button" class="close close-icon">
                    <span class="material-icons-sharp">close</span>
                </button>
            </div>
            <!--  Modal Body  -->
            <div class="modal_container">
                <div class="table">
                    <div class="table_header">
                        <p>Service Centers</p>
                        <div>
                            <input placeholder="Search" id="search" onkeyup="searchTable()" />
                            <button class="add_new" onclick="openAddModal()">Add Service Center</button>
                        </div>
                    </div>
                    <div class="table_section">
                        <table id="serviceCentersTable">
                            <thead>
                                <tr>
                                    <th>Service Center ID</th>
                                    <th>Service Center Name</th>
                                    <th>Task</th>
                                    <th>Date Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT sc.service_center_id, sc.service_center_name, mt.task_name, sc.date_created 
                                          FROM service_centers sc
                                          JOIN maintenance_tasks mt ON sc.task_id = mt.task_id";
                                $result = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>{$row['service_center_id']}</td>";
                                    echo "<td contenteditable='true' data-original='{$row['service_center_name']}'>{$row['service_center_name']}</td>";
                                    echo "<td data-original='{$row['task_name']}'>
                                            <select onchange=\"enableSave(this, {$row['service_center_id']})\">";
                                    $task_query = "SELECT task_id, task_name FROM maintenance_tasks";
                                    $task_result = mysqli_query($conn, $task_query);
                                    while ($task_row = mysqli_fetch_assoc($task_result)) {
                                        $selected = ($task_row['task_name'] == $row['task_name']) ? "selected" : "";
                                        echo "<option value='{$task_row['task_id']}' $selected>{$task_row['task_name']}</option>";
                                    }
                                    echo "</select>
                                          </td>";
                                    echo "<td>{$row['date_created']}</td>";
                                    echo "<td>
                          <button onclick=\"saveServiceCenter(this, {$row['service_center_id']})\" disabled><i class='fa-solid fa-save'></i></button>
                          <button onclick=\"deleteServiceCenter({$row['service_center_id']})\"><i class='fa-solid fa-trash'></i></button>
                        </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--  End of Modal Body  -->
        </div>
    </div>

    <!-- Add Service Center Modal -->
    <div id="addModal" class="modal" style="display: none">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">Add Service Center</h5>
                <button type="button" class="close close-icon" onclick="closeAddModal()">
                    <span class="material-icons-sharp">close</span>
                </button>
            </div>
            <div class="modal_container">
                <form id="addServiceCenterForm">
                    <label for="service_center_name">Service Center Name:</label>
                    <input type="text" id="service_center_name" name="service_center_name" required>

                    <label for="task_id">Task:</label>
                    <select id="task_id" name="task_id" required>
                        <?php
                        $task_query = "SELECT task_id, task_name FROM maintenance_tasks";
                        $task_result = mysqli_query($conn, $task_query);
                        while ($task_row = mysqli_fetch_assoc($task_result)) {
                            echo "<option value='{$task_row['task_id']}'>{$task_row['task_name']}</option>";
                        }
                        ?>
                    </select>

                    <button class="add_new" type="submit">Add Service Center</button>
                </form>
            </div>
        </div>
    </div>

    <!-- js  -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        function searchTable() {
            const input = document.getElementById("search");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("serviceCentersTable");
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

        function validateServiceCenterName(serviceCenterName) {
            return serviceCenterName.length >= 2 && serviceCenterName.length <= 255;
        }

        function saveServiceCenter(button, serviceCenterId) {
            const row = button.parentElement.parentElement;
            const serviceCenterNameCell = row.cells[1];
            const taskSelect = row.cells[2].querySelector("select");

            const serviceCenterName = serviceCenterNameCell.textContent.trim();
            const taskId = taskSelect.value;

            // Input validation
            if (!validateServiceCenterName(serviceCenterName)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Service Center Name',
                    text: 'Service Center Name should be 2-255 characters long.',
                });
                return;
            }

            const checkAndSave = () => {
                const changedFields = {};
                if (serviceCenterName !== serviceCenterNameCell.getAttribute('data-original')) {
                    changedFields.service_center_name = serviceCenterName;
                }
                if (taskId !== taskSelect.getAttribute('data-original')) {
                    changedFields.task_id = taskId;
                }

                if (Object.keys(changedFields).length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Changes',
                        text: 'No changes detected to save.',
                    });
                    return;
                }

                // Send the data using AJAX
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "update_service_center.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Service Center Updated',
                            text: 'Service center details have been successfully updated.',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            // Update the original data attributes with new values
                            serviceCenterNameCell.setAttribute('data-original', serviceCenterName);
                            taskSelect.setAttribute('data-original', taskId);

                            // Disable the save button
                            button.disabled = true;
                        });
                    }
                };

                const data = `service_center_id=${serviceCenterId}&changes=${encodeURIComponent(JSON.stringify(changedFields))}`;
                xhr.send(data);
            };

            // Proceed with saving the service center details
            checkAndSave();
        }

        function deleteServiceCenter(serviceCenterId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "delete_service_center.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: 'Service center has been successfully deleted.',
                                timer: 2000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    };
                    xhr.send(`service_center_id=${serviceCenterId}`);
                }
            });
        }

        function openAddModal() {
            document.getElementById("addModal").style.display = "block";
        }

        function closeAddModal() {
            document.getElementById("addModal").style.display = "none";
        }

        document.getElementById("addServiceCenterForm").addEventListener("submit", function(event) {
            event.preventDefault();
            const serviceCenterName = document.getElementById("service_center_name").value;
            const taskId = document.getElementById("task_id").value;

            // Input validation
            if (!validateServiceCenterName(serviceCenterName)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Service Center Name',
                    text: 'Service Center Name should be 2-255 characters long.',
                });
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "add_service_center.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Service Center Added',
                        text: 'Service center has been successfully added.',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            };
            const data = `service_center_name=${encodeURIComponent(serviceCenterName)}&task_id=${taskId}`;
            xhr.send(data);
        });

        function enableSave(selectElement, serviceCenterId) {
            const saveButton = selectElement.closest('tr').querySelector('button');
            saveButton.disabled = false;
        }
    </script>
</body>

</html>

<!--  Css  -->
<style>
    /* Reset CSS */
    * {
        margin: 0;
        padding: 0;
        /*box-sizing: border-box;*/
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
        /* Change to auto to adjust column width based on content */
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

    .add_new {
        padding: 10px 20px;
        color: #ffffff;
        background-color: #0298cf;
    }

    /* Input */
    textarea,
    input, select {
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
<!--  End of Css  -->