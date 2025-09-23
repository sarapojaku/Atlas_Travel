<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ClientName = trim($_POST['ClientName']);
    $ClientSurname = trim($_POST['ClientSurname']);
    $DestinationID = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($DestinationID <= 0) {
        die("Invalid destination ID.");
    }

    // 1. Find the client in DB
    $sql = "SELECT ClientID FROM client WHERE ClientName = ? AND ClientSurname = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $ClientName, $ClientSurname);
    $stmt->execute();
    $stmt->bind_result($ClientID);
    $stmt->fetch();
    $stmt->close();

    if (!$ClientID) {
        die("Client not found. Please register first.");
    }

    // 2. Get destination price
    $sql = "SELECT DestinationPrice FROM destination WHERE DestinationID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $DestinationID);
    $stmt->execute();
    $stmt->bind_result($price);
    $stmt->fetch();
    $stmt->close();

    if (!$price) {
        die("Invalid destination.");
    }

    // 3. Insert booking
    $sql = "INSERT INTO booking (ClientSpendings, ClientID, DestinationID) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dii", $price, $ClientID, $DestinationID);
    $stmt->execute();
    $stmt->close();

    // 4. Update client spending
    $sql = "UPDATE client SET Spending = Spending + ? WHERE ClientID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $price, $ClientID);
    $stmt->execute();
    $stmt->close();

    // 5. Update destination revenue
    $sql = "UPDATE destination SET Revenue = Revenue + ? WHERE DestinationID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $price, $DestinationID);
    $stmt->execute();
    $stmt->close();

    echo "<p style='color:green;font-weight:bold;'>Booking successful! Spending and destination revenue updated.</p>";
}

?>