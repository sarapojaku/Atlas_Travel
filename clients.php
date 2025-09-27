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

    .table-wrapper {
        width: 90%;
        max-width: 1200px;
        margin: 1rem auto 2rem auto;
        overflow-x: auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
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

    a.delete, a.edit {
        color: red; 
        text-decoration: none;
        font-weight: bold;
    } 

    a.delete:hover, a.edit:hover {
        color: #ff0000; 
    }

    /* Responsive styling */
    @media (max-width: 768px) {
        .clients-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .clients-header h2 {
            margin-bottom: 0.5rem;
        }
        table th, table td {
            font-size: 13px;
            padding: 0.5rem;
        }
    }

    @media (max-width: 480px) {
        .clients-header h2 {
            font-size: 1.2rem;
        }
        .search-box input {
            max-width: 100%;
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

<div class="table-wrapper">
<table id="clientsTable">
    <tr>
        <th>Full Name</th>
        <th>Username</th>
        <th>Email</th>
        <th>Gender</th>
        <th>Action</th>
    </tr>
    <?php 
    $result = $conn->query("
        SELECT ClientID, CONCAT(ClientName, ' ', ClientSurname) AS FullName, Username, Email, Gender 
        FROM client 
        ORDER BY LOWER(ClientName) ASC, LOWER(ClientSurname) ASC
    ");
    while($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['FullName']}</td>
            <td>{$row['Username']}</td>
            <td>{$row['Email']}</td>
            <td>{$row['Gender']}</td>
            <td><a class='delete' href='delete.php?id={$row['ClientID']}&table=client' onclick='return confirm(\"Are you sure?\")'>Delete</a></td>
        </tr>";
    }
    ?>
</table>
</div>

<script>
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#clientsTable tr:not(:first-child)");
        
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });
</script>

</body>
</html>
