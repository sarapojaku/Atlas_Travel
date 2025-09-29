<?php
session_start();
include 'db_connect.php';
require 'send_confirmation.php'; // PHPMailer function


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ClientName    = trim($_POST['ClientName']);
    $ClientSurname = trim($_POST['ClientSurname']);
    $email         = trim($_POST['email']);   

    $DestinationID = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($DestinationID <= 0) {
        die("Invalid destination ID");
    }

    // 1. Find or create client
    $stmt = $conn->prepare("SELECT ClientID FROM client WHERE ClientName=? AND ClientSurname=? AND Email=?");
    $stmt->bind_param("sss", $ClientName, $ClientSurname, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $client = $result->fetch_assoc();

    if ($client) {
        $ClientID = $client['ClientID'];
    } else {
        $stmt = $conn->prepare("INSERT INTO client (ClientName, ClientSurname, Email, Spending, Rating) VALUES (?, ?, ?, 0, 0)");
        $stmt->bind_param("sss", $ClientName, $ClientSurname, $email);
        $stmt->execute();
        $ClientID = $stmt->insert_id;
    }

    // 2. Get destination price
    $stmt = $conn->prepare("SELECT DestinationPrice FROM destination WHERE DestinationID=?");
    $stmt->bind_param("i", $DestinationID);
    $stmt->execute();
    $result = $stmt->get_result();
    $dest = $result->fetch_assoc();
    if (!$dest) die("Destination not found.");
    $price = $dest['DestinationPrice'];

    
    // 3. Insert booking
    $stmt = $conn->prepare("INSERT INTO booking (ClientID, DestinationID) VALUES (?, ?)");
    $stmt->bind_param("ii", $ClientID, $DestinationID);
    $stmt->execute();

    // 4. Update client spending
    $stmt = $conn->prepare("UPDATE client SET Spending = Spending + ? WHERE ClientID=?");
    $stmt->bind_param("di", $price, $ClientID);
    $stmt->execute();

    // 5. Update destination revenue
    $stmt = $conn->prepare("UPDATE destination SET Revenue = Revenue + ? WHERE DestinationID=?");
    $stmt->bind_param("di", $price, $DestinationID);
    $stmt->execute();

    // 6. Send confirmation email
    $emailStatus = sendConfirmationEmail($ClientName, $ClientSurname, $email, $DestinationID, $price);

    // ✅ Store client info in session
    $_SESSION['clientName'] = $ClientName;
    $_SESSION['clientSurname'] = $ClientSurname;
    $_SESSION['clientEmail'] = $email;

    // 7. Redirect to bill.php
    header("Location: bill.php?id=$DestinationID&status=success");
    exit;
}
?>
