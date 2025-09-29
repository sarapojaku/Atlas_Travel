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
<title>Our Staff</title>
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
        color: #fff;
        margin-top: 1rem;
    }

    form {
        background: #ffffff;
        padding: 1rem;
        border-radius: 12px;
        margin: 1.5rem auto;
        max-width: 1000px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }
    form input, form select {
        padding: 8px;
        border-radius: 8px;
        border: 1px solid #ccc;
        flex: 1 1 200px;
        font-size: 14px;
    }
    form button {
        background: #625d5d;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        flex: 1 1 100%;
        max-width: 200px;
    }
    form button:hover {
        background: #4a4545;
    }

    .staff-header {
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

    .staff-header h2 {
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
    .staff-container {
        width: 90%;
        max-width: 1200px;
        margin: 1rem auto 2rem auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }

    .staff-card {
        background: #fff;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .staff-card h3 {
        margin: 0;
        font-size: 1.1rem;
        color: #333;
    }

    .staff-info {
        font-size: 0.9rem;
        color: #555;
    }

    .staff-actions {
        margin-top: 0.5rem;
    }

    .staff-actions a {
        font-size: 0.9rem;
        font-weight: bold;
        text-decoration: none;
        margin-right: 10px;
    }
    .staff-actions a.edit { color: #007bff; }
    .staff-actions a.edit:hover { color: #0056b3; }
    .staff-actions a.delete { color: red; }
    .staff-actions a.delete:hover { color: #ff0000; }

    /* Responsive adjustments */
    @media (max-width: 480px) {
        .staff-header h2 {
            font-size: 1.2rem;
        }
        .search-box input {
            max-width: 100%;
        }
        .staff-card {
            padding: 0.8rem;
        }
        .staff-card h3 {
            font-size: 1rem;
        }
        .staff-info {
            font-size: 0.85rem;
        }
    }
</style>
</head>
<body>

<h1>Add New Staff</h1>
<form action="addStaff.php" method="post">
    <input type="text" name="StaffName" placeholder="First Name" required>
    <input type="text" name="StaffSurname" placeholder="Last Name" required>
    <input type="text" name="Username" placeholder="Username" required>
    <input type="email" name="Email" placeholder="Email" required>
    <select name="Gender" required>
        <option value="">Select Gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
    </select>
    <input type="text" name="Phone" placeholder="Phone" required>
    <input type="password" name="Password" placeholder="Password" required>
    <input type="password" name="ConfirmPassword" placeholder="Confirm Password" required>
    <select name="Type" required>
        <option value="">Select Type</option>
        <option value="Employee">Employee</option>
        <option value="Manager">Manager</option>
    </select>
    <input type="hidden" name="DateEmployed" value="<?= date('Y-m-d'); ?>">
    <button type="submit" name="submit">Add Staff</button>
</form>

<div class="staff-header">
    <h2>All Staff</h2>
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search staff...">
    </div>
</div>

<div class="staff-container" id="staffContainer">
<?php 
$result = $conn->query("
    SELECT StaffID, CONCAT(StaffName, ' ', StaffSurname) AS FullName, Username, Email, Gender, Type
    FROM staff
    ORDER BY LOWER(StaffName) ASC, LOWER(StaffSurname) ASC
");
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div class='staff-card'>
                <h3>".htmlspecialchars($row['FullName'])."</h3>
                <div class='staff-info'><strong>Username:</strong> ".htmlspecialchars($row['Username'])."</div>
                <div class='staff-info'><strong>Email:</strong> ".htmlspecialchars($row['Email'])."</div>
                <div class='staff-info'><strong>Gender:</strong> ".htmlspecialchars($row['Gender'])."</div>
                <div class='staff-info'><strong>Type:</strong> ".htmlspecialchars($row['Type'])."</div>
                <div class='staff-actions'>
                    <a class='edit' href='editStaff.php?id={$row['StaffID']}&table=staff' onclick='return confirm(\"Are you sure?\")'>Edit</a>
                    <a class='delete' href='delete.php?id={$row['StaffID']}&table=staff' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                </div>
            </div>";
    }
}
?>
</div>

<script>
    // Client-side search filter for staff cards
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        let cards = document.querySelectorAll(".staff-card");
        
        cards.forEach(card => {
            let text = card.textContent.toLowerCase();
            card.style.display = text.includes(filter) ? "" : "none";
        });
    });
</script>

</body>
</html>
