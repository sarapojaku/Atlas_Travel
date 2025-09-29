<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    $StaffName = trim($_POST['StaffName']);
    $StaffSurname = trim($_POST['StaffSurname']);
    $Username = trim($_POST['Username']);
    $Email = trim($_POST['Email']);
    $Gender = $_POST['Gender'];
    $Phone = trim($_POST['Phone']);
    $Type = $_POST['Type'];
    $Password = $_POST['Password'];
    $ConfirmPassword = $_POST['ConfirmPassword'];
    $DateEmployed = $_POST['DateEmployed'];

    // Check passwords match
    if ($Password !== $ConfirmPassword) {
        echo "<script>alert('Passwords do not match'); window.location.href='staff.php';</script>";
        exit;
    }

    // Check if username exists
    $stmt = $conn->prepare("SELECT * FROM staff WHERE Username = ?");
    $stmt->bind_param("s", $Username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Username already exists'); window.location.href='staff.php';</script>";
        exit;
    }

    // Hash password
    $hashed_password = password_hash($Password, PASSWORD_DEFAULT);

    // Insert staff
    $insert = $conn->prepare("INSERT INTO staff (StaffName, StaffSurname, Username, Email, Gender, Phone, Password, Type, DateEmployed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("sssssssss", $StaffName, $StaffSurname, $Username, $Email, $Gender, $Phone, $hashed_password, $Type, $DateEmployed);

    if ($insert->execute()) {
        echo "<script>alert('Staff added successfully'); window.location.href='admin.php#staff';</script>";
        exit;
    } else {
        echo "<script>alert('Failed to add staff'); window.location.href='admin.php#staff';</script>";
        exit;
    }
}
$conn->close();
?>
