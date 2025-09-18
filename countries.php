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
    .countries-header {
        width: 70%;
        margin: 2rem auto 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #ffffff; 
    }
    .countries-header h2 {
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
    a.delete, a.edit, a.download-link {
        text-decoration: none;
        font-weight: bold;
    }
    a.delete {
        color: red;
    }
    a.edit {
        color: #007bff;
    }
    a.download-link {
        color: #222;
    }
    a.delete:hover {
        color: #ff0000; 
    }
    a.edit:hover {
        color: #0056b3;
    }
    a.download-link:hover {
        text-decoration: underline;
        color: #444;
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

<table id="countriesTable">
    <tr>
        <th>Country Name</th>
        <th>Country Info</th>
        <th>Action</th>
    </tr>
    <?php 
    // Alphabetical order, case-insensitive
    $result = $conn->query("
        SELECT CountryID, CountryName, CountryInfo
        FROM country
        ORDER BY LOWER(CountryName) ASC
    ");
    while($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['CountryName']}</td>
            <td>{$row['CountryInfo']}</td>
            <td><a class='edit' href='editCountry.php?id={$row['CountryID']}&table=country' onclick='return confirm(\"Are you sure?\")'>Edit</a> / 
                <a class='delete' href='delete.php?id={$row['CountryID']}&table=country' onclick='return confirm(\"Are you sure?\")'>Delete</a>
            </td>
        </tr>";
    }
    ?>
</table>
</body>
</html>
