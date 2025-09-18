<?php
session_start();
include 'db_connect.php'; 

// Fetch all destinations
$sql = "SELECT DestinationID, DestinationName, DestinationImage, DestinationPrice, StartDate, EndDate 
        FROM Destination 
        ORDER BY RAND()";
$result = $conn->query($sql);

// Put results into an array
$destinations = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $destinations[] = $row;
    }
}
shuffle($destinations);


// Path to uploaded images
$imagePath = "uploads/";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Destinations</title>
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

h5 {
  font-size: 33px;
  text-align: center;
  margin-top: 10px;
  margin-bottom: 30px;
}

.carousel-container {
  position: relative;
  width: 100%;
  max-width: 1100px;
  margin: auto;
  overflow: hidden;
}

.deals {
  display: flex;
  transition: transform 0.5s ease;
  margin-top: 10px;
  margin-bottom: 10px;
}

.deal {
  flex: 0 0 28%;
  box-sizing: border-box;
  background: var(--card);
  border-radius: 16px;
  padding: 1.5rem;
  margin: 0 1%;
  text-align: center;  
  box-shadow: 0 0 15px rgba(0,0,0,0.2);
}

.deal h2 {
  margin-bottom: 5px;
}

.deal img {
  display: block;
  margin: 0 auto 1rem auto;
  width: 160px;
  height: 160px;
  border-radius: 20px;
  object-fit: cover;
}

.deal ul {
  list-style: disc;
  text-align: left;
  margin: 0 auto 20px auto;
  padding: 0;
  max-width: 200px;
}

.deal li {
  margin: 5px 0;
}

.learn-more {
  display: inline-block;
  margin-top: auto;
  padding: 8px 16px;
  border-radius: 20px;
  background: var(--bg);
  color: var(--card);
  font-weight: bold;
  text-decoration: none;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.learn-more:hover {
  background: var(--muted);
  transform: scale(1.05);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  color: black;
  border: none;
  font-size: 32px;
  cursor: pointer;
  padding: 0;
  z-index: 10;
}

.arrow.left {
  left: 10px;
}

.arrow.right {
  right: 10px;
}

.arrow:focus {
  outline: none;
}
</style>
</head>
<body>

<h5>Here are some of our best deals of the moment!</h5>

<div class="carousel-container">
  <div class="deals" id="deals">
    <?php foreach($destinations as $dest): ?>
      <div class="deal">
        <h2><?php echo htmlspecialchars($dest['DestinationName']); ?></h2>
        <img src="<?php echo $imagePath . htmlspecialchars($dest['DestinationImage']); ?>" 
             alt="<?php echo htmlspecialchars($dest['DestinationName']); ?>" />
        <ul>
          <li>Start: <?php echo htmlspecialchars($dest['StartDate']); ?></li>
          <li>End: <?php echo htmlspecialchars($dest['EndDate']); ?></li>
          <li>Price: €<?php echo htmlspecialchars($dest['DestinationPrice']); ?></li>
        </ul>
        <a href="info.php?id=<?php echo $dest['DestinationID']; ?>" class="learn-more">Learn More</a>
      </div>
    <?php endforeach; ?>
  </div>

  <button class="arrow left" onclick="prevDeal()">&#8249;</button>
  <button class="arrow right" onclick="nextDeal()">&#8250;</button>
</div>

<script>
const deals = document.getElementById("deals");
let cards = Array.from(deals.children);
const visibleDeals = 3;
let currentIndex = 0;

// Clone for infinite loop only if enough items
if(cards.length > visibleDeals){
  const firstClones = cards.slice(0, visibleDeals).map(c => c.cloneNode(true));
  const lastClones = cards.slice(-visibleDeals).map(c => c.cloneNode(true));
  firstClones.forEach(clone => deals.appendChild(clone));
  lastClones.forEach(clone => deals.insertBefore(clone, deals.firstChild));
  
  cards = Array.from(deals.children);
  currentIndex = visibleDeals;
  deals.style.transform = `translateX(-${currentIndex * (100 / visibleDeals)}%)`;
} else {
  deals.style.transform = `translateX(0%)`;
}

function updateSlide() {
  deals.style.transition = "transform 0.5s ease";
  deals.style.transform = `translateX(-${currentIndex * (100 / visibleDeals)}%)`;
}

function nextDeal() {
  if(cards.length <= visibleDeals) return; // No sliding needed
  currentIndex++;
  updateSlide();
  if (currentIndex >= cards.length - visibleDeals) {
    setTimeout(() => {
      deals.style.transition = "none";
      currentIndex = visibleDeals;
      deals.style.transform = `translateX(-${currentIndex * (100 / visibleDeals)}%)`;
    }, 500);
  }
}

function prevDeal() {
  if(cards.length <= visibleDeals) return;
  currentIndex--;
  updateSlide();
  if (currentIndex < visibleDeals) {
    setTimeout(() => {
      deals.style.transition = "none";
      currentIndex = cards.length - (2 * visibleDeals);
      deals.style.transform = `translateX(-${currentIndex * (100 / visibleDeals)}%)`;
    }, 500);
  }
}
</script>

</body>
</html>
