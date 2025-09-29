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
<title>Our Clients</title>
<style>
    body {
        background: #625d5d;
        font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #222;
        margin: 0;
        padding: 0;
    }

    .clients-header {
        width: 90%;
        max-width: 1200px;
        margin: 1.5rem auto;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        color: #ffffff;
        gap: 0.5rem;
    }

    .clients-header h2 {
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
    .clients-container {
        width: 90%;
        max-width: 1200px;
        margin: 1rem auto 2rem auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }

    .client-card {
        background: #fff;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .client-card h3 {
        margin: 0;
        font-size: 1.1rem;
        color: #333;
    }

    .client-info {
        font-size: 0.9rem;
        color: #555;
    }

    .client-actions {
        margin-top: 0.5rem;
    }

    .client-actions a {
        color: red;
        text-decoration: none;
        font-weight: bold;
        font-size: 0.9rem;
    }

    .client-actions a:hover {
        color: #ff0000;
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
        .clients-header h2 {
            font-size: 1.2rem;
        }
        .search-box input {
            max-width: 100%;
        }
        .client-card {
            padding: 0.8rem;
        }
        .client-card h3 {
            font-size: 1rem;
        }
        .client-info {
            font-size: 0.85rem;
        }
    }
</style>
</head>
<body>

<div class="clients-header">
    <h2>All Clients</h2>
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search client...">
    </div>
</div>

<div class="clients-container" id="clientsContainer">
<?php 
$result = $conn->query("
    SELECT ClientID, CONCAT(ClientName, ' ', ClientSurname) AS FullName, Username, Email, Gender 
    FROM client 
    ORDER BY LOWER(ClientName) ASC, LOWER(ClientSurname) ASC
");
while($row = $result->fetch_assoc()) {
    echo "<div class='client-card'>
            <h3>{$row['FullName']}</h3>
            <div class='client-info'><strong>Username:</strong> {$row['Username']}</div>
            <div class='client-info'><strong>Email:</strong> {$row['Email']}</div>
            <div class='client-info'><strong>Gender:</strong> {$row['Gender']}</div>
            <div class='client-actions'>
                <a href='delete.php?id={$row['ClientID']}&table=client' onclick='return confirm(\"Are you sure?\")'>Delete</a>
            </div>
        </div>";
}
?>
</div>

<script>
    // Client-side search filter for cards
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        let cards = document.querySelectorAll(".client-card");
        
        cards.forEach(card => {
            let text = card.textContent.toLowerCase();
            card.style.display = text.includes(filter) ? "" : "none";
        });
    });
</script>

</body>
</html>
