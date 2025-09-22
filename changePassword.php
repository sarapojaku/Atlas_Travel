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
.password-status, .confirm-status {
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
</style>
</head>
<body>
    <form method="POST">
        <h1>Change Password</h1>
        <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>

        <h4>Enter Old Password</h4>
        <input type="password" id="OldPassword" name="old_password" placeholder="Old Password" required />

        <h4>Enter New Password</h4>
        <input type="password" id="NewPassword" name="new_password" placeholder="New Password" required />
        <div id="password-status" class="password-status"></div>

        <h4>Confirm New Password</h4>
        <input type="password" id="ConfirmPassword" name="confirm_password" placeholder="Confirm New Password" required />
        
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
</body>
</html>
