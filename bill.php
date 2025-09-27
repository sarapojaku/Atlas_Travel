<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid destination ID");
}

$destinationId = intval($_GET['id']);

// ✅ Get client info from session
$clientName    = isset($_SESSION['clientName']) ? $_SESSION['clientName'] : '';
$clientSurname = isset($_SESSION['clientSurname']) ? $_SESSION['clientSurname'] : '';
$clientEmail   = isset($_SESSION['clientEmail']) ? $_SESSION['clientEmail'] : '';

// Fetch destination details
$sql = "SELECT DestinationName, DestinationPrice FROM destination WHERE DestinationID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $destinationId);
$stmt->execute();
$result = $stmt->get_result();
$dest = $result->fetch_assoc();

if (!$dest) die("Destination not found.");

$destName = $dest['DestinationName'];
$productPrice = $dest['DestinationPrice'];

// Tax + total
$tax = 0.2 * $productPrice;
$final_price = $productPrice + $tax;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill</title>
    <link rel="icon" href="images/logo.png" type="image/png" />
    <link rel="shortcut icon" href="images/logo.png" type="image/png" />
    <style>
        /* body { font-family: Arial, sans-serif; padding: 20px; }
        .main { max-width: 700px; margin: 0 auto; }
        .hello h2 { margin-bottom: 5px; }
        .payment, .info, table, .total { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        table, td, th { border: 1px solid #ccc; padding: 10px; }
        table thead { font-weight: bold; }
        .total h2 { text-align: right; } */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #625d5d;
            margin: 0;
        }
        .main {
            background: #ffffff;
            padding: 2rem;
            border-radius: 4px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            width: 500px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        table, 
        td, 
        th {
            border: none;
            padding: 10px;
        }
        table thead {
            font-weight: bold;
        }
        .total h2 {
            text-align: left;
        }


    </style>
</head>
<body>
    <div class="main">
        <div class="hello">
            <h2>Hello <?php echo htmlspecialchars($clientName); ?>,</h2>
            <p>This is the receipt for a payment of €<?php echo number_format($productPrice, 2); ?></p>
        </div>

        <hr>

        <div class="payment">
            <div class="number">
                <?php $paymentNumber = '#' . rand(10000, 99999); ?>
                <strong>Payment Number: <?php echo $paymentNumber; ?></strong>
            </div>
            <div class="date">
                <?php $paymentDate = date("d/m/Y"); ?>
                <strong>Date: <?php echo $paymentDate; ?></strong>
            </div>
        </div>

        <hr>

        <div class="info">
            <div class="client">
                <strong><?php echo htmlspecialchars($clientName . ' ' . $clientSurname); ?></strong>
                <p>Email: <a href="mailto:<?php echo htmlspecialchars($clientEmail); ?>"><?php echo htmlspecialchars($clientEmail); ?></a></p>
            </div>
            <div class="agency">
                <strong>Atlas Travel</strong>
                <p>Email: <a href="mailto:travelatlas24@gmail.com">travelatlas24@gmail.com</a></p>
            </div>
        </div>

        <hr>

        <table>
            <thead>
                <th>Description Details</th>
            </thead>
            <tbody>
                <tr>
                    <td>Destination Name</td>
                    <td><?php echo htmlspecialchars($destName); ?></td>
                </tr>
                <tr>
                    <td>Destination Price</td>
                    <td>€<?php echo number_format($productPrice, 2); ?></td>
                </tr>
                <tr>
                    <td>Added Tax</td>
                    <td>€<?php echo number_format($tax, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="total">
            <h2>Total: €<?php echo number_format($final_price, 2); ?></h2>
        </div>
    </div>
</body>
</html>