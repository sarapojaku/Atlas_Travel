<?php
$conn = mysqli_connect("localhost", "root", "", "agencydb");
if (!$conn) die("Connection Failed: " . mysqli_connect_error());

// Dashboard counts
$client_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS client_count FROM client"))['client_count'];
$staff_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS staff_count FROM staff"))['staff_count'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(Revenue) AS total_revenue FROM destination"))['total_revenue'];
$destination_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS destination_count FROM destination"))['destination_count'];

// Top Clients (dynamic ≤5 or top 5 by spending)
$total_clients_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM client");
$total_clients = mysqli_fetch_assoc($total_clients_result)['total'];

if ($total_clients <= 5) {
    $top_clients_result = mysqli_query(
        $conn,
        "SELECT ClientName, ClientSurname, COALESCE(Spending, 0) AS Spending 
         FROM client 
         ORDER BY ClientID DESC"
    );
} else {
    $top_clients_result = mysqli_query(
        $conn,
        "SELECT ClientName, ClientSurname, COALESCE(Spending, 0) AS Spending 
         FROM client 
         ORDER BY Spending DESC 
         LIMIT 5"
    );
}

$top_clients_data = [];
while ($row = mysqli_fetch_assoc($top_clients_result)) {
    $top_clients_data[$row['ClientName'] . ' ' . $row['ClientSurname']] = floatval($row['Spending']);
}

// Top 5 Destinations
$top_destinations_result = mysqli_query($conn, "SELECT DestinationName, COALESCE(Revenue, 0) AS Revenue FROM destination ORDER BY Revenue DESC LIMIT 5");
$top_destinations_data = [];
while ($row = mysqli_fetch_assoc($top_destinations_result)) {
    $top_destinations_data[$row['DestinationName']] = floatval($row['Revenue']);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
<style>
body { 
    margin: 0; 
    font-family: sans-serif; 
    background: #625d5d; 
    color: #fff; 
}
.main-container { 
    padding: 20px; 
}
.main-title h2 { 
    margin-bottom: 20px; 
}

/* Dashboard Cards */
.main-cards { 
    display: flex; 
    gap: 20px; 
    flex-wrap: wrap; 
    justify-content: center; 
    margin-bottom: 30px; 
}
.card { 
    flex: 0 0 220px; 
    height: 160px; 
    border-radius: 6px; 
    padding: 15px 20px; 
    display: flex; 
    flex-direction: column; 
    justify-content: center; 
    box-shadow: 0 4px 10px rgba(0,0,0,0.2); 
    color: #fff; 
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
      transform: scale(1.05); /* expands slightly */

}
.card-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
}
.card-header h3 { 
    margin: 0; 
    font-size: 16px; 
}
.card-header .material-icons-outlined { 
    font-size: 36px; 
}
.card h1 { 
    margin-top: 15px; 
    font-size: 28px; 
    text-align: left; 
}

/* Card Colors */
.card-clients { 
    background: #2563eb; 
}
.card-staff { 
    background: #f89413; 
}
.card-sales { 
    background: #01b50a; 
}
.card-destinations { 
    background: #bd0404; 
}

/* Charts Section */
.charts { 
    display: flex; 
    flex-direction: column; 
    gap: 30px; 
    align-items: center; 
}
.chart-card { 
    background: #514e50; 
    padding: 20px; 
    border-radius: 12px; 
    width: 90%; 
    max-width: 800px; 
}
.chart-card h2 { 
    margin-bottom: 15px; 
    }
</style>
</head>
<body>

<main class="main-container">
    <div class="main-title">
        <h2>Welcome Admin</h2>
    </div>

    <!-- Dashboard Cards -->
    <div class="main-cards">
        <div class="card card-clients">
            <div class="card-header">
                <h3>Clients</h3>
                <span class="material-icons-outlined">people</span>
            </div>
            <h1><?php echo htmlspecialchars($client_count); ?></h1>
        </div>
        <div class="card card-staff">
            <div class="card-header">
                <h3>Staff</h3>
                <span class="material-icons-outlined">manage_accounts</span>
            </div>
            <h1><?php echo htmlspecialchars($staff_count); ?></h1>
        </div>
        <div class="card card-sales">
            <div class="card-header">
                <h3>Total Sales</h3>
                <span class="material-icons-outlined">monetization_on</span>
            </div>
            <h1><?php echo htmlspecialchars($total_revenue); ?></h1>
        </div>
        <div class="card card-destinations">
            <div class="card-header">
                <h3>Destinations</h3>
                <span class="material-icons-outlined">place</span>
            </div>
            <h1><?php echo htmlspecialchars($destination_count); ?></h1>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts">
        <div class="chart-card">
            <h2>Top 5 Clients</h2>
            <div id="bar-chart"></div>
        </div>

        <div class="chart-card">
            <h2>Top 5 Destinations</h2>
            <div id="pie-chart"></div>
        </div>
    </div>
</main>

<!-- Export PHP data to JS globals -->
<script>
window.topClientsData = <?php echo json_encode($top_clients_data); ?>;
window.topDestinationsData = <?php echo json_encode($top_destinations_data); ?>;
</script>

</body>
</html>
