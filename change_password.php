<!-- php Code -->
<?php
require 'db_config/db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  try {
    // Retrieve form data
    $oldPassword = $_POST["old_password"];
    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];

    // Verify that new password and confirm password match
    if ($newPassword !== $confirmPassword) {
      throw new Exception("New password and confirm password do not match.");
    }

    // Get the current user's ID from the session 
    session_start();
    $userId = $_SESSION['user_id'];

    // Fetch the user's current password from the database
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($hashedPasswordFromDb);
    if ($stmt->num_rows == 0) {
      throw new Exception("User not found.");
    }
    $stmt->fetch();

    // Verify the old password
    if (!password_verify($oldPassword, $hashedPasswordFromDb)) {
      throw new Exception("Old password is incorrect.");
    }

    // Check if the new password is the same as the old password
    if (password_verify($newPassword, $hashedPasswordFromDb)) {
      throw new Exception("New password cannot be the same as the old password.");
    }

    // Hash the new password
    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database and set password_change to true
    $stmt = $conn->prepare("UPDATE users SET password = ?, password_change = ? WHERE user_id = ?");
    $passwordChange = true;
    $stmt->bind_param("sii", $hashedNewPassword, $passwordChange, $userId);

    // Execute the update query
    if (!$stmt->execute()) {
      throw new Exception("Failed to update password: " . $stmt->error);
    }

    // Success message
    echo "<div class='message-box success'>Password successfully changed.</div>";
  } catch (Exception $e) {
    // Error message
    echo "<div class='message-box error'>Error: " . $e->getMessage() . "</div>";
  } finally {
    // Close the statement and connection
    if (isset($stmt)) {
      $stmt->close();
    }
    $conn->close();
  }

  // Prevent further processing
  exit();
}
?>

<!-- HTML Code -->
<head>
  <link rel="stylesheet" href="assets/css/message_box.css" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
</head>

<div id="modalDialog" class="modal" style="display: block">
  <div class="modal-content animate-top">
    <div class="modal-header">
      <h5 class="modal-title">Change Password</h5>
      <button type="button" class="close close-icon" onclick="window.location.href='logout.php'">
        <span class="material-icons-sharp">close</span>
      </button>
    </div>
    <div class="modal_container">
      <div id="message-container"></div>
      <form id="changePasswordForm" method="post" action="">
        <div class="user-details">
          <div class="input-box">
            <span class="details">Old Password</span>
            <input type="password" name="old_password" placeholder="Enter your old password" required />
          </div>
          <div class="input-box">
            <span class="details">New Password</span>
            <input type="password" name="new_password" placeholder="Enter your new password" required />
          </div>
          <div class="input-box">
            <span class="details">Confirm Password</span>
            <input type="password" name="confirm_password" placeholder="Confirm password" required />
          </div>
        </div>
        <div class="button">
          <input type="submit" value="Change Password" />
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JavaScript Code -->
<!-- JavaScript Code -->
<script>
  document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);
    const newPassword = formData.get('new_password');

    // Password validation regex
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    if (!passwordRegex.test(newPassword)) {
      // Display error message if password does not meet requirements
      document.getElementById('message-container').innerHTML = '<div class="message-box error">Password must have at least 8 characters, contain at least one lowercase letter, one uppercase letter, one digit, and one special character.</div>';
    } else {
      fetch('change_password.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then(data => {
          document.getElementById('message-container').innerHTML = data;
        })
        .catch(error => {
          console.error('Error:', error);
          document.getElementById('message-container').innerHTML = '<div class="message-box error">An error occurred. Please try again later.</div>';
        });
    }
  });
</script>

<!-- HTML Code -->

<!--  Css  -->
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
  }

  body {
    display: flex;
    height: auto;
    justify-content: center;
    align-items: center;
    padding: 10px;
  }

  .modal_container {
    padding: 20px;
  }

  ::-webkit-input-placeholder {
    /* Chrome/Opera/Safari */
    color: #969494;
  }

  ::-moz-placeholder {
    /* Firefox 19+ */
    color: #969494;
  }

  :-ms-input-placeholder {
    /* IE 10+ */
    color: #969494;
  }

  :-moz-placeholder {
    /* Firefox 18- */
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
    z-index: 5;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    /* Semi-transparent background */
    backdrop-filter: blur(5px);
    /* Apply blur effect */
  }

  .modal-content {
    margin: 8% auto;
    border: 1px solid #888;
    max-width: 400px;
    width: auto;
    height: auto;
    background-color: #fff;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 10px;
    outline: 0;
  }

  .modal-header {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: start;
    -ms-flex-align: start;
    align-items: flex-start;
    -webkit-box-pack: justify;
    -ms-flex-pack: justify;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    border-top-left-radius: 0.3rem;
    border-top-right-radius: 0.3rem;
  }

  .modal-title {
    margin-bottom: 0;
    line-height: 1.5;
    margin-top: 0;
  }

  h5.modal-title {
    font-size: 1.25rem;
    color: #666;
  }

  .close {
    float: right;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: 0.5;
  }

  button.close {
    padding: 0;
    background-color: transparent;
    border: 0;
    /*-webkit-appearance: none;*/
  }

  .modal-header .close {
    padding: 1rem;
    margin: -1rem -1rem -1rem auto;
  }

  .close:not(:disabled):not(.disabled) {
    cursor: pointer;
  }

  .modal-body {
    flex: 1 1 auto;
    padding: 1rem;
  }

  .modal-body p {
    margin-top: 0;
    margin-bottom: 1rem;
  }

  .modal-footer {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: end;
    -ms-flex-pack: end;
    justify-content: flex-end;
    padding: 1rem;
    border-top: 1px solid #e9ecef;
  }

  .modal_container form .user-details {
    display: block;
    flex-wrap: wrap;
    justify-content: space-between;
    /*margin: 20px 0 12px 0;*/
  }

  form .user-details .input-box {
    margin-bottom: 15px;
    width: auto;
  }

  .user-details .input-box .details {
    display: block;
    font-weight: 500;
    margin-bottom: 5px;
  }

  form .user-details .input-box input {
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

  .user-details .input-box input:focus,
  .user-details .input-box input:valid {
    border-color: #6c9bcf;
  }

  form .gender-details .gender-title {
    font-size: 20px;
    font-weight: 500;
  }

  form .gender-details .category {
    display: flex;
    width: 80%;
    margin: 14px 0;
    justify-content: space-between;
  }

  .gender-details .category label {
    display: flex;
  }

  .gender-details .category .dot {
    height: 18px;
    width: 18px;
    background: #d9d9d9;
    border-radius: 50%;
    margin-right: 10px;
    border: 5px solid transparent;
    transition: all 0.3s ease;
  }

  #dot-1:checked~.category label .one,
  #dot-2:checked~.category label .two,
  #dot-3:checked~.category label .three {
    border-color: #d9d9d9;
    background: #6c9bcf;
  }

  form input[type="radio"] {
    display: none;
  }

  form .button {
    height: 45px;
    width: auto;
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
    background: #6c9bcf;
  }

  form .button input:hover {
    background: linear-gradient(-135deg, #71b7e6, #6c9bcf);
  }

  @media (max-width: 584px) {
    .modal_container {
      max-width: 100%;
    }

    form .user-details .input-box {
      margin-bottom: 15px;
      width: 100%;
    }

    form .gender-details .category {
      width: 100%;
    }

    .modal_container form .user-details {
      max-height: 300px;
      overflow-y: scroll;
    }

    .user-details::-webkit-scrollbar {
      width: 0;
    }
  }
</style>
<!--  End of Css  -->