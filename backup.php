<script>
    document.getElementById("createUserForm").addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent default form submission

        // Display the loading alert
        Swal.fire({
            title: 'Creating User...',
            text: 'Please wait while we process your request.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Get the form data
        var formData = new FormData(this);

        // Send the form data via AJAX
        fetch("<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>", {
            method: "POST",
            body: formData
        }).then(response => response.text()).then(data => {
            // Close the loading alert
            Swal.close();

            // Handle the response from the server
            var parser = new DOMParser();
            var doc = parser.parseFromString(data, 'text/html');
            var messageBox = doc.querySelector('.message-box');
            if (messageBox) {
                if (messageBox.classList.contains('success')) {
                    Swal.fire('Success', messageBox.innerHTML, 'success');
                } else if (messageBox.classList.contains('error')) {
                    Swal.fire('Error', messageBox.innerHTML, 'error');
                }
            } else {
                Swal.fire('Error', 'An unknown error occurred.', 'error');
            }
        }).catch(error => {
            Swal.close();
            Swal.fire('Error', 'Failed to create user. Please try again later.', 'error');
        });
    });
</script>