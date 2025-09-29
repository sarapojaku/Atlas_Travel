<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'agencydb';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Destinations</title>
<style>
body {
    background: #625d5d;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #222;
    margin: 0;
    padding: 0;
}

h1 {
    text-align: center;
    color: #ffffff;
    margin-top: 1.5rem;
}

/* Form styling */
form {
    background: #ffffff;
    padding: 1rem;
    border-radius: 12px;
    margin: 2rem auto;
    max-width: 800px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
}

form input, form select {
    padding: 8px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

form button {
    background: #625d5d;
    color: #fff;
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

form button:hover {
    background: #4a4545;
}

/* Header and search box */
.dest-header {
    width: 90%;
    max-width: 1200px;
    margin: 1.5rem auto 0 auto;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    color: #ffffff; 
    gap: 0.5rem;
}

.dest-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #ffffff;
}

.search-box input {
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    width: 100%;
    max-width: 250px;
}

/* Cards container */
.destinations-container {
    width: 90%;
    max-width: 1200px;
    margin: 1rem auto 2rem auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}

.destination-card {
    background: #fff;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.destination-card h3 {
    margin: 0;
    font-size: 1.1rem;
    color: #333;
}

.destination-info {
    font-size: 0.9rem;
    color: #555;
}

.destination-actions {
    margin-top: 0.5rem;
}

.destination-actions a {
    text-decoration: none;
    font-weight: bold;
    font-size: 0.9rem;
    margin-right: 0.5rem;
}

.destination-actions a.edit {
    color: #007bff;
}

.destination-actions a.delete {
    color: red;
}

.destination-actions a.edit:hover {
    color: #0056b3;
}

.destination-actions a.delete:hover {
    color: #ff0000;
}

.destination-image a {
    text-decoration: none;
    color: #222;
}

.destination-image a:hover {
    text-decoration: underline;
    color: #444;
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .dest-header h2 {
        font-size: 1.2rem;
    }
    .search-box input {
        max-width: 100%;
    }
    .destination-card {
        padding: 0.8rem;
    }
    .destination-card h3 {
        font-size: 1rem;
    }
    .destination-info {
        font-size: 0.85rem;
    }
}
</style>
</head>
<body>

<h1>Add a New Destination</h1>
<form action="addDest.php" method="post" enctype="multipart/form-data">
    <input type="text" name="DestinationName" placeholder="Destination Name" required>
    <input type="text" name="DestinationInfo" placeholder="Destination Info" required>
    <input type="text" name="DestinationPlaces" placeholder="Destination Places" required>
    <input type="number" name="DestinationPrice" placeholder="Destination Price" required>
    <input type="date" name="StartDate" placeholder="Start Date" required>
    <input type="date" name="EndDate" placeholder="End Date" required>
    <input type="file" name="DestinationImage" placeholder="Destination Image" required>
    <input type="number" name="CountryID" placeholder="Country ID" required>
    <button type="submit" name="submit">Add a New Destination</button>
</form>

<div class="dest-header">
    <h2>All Destinations</h2>
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search a destination...">
    </div>
</div>

<div class="destinations-container" id="destinationsContainer">
<?php 
$result = $conn->query("
    SELECT DestinationID, DestinationName, DestinationInfo, DestinationPlaces, DestinationPrice, StartDate, EndDate, DestinationImage, CountryID
    FROM destination
    ORDER BY StartDate ASC
");
while($row = $result->fetch_assoc()) {
    $imagePath = "uploads/" . $row['DestinationImage'];
    $imageName = $row['DestinationImage'];
    echo "<div class='destination-card'>
            <h3>{$row['DestinationName']}</h3>
            <div class='destination-info'><strong>Info:</strong> {$row['DestinationInfo']}</div>
            <div class='destination-info'><strong>Places:</strong> {$row['DestinationPlaces']}</div>
            <div class='destination-info'><strong>Price:</strong> {$row['DestinationPrice']}</div>
            <div class='destination-info'><strong>Start:</strong> {$row['StartDate']}</div>
            <div class='destination-info'><strong>End:</strong> {$row['EndDate']}</div>
            <div class='destination-info destination-image'><strong>Image:</strong> ";
    if (!empty($row['DestinationImage'])) {
        echo "<a href='$imagePath' download='$imageName'>$imageName</a>";
    } else {
        echo "No image";
    }
    echo "</div>
            <div class='destination-info'><strong>Country ID:</strong> {$row['CountryID']}</div>
            <div class='destination-actions'>
                <a class='edit' href='editDest.php?id={$row['DestinationID']}&table=destination' onclick='return confirm(\"Are you sure?\")'>Edit</a>
                <a class='delete' href='delete.php?id={$row['DestinationID']}&table=destination' onclick='return confirm(\"Are you sure?\")'>Delete</a>
            </div>
        </div>";
}
?>
</div>

<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    let cards = document.querySelectorAll(".destination-card");
    
    cards.forEach(card => {
        let text = card.textContent.toLowerCase();
        card.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

</body>
</html>
