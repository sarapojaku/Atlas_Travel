<?php
session_start();
if (!isset($_SESSION['staff_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

include 'db_connect.php';

// ✅ Fetch summary data
$totalRevenue = 0;
$highestDestination = "N/A";
$highestRevenue = 0;
$avgRevenue = 0;

// ✅ Average Revenue
$result = $conn->query("
    SELECT SUM(d.DestinationPrice) AS TotalRevenue, AVG(d.DestinationPrice) AS AvgRevenue
    FROM booking b
    JOIN destination d ON b.DestinationID = d.DestinationID
");
if ($row = $result->fetch_assoc()) {
    $totalRevenue = $row['TotalRevenue'] ?? 0;
    $avgRevenue = $row['AvgRevenue'] ?? 0;
}


// ✅ Highest revenue destination
$result = $conn->query("SELECT DestinationName, Revenue FROM destination ORDER BY Revenue DESC LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $highestDestination = $row['DestinationName'];
    $highestRevenue = $row['Revenue'];
}

// ✅ Table data
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.49.0/apexcharts.min.js"></script>
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
}
.card:hover {
    transform: scale(1.05); /* expands slightly */

}
/* Numbered cards for custom colors */
.card-1 { background: #2563eb; }  /* Blue */
.card-2 { background: #f89413; }  /* Orange */
.card-3 { background: #01b50a; }  /* Green */

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
.charts {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 2rem;
    margin: 2rem auto;
    max-width: 1000px;
}
.chart-box {
    flex: 1 1 400px;
    background: #fff;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    color: #000;
}
table {
    width: 80%; 
    margin: 2rem auto; 
    border-collapse: collapse; 
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    color: #000;
}
th {
    background: #625d5d;
    color: #ffffff;
    font-size: 15px;
}
th, td {
    padding: 0.6rem; 
    border: 1px solid #ddd; 
    text-align: center;
    font-size: 14px;
}
@media (max-width: 768px) {
    .cards { flex-direction: column; align-items: center; }
    .charts { flex-direction: column; }
    table { width: 95%; }
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

<!-- Table -->
<table>
    <thead>
        <tr>
            <th>Destination</th>
            <th>Price</th>
            <th>Revenue</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Type</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $tableData->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['DestinationName']) ?></td>
            <td>$<?= number_format($row['DestinationPrice'], 2) ?></td>
            <td>$<?= number_format($row['Revenue'], 2) ?></td>
            <td><?= htmlspecialchars($row['StartDate']) ?></td>
            <td><?= htmlspecialchars($row['EndDate']) ?></td>
            <td><?= htmlspecialchars($row['Type']) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
