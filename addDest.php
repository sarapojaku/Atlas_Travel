<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {

    $DestinationName = trim($_POST['DestinationName']);
    $DestinationInfo = trim($_POST['DestinationInfo']);
    $DestinationPrice = trim($_POST['DestinationPrice']);
    $StartDate = trim($_POST['StartDate']);
    $EndDate = trim($_POST['EndDate']);
    $CountryID = $_POST['CountryID'];

    // Handle file upload
    $DestinationImage = "";
    if (isset($_FILES['DestinationImage']) && $_FILES['DestinationImage']['error'] === 0) {
        $targetDir = "uploads/";
        $DestinationImage = basename($_FILES['DestinationImage']['name']);
        $targetFile = $targetDir . $DestinationImage;

        if (!move_uploaded_file($_FILES['DestinationImage']['tmp_name'], $targetFile)) {
            echo "<script>alert('Failed to upload image'); window.location.href='admin.php#destinations';</script>";
            exit;
        }
    }

    // Insert destination
    $insert = $conn->prepare("INSERT INTO destination (DestinationName, DestinationInfo, DestinationPrice, StartDate, EndDate, DestinationImage, CountryID) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("ssdsssi", $DestinationName, $DestinationInfo, $DestinationPrice, $StartDate, $EndDate, $DestinationImage, $CountryID);

    if ($insert->execute()) {
        echo "<script>alert('Destination added successfully'); window.location.href='admin.php#destinations';</script>";
        exit;
    } else {
        echo "<script>alert('Failed to add destination'); window.location.href='admin.php#destinations';</script>";
        exit;
    }
}
$conn->close();
?>
