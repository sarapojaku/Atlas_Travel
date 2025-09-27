<?php   
session_start();
include 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: client_login.php");
    exit;
}

$username = $_SESSION['username'];

// Fetch client details
$stmt = $conn->prepare("SELECT * FROM Client WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

// Spending overview (SUM of destination prices for booked trips)
$spendingQuery = $conn->prepare("
    SELECT SUM(d.DestinationPrice) AS totalSpend, COUNT(b.BookingID) AS totalBookings
    FROM booking b
    JOIN destination d ON b.DestinationID = d.DestinationID
    WHERE b.ClientID = ?
");
$spendingQuery->bind_param("i", $client['ClientID']);
$spendingQuery->execute();
$spending = $spendingQuery->get_result()->fetch_assoc();

// Upcoming trips
$upcomingQuery = $conn->prepare("
    SELECT b.BookingID, d.DestinationName, d.StartDate, d.EndDate, d.DestinationImage, d.DestinationPrice
    FROM booking b
    JOIN destination d ON b.DestinationID = d.DestinationID
    WHERE b.ClientID = ? AND d.EndDate >= CURDATE()
    ORDER BY d.StartDate ASC
");
$upcomingQuery->bind_param("i", $client['ClientID']);
$upcomingQuery->execute();
$upcomingTrips = $upcomingQuery->get_result();

// Past trips
$pastQuery = $conn->prepare("
    SELECT d.DestinationName, d.StartDate, d.EndDate, d.DestinationImage, d.DestinationPrice
    FROM booking b
    JOIN destination d ON b.DestinationID = d.DestinationID
    WHERE b.ClientID = ? AND d.EndDate < CURDATE()
    ORDER BY d.EndDate DESC
");
$pastQuery->bind_param("i", $client['ClientID']);
$pastQuery->execute();
$pastTrips = $pastQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile</title>
  <link rel="icon" href="images/logo.png" type="image/png" />
  <link rel="shortcut icon" href="images/logo.png" type="image/png" />
  <style>
    /* Reset & base */
    * { box-sizing: border-box; margin: 0; padding: 0; }
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
    body {
        font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        background: var(--card);
        color: var(--fg);
        line-height: 1.6;
    }
    a { color: inherit; text-decoration: none; }
    img { display: block; max-width: 100%; height: auto; }
    .container { max-width: 1100px; padding: 0 1rem; margin: 0 auto; }
    .navbar {
        color: #ffffff;
        position: sticky;
        top: 0; z-index: 50;
        background: var(--bg);  
        backdrop-filter: blur(8px);
        border-bottom: 1px solid var(--border); 
    }
    .nav-inner { height: 64px; display: flex; align-items: center; justify-content: space-between; }
    .logo { display: flex; align-items: center; gap: 0.5rem; font-weight: 900; letter-spacing: 0.2px; }
    .logo-icon { width: 32px; height: 32px; display: grid; place-items: center; }
    .nav-links { display: flex; align-items: center; gap: 10px; }
    .nav-links > a { font-weight: bold; color: #ffffff; cursor: pointer; }
    .nav-links > a:hover { text-decoration: underline; }
    .sep { color: #ffffff; }

    .section { margin: 2rem auto; }
    .section h2 { margin-bottom: 1rem; color: var(--bg); }
    .card {
        background: var(--card);
        border-radius: var(--radius);
        box-shadow: var(--shadow);  
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    .profile-info { display: flex; gap: 2rem; align-items: center; }
    .profile-info img {
        width: 120px; height: 120px; border-radius: 50%; object-fit: cover;
        border: 3px solid var(--primary);
    }
    .stats { display: flex; gap: 2rem; }
    .stat { background: var(--primary-10); padding: 1rem; border-radius: var(--radius); text-align: center; flex: 1; }
    .stat h3 { margin: 0; color: var(--primary); }

    .trip-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
    .trip-card { background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
    .trip-card img { width: 100%; height: 160px; object-fit: cover; }
    .trip-card-body-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .trip-card-body { flex: 1 1 70%; }
    .trip-card-body h3 { margin-bottom: .5rem; }
    .cancel-btn {
        padding: 0.5rem 1rem;
        background: #ef4444;
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        white-space: nowrap;
        flex-shrink: 0;
        height: fit-content;
        cursor: pointer;
        border: none;
        margin-top: 20px;
    }
    .cancel-btn:hover { background: #dc2626; }
    @media (max-width: 600px) {
        .trip-card-body-wrapper { flex-direction: column; }
        .trip-card-body { flex: 1 1 100%; }
        .cancel-btn { align-self: flex-start; }
    }
  </style>
  <script>
    function cancelBooking(bookingID, el) {
        if(!confirm("Are you sure you want to cancel this trip?")) return;
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "cancelBooking.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Remove the trip card from the DOM
                el.closest('.trip-card').remove();
            } else {
                alert("Failed to cancel booking.");
            }
        };
        xhr.send("bookingID=" + bookingID);
    }
  </script>
</head>
<body>
<header class="navbar">
  <div class="container nav-inner">
    <a href="index.php" class="logo">
      <div class="logo-icon"><img src="images/logo.png" /></div>
      <span>Atlas Agency</span>
    </a>
    <nav class="nav-links">
        <a href="myprofile.php" class="btn username-link"><?php echo htmlspecialchars($_SESSION['username']); ?></a> 
        <span class="sep">/</span>
        <a href="client_logout.php" class="btn logout-link">Log Out</a>
    </nav>
  </div>
</header>

<main class="container">
  <!-- Profile Info -->
<section class="section">
  <div class="card profile-info">
    <img src="<?= $client['ProfileImage'] ? htmlspecialchars($client['ProfileImage']) : 'images/default-profile.png' ?>" alt="Profile Picture">
    <div>
      <h2><?= htmlspecialchars($client['ClientName'] . " " . $client['ClientSurname']) ?></h2>
      <p><strong>Email:</strong> <?= htmlspecialchars($client['Email']) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($client['Phone']) ?></p>
      <p><strong>Gender:</strong> <?= htmlspecialchars($client['Gender']) ?></p>
      <p style="margin-top: 1rem;">
        <a href="edit_profile.php" style="
          display:inline-block;
          padding:0.6rem 1.2rem;
          background: var(--primary);
          color:#fff;
          border-radius:8px;
          text-decoration:none;
          font-weight:bold;
        ">Edit Profile</a>
      </p>
    </div>
  </div>
</section>

<!-- Spending Overview -->
<section class="section">
    <h2>Spending Overview</h2>
    <div class="stats">
      <div class="stat">
        <h3>$<?= number_format($spending['totalSpend'] ?? 0, 2) ?></h3>
        <p>Total Spendings</p>
      </div>
      <div class="stat">
        <h3><?= $spending['totalBookings'] ?? 0 ?></h3>
        <p>Total Trips</p>
      </div>
    </div>
</section>

<!-- Upcoming Trips -->
<section class="section">
    <h2>Upcoming Trips</h2>
    <div class="trip-grid">
      <?php if ($upcomingTrips->num_rows > 0): ?>
        <?php while ($trip = $upcomingTrips->fetch_assoc()): ?>
          <?php 
            $imgPath = $trip['DestinationImage'];
            if ($imgPath && !preg_match('/^uploads\//', $imgPath)) {
                $imgPath = 'uploads/' . $imgPath;
            }
          ?>
          <div class="trip-card">
            <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($trip['DestinationName']) ?>">
            <div class="trip-card-body-wrapper">
              <div class="trip-card-body">
                <h3><?= htmlspecialchars($trip['DestinationName']) ?></h3>
                <p><?= htmlspecialchars($trip['StartDate']) ?> → <?= htmlspecialchars($trip['EndDate']) ?></p>
                <p><strong>Paid:</strong> $<?= number_format($trip['DestinationPrice'], 2) ?></p>
              </div>
                <button class="cancel-btn" onclick="cancelBooking(<?= $trip['BookingID'] ?>, this)">Cancel</button>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No upcoming trips.</p>
      <?php endif; ?>
    </div>
</section>


<!-- Past Trips -->
<section class="section">
    <h2>Past Trips</h2>
    <div class="trip-grid">
      <?php if ($pastTrips->num_rows > 0): ?>
        <?php while ($trip = $pastTrips->fetch_assoc()): ?>
          <?php 
            $imgPath = $trip['DestinationImage'];
            if ($imgPath && !preg_match('/^uploads\//', $imgPath)) {
                $imgPath = 'uploads/' . $imgPath;
            }
          ?>
          <div class="trip-card">
            <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($trip['DestinationName']) ?>">
            <div class="trip-card-body-wrapper">
                <div class="trip-card-body">
                    <h3><?= htmlspecialchars($trip['DestinationName']) ?></h3>
                    <p><?= htmlspecialchars($trip['StartDate']) ?> → <?= htmlspecialchars($trip['EndDate']) ?></p>
                    <p><strong>Paid:</strong> $<?= number_format($trip['DestinationPrice'], 2) ?></p>
                </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No past trips yet.</p>
      <?php endif; ?>
    </div>
</section>
</main>

<?php include 'footer.php';?> 

</body>
</html>
