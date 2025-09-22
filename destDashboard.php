<?php
include 'db_connect.php';

$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Fetch destinations
$sql = "SELECT d.DestinationID, d.DestinationName, d.DestinationImage, d.DestinationPrice, 
               d.StartDate, d.EndDate, c.CountryName
        FROM destination d
        JOIN country c ON d.CountryID = c.CountryID
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$destinations = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $destinations[] = $row;
    }
}

// Count total for pagination
$countSql = "SELECT COUNT(*) AS total FROM destination";
$total = $conn->query($countSql)->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);

$imagePath = "uploads/";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Destinations</title>
    <style>
    </style>
</head>
<body>
    <h2>All Destinations</h2>
    <?php if (count($destinations) > 0): ?>
        <?php foreach ($destinations as $dest): ?>
            <div class="destinations">
                <div class="destination-card">
                    <img src="<?php echo $imagePath . (!empty($dest['DestinationImage']) ? $dest['DestinationImage'] : 'noimage.jpg'); ?>" alt="<?php echo htmlspecialchars($dest['DestinationName']); ?>">
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
                <a href="?page=<?php echo $page - 1; ?>">&lt;</a>
            <?php endif; ?>
    
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
    
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>">&gt;</a>
            <?php endif; ?>
        </div>
    
    <?php else: ?>
        <p>No destinations available.</p>
    <?php endif; ?>
</body>
</html>
