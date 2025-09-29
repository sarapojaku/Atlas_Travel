<?php
session_start();
include 'db_connect.php';

if (!isset($_POST['bookingID'])) exit;

$bookingID = intval($_POST['bookingID']);
$username  = $_SESSION['username'] ?? '';

if (!$username) exit;

// Get client ID
$stmt = $conn->prepare("SELECT ClientID FROM client WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
if (!$client) exit;

$clientID = $client['ClientID'];

// ✅ Get booking details (DestinationID + AmountPaid)
$stmt = $conn->prepare("
    SELECT b.DestinationID, d.DestinationPrice 
    FROM booking b 
    JOIN destination d ON b.DestinationID = d.DestinationID 
    WHERE b.BookingID=? AND b.ClientID=?
");
$stmt->bind_param("ii", $bookingID, $clientID);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    http_response_code(404);
    echo "Booking not found";
    exit;
}

$DestinationID = $booking['DestinationID'];
$price = $booking['DestinationPrice'];

// ✅ Delete booking
$stmt = $conn->prepare("DELETE FROM booking WHERE BookingID=? AND ClientID=?");
$stmt->bind_param("ii", $bookingID, $clientID);

if ($stmt->execute()) {
    // ✅ Subtract from client spending
    $stmt = $conn->prepare("UPDATE client SET Spending = Spending - ? WHERE ClientID=?");
    $stmt->bind_param("di", $price, $clientID);
    $stmt->execute();

    // ✅ Subtract from destination revenue
    $stmt = $conn->prepare("UPDATE destination SET Revenue = Revenue - ? WHERE DestinationID=?");
    $stmt->bind_param("di", $price, $DestinationID);
    $stmt->execute();

    http_response_code(200);
    echo "Booking cancelled";
} else {
    http_response_code(500);
    echo "Error cancelling booking";
}
?>
