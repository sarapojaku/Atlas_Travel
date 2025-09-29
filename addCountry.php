<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    $CountryName = trim($_POST['CountryName']);
    $CountryInfo = trim($_POST['CountryInfo']);

    // Check if country exists
    $stmt = $conn->prepare("SELECT * FROM country WHERE CountryName = ?");
    $stmt->bind_param("s", $CountryName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Country already exists'); window.location.href='admin.php#countries';</script>";
        exit;
    }

    // Insert country into DB
    $insert = $conn->prepare("INSERT INTO country (CountryName, CountryInfo) VALUES (?, ?)");
    $insert->bind_param("ss", $CountryName, $CountryInfo);

    if ($insert->execute()) {
        echo "<script>alert('Country added successfully'); window.location.href='admin.php#countries';</script>";
        exit;
    } else {
        echo "<script>alert('Failed to add country'); window.location.href='admin.php#countries';</script>";
        exit;
    }
}

$conn->close();
?>
