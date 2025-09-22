<?php 
session_start();
include 'db_connect.php';
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
    background: #ffffff;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    width: 280px;
}
input {
    display: block; 
    padding: 0.7rem; 
    border-radius: 8px; 
    border: 1px solid #ddd;
    width: 90%;
    margin: 0.5rem auto;    
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
.password-status {
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

    </style>
</head>
<body>
    <form method="POST">
    <h1>Change Password</h1>
    <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
    <input type="password" id="OldPassword" name="password" placeholder="Old Password" required />
    <input type="password" id="NewPassword" name="password" placeholder="New Password" required />
    <div id="password-status" class="password-status"></div>
    
    <input type="password" id="ConfirmPassword" name="password" placeholder="Confirm New Password" required />
    <div class="strength-meter">
        <div id="strength-bar"></div>
    </div>
    <div id="confirm-status" class="password-status"></div>

    <button type="submit">Save Changes</button>
<script>

</script>
</body>
</html>