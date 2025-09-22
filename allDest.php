<?php 
include 'db_connect.php';

$selectedCountry = isset($_GET['country']) ? $_GET['country'] : null;

// Pagination setup
$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Fetch countries for sidebar
$countrySql = "SELECT CountryID, CountryName FROM Country ORDER BY CountryName ASC";
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
            FROM Destination d
            JOIN Country c ON d.CountryID = c.CountryID
            WHERE c.CountryID = ?
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $selectedCountry, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // Count total for pagination
    $countSql = "SELECT COUNT(*) AS total FROM Destination WHERE CountryID = ?";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("i", $selectedCountry);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
} else {
    $sql = "SELECT d.DestinationID, d.DestinationName, d.DestinationImage, d.DestinationPrice, 
                   d.StartDate, d.EndDate, c.CountryName
            FROM Destination d
            JOIN Country c ON d.CountryID = c.CountryID
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // Count total for pagination
    $countSql = "SELECT COUNT(*) AS total FROM Destination";
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
    }
    .sidebar a:hover {
        background: #bbb;
    }
    .main-content {
        flex: 1;
        padding: 20px;
    }
    .destination-card {
        border: 1px solid #ddd;
        padding: 10px;
        margin: 10px 0;
        border-radius: 6px;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .destination-card img {
        width: 120px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
    }
    .pagination {
        margin-top: 20px;
    }
    .pagination a {
        display: inline-block;
        padding: 6px 10px;
        margin: 0 3px;
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
</style>
</head>
<body>
<?php include 'header.php';?>

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
                            <div>
                                <h4><?php echo htmlspecialchars($dest['DestinationName']); ?></h4>
                                <p>Price: $<?php echo $dest['DestinationPrice']; ?></p>
                                <p>
                                    <?php echo date("d/m/Y", strtotime($dest['StartDate'])); ?> 
                                    - 
                                    <?php echo date("d/m/Y", strtotime($dest['EndDate'])); ?>
                                </p>
                                <p><b>Country:</b> <?php echo htmlspecialchars($dest['CountryName']); ?></p>
                            </div>
                        </div>
                        <div class="learn-more">
                            <a href="info.php?id=<?php echo $dest['DestinationID']; ?>" class="learn-more">Learn More</a> 
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
