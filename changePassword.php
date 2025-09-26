<?php
session_start();
include 'db_connect.php';

$error = "";
$success = "";

// Use the correct session variable from your login
$userId = $_SESSION['client_id'] ?? null;

if (!$userId) {
    die("You must be logged in to change your password.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldpassword = $_POST['OldPassword'] ?? '';
    $newpassword = $_POST['NewPassword'] ?? '';
    $confirm     = $_POST['ConfirmPassword'] ?? '';

    // Check if new and confirm match
    if ($newpassword !== $confirm) {
        $error = "New password and confirm password do not match!";
    } 
    // Check password strength
    elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $newpassword)) {
        $error = "Password must be at least 8 characters, include 1 uppercase letter, 1 number, and 1 special character.";
    } 
    else {
        // Fetch current hashed password from DB
        $stmt = $conn->prepare("SELECT Password FROM Client WHERE ClientID = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($stored_hash);
        $stmt->fetch();
        $stmt->close();

        // Verify old password
        if (!$stored_hash || !password_verify($oldpassword, $stored_hash)) {
            $error = "Old password is incorrect!";
        } else {
            // Optional: prevent using the same password
            if (password_verify($newpassword, $stored_hash)) {
                $error = "New password cannot be the same as the old password!";
            } else {
                // Hash new password
                $new_hash = password_hash($newpassword, PASSWORD_DEFAULT);

                // Update password in DB
                $update = $conn->prepare("UPDATE Client SET Password = ? WHERE ClientID = ?");
                $update->bind_param("si", $new_hash, $userId);

                if ($update->execute()) {
                    $success = "Password updated successfully!";

                    header("Location: myprofile.php");
                } else {
                    $error = "Something went wrong. Please try again.";
                }

                $update->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Change Password</title>
    <link rel="icon" href="images/logo.png" type="image/png" />
    <link rel="shortcut icon" href="images/logo.png" type="image/png" />
<style>
body { 
    font-family: sans-serif; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    height: 100vh; 
    background: #625d5d; 
}
form { 
    background: #fff; 
    padding: 2rem; 
    border-radius: 12px; 
    box-shadow: 0 8px 20px rgba(0,0,0,0.2); 
    width: 300px; 
}
h1 { 
    text-align: center; 
    margin-bottom: 1rem; 
}
h4 { 
    font-weight: lighter; 
    opacity: 0.7; 
    font-size: 13px; 
    margin: 10px 10px 4px 5px; 
    text-align: left; 
}
input { 
    display: block; 
    padding: 0.6rem; 
    border-radius: 8px; 
    border: 1px solid #ddd; 
    width: 90%; 
    margin: 0 auto 0.8rem auto; 
}
button { 
    padding: 0.7rem 1.5rem; 
    background: #625d5d; 
    color: #fff; 
    border: none; 
    border-radius: 8px; 
    cursor: pointer; 
    display: block; 
    margin: 1rem auto; 
}
.password-status, 
.confirm-status { 
    font-size: 0.9rem; 
    margin-top: -5px; 
    margin-bottom: 10px; 
    text-align: center; 
}
.strength-meter { 
    width: 90%; 
    height: 8px; 
    background: #ddd; 
    border-radius: 5px; 
    margin: 5px auto 10px auto; 
    overflow: hidden; 
}
#strength-bar { 
    height: 100%; 
    width: 0%; 
    background: red; 
    transition: width 0.3s ease, background 0.3s ease; 
}
.password-requirements { 
    font-size: 0.9rem; 
    margin-bottom: 10px; 
    padding-left: 5%; 
}
.password-requirements div { 
    text-align: left; 
    font-size: 0.85rem; 
}
.message { 
    text-align: center; 
    font-weight: bold; 
    margin-bottom: 10px; 
}
</style>
</head>
<body>
<form method="POST">
    <h1>Change Password</h1>

    <?php 
    if (!empty($error)) echo "<div class='message' style='color:red;'>$error</div>";
    if (!empty($success)) echo "<div class='message' style='color:green;'>$success</div>";
    ?>

    <h4>Enter Old Password</h4>
    <input type="password" id="OldPassword" name="OldPassword" placeholder="Old Password" required />

    <h4>Enter New Password</h4>
    <input type="password" id="NewPassword" name="NewPassword" placeholder="New Password" required />
    <div id="password-status" class="password-status"></div>

    <h4>Confirm New Password</h4>
    <input type="password" id="ConfirmPassword" name="ConfirmPassword" placeholder="Confirm New Password" required />
    <div class="strength-meter">
        <div id="strength-bar"></div>
    </div>

    <div class="password-requirements">
        <div id="req-length">• At least 8 characters</div>
        <div id="req-uppercase">• At least 1 uppercase letter</div>
        <div id="req-number">• At least 1 number</div>
        <div id="req-special">• At least 1 special character</div>
    </div>

    <div id="confirm-status" class="confirm-status"></div>

    <button type="submit">Save Changes</button>
</form>

<script>
// Password strength
const passwordField = document.getElementById("NewPassword");
passwordField.addEventListener("keyup", function() {
    const value = passwordField.value;
    document.getElementById("req-length").style.color = value.length >= 8 ? "green" : "red";
    document.getElementById("req-uppercase").style.color = /[A-Z]/.test(value) ? "green" : "red";
    document.getElementById("req-number").style.color = /\d/.test(value) ? "green" : "red";
    document.getElementById("req-special").style.color = /[\W_]/.test(value) ? "green" : "red";

    let strength = 0;
    if (value.length >= 8) strength++;
    if (/[A-Z]/.test(value)) strength++;
    if (/\d/.test(value)) strength++;
    if (/[\W_]/.test(value)) strength++;

    const bar = document.getElementById("strength-bar");
    switch (strength) {
        case 0: bar.style.width="0%"; bar.style.background="red"; break;
        case 1: bar.style.width="25%"; bar.style.background="red"; break;
        case 2: bar.style.width="50%"; bar.style.background="orange"; break;
        case 3: bar.style.width="70%"; bar.style.background="blue"; break;
        case 4: bar.style.width="100%"; bar.style.background="green"; break;
    }
});

// Confirm password match
document.getElementById("ConfirmPassword").addEventListener("keyup", function() {
    let password = document.getElementById("NewPassword").value;
    let confirm = this.value;
    let status = document.getElementById("confirm-status");

    if(!confirm) {
        status.textContent = "";
    } else if (password !== confirm) {
        status.textContent = "Passwords do not match!";
        status.style.color = "red";           
    } else {
        status.textContent = "Passwords match.";
        status.style.color = "green";
    }
});
</script>
</body>
</html>
