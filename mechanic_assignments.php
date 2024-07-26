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
    <title>View Service Center Mechanics</title>
</head>

<body>
    <div id="modalDialog" class="modal" style="display: none">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">View Service Center Mechanics</h5>
                <button type="button" class="close close-icon">
                    <span class="material-icons-sharp">close</span>
                </button>
            </div>
            <div class="modal_container">
                <div class="table">
                    <div class="table_header">
                        <p>Service Center Mechanics</p>
                        <div>
                            <input placeholder="Search" id="search" onkeyup="searchTable()" />
                            <button class="add_new" onclick="openAddModal()">Add Assignment</button>
                        </div>
                    </div>
                    <div class="table_section">
                        <table id="assignmentsTable">
                            <thead>
                                <tr>
                                    <th>Assignment ID</th>
                                    <th>Service Center</th>
                                    <th>Mechanic</th>
                                    <th>Date Assigned</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT scm.service_center_mechanic_id, sc.service_center_name, u.full_name, scm.date_assigned
                                          FROM service_center_mechanics scm
                                          JOIN service_centers sc ON scm.service_center_id = sc.service_center_id
                                          JOIN users u ON scm.mechanic_id = u.user_id
                                          WHERE u.role = 'mechanic'";
                                $result = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>{$row['service_center_mechanic_id']}</td>";
                                    echo "<td data-original='{$row['service_center_name']}'>
                                            <select onchange=\"enableSave(this, {$row['service_center_mechanic_id']}, 'service_center')\">";
                                    $service_center_query = "SELECT service_center_id, service_center_name FROM service_centers";
                                    $service_center_result = mysqli_query($conn, $service_center_query);
                                    while ($service_center_row = mysqli_fetch_assoc($service_center_result)) {
                                        $selected = ($service_center_row['service_center_name'] == $row['service_center_name']) ? "selected" : "";
                                        echo "<option value='{$service_center_row['service_center_id']}' $selected>{$service_center_row['service_center_name']}</option>";
                                    }
                                    echo "</select>
                                          </td>";
                                    echo "<td data-original='{$row['full_name']}'>
                                            <select onchange=\"enableSave(this, {$row['service_center_mechanic_id']}, 'mechanic')\">";
                                    $mechanic_query = "SELECT user_id, full_name FROM users WHERE role = 'mechanic'";
                                    $mechanic_result = mysqli_query($conn, $mechanic_query);
                                    while ($mechanic_row = mysqli_fetch_assoc($mechanic_result)) {
                                        $selected = ($mechanic_row['full_name'] == $row['full_name']) ? "selected" : "";
                                        echo "<option value='{$mechanic_row['user_id']}' $selected>{$mechanic_row['full_name']}</option>";
                                    }
                                    echo "</select>
                                          </td>";
                                    echo "<td>{$row['date_assigned']}</td>";
                                    echo "<td>
                          <button onclick=\"saveAssignment(this, {$row['service_center_mechanic_id']})\" disabled><i class='fa-solid fa-save'></i></button>
                          <button onclick=\"deleteAssignment({$row['service_center_mechanic_id']})\"><i class='fa-solid fa-trash'></i></button>
                        </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="addModal" class="modal" style="display: none">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">Add Assignment</h5>
                <button type="button" class="close close-icon" onclick="closeAddModal()">
                    <span class="material-icons-sharp">close</span>
                </button>
            </div>
            <div class="modal_container">
                <form id="addAssignmentForm">
                    <label for="service_center_id">Service Center:</label>
                    <select id="service_center_id" name="service_center_id" required>
                        <?php
                        $service_center_query = "SELECT service_center_id, service_center_name FROM service_centers";
                        $service_center_result = mysqli_query($conn, $service_center_query);
                        while ($service_center_row = mysqli_fetch_assoc($service_center_result)) {
                            echo "<option value='{$service_center_row['service_center_id']}'>{$service_center_row['service_center_name']}</option>";
                        }
                        ?>
                    </select>

                    <label for="mechanic_id">Mechanic:</label>
                    <select id="mechanic_id" name="mechanic_id" required>
                        <?php
                        $mechanic_query = "SELECT user_id, full_name FROM users WHERE role = 'mechanic'";
                        $mechanic_result = mysqli_query($conn, $mechanic_query);
                        while ($mechanic_row = mysqli_fetch_assoc($mechanic_result)) {
                            echo "<option value='{$mechanic_row['user_id']}'>{$mechanic_row['full_name']}</option>";
                        }
                        ?>
                    </select>

                    <button class="add_new" type="submit">Add Assignment</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        function searchTable() {
            const input = document.getElementById("search");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("assignmentsTable");
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

        function saveAssignment(button, assignmentId) {
            console.log('Save button clicked for assignment ID:', assignmentId); // Debugging line

            const row = button.parentElement.parentElement;
            const serviceCenterSelect = row.cells[1].querySelector("select");
            const mechanicSelect = row.cells[2].querySelector("select");

            const serviceCenterId = serviceCenterSelect.value;
            const mechanicId = mechanicSelect.value;

            const changes = {};
            if (serviceCenterId != serviceCenterSelect.getAttribute('data-original')) {
                changes.service_center_id = serviceCenterId;
            }
            if (mechanicId != mechanicSelect.getAttribute('data-original')) {
                changes.mechanic_id = mechanicId;
            }

            console.log('Changes to be saved:', changes); // Debugging line

            if (Object.keys(changes).length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Changes',
                    text: 'No changes detected to save.',
                });
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_assignment.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Assignment Updated',
                            text: response.message,
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            serviceCenterSelect.setAttribute('data-original', serviceCenterId);
                            mechanicSelect.setAttribute('data-original', mechanicId);
                            button.disabled = true;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                        });
                    }
                }
            };
            xhr.send("assignment_id=" + assignmentId + "&changes=" + encodeURIComponent(JSON.stringify(changes)));
        }


        function enableSave(selectElement, assignmentId, type) {
            console.log('Enable save called for:', assignmentId, type); // Debugging line

            const row = selectElement.parentElement.parentElement;
            const saveButton = row.querySelector("button[onclick^='saveAssignment']");
            if (saveButton) {
                saveButton.disabled = false;
            } else {
                console.error('Save button not found in the row for assignment ID:', assignmentId); // Debugging line
            }
        }

        function deleteAssignment(assignmentId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "delete_assignment.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 2000,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                }).then(() => {
                                    document.querySelector(`tr[data-id='${assignmentId}']`).remove();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                });
                            }
                        }
                    };
                    xhr.send("assignment_id=" + assignmentId);
                }
            });
        }

        function openAddModal() {
            document.getElementById("addModal").style.display = "block";
        }

        function closeAddModal() {
            document.getElementById("addModal").style.display = "none";
        }

        document.getElementById("addAssignmentForm").addEventListener("submit", function(event) {
            event.preventDefault();

            const formData = new FormData(this);

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "add_assignment.php", true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Assignment Added',
                            text: response.message,
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                        });
                    }
                }
            };

            xhr.send(formData);
        });
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
    input,
    select {
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