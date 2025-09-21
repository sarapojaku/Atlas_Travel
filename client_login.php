<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT ClientID, Password, ClientName, ClientSurname FROM Client WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['client_id'] = $user['ClientID'];
        $_SESSION['username'] = $username;
        $_SESSION['ClientName'] = $user['ClientName'];
        $_SESSION['ClientSurname'] = $user['ClientSurname'];

        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <link rel="stylesheet" href="styles.css" />
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
    h1, p {
        text-align: center;
        color: #000;
    }
    a {
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
<form method="POST">
    <h1>Log In</h1>
    <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Log In</button>
    <p>Don’t have an account?</p>
    <p><a href="clientRegister.php">Sign Up</a></p>
</form>
</body>
</html>
