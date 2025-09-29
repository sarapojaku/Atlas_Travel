<?php
session_start();
include 'db_connect.php';
require 'vendor/autoload.php';

use Dompdf\Dompdf;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid destination ID");
}

$destinationId = intval($_GET['id']);

// ✅ Client info
$clientName    = $_SESSION['clientName'] ?? '';
$clientSurname = $_SESSION['clientSurname'] ?? '';
$clientEmail   = $_SESSION['clientEmail'] ?? '';

// ✅ Fetch destination
$sql = "SELECT DestinationName, DestinationPrice, StartDate, EndDate FROM destination WHERE DestinationID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $destinationId);
$stmt->execute();
$result = $stmt->get_result();
$dest = $result->fetch_assoc();

if (!$dest) die("Destination not found.");

$destName     = $dest['DestinationName'];
$productPrice = $dest['DestinationPrice'];
$StartDate    = $dest['StartDate'];
$EndDate      = $dest['EndDate'];

$final_price  = $productPrice;
$paymentNumber = '#' . rand(10000, 99999);
$paymentDate   = date("d/m/Y");

// ✅ Build HTML for PDF
$html = "
<h2>Hello $clientName,</h2>
<p>This is the receipt for a payment of <strong>€" . number_format($productPrice, 2) . "</strong> you made to Atlas Travel.</p>

<h4>Payment Info</h4>
<p><strong>Payment No:</strong> $paymentNumber<br>
<strong>Payment Date:</strong> $paymentDate</p>

<h4>Client Info</h4>
<p><strong>$clientName $clientSurname</strong><br>
Email: $clientEmail</p>

<h4>Details</h4>
<table border='1' cellspacing='0' cellpadding='8' width='100%'>
  <tr><td><b>Destination Name</b></td><td>$destName</td></tr>
  <tr><td><b>Start Date</b></td><td>$StartDate</td></tr>
  <tr><td><b>End Date</b></td><td>$EndDate</td></tr>
  <tr><td><b>Destination Price</b></td><td>€" . number_format($productPrice, 2) . "</td></tr>
  <tr><td><b>Total</b></td><td><strong>€" . number_format($final_price, 2) . "</strong></td></tr>
</table>
";

// ✅ Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ✅ Stream to browser (force download)
$dompdf->stream("bill_$paymentNumber.pdf", ["Attachment" => true]);
