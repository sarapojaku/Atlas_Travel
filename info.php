<?php 
include 'db_connect.php';

//Get ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid destination ID");
}

$destinationId = intval($_GET['id']);

// Fetch all destinations
$sql = "SELECT DestinationName, DestinationInfo, DestinationPlaces, DestinationImage, DestinationPrice, StartDate, EndDate
        FROM Destination
        WHERE DestinationID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $destinationId);
$stmt->execute();
$result = $stmt->get_result();
$dest = $result->fetch_assoc();

if (!$dest) {
    die("Destination not found.");
}

// Path to uploaded images
$imagePath = "uploads/";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learn More</title>
    <link rel="stylesheet" href="styles.css"/>
      <link rel="icon" href="images/logo.png" type="image/png" />
  <link rel="shortcut icon" href="images/logo.png" type="image/png" />
    <style>
    /* Info */
    .learn-more {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 10px;
        margin-bottom: 20px;
        gap: 50px;
        flex-wrap: wrap;
        text-align: center;
    }
    h2 {
        margin-top: 10px;
        text-align: center;
        font-size: 30px;
    }
    .name-image img {
        width: 300px;
        height: 300px;
        border-radius: 20px;
        flex: 0 0 auto;
    }
    .info {
        max-width: 425px;
        text-align: left;
    }
    .info p {
        margin-bottom: 5px;
        line-height: 1.5;
    }
    .info ul {
        list-style: none;
    }
    .info ul span {
        font-weight: bold;
    }
    </style>
</head>
<body>
    
    <!-- Navbar Section -->
<?php include 'header.php'; ?>

<!-- Hero Section -->
<section class="hello">
  <h1>Welcome to our agency!</h1>
  <p>
    Travel made easy. Let us guide you to amazing destinations while you sit
    back and enjoy the journey.
  </p>
</section>

<!-- Destination Info Section -->
<h2><?php echo htmlspecialchars($dest['DestinationName']); ?></h2>

<section class="learn-more">
    <div class="name-image">
        <img src="<?php echo $imagePath . htmlspecialchars($dest['DestinationImage']); ?>" alt="<?php echo htmlspecialchars($dest['DestinationName']); ?>" />
    </div>
    <div class="info">
        <p><?php echo nl2br($dest['DestinationInfo']); ?></p>
        <!-- Top 3 Places to Visit -->
        <?php if (!empty($dest['DestinationPlaces'])): ?>
            <h3>Top Places to Visit:</h3>
            <ul style="list-style: disc; padding-left: 20px; margin-bottom: 5px;">
                <?php $places = explode(',', $dest['DestinationPlaces']);
                foreach ($places as $place): 
                ?>
                <li><?php echo htmlspecialchars(trim($place)); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <ul>
                <span>Date: </span><?php echo date("d/m/Y", strtotime($dest['StartDate'])) . " - " . date("d/m/Y", strtotime($dest['EndDate'])); ?>
                <li><span>Price: €</span><?php echo htmlspecialchars($dest['DestinationPrice']); ?></li>
            </ul>
            <a style="background: var(--bg); color:#ffffff; margin-top: 5px;" href="booking.php?id=<?php echo $destinationId; ?>" class="btn">Book Now</a>
        </div>
        <!-- <div class="book-btn"> -->
        <!-- </div> -->
</section>

<!-- Contact Us Section -->
<?php include 'contact.php';?>

<!-- Footer Section -->
<?php include 'footer.php';?>

<script src="script.js"></script>
</body>
</html>