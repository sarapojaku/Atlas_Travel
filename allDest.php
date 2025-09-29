<?php 
include 'db_connect.php';

$selectedCountry = isset($_GET['country']) ? $_GET['country'] : null;

// Pagination setup
$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Fetch countries for sidebar
$countrySql = "SELECT CountryID, CountryName FROM country ORDER BY CountryName ASC";
$countryResult = $conn->query($countrySql);
$countries = [];
if ($countryResult && $countryResult->num_rows > 0) {
    while ($row = $countryResult->fetch_assoc()) {
        $countries[] = $row;
    }
}

// Build query for destinations
if ($selectedCountry) {
    $sql = "SELECT d.DestinationID, d.DestinationName, d.DestinationImage, d.DestinationPrice, 
                   d.StartDate, d.EndDate, c.CountryName
            FROM destination d
            JOIN country c ON d.CountryID = c.CountryID
            WHERE c.CountryID = ? AND d.StartDate >= CURDATE()
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $selectedCountry, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // Count total for pagination
    $countSql = "SELECT COUNT(*) AS total FROM destination WHERE CountryID = ? AND StartDate >= CURDATE()";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("i", $selectedCountry);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
} else {
    $sql = "SELECT d.DestinationID, d.DestinationName, d.DestinationImage, d.DestinationPrice, 
                   d.StartDate, d.EndDate, c.CountryName
            FROM destination d
            JOIN country c ON d.CountryID = c.CountryID
            WHERE d.StartDate >= CURDATE()
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // Count total for pagination
    $countSql = "SELECT COUNT(*) AS total FROM destination WHERE StartDate >= CURDATE()";
    $total = $conn->query($countSql)->fetch_assoc()['total'];
}

$destinations = []; 
if ($result && $result->num_rows > 0) { 
    while ($row = $result->fetch_assoc()) { 
        $destinations[] = $row; 
    } 
}

$totalPages = ceil($total / $limit);
$imagePath = "uploads/"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>All Destinations</title>
<link rel="icon" href="images/logo.png" type="image/png" />
<link rel="shortcut icon" href="images/logo.png" type="image/png" />
<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
}

.layout {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 10px;
}

/* Sidebar */
.sidebar-toggle {
    display: none;
    background: #333;
    color: #fff;
    padding: 10px 15px;
    font-size: 18px;
    border: none;
    cursor: pointer;
    width: 100%;
    text-align: left;
}
.sidebar {
    width: 200px;
    padding: 10px;
    background: #f4f4f4;
    position: sticky;
    top: 10px;
    align-self: flex-start;
    max-height: calc(100vh - 20px);
    overflow-y: auto;
    border-radius: 6px;
    flex-shrink: 0;
    transition: max-height 0.3s ease, opacity 0.3s ease;
}
.sidebar h3 {
    margin-top: 0;
}
.sidebar a {
    display: block;
    padding: 8px;
    margin-bottom: 5px;
    background: #f4f4f4;
    text-decoration: none;
    color: #333;
    border-radius: 4px;
}
.sidebar a:hover {
    background: #bbb;
}

/* Main content */
.main-content {
    flex: 1;
    padding: 10px;
}
.destinations {
    margin-bottom: 20px;
}
.destination-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid #ddd;
    padding: 15px;
    margin: 10px 0;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    gap: 15px;
}
.destination-card img {
    width: 150px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    flex-shrink: 0;
}
.card-content {
    flex: 1;
}
.card-action {
    margin-left: 20px;
    display: flex;
    align-items: center;
}
.learn-more {
    display: inline-block;
    padding: 8px 12px;
    background: #625d5d;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.3s ease;
    white-space: nowrap;
}
.learn-more:hover {
    background: #767778;
}

/* Pagination */
.pagination {
    margin-top: 20px;
    text-align: center;
    flex-wrap: wrap;
}
.pagination a {
    display: inline-block;
    padding: 6px 10px;
    margin: 3px;
    border: 1px solid #ddd;
    text-decoration: none;
    color: #333;
    border-radius: 4px;
}
.pagination a.active {
    background: #333;
    color: #fff;
}
.pagination a:hover {
    background: #555;
    color: #fff;
}

/* ---- Responsive styles ---- */
@media (max-width: 768px) {
    .layout {
        flex-direction: column;
    }
    .sidebar-toggle {
        display: block;
    }
    .sidebar {
        width: 100%;
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        position: relative;
        transition: max-height 0.3s ease, opacity 0.3s ease;
    }
    .sidebar.open {
        max-height: 70vh;   /* allows scrolling */
        opacity: 1;
        overflow-y: auto;   /* scroll inside if too tall */
    }
    .destination-card {
        flex-direction: column;
        align-items: flex-start;
    }
    .destination-card img {
        width: 100%;
        height: auto;
        margin-bottom: 10px;
    }
    .card-action {
        margin-left: 0;
        margin-top: 10px;
        width: 100%;
    }
    .learn-more {
        display: block;
        text-align: center;
        width: 100%;
    }
}
</style>
</head>
<body>
<?php include 'header.php';?>

<!-- Sidebar toggle for mobile -->
<button class="sidebar-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">
    ☰ Countries
</button>

<div class="layout">
    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Countries</h3>
        <a href="allDest.php">All</a>
        <?php foreach ($countries as $country): ?>
            <a href="allDest.php?country=<?php echo $country['CountryID']; ?>">
                <?php echo htmlspecialchars($country['CountryName']); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php if (!$selectedCountry): ?>
            <?php include 'destDashboard.php'; ?>
        <?php else: ?>
            <h2>Destinations in 
                <?php 
                $countryName = array_column($countries, 'CountryName', 'CountryID')[$selectedCountry] ?? 'Unknown';
                echo htmlspecialchars($countryName);
                ?>
            </h2>
            <?php if (count($destinations) > 0): ?>
                <?php foreach ($destinations as $dest): ?>
                    <div class="destinations">
                        <div class="destination-card">
                            <img src="<?php echo $imagePath . (!empty($dest['DestinationImage']) ? $dest['DestinationImage'] : 'noimage.jpg'); ?>" 
                                alt="<?php echo htmlspecialchars($dest['DestinationName']); ?>">
                            <div class="card-content">
                                <h4><?php echo htmlspecialchars($dest['DestinationName']); ?></h4>
                                <p>Price: €<?php echo $dest['DestinationPrice']; ?></p>
                                <p>
                                    <?php echo date("d/m/Y", strtotime($dest['StartDate'])); ?> 
                                    - 
                                    <?php echo date("d/m/Y", strtotime($dest['EndDate'])); ?>
                                </p>
                                <p><b>Country:</b> <?php echo htmlspecialchars($dest['CountryName']); ?></p>
                            </div>
                            <div class="card-action">
                                <a href="booking.php?id=<?php echo $dest['DestinationID']; ?>" class="learn-more">Learn More</a>
                            </div>
                        </div>
                </div>
                <?php endforeach; ?>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo ($selectedCountry ? "country=$selectedCountry&" : ""); ?>page=<?php echo $page - 1; ?>">&lt;</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?<?php echo ($selectedCountry ? "country=$selectedCountry&" : ""); ?>page=<?php echo $i; ?>" 
                           class="<?php echo $i == $page ? 'active' : ''; ?>">
                           <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?php echo ($selectedCountry ? "country=$selectedCountry&" : ""); ?>page=<?php echo $page + 1; ?>">&gt;</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No destinations found in this country.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Footer Section -->
<?php include 'footer.php';?>

</body>
</html>
