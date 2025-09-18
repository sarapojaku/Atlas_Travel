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
    <title>Destinations</title>
    <style>
    body {
        background: #625d5d;
        font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #222;
    }
    h1 {
        text-align: center;
        color: #ffffff;
    }
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
    .dest-header {
        width: 70%;
        margin: 2rem auto 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #ffffff; 
    }
    .dest-header h2 {
        margin: 0;
        text-align: left;
        color: #ffffff;
    }
    .search-box input {
        padding: 6px 10px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 14px;
    }
    table {
        width: 70%; 
        margin: 1rem auto 2rem auto; 
        border-collapse: collapse; 
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
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
    td {
        color: #000000;
    }
    a.delete {
        color: red;
        text-decoration: none;
        font-weight: bold;
    }
    a.delete:hover {
        color: #ff0000; 
    }
    a.edit {
        color: #007bff;
        text-decoration: none;
        font-weight: bold;
    }
    a.edit:hover {
        color: #0056b3;
    }
    </style>
</head>
<body>

<h1>Add a New Destination</h1>
<form action="addDest.php" method="post" enctype="multipart/form-data">
    <input type="text" name="DestinationName" placeholder="Destination Name" required>
    <input type="text" name="DestinationInfo" placeholder="Destination Info" required>
    <input type="int" name="DestinationPrice" placeholder="Destination Price" required>
    <input type="date" name="StartDate" placeholder="Start Date" required>
    <input type="date" name="EndDate" placeholder="End Date" required>
    <input type="file" name="DestinationImage" placeholder="Destination Image" required>
    <input type="int" name="CountryID" placeholder="Country ID" required>


    <button type="submit" name="submit">Add a New Destination</button>
</form>

<div class="dest-header">
    <h2>All Destinations</h2>
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search a destination...">
    </div>
</div>

<table id="destinationsTable">
    <tr>
        <th>Destination Name</th>
        <th>Destination Info</th>
        <th>Destination Price</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Destination Image</th>
        <th>CountryID</th>
        <th>Action</th>
    </tr>
    <?php 
    // Alphabetical order, case-insensitive
    $result = $conn->query("
        SELECT DestinationID, DestinationName, DestinationInfo, DestinationPrice, StartDate, EndDate, DestinationImage, CountryID
        FROM destination
        ORDER BY LOWER(StartDate) ASC 
    ");
    while($row = $result->fetch_assoc()) {
        $imagePath = "uploads/" . $row['DestinationImage'];
        $imageName = $row['DestinationImage'];
        echo "<tr>
            <td>{$row['DestinationName']}</td>
            <td>{$row['DestinationInfo']}</td>
            <td>{$row['DestinationPrice']}</td>
            <td>{$row['StartDate']}</td>
            <td>{$row['EndDate']}</td>
            <td>";

        if (!empty($row['DestinationImage'])) {
            echo "<a class='download-link' href='$imagePath' download='$imageName'>$imageName</a>";
        } else {
            echo "No image.";
        }
        echo "</td>
            <td>{$row['CountryID']}</td>
            <td><a class='edit' href='editDest.php?id={$row['DestinationID']}&table=destination' onclick='return confirm(\"Are you sure?\")'>Edit</a> / <a class='delete' href='delete.php?id={$row['DestinationID']}&table=destination' onclick='return confirm(\"Are you sure?\")'>Delete</a></td>
        </tr>";
    }
    ?>
</table>
</body>
</html>