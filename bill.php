<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid destination ID");
}

$destinationId = intval($_GET['id']);

// Fetch destination details
    $sql = "SELECT DestinationName, DestinationInfo, DestinationPlaces, DestinationPrice, StartDate, EndDate
            FROM destination
            WHERE DestinationID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $destinationId);
    $stmt->execute();
    $result = $stmt->get_result();
    $dest = $result->fetch_assoc();

 // Get destination price
    $stmt = $conn->prepare("SELECT DestinationPrice FROM destination WHERE DestinationID=?");
    $stmt->bind_param("i", $DestinationID);
    $stmt->execute();
    $result = $stmt->get_result();
    $dest = $result->fetch_assoc();
    if (!$dest) die("Destination not found.");

    $price = $dest['DestinationPrice'];

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

    </style>
</head>
<body>
    <div class="main">
        <div class="hello">
            <h2>Hello <?php echo $clientName; ?>,</h2>
            <p>This is the receipt for a payment of <?php echo $price;?></p>
        </div>

        <hr>  <!-- Line after -->

        <div class="payment">
            <div class="number">
                <?php
                  // Generate a random payment number
                  $paymentNumber = '#' . rand(10000, 99999); // Generates a random 5-digit number prefixed with #
                  ?>
                  <strong><?php echo $paymentNumber; ?></strong>
            </div>
            <div class="date">
                <?php
                  // Get the current date and format it
                  $paymentDate = date("d/M/Y");
                  ?>
                  <strong><?php echo $paymentDate; ?></strong>
            </div>
        </div>

        <div class="info">
            <div class="client">
                <strong><?php echo $clientName.' '.$clientSurname;?></strong>
                <p>
                    <br />
                    <a href="#!" class="text-purple"><?php echo $clientEmail; ?></a>
                  </p>
            </div>
            <div class="agency">
                <a href="index.php" class="logo">
                    <div class="logo-icon"><img src="images/logo.png" /></div>
                    <span>Atlas Travel</span>
                </a>                
                <p>
                    <br />
                    <a href="#!" class="text-purple">travelatlas24@gmail.com</a>
                  </p>
            </div>
        </div>

        <table class="details">
            <thead>Description Details</thead>
            <tbody>
                <tr>
                  <td>Destination Name</td>
                  <td><?php echo $destName; ?></td>
                </tr>
                <tr>
                  <td>Destination Price</td>
                  <td><?php echo '$'.$productPrice; ?></td>
                </tr>
                <tr>
                  <td>Added Tax</td>
                  <?php
                  $tax=0.2*$productPrice;
                  ?>
                  <td><?php echo '€'.$tax; ?></td>
                </tr>
            </tbody>
        </table>

        <div class="total">
            <?php
            $final_price=$productPrice+$tax;
            ?>
            <h2><?php echo '€' .$final_price;?></h2>
        </div>

    </div>

    
</body>
</html>