<?php
session_start();
include 'db_connect.php';

if(!isset($_POST['bookingID'])) exit;

$bookingID = intval($_POST['bookingID']);
$username = $_SESSION['username'] ?? '';

if(!$username) exit;

// Get client ID
$stmt = $conn->prepare("SELECT ClientID FROM client WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
if(!$client) exit;

// Delete the booking
$delete = $conn->prepare("DELETE FROM booking WHERE BookingID = ? AND ClientID = ?");
$delete->bind_param("ii", $bookingID, $client['ClientID']);
if($delete->execute()){
    http_response_code(200);
    echo "Booking cancelled";
} else {
    http_response_code(500);
    echo "Error";
}
?>
