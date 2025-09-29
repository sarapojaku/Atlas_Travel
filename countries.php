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
<title>Countries</title>
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
.countries-header {
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

.countries-header h2 {
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
.countries-container {
    width: 90%;
    max-width: 1200px;
    margin: 1rem auto 2rem auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}

.country-card {
    background: #fff;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.country-card h3 {
    margin: 0;
    font-size: 1.1rem;
    color: #333;
}

.country-info {
    font-size: 0.9rem;
    color: #555;
}

.country-actions {
    margin-top: 0.5rem;
}

.country-actions a {
    text-decoration: none;
    font-weight: bold;
    font-size: 0.9rem;
    margin-right: 0.5rem;
}

.country-actions a.edit {
    color: #007bff;
}

.country-actions a.delete {
    color: red;
}

.country-actions a.edit:hover {
    color: #0056b3;
}

.country-actions a.delete:hover {
    color: #ff0000;
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .countries-header h2 {
        font-size: 1.2rem;
    }
    .search-box input {
        max-width: 100%;
    }
    .country-card {
        padding: 0.8rem;
    }
    .country-card h3 {
        font-size: 1rem;
    }
    .country-info {
        font-size: 0.85rem;
    }
}
</style>
</head>
<body>

<h1>Add a New Country</h1>
<form action="addCountry.php" method="post" enctype="multipart/form-data">
    <input type="text" name="CountryName" placeholder="Country Name" required>
    <input type="text" name="CountryInfo" placeholder="Country Info" required>
    <button type="submit" name="submit">Add a New Country</button>
</form>

<div class="countries-header">
    <h2>All Countries</h2>
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search a country...">
    </div>
</div>

<div class="countries-container" id="countriesContainer">
<?php 
$result = $conn->query("
    SELECT CountryID, CountryName, CountryInfo
    FROM country
    ORDER BY LOWER(CountryName) ASC
");
while($row = $result->fetch_assoc()) {
    echo "<div class='country-card'>
            <h3>{$row['CountryName']}</h3>
            <div class='country-info'>{$row['CountryInfo']}</div>
            <div class='country-actions'>
                <a class='edit' href='editCountry.php?id={$row['CountryID']}&table=country' onclick='return confirm(\"Are you sure?\")'>Edit</a>
                <a class='delete' href='delete.php?id={$row['CountryID']}&table=country' onclick='return confirm(\"Are you sure?\")'>Delete</a>
            </div>
        </div>";
}
?>
</div>

<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    let cards = document.querySelectorAll(".country-card");
    
    cards.forEach(card => {
        let text = card.textContent.toLowerCase();
        card.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

</body>
</html>
