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
  <title>View Users</title>
  <style>
    /* Include your existing CSS here */
    /* ... */
  </style>
</head>

<body>
  <div id="modalDialog" class="modal" style="display: none">
    <div class="modal-content animate-top">
      <div class="modal-header">
        <h5 class="modal-title">View Users</h5>
        <button type="button" class="close close-icon">
          <span class="material-icons-sharp">close</span>
        </button>
      </div>
      <!--  Modal Body  -->
      <div class="modal_container">
        <div class="table">
          <div class="table_header">
            <p>Users Details</p>
            <div>
              <input placeholder="Search" id="search" onkeyup="searchTable()" />
            </div>
          </div>
          <div class="table_section">
            <table id="usersTable">
              <thead>
                <tr>
                  <th>User ID</th>
                  <th>Username</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone Number</th>
                  <th>Date of Birth</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Date Created</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $query = "SELECT * FROM users";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                  echo "<tr>";
                  echo "<td>{$row['user_id']}</td>";
                  echo "<td contenteditable='true'>{$row['username']}</td>";
                  echo "<td contenteditable='true'>{$row['full_name']}</td>";
                  echo "<td contenteditable='true'>{$row['email']}</td>";
                  echo "<td contenteditable='true'>{$row['phone_number']}</td>";
                  echo "<td contenteditable='true'>{$row['dob']}</td>";
                  echo "<td>
                                    <select>
                                        <option value='Manager' " . ($row['role'] == 'Manager' ? 'selected' : '') . ">Manager</option>
                                        <option value='Driver' " . ($row['role'] == 'Driver' ? 'selected' : '') . ">Driver</option>
                                        <option value='Mechanic' " . ($row['role'] == 'Mechanic' ? 'selected' : '') . ">Mechanic</option>
                                    </select>
                                </td>";
                  echo "<td>
                                    <select>
                                        <option value='active' " . ($row['status'] == 'active' ? 'selected' : '') . ">Active</option>
                                        <option value='suspended' " . ($row['status'] == 'suspended' ? 'selected' : '') . ">Suspended</option>
                                    </select>
                                </td>";
                  echo "<td>{$row['date_created']}</td>";
                  echo "<td>
                                    <button onclick=\"saveUser(this, {$row['user_id']})\"><i class='fa-solid fa-save'></i></button>
                                    <button onclick=\"deleteUser({$row['user_id']})\"><i class='fa-solid fa-trash'></i></button>
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

  <!-- js  -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <script>
    function searchTable() {
      const input = document.getElementById("search");
      const filter = input.value.toLowerCase();
      const table = document.getElementById("usersTable");
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

    function validateUsername(username) {
      // Username should be 2-20 characters alphanumeric with no spaces,
      // and can include underscores and hyphens.
      const usernameRegex = /^[a-zA-Z0-9_-]{2,20}$/;
      return usernameRegex.test(username);
    }

    function validateName(name) {
      // Name should be 2-100 alphabetical characters and can include hyphens,
      // apostrophes, and spaces between names.
      const nameRegex = /^[a-zA-Z'-]{2,100}( [a-zA-Z'-]{2,100})?$/;
      return nameRegex.test(name);
    }

    function validateEmail(email) {
      // Validate email format
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email);
    }

    function validatePhoneNumber(phoneNumber) {
      // Validate phone number format
      const phoneRegex = /^\d{10}$/; // Assuming 10-digit phone number
      return phoneRegex.test(phoneNumber);
    }

    function validateDOB(dob) {
      // Validate date of birth
      const dobDate = new Date(dob);
      const currentDate = new Date();
      const minDOB = new Date(currentDate.getFullYear() - 100, currentDate.getMonth(), currentDate.getDate());
      const maxDOB = new Date(currentDate.getFullYear() - 18, currentDate.getMonth(), currentDate.getDate());
      return dobDate > minDOB && dobDate < maxDOB;
    }


    function saveUser(button, userId) {
      const row = button.parentElement.parentElement;
      const username = row.cells[1].textContent.trim();
      const fullName = row.cells[2].textContent.trim();
      const email = row.cells[3].textContent.trim();
      const phoneNumber = row.cells[4].textContent.trim();
      const dob = row.cells[5].textContent.trim();
      const role = row.cells[6].querySelector('select').value;
      const status = row.cells[7].querySelector('select').value;

      // Input validation
      if (!validateUsername(username)) {
        // Handle invalid username
        Swal.fire({
          icon: 'error',
          title: 'Invalid Username',
          text: 'Username should be 2-20 characters alphanumeric with no spaces, and can include underscores and hyphens.',
        });
        return;
      }
      if (!validateName(fullName)) {
        // Handle invalid name
        Swal.fire({
          icon: 'error',
          title: 'Invalid Name',
          text: 'Name should be 2-100 alphabetical characters and can include hyphens, apostrophes, and spaces between names.',
        });
        return;
      }
      if (!validateEmail(email)) {
        // Handle invalid email
        Swal.fire({
          icon: 'error',
          title: 'Invalid Email',
          text: 'Please enter a valid email address.',
        });
        return;
      }
      if (!validatePhoneNumber(phoneNumber)) {
        // Handle invalid phone number
        Swal.fire({
          icon: 'error',
          title: 'Invalid Phone Number',
          text: 'Please enter a valid phone number.',
        });
        return;
      }
      if (!validateDOB(dob)) {
        // Handle invalid date of birth
        Swal.fire({
          icon: 'error',
          title: 'Invalid Date of Birth',
          text: 'Date of Birth should be more than 18 years ago and less than 100 years ago.',
        });
        return;
      }

      // Perform AJAX request to check for uniqueness of username and email
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "check_uniqueness.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
          const response = JSON.parse(this.responseText);
          if (response.usernameExists) {
            Swal.fire({
              icon: 'error',
              title: 'Username already exists',
              text: 'Please choose a different username.',
            });
          } else if (response.emailExists) {
            Swal.fire({
              icon: 'error',
              title: 'Email already exists',
              text: 'Please choose a different email address.',
            });
          } else {
            // No conflicts, proceed with updating user
            updateUser(userId, username, fullName, email, phoneNumber, dob, role, status);
          }
        }
      };
      xhr.send(`username=${username}&email=${email}`);
    }

    function updateUser(userId, username, fullName, email, phoneNumber, dob, role, status) {
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "update_user.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
          // Handle the response from the server if needed
          const response = this.responseText;
          // Example: Show a success message using a library like SweetAlert
          Swal.fire({
            icon: 'success',
            title: 'User Updated',
            text: response,
          }).then(() => {
            // Optionally reload the page or update the UI
            location.reload();
          });
        }
      };
      xhr.send(`user_id=${userId}&username=${username}&full_name=${fullName}&email=${email}&phone_number=${phoneNumber}&dob=${dob}&role=${role}&status=${status}`);
    }

    function deleteUser(userId) {
      Swal.fire({
        title: "Are you sure you want to delete this user?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete!',
        customClass: {
          popup: 'small-alert'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          const xhr = new XMLHttpRequest();
          xhr.open("POST", "delete_user.php", true);
          xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
          xhr.onreadystatechange = function() {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
              Swal.fire({
                title: 'Deleted!',
                text: 'User deleted successfully.',
                icon: 'success',
                timer: 2000,
                timerProgressBar: true,
              }).then(() => {
                location.reload();
              });
            }
          }
          xhr.send("user_id=" + userId);
        }
      });
    }

    document.querySelector('.close-icon').addEventListener('click', function() {
      document.getElementById('modalDialog').style.display = 'none';
    });
  </script>

  <!-- end of js  -->
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

<!--  End of Css  -->