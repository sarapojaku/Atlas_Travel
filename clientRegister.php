<?php
session_start();
include 'db_connect.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ClientName    = $_POST['ClientName'];
    $ClientSurname = $_POST['ClientSurname'];
    $Username      = $_POST['Username'];
    $Email         = $_POST['Email'];
    $Gender        = $_POST['Gender'];
    $Phone         = $_POST['Phone'];
    $Password      = password_hash($_POST['Password'], PASSWORD_BCRYPT);

    $check = $conn->prepare("SELECT ClientID FROM Client WHERE Username = ?");
    $check->bind_param("s", $Username);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = "Username already taken!";
    } else {
        $stmt = $conn->prepare("INSERT INTO Client (ClientName, ClientSurname, Username, Email, Gender, Phone, Password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $ClientName, $ClientSurname, $Username, $Email, $Gender, $Phone, $Password);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $Username;
            header("Location: header.php");
            exit;
        } else {
            $error = "Error creating account!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign Up</title>
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
        width: 300px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    input, select {
        display: block;
        margin: 0.5rem auto;
        padding: 0.7rem;
        border-radius: 8px;
        border: 1px solid #ddd;
        width: 90%;
        box-sizing: border-box;
        height: 40px;
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
    h1 {
        text-align: center;
        margin-bottom: 1rem;
    }
    .message {
        color: red;
        margin-bottom: 1rem;
        text-align: center;
    }
    p {
        text-align: center;
        margin-top: 0.5rem;
    }
    a {
        color: #000000;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<form method="POST" enctype="multipart/form-data">
    <h1>Sign Up</h1>
    <?php if (!empty($error)) echo "<div class='message'>$error</div>"; ?>
    <input type="text" name="ClientName" placeholder="First Name" required>
    <input type="text" name="ClientSurname" placeholder="Surname" required>
    <input type="text" name="Username" placeholder="Username" required>
    <input type="email" name="Email" placeholder="Email" required>
    <select name="Gender" required>
        <option value="">Select Gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
    </select>
    <input type="text" name="Phone" placeholder="Phone Number" required>
    <input type="password" name="Password" placeholder="Password" required>
    <button type="submit">Sign Up</button>
    <p>Already have an account? <a href="client_login.php">Log In</a></p>
</form>

</body>
</html>
