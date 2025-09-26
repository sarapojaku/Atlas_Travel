<?php
session_start();
include 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: client_login.php");
    exit;
}

$username = $_SESSION['username'];

// Fetch client details
$stmt = $conn->prepare("SELECT ClientID, ProfileImage FROM client WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

if (!$client) {
    $error = "User not found.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'yes') {
            // Remove profile image if it exists and is not default
            if (!empty($client['ProfileImage']) && $client['ProfileImage'] !== "images/default-profile.png" && file_exists($client['ProfileImage'])) {
                unlink($client['ProfileImage']);
            }

            // Delete client from database
            $stmt = $conn->prepare("DELETE FROM client WHERE ClientID = ?");
            $stmt->bind_param("i", $client['ClientID']);
            if ($stmt->execute()) {
                // Logout user
                session_unset();
                session_destroy();
                header("Location: index.php?deleted=1");
                exit;
            } else {
                $error = "Failed to delete account. Please try again.";
            }
        } elseif ($_POST['action'] === 'no') {
            header("Location: edit_profile.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delete My Account</title>
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
        width: 390px;
    }
    h1 { text-align: center; margin-bottom: 15px; }
    .buttons-grid { display: grid; justify-content: center; align-items: center; }
    .buttons-grid h4 { font-weight: lighter; text-align: center; }
    .buttons-row { display: flex; justify-content: center; gap: 20px; margin-top: 10px; }
    button {
        padding: 11px 25px; 
        border: none; 
        border-radius: 8px; 
        cursor: pointer; 
        color: #fff; 
        font-size: 15px;
    }
    .btn-yes {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #d9534f;
    }
    .btn-yes:hover { transform: scale(1.05); background: #bb241fff; }
    .btn-no {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #5cb85c;
    }
    .btn-no:hover { transform: scale(1.05); background: #29b329ff; }
    .message { text-align: center; margin-bottom: 1rem; }
</style>
</head>
<body>
<form method="post">
    <h1>Delete my Account</h1>

    <?php 
    if (!empty($error)) echo "<div class='message' style='color:red;'>$error</div>";
    ?>

    <div class="buttons-grid">
        <h4>Are you sure you want to delete your account?</h4>
        <div class="buttons-row">
            <button type="submit" name="action" value="yes" class="btn-yes">Yes</button>
            <button type="submit" name="action" value="no" class="btn-no">No</button>
        </div>
    </div>
</form>
</body>
</html>