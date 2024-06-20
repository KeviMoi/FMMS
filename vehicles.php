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
  <title>View Vehicles</title>
</head>

<body>
  <div id="modalDialog" class="modal" style="display: none">
    <div class="modal-content animate-top">
      <div class="modal-header">
        <h5 class="modal-title">View Vehicles</h5>
        <button type="button" class="close close-icon">
          <span class="material-icons-sharp">close</span>
        </button>
      </div>
      <!--  Modal Body  -->
      <div class="modal_container">
        <div class="table">
          <div class="table_header">
            <p>Vehicle Details</p>
            <div>
              <input placeholder="Search" id="search" onkeyup="searchTable()" />
            </div>
          </div>
          <div class="table_section">
            <table id="vehiclesTable">
              <thead>
                <tr>
                  <th>Vehicle ID</th>
                  <th>License Plate</th>
                  <th>Make</th>
                  <th>Model</th>
                  <th>Year</th>
                  <th>VIN</th>
                  <th>Mileage</th>
                  <th>Fuel Type</th>
                  <th>Status</th>
                  <th>Assigned Driver ID</th>
                  <th>Registration Date</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $query = "SELECT * FROM vehicles";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                  echo "<tr>";
                  echo "<td>{$row['vehicle_id']}</td>";
                  echo "<td contenteditable='true' data-original='{$row['license_plate']}'>{$row['license_plate']}</td>";
                  echo "<td contenteditable='true' data-original='{$row['make']}'>{$row['make']}</td>";
                  echo "<td contenteditable='true' data-original='{$row['model']}'>{$row['model']}</td>";
                  echo "<td contenteditable='true' data-original='{$row['year']}'>{$row['year']}</td>";
                  echo "<td contenteditable='true' data-original='{$row['vin']}'>{$row['vin']}</td>";
                  echo "<td contenteditable='true' data-original='{$row['mileage']}'>{$row['mileage']}</td>";
                  echo "<td data-original='{$row['fuel_type']}'>
                          <select>
                            <option value='Petrol' " . ($row['fuel_type'] == 'Petrol' ? 'selected' : '') . ">Petrol</option>
                            <option value='Diesel' " . ($row['fuel_type'] == 'Diesel' ? 'selected' : '') . ">Diesel</option>
                            <option value='Electric' " . ($row['fuel_type'] == 'Electric' ? 'selected' : '') . ">Electric</option>
                            <option value='Hybrid' " . ($row['fuel_type'] == 'Hybrid' ? 'selected' : '') . ">Hybrid</option>
                          </select>
                        </td>";
                  echo "<td data-original='{$row['status']}'>
                          <select>
                            <option value='Active' " . ($row['status'] == 'Active' ? 'selected' : '') . ">Active</option>
                            <option value='Inactive' " . ($row['status'] == 'Inactive' ? 'selected' : '') . ">Inactive</option>
                            <option value='In Service' " . ($row['status'] == 'In Service' ? 'selected' : '') . ">In Service</option>
                            <option value='Retired' " . ($row['status'] == 'Retired' ? 'selected' : '') . ">Retired</option>
                          </select>
                        </td>";
                  echo "<td contenteditable='true' data-original='{$row['assigned_driver_id']}'>{$row['assigned_driver_id']}</td>";
                  echo "<td>{$row['registration_date']}</td>";
                  echo "<td>
                          <button onclick=\"saveVehicle(this, {$row['vehicle_id']})\" disabled><i class='fa-solid fa-save'></i></button>
                          <button onclick=\"deleteVehicle({$row['vehicle_id']})\"><i class='fa-solid fa-trash'></i></button>
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
      const table = document.getElementById("vehiclesTable");
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

    function validateLicensePlate(licensePlate) {
      const licensePlateRegex = /^K[A-Z]{2}\s*\d{3}[A-Z]$/;
      return licensePlateRegex.test(licensePlate);
    }

    function validateMake(make) {
      const makeRegex = /^[a-zA-Z0-9\s-]{1,50}$/;
      return makeRegex.test(make);
    }

    function validateModel(model) {
      const modelRegex = /^[a-zA-Z0-9\s-]{1,50}$/;
      return modelRegex.test(model);
    }

    function validateYear(year) {
      const yearRegex = /^(19|20)\d{2}$/;
      const currentYear = new Date().getFullYear();
      return yearRegex.test(year) && year <= currentYear;
    }

    function validateVIN(vin) {
      const vinRegex = /^[A-HJ-NPR-Z0-9]{17}$/;
      return vinRegex.test(vin);
    }

    function validateMileage(mileage) {
      return Number.isInteger(Number(mileage)) && mileage >= 0;
    }

    function checkUniqueness(field, value, vehicleId, callback) {
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "check_vehicle_uniqueness.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
          const response = JSON.parse(xhr.responseText);
          callback(response.unique);
        }
      };
      xhr.send(`field=${field}&value=${value}&vehicle_id=${vehicleId}`);
    }

    function saveVehicle(button, vehicleId) {
      const row = button.parentElement.parentElement;
      const licensePlateCell = row.cells[1];
      const makeCell = row.cells[2];
      const modelCell = row.cells[3];
      const yearCell = row.cells[4];
      const vinCell = row.cells[5];
      const mileageCell = row.cells[6];
      const fuelTypeCell = row.cells[7].querySelector('select');
      const statusCell = row.cells[8].querySelector('select');
      const assignedDriverIdCell = row.cells[9];

      const licensePlate = licensePlateCell.textContent.trim();
      const make = makeCell.textContent.trim();
      const model = modelCell.textContent.trim();
      const year = yearCell.textContent.trim();
      const vin = vinCell.textContent.trim();
      const mileage = mileageCell.textContent.trim();
      const fuelType = fuelTypeCell.value;
      const status = statusCell.value;
      const assignedDriverId = assignedDriverIdCell.textContent.trim();

      // Input validation
      if (!validateLicensePlate(licensePlate)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid License Plate',
          text: 'License plate should be 1-15 characters alphanumeric with no spaces, and can include hyphens.',
        });
        return;
      }
      if (!validateMake(make)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Make',
          text: 'Make should be 1-50 characters and can include alphanumeric, spaces, and hyphens.',
        });
        return;
      }
      if (!validateModel(model)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Model',
          text: 'Model should be 1-50 characters and can include alphanumeric, spaces, and hyphens.',
        });
        return;
      }
      if (!validateYear(year)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Year',
          text: 'Year should be a valid 4-digit number and not greater than the current year.',
        });
        return;
      }
      if (!validateVIN(vin)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid VIN',
          text: 'VIN should be exactly 17 characters alphanumeric with no spaces.',
        });
        return;
      }
      if (!validateMileage(mileage)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Mileage',
          text: 'Mileage should be a non-negative integer.',
        });
        return;
      }

      const saveChanges = () => {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "update_vehicle.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4 && xhr.status === 200) {
            Swal.fire({
              icon: 'success',
              title: 'Vehicle Updated',
              text: xhr.responseText,
              timer: 2000,
              timerProgressBar: true,
              showConfirmButton: false
            }).then(() => {
              licensePlateCell.dataset.original = licensePlate;
              makeCell.dataset.original = make;
              modelCell.dataset.original = model;
              yearCell.dataset.original = year;
              vinCell.dataset.original = vin;
              mileageCell.dataset.original = mileage;
              fuelTypeCell.dataset.original = fuelType;
              statusCell.dataset.original = status;
              assignedDriverIdCell.dataset.original = assignedDriverId;
              button.disabled = true;
            });
          } else if (xhr.readyState === 4) {
            Swal.fire({
              icon: 'error',
              title: 'Update Failed',
              text: xhr.responseText,
            });
          }
        };
        xhr.send(`vehicle_id=${vehicleId}&license_plate=${licensePlate}&make=${make}&model=${model}&year=${year}&vin=${vin}&mileage=${mileage}&fuel_type=${fuelType}&status=${status}&assigned_driver_id=${assignedDriverId}`);
      };

      checkUniqueness('license_plate', licensePlate, vehicleId, (isUnique) => {
        if (!isUnique) {
          Swal.fire({
            icon: 'error',
            title: 'Duplicate License Plate',
            text: 'The license plate is already in use. Please use a unique license plate.',
          });
          return;
        }

        checkUniqueness('vin', vin, vehicleId, (isUnique) => {
          if (!isUnique) {
            Swal.fire({
              icon: 'error',
              title: 'Duplicate VIN',
              text: 'The VIN is already in use. Please use a unique VIN.',
            });
            return;
          }

          checkUniqueness('assigned_driver_id', assignedDriverId, vehicleId, (isUnique) => {
            if (!isUnique) {
              Swal.fire({
                icon: 'error',
                title: 'Driver Already Assigned',
                text: 'The assigned driver is already assigned to another vehicle. Please choose a different driver.',
              });
              return;
            }

            saveChanges();
          });
        });
      });
    }


    function deleteVehicle(vehicleId) {
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
          xhr.open("POST", "delete_vehicle.php", true);
          xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
          xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
              Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'Vehicle has been deleted.',
              timer: 2000,
              timerProgressBar: true,
              showConfirmButton: false
            }).then(() => {
                location.reload();
              });
            }
          };
          xhr.send(`vehicle_id=${vehicleId}`);
        }
      });
    }

    document.querySelectorAll("[contenteditable='true']").forEach(cell => {
      cell.addEventListener('input', function() {
        const row = this.parentElement;
        const button = row.querySelector('button');
        button.disabled = false;
      });
    });

    document.querySelectorAll("select").forEach(select => {
      select.addEventListener('change', function() {
        const row = this.parentElement.parentElement;
        const button = row.querySelector('button');
        button.disabled = false;
      });
    });

    document.querySelectorAll('.close-icon').forEach(button => {
      button.addEventListener('click', function() {
        document.getElementById('modalDialog').style.display = 'none';
      });
    });

    function showVehicleModal() {
      document.getElementById('modalDialog').style.display = 'block';
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