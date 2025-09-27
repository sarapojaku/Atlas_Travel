<?php
session_start();
include 'db_connect.php';
require 'send_confirmation.php'; // PHPMailer function
require 'vendor/autoload.php'; // PHPMailer & dompdf
use Dompdf\Dompdf;

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

    // 2. Get destination info
    $stmt = $conn->prepare("SELECT DestinationName, DestinationPrice FROM destination WHERE DestinationID=?");
    $stmt->bind_param("i", $DestinationID);
    $stmt->execute();
    $result = $stmt->get_result();
    $dest = $result->fetch_assoc();
    if (!$dest) die("Destination not found.");
    $price = $dest['DestinationPrice'];
    $destName = $dest['DestinationName'];

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

    // 6. Generate PDF of the bill
    $dompdf = new Dompdf();
    $tax = 0.2 * $price;
    $totalPrice = $price + $tax;

    $billHtml = "
    <h2>Booking Receipt</h2>
    <p>Hello {$ClientName} {$ClientSurname},</p>
    <p>Destination: <strong>{$destName}</strong></p>
    <p>Price: €" . number_format($price, 2) . "</p>
    <p>Tax (20%): €" . number_format($tax, 2) . "</p>
    <p><strong>Total: €" . number_format($totalPrice, 2) . "</strong></p>
    <p>Date: " . date('d/m/Y') . "</p>
    ";

    $dompdf->loadHtml($billHtml);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $pdfOutput = $dompdf->output();
    $pdfFilePath = tempnam(sys_get_temp_dir(), 'bill_') . '.pdf';
    file_put_contents($pdfFilePath, $pdfOutput);

    // 7. Send confirmation email with PDF
    $emailStatus = sendConfirmationEmailWithPDF($ClientName, $ClientSurname, $email, $DestinationID, $price, $pdfFilePath);

    // 8. Store client info in session
    $_SESSION['clientName'] = $ClientName;
    $_SESSION['clientSurname'] = $ClientSurname;
    $_SESSION['clientEmail'] = $email;

    // 9. Redirect to bill.php
    header("Location: bill.php?id=$DestinationID&status=success");
    exit;
}
?>
