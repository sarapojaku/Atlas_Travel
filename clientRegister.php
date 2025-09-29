<?php  
session_start();
include 'db_connect.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['Password'];
    $confirm  = $_POST['ConfirmPassword'];

    // Backend password validation
    if ($password !== $confirm) {
        $error = "Passwords do not match!";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $error = "Password must be at least 8 characters, include 1 uppercase letter, 1 number, and 1 special character.";
    } else {
        $ClientName    = $_POST['ClientName'];
        $ClientSurname = $_POST['ClientSurname'];
        $Username      = $_POST['Username'];
        $Email         = $_POST['Email'];
        $Gender        = $_POST['Gender'];
        $Phone         = $_POST['Phone'];
        $PasswordHash  = password_hash($password, PASSWORD_BCRYPT);

        $check = $conn->prepare("SELECT ClientID FROM Client WHERE Username = ?");
        $check->bind_param("s", $Username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Username already taken!";
        } else {
            $stmt = $conn->prepare("INSERT INTO Client (ClientName, ClientSurname, Username, Email, Gender, Phone, Password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $ClientName, $ClientSurname, $Username, $Email, $Gender, $Phone, $PasswordHash);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $Username;
                header("Location: index.php");
                exit;
            } else {
                $error = "Error creating account!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign Up</title>
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
        margin: 0;
    }
    form {
        background: #fff;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        width: 500px;
    }
    h1 {
        text-align: center;
        margin-bottom: 1rem;
    }
    .message {
        color: red;
        margin-bottom: 1rem;
        text-align: center;
    }
    .form-row {
        display: flex;
        gap: 10px;
        margin-bottom: 0.8rem;
    }
    .form-row input,
    .form-row select {
        flex: 1;
        padding: 0.7rem;
        border-radius: 8px;
        border: 1px solid #ddd;
        box-sizing: border-box;
        height: 40px;
    }
    .password-requirements, .confirm-status {
        font-size: 0.9rem;
        margin-top: -5px;
        margin-bottom: 10px;
        text-align: center;
    }
    .password-requirements div {
        text-align: left;
        font-size: 0.85rem;
        margin-left: 5%;
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
    button {
        padding: 0.7rem 1.5rem;
        background: #625d5d;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        display: block;
        margin: 1rem auto 0 auto;
        height: 35px;
    }
    button:hover {
        background: #4a4545;
    }
    a {
        color: #000000;
        text-decoration: none;
    }
    a:hover{
        text-decoration: underline;
    }
</style>
</head>
<body>

<form method="POST" onsubmit="return validateForm()">
    <h1>Sign Up</h1>
    <?php if (!empty($error)) echo "<div class='message'>$error</div>"; ?>
    
    <div class="form-row">
        <input type="text" id="fname" name="ClientName" placeholder="First Name" required>
        <input type="text" id="lname" name="ClientSurname" placeholder="Surname" required>
    </div>

    <div class="form-row">
        <input type="text" id="username" name="Username" placeholder="Username" required>
        <input type="email" name="Email" placeholder="Email" required>
    </div>
    <div id="username-status" class="username-status"></div>

    <div class="form-row">
        <select name="Gender" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>
        <input type="text" name="Phone" placeholder="Phone Number" required>
    </div>
    
    <div class="form-row">
        <input type="password" id="Password" name="Password" placeholder="Password" required>
        <input type="password" id="ConfirmPassword" name="ConfirmPassword" placeholder="Confirm Password" required>
    </div>
    
    <div class="strength-meter">
        <div id="strength-bar"></div>
    </div>
    
    <div id="password-status" class="password-requirements">
        <div id="req-length">• At least 8 characters</div>
        <div id="req-uppercase">• At least 1 uppercase letter</div>
        <div id="req-number">• At least 1 number</div>
        <div id="req-special">• At least 1 special character</div>
    </div>
    
    <div id="confirm-status" class="confirm-status"></div>
    
    <button type="submit">Sign Up</button>
    <p style="text-align:center;">Already have an account? <a href="client_login.php">Log In</a></p>
</form>

<script>
// --- Real-time username generator ---
function generateUsername() {
    let fname = document.getElementById("fname").value.trim().toLowerCase();
    let lname = document.getElementById("lname").value.trim().toLowerCase();
    let usernameField = document.getElementById("username");

    if (fname && lname) {
        let initials = lname.charAt(0);
        let reverseInitials = fname.charAt(0);
        let randomNum = Math.floor(Math.random() * 900 + 100);

        let suggestions = [
            fname + initials,
            lname + reverseInitials,
            fname + lname + randomNum
        ];

        usernameField.value = suggestions[Math.floor(Math.random() * suggestions.length)];
        checkUsername();
    }
}

document.getElementById("fname").addEventListener("keyup", generateUsername);
document.getElementById("lname").addEventListener("keyup", generateUsername);

// --- AJAX username check (after 3 characters) ---
function checkUsername() {
    let username = document.getElementById("username").value;
    let status = document.getElementById("username-status");

    if (username.length < 3) {
        status.textContent = "";
        return;
    }

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "check_username.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (this.responseText === "taken") {
            status.textContent = "Username already taken!";
            status.style.color = "red";
        } else {
            status.textContent = "Username available ✓";
            status.style.color = "green";
        }
    };
    xhr.send("username=" + encodeURIComponent(username));
}

// --- Password validation ---
const passwordField = document.getElementById("Password");
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
        case 3: bar.style.width="75%"; bar.style.background="blue"; break;
        case 4: bar.style.width="100%"; bar.style.background="green"; break;
    }
});

// --- Confirm password ---
document.getElementById("ConfirmPassword").addEventListener("keyup", function() {
    let password = document.getElementById("Password").value;
    let confirm = this.value;
    let status = document.getElementById("confirm-status");

    if (!confirm) {
        status.textContent = "";
    } else if (password !== confirm) {
        status.textContent = "Passwords do not match!";
        status.style.color = "red";
    } else {
        status.textContent = "Passwords match.";
        status.style.color = "green";
    }
});

// --- Form validation ---
function validateForm() {
    const password = passwordField.value;
    const confirm = document.getElementById("ConfirmPassword").value;
    const regex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

    if (!regex.test(password)) return false;
    if (password !== confirm) return false;
    return true;
}
</script>

</body>
</html>
