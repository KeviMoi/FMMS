<script>
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
            text: 'License Plate should be 1-15 alphanumeric characters.',
        });
        return;
    }
    if (!validateYear(year)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Year',
            text: 'Year should be a valid four-digit number.',
        });
        return;
    }
    if (!validateVIN(vin)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid VIN',
            text: 'VIN should be exactly 17 alphanumeric characters.',
        });
        return;
    }
    if (!validateMileage(mileage)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Mileage',
            text: 'Mileage should be a valid number.',
        });
        return;
    }

    // Check for uniqueness
    checkUniqueness("license_plate", licensePlate, vehicleId, function (isLicensePlateUnique) {
        if (!isLicensePlateUnique) {
            Swal.fire({
                icon: 'error',
                title: 'Duplicate License Plate',
                text: 'The license plate already exists in the database.',
            });
            return;
        }

        checkUniqueness("vin", vin, vehicleId, function (isVinUnique) {
            if (!isVinUnique) {
                Swal.fire({
                    icon: 'error',
                    title: 'Duplicate VIN',
                    text: 'The VIN already exists in the database.',
                });
                return;
            }

            // Proceed to save
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_vehicle.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Vehicle details updated successfully.',
                        });
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
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to update vehicle details. Please try again.',
                        });
                    }
                }
            };
            const data = `vehicle_id=${vehicleId}&license_plate=${licensePlate}&make=${make}&model=${model}&year=${year}&vin=${vin}&mileage=${mileage}&fuel_type=${fuelType}&status=${status}&assigned_driver_id=${assignedDriverId}`;
            xhr.send(data);
        });
    });
}

document.querySelectorAll("td[contenteditable=true]").forEach(cell => {
    cell.addEventListener("input", function() {
        const originalValue = this.dataset.original.trim();
        const currentValue = this.textContent.trim();
        const saveButton = this.parentElement.querySelector("button");
        saveButton.disabled = (originalValue === currentValue);
    });
});

document.querySelectorAll("select").forEach(select => {
    select.addEventListener("change", function() {
        const originalValue = this.parentElement.dataset.original.trim();
        const currentValue = this.value;
        const saveButton = this.parentElement.parentElement.querySelector("button");
        saveButton.disabled = (originalValue === currentValue);
    });
});
</script>
