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
    <title>View Maintenance Tasks</title>
</head>

<body>
    <div id="modalDialog" class="modal" style="display: none">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">View Maintenance Tasks</h5>
                <button type="button" class="close close-icon">
                    <span class="material-icons-sharp">close</span>
                </button>
            </div>
            <!--  Modal Body  -->
            <div class="modal_container">
                <div class="table">
                    <div class="table_header">
                        <p>Maintenance Tasks</p>
                        <div>
                            <input placeholder="Search" id="search" onkeyup="searchTable()" />
                            <button class="add_new" onclick="openAddModal()">Add Task</button>
                        </div>
                    </div>
                    <div class="table_section">
                        <table id="tasksTable">
                            <thead>
                                <tr>
                                    <th>Task ID</th>
                                    <th>Task Name</th>
                                    <th>Estimated Time (hours)</th>
                                    <th>Additional Details</th>
                                    <th>Date Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM maintenance_tasks";
                                $result = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>{$row['task_id']}</td>";
                                    echo "<td contenteditable='true' data-original='{$row['task_name']}'>{$row['task_name']}</td>";
                                    echo "<td contenteditable='true' data-original='{$row['estimated_time']}'>{$row['estimated_time']}</td>";
                                    echo "<td contenteditable='true' data-original='{$row['additional_details']}'>{$row['additional_details']}</td>";
                                    echo "<td>{$row['date_created']}</td>";
                                    echo "<td>
                          <button onclick=\"saveTask(this, {$row['task_id']})\" disabled><i class='fa-solid fa-save'></i></button>
                          <button onclick=\"deleteTask({$row['task_id']})\"><i class='fa-solid fa-trash'></i></button>
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

    <!-- Add Task Modal -->
    <div id="addModal" class="modal" style="display: none">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">Add Maintenance Task</h5>
                <button type="button" class="close close-icon" onclick="closeAddModal()">
                    <span class="material-icons-sharp">close</span>
                </button>
            </div>
            <div class="modal_container">
                <form id="addTaskForm">
                    <label for="task_name">Task Name:</label>
                    <input type="text" id="task_name" name="task_name" required>

                    <label for="estimated_time">Estimated Time (hours):</label>
                    <input type="number" id="estimated_time" name="estimated_time" required>

                    <label for="additional_details">Additional Details:</label>
                    <textarea id="additional_details" name="additional_details"></textarea>

                    <button class="add_new" type="submit">Add Task</button>
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
            const table = document.getElementById("tasksTable");
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

        function validateTaskName(taskName) {
            return taskName.length >= 2 && taskName.length <= 255;
        }

        function validateEstimatedTime(time) {
            return time > 0 && Number.isInteger(time);
        }

        function validateAdditionalDetails(details) {
            return details.length <= 1000;
        }

        function saveTask(button, taskId) {
            const row = button.parentElement.parentElement;
            const taskNameCell = row.cells[1];
            const estimatedTimeCell = row.cells[2];
            const additionalDetailsCell = row.cells[3];

            const taskName = taskNameCell.textContent.trim();
            const estimatedTime = parseInt(estimatedTimeCell.textContent.trim(), 10);
            const additionalDetails = additionalDetailsCell.textContent.trim();

            // Input validation
            if (!validateTaskName(taskName)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Task Name',
                    text: 'Task Name should be 2-255 characters long.',
                });
                return;
            }
            if (!validateEstimatedTime(estimatedTime)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Estimated Time',
                    text: 'Estimated Time should be a positive integer.',
                });
                return;
            }
            if (!validateAdditionalDetails(additionalDetails)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Additional Details',
                    text: 'Additional Details should be up to 1000 characters long.',
                });
                return;
            }

            const checkAndSave = () => {
                const changedFields = {};
                if (taskName !== taskNameCell.getAttribute('data-original')) {
                    changedFields.task_name = taskName;
                }
                if (estimatedTime !== estimatedTimeCell.getAttribute('data-original')) {
                    changedFields.estimated_time = estimatedTime;
                }
                if (additionalDetails !== additionalDetailsCell.getAttribute('data-original')) {
                    changedFields.additional_details = additionalDetails;
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
                xhr.open("POST", "update_task.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Task Updated',
                            text: 'Task details have been successfully updated.',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            // Update the original data attributes with new values
                            taskNameCell.setAttribute('data-original', taskName);
                            estimatedTimeCell.setAttribute('data-original', estimatedTime);
                            additionalDetailsCell.setAttribute('data-original', additionalDetails);

                            // Disable the save button
                            button.disabled = true;
                        });
                    }
                };

                const data = `task_id=${taskId}&changes=${encodeURIComponent(JSON.stringify(changedFields))}`;
                xhr.send(data);
            };

            // Proceed with saving the task details
            checkAndSave();
        }

        function deleteTask(taskId) {
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
                    xhr.open("POST", "delete_task.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: 'Task has been successfully deleted.',
                                timer: 2000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    };
                    xhr.send(`task_id=${taskId}`);
                }
            });
        }

        document.querySelectorAll('#tasksTable td[contenteditable=true]').forEach(cell => {
            cell.addEventListener('input', (event) => {
                const row = event.target.parentElement;
                const saveButton = row.querySelector('button[onclick^="saveTask"]');
                saveButton.disabled = false;
            });
        });

        function openAddModal() {
            document.getElementById("addModal").style.display = "block";
        }

        function closeAddModal() {
            document.getElementById("addModal").style.display = "none";
        }

        document.getElementById('addTaskForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const taskName = document.getElementById('task_name').value.trim();
            const estimatedTime = parseInt(document.getElementById('estimated_time').value.trim(), 10);
            const additionalDetails = document.getElementById('additional_details').value.trim();

            // Input validation
            if (!validateTaskName(taskName)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Task Name',
                    text: 'Task Name should be 2-255 characters long.',
                });
                return;
            }
            if (!validateEstimatedTime(estimatedTime)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Estimated Time',
                    text: 'Estimated Time should be a positive integer.',
                });
                return;
            }
            if (!validateAdditionalDetails(additionalDetails)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Additional Details',
                    text: 'Additional Details should be up to 1000 characters long.',
                });
                return;
            }

            // Send the data using AJAX
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "add_task.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Task Added',
                        text: 'New task has been successfully added.',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            };

            const data = `task_name=${encodeURIComponent(taskName)}&estimated_time=${estimatedTime}&additional_details=${encodeURIComponent(additionalDetails)}`;
            xhr.send(data);
        });

        document.querySelector('.close-icon').addEventListener('click', () => {
            document.getElementById("modalDialog").style.display = "none";
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
<!--  End of Css  -->