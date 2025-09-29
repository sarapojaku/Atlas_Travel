<?php
session_start();
if (!isset($_SESSION['staff_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

include 'db_connect.php';

// Fetch summary data
$totalRevenue = 0;
$highestDestination = "N/A";
$highestRevenue = 0;
$avgRevenue = 0;

// Average Revenue
$result = $conn->query("
    SELECT SUM(d.DestinationPrice) AS TotalRevenue, AVG(d.DestinationPrice) AS AvgRevenue
    FROM booking b
    JOIN destination d ON b.DestinationID = d.DestinationID
");
if ($row = $result->fetch_assoc()) {
    $totalRevenue = $row['TotalRevenue'] ?? 0;
    $avgRevenue = $row['AvgRevenue'] ?? 0;
}

// Highest revenue destination
$result = $conn->query("SELECT DestinationName, Revenue FROM destination ORDER BY Revenue DESC LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $highestDestination = $row['DestinationName'];
    $highestRevenue = $row['Revenue'];
}

// Destination data
$tableData = $conn->query("
    SELECT DestinationName, DestinationPrice, Revenue, StartDate, EndDate, Type 
    FROM destination
    ORDER BY Revenue DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Report</title>
<style>
body {
    background: #625d5d;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #fff;
    margin: 0;
}
h1 {
    text-align: center;
    margin: 1rem 0;
}

/* Summary Cards */
.cards {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin: 1rem auto;
    max-width: 1000px;
}
.card {
    flex: 1 1 200px;
    padding: 1rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    min-width: 200px;
    max-width: 250px;
}
.card:hover {
    transform: scale(1.05);
}
.card-1 { background: #2563eb; }
.card-2 { background: #f89413; }
.card-3 { background: #01b50a; }

.card h2 {
    margin: 0;
    font-size: 20px;
    color: #fff;
}
.card p {
    font-size: 18px;
    font-weight: bold;
    color: #fff;
    margin-top: 0.5rem;
}

/* Destination Cards */
.destinations-container {
    width: 90%;
    max-width: 1000px;
    margin: 2rem auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}

.destination-card {
    background: #fff;
    color: #000;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    word-wrap: break-word; /* Text wraps inside card */
}

.destination-card h3 {
    margin: 0;
    font-size: 1.2rem;
    color: #333;
}

.destination-info {
    font-size: 0.95rem;
    color: #555;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .cards { flex-direction: column; align-items: center; }
    .destinations-container { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<h1>Sales Report</h1>

<!-- Summary Cards -->
<div class="cards">
    <div class="card card-1">
        <h2>Total Revenue</h2>
        <p>$<?= number_format($totalRevenue, 2) ?></p>
    </div>
    <div class="card card-2">
        <h2>Highest Destination</h2>
        <p><?= htmlspecialchars($highestDestination) ?> ($<?= number_format($highestRevenue, 2) ?>)</p>
    </div>
    <div class="card card-3">
        <h2>Avg Revenue</h2>
        <p>$<?= number_format($avgRevenue, 2) ?></p>
    </div>
</div>

<!-- Destination Cards -->
<div class="destinations-container">
<?php while($row = $tableData->fetch_assoc()): ?>
    <div class="destination-card">
        <h3><?= htmlspecialchars($row['DestinationName']) ?></h3>
        <div class="destination-info"><strong>Price:</strong> $<?= number_format($row['DestinationPrice'], 2) ?></div>
        <div class="destination-info"><strong>Revenue:</strong> $<?= number_format($row['Revenue'], 2) ?></div>
        <div class="destination-info"><strong>Start Date:</strong> <?= htmlspecialchars($row['StartDate']) ?></div>
        <div class="destination-info"><strong>End Date:</strong> <?= htmlspecialchars($row['EndDate']) ?></div>
        <div class="destination-info"><strong>Type:</strong> <?= htmlspecialchars($row['Type']) ?></div>
    </div>
<?php endwhile; ?>
</div>

</body>
</html>
