<?php 
include 'db_connect.php';

//Get ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid destination ID");
}

$destinationId = intval($_GET['id']);

// Fetch all destinations
$sql = "SELECT DestinationName, DestinationInfo, DestinationPlaces, DestinationImage, DestinationPrice, StartDate, EndDate
        FROM destination
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
    <link rel="icon" href="images/logo.png" type="image/png" />
    <link rel="shortcut icon" href="images/logo.png" type="image/png" />
    <style>
    /* Info */
    .learn-more {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        margin-top: 10px;
        margin-bottom: 20px;
        gap: 50px;
        flex-wrap: wrap;
        text-align: center;
        padding: 0 15px;
    }
    h2 {
        margin-top: 10px;
        text-align: center;
        font-size: 30px;
    }
    .name-image img {
        width: 300px;
        max-width: 300px;
        height: auto;
        border-radius: 20px;
    }
    .info {
        max-width: 425px;
        text-align: left;
    }
    .info p {
        margin-bottom: 5px;
        line-height: 1.5;
        /* font-size: 16px; */
    }
    .info ul {
        list-style: none;
 
        /* padding: 0;
        margin: 5px 0;
        font-size: 15px; */
    }
    .info ul span {
        font-weight: bold;
    }
    .info h3 {
        margin-top: 15px;
        margin-bottom: 8px;
        font-size: 18px;
    }
    /* .book-btn {
        display: inline-block;
         width: fit-content;
        align-items: left;
        background: #625d5d;
        color: #ffffff;
        border-radius: 10px;
        padding: 10px 20px;
        margin: 15px auto 0 auto;
        text-decoration: none;
        font-size: 16px;
        transition: transform 0.3s ease, box-shadow 0.3s ease; 
    }
    .book-btn:hover {
        background: #767778;
        transform: scale(1.05); 
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); 
    }

    @media (max-width: 768px) {
        h2 {
            font-size: 22px;
        }
        .info {
            text-align: center;
            max-width: 100%;
        }
        .info ul {
            text-align: left;
            display: inline-block;
        }
        .book-btn {
            margin: 20px auto 0 auto;
        }
    }

    @media (max-width: 480px) {
        h2 {
            font-size: 20px;
        }
        .info p {
            font-size: 14px;
        }
        .book-btn {
            padding: 8px 16px;
            font-size: 14px;
        }
    } */
        .book-btn {
    display: inline-block;
    background: #625d5d;
    color: #ffffff;
    border-radius: 10px;
    padding: 10px 20px;
    margin-top: 15px; /* no auto margin here */
    text-decoration: none;
    font-size: 16px;
    transition: transform 0.3s ease, box-shadow 0.3s ease; 
}
.book-btn:hover {
    background: #767778;
    transform: scale(1.05); 
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); 
}

/* Mobile */
@media (max-width: 768px) {
    h2 {
        font-size: 22px;
    }
    .info {
        text-align: center;
        max-width: 100%;
    }
    .info ul {
        text-align: left;
        display: inline-block;
    }
    .book-btn {
        display: block;
        width: fit-content;
        margin: 20px auto 0 auto; 
    }
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
            <ul style="list-style: disc; padding-left: 20px; margin-bottom: 10px;">
                <?php $places = explode(',', $dest['DestinationPlaces']);
                foreach ($places as $place): 
                ?>
                <li><?php echo htmlspecialchars(trim($place)); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- Date & Price -->
        <ul style="list-style: none; padding-left: 0; margin-top: 10px;">
            <li><span>Date: </span><?php echo date("d/m/Y", strtotime($dest['StartDate'])) . " - " . date("d/m/Y", strtotime($dest['EndDate'])); ?></li>
            <li><span>Price: €</span><?php echo htmlspecialchars($dest['DestinationPrice']); ?></li>
        </ul>

        <!-- Book Now button under lists -->
        <a href="booking.php?id=<?php echo $destinationId; ?>" class="book-btn">Book Now</a>
    </div>
</section>

<!-- Contact Us Section -->
<?php include 'contact.php';?>

<!-- Footer Section -->
<?php include 'footer.php';?>

<script src="script.js"></script>
</body>
</html>
