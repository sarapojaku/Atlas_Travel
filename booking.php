<?php 
// session_start();
include 'db_connect.php';

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

$imagePath = "uploads/";

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking</title>
    <link rel="icon" href="images/logo.png" type="image/png" />
    <link rel="shortcut icon" href="images/logo.png" type="image/png" />
    <style>
        :root {
            --bg: #625d5d;
            --fg: #000000;
            --muted: #767778;
            --border: #e5e7eb;
            --card: #ffffff;
            --primary: #2563eb;
            --primary-10: #e0ecff;
            --shadow: 0 0 15px #b3acac;
            --radius: 16px;
        }
    /* @media screen and (max-width: 900px) {
        .infos {
            flex-direction: column;
            align-items: center;
        }
        .left-col,
        .form {
            max-width: 100%;
        }
    } */
        .book-form{
            margin-top: 1rem;
            display: grid;
            gap: 0.9rem;
            margin-left: 200px;
            margin-right: 200px;
        }
        h1 {
            text-align: center;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .book-form input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: #ffffff;
            font-size: 15px;
            margin-bottom: 10px;
        }
        .book-form button {
            background: #625d5d;
            color: #ffffff;
            border-radius: 10px;
            padding: 10px;
            font-size: 15px;
            font-weight: bold;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;   
        }
        .book-form button:hover {
            text-decoration: none;
            background: #767778;
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        #book-response {
            padding: 10px;
            border-radius: 5px;
            transition: opacity 0.5s ease;
        }
        .card h2 {
            font-weight: lighter;
            font-size: 20px;
        }
        .first {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 50px;
            padding: 40px 20px;
            flex-wrap: wrap;
        }
        .info {
            max-width: 450px;
            text-align: center;
        }
        .info h2 {
            text-align: center;
        }
        .info img {
            width: 300px;
            height: 300px;
            border-radius: 20px;
            object-fit: cover;
        }
        .info ul {
        list-style: none;
        padding-left: 0;
        margin-bottom: 10px;
        }
        .info ul span {
        font-weight: bold; 
        }
    </style>
</head>
<body>
<?php include 'header.php';?>

    <section id=infos class="first">
            <div class="info">
                <h2><?php echo htmlspecialchars($dest['DestinationName']); ?></h2>

                <img src="<?php echo $imagePath . htmlspecialchars($dest['DestinationImage']); ?>" 
                    alt="<?php echo htmlspecialchars($dest['DestinationName']); ?>" />

                <p><?php echo nl2br($dest['DestinationInfo']); ?></p>

                <?php if (!empty($dest['DestinationPlaces'])): ?>
                    <h3>Top Places to Visit:</h3>
                    <ul>
                        <?php $places = explode(',', $dest['DestinationPlaces']);
                        foreach ($places as $place): ?>
                            <li><?php echo htmlspecialchars(trim($place)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <ul>
                    <li><span>Date: </span><?php echo date("d/m/Y", strtotime($dest['StartDate'])) . " - " . date("d/m/Y", strtotime($dest['EndDate'])); ?></li>
                    <li><span>Price: €</span><?php echo htmlspecialchars($dest['DestinationPrice']); ?></li>
                </ul>

            </div>

    </section>

    <section id="booking" class="second">
        <div class="container book">
            <div class="second-head">
                <h1>Book now!</h1>
                <form id="book-form" class="book-form" action="book_form.php" method="post" >        
                    <div class="form-row">
                        <input type="text" id="fname" name="ClientName" placeholder="First Name" required>
                        <input type="text" id="lname" name="ClientSurname" placeholder="Surname" required>
                    </div>

                    <div class="card">
                        <h2>Card Details.</h2>
                        <input type="text" id="number" name="number" placeholder="Card Number" required>
                        <input type="month" id="exDate" name="exDate" placeholder="MM/YY" required>
                        <input type="text" id="cvv" name="cvv" placeholder="CVV" required>
                    </div>
                    <button type="submit">Submit Payment.</button>
                    <div id="book-response"></div>
                </form>
            </div>
        </div>
    </section>

<?php include 'footer.php';?>
</body>
</html>
