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
}
h1, h2 {
    text-align: center;
    color: #fff;
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
.staff-header {
    width: 70%;
    margin: 2rem auto 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #fff;
}
.staff-header h2 {
    margin: 0;
    text-align: left;
    color: #fff;
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
    color: #000; 
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
#noResults {
    text-align: center;
    color: #fff;
    margin-top: 1rem;
    display: none;
    font-size: 16px;
    font-weight: bold;
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
        <input type="search" id="searchInput" placeholder="Search staff...">
    </div>
</div>

<table id="staffTable">
    <thead>
        <tr>
            <th>Full Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Gender</th>
            <th>Type</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php 
    $result = $conn->query("
        SELECT StaffID, CONCAT(StaffName, ' ', StaffSurname) AS FullName, Username, Email, Gender, Type
        FROM staff
        ORDER BY LOWER(StaffName) ASC, LOWER(StaffSurname) ASC
    ");
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>".htmlspecialchars($row['FullName'])."</td>
                <td>".htmlspecialchars($row['Username'])."</td>
                <td>".htmlspecialchars($row['Email'])."</td>
                <td>".htmlspecialchars($row['Gender'])."</td>
                <td>".htmlspecialchars($row['Type'])."</td>
                <td><a class='edit' href='editStaff.php?id={$row['StaffID']}&table=staff' onclick='return confirm(\"Are you sure?\")'>Edit</a> / <a class='delete' href='delete.php?id={$row['StaffID']}&table=staff' onclick='return confirm(\"Are you sure?\")'>Delete</a></td>
            </tr>";
        }
    }
    ?>
    </tbody>
</table>

<div id="noResults">No results found</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("searchInput");
    const rows = document.querySelectorAll("#staffTable tbody tr");
    const noResults = document.getElementById("noResults");

    function normalize(str) {
        return str
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, ""); // remove accents
    }

    function filterRows() {
        const filter = normalize(searchInput.value.trim());
        let matches = 0;

        rows.forEach(row => {
            // grab ALL cell text except the last Action column
            const cells = Array.from(row.querySelectorAll("td"));
            const text = normalize(
                cells.slice(0, -1).map(td => td.textContent).join(" ")
            );

            if (text.includes(filter)) {
                row.style.display = "";
                matches++;
            } else {
                row.style.display = "none";
            }
        });

        // Show or hide "No results"
        noResults.style.display = matches === 0 ? "block" : "none";
    }

    // Run filter every time user types
    searchInput.addEventListener("input", filterRows);

    // Run once on load (in case table is empty)
    filterRows();
});
</script>


</body>
</html>