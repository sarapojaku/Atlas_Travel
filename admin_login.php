<?php
session_start();
include 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // ✅ Default admin login (bypasses DB)
    if ($username === "Admin" && $password === "Adminpass.24") {
        session_regenerate_id(true); // security
        $_SESSION['staff_logged_in'] = true;
        $_SESSION['username'] = "Admin";
        $_SESSION['type'] = "Manager"; // Default admin treated as Manager
        header("Location: admin.php");
        exit;
    }

    // ✅ Staff login from database
    $stmt = $conn->prepare("SELECT Password, Type FROM Staff WHERE Username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // ✅ Compare entered password with hashed password
            $validPassword = password_verify($password, $row['Password']);

            if ($validPassword) {
                session_regenerate_id(true); // prevent session fixation
                $_SESSION['staff_logged_in'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['type'] = $row['Type'];

                // ✅ Always go to admin.php
                header("Location: admin.php");
                exit;
            } else {
                $message = "Invalid username or password.";
            }
        } else {
            $message = "Invalid username or password.";
        }

        $stmt->close();
    } else {
        $message = "Database error: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff Login</title>
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
    box-shadow: 0 8px 20px;
    width: 280px;
}
input {
    display: block; 
    margin: 0 auto 1rem auto;
    padding: 0.7rem; 
    border-radius: 8px; 
    border: 1px solid #ddd;
    width: 80%;
}
button {
    padding: 0.7rem 1.5rem; 
    background: #625d5d; 
    color: #ffffff; 
    border: none; 
    border-radius: 8px; 
    cursor: pointer; 
    display: block;
    margin: 0 auto;
}
.message {
    color: red; 
    margin-bottom: 1rem;
    text-align: center;
}
h2 {
    text-align: center;
}
</style>
</head>
<body>

<form method="post">
    <h2>Staff Login</h2>
    <?php if($message) echo "<div class='message'>$message</div>"; ?>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

</body>
</html>
