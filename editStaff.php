<?php 
session_start();
include 'db_connect.php';

// Function to show messages at the top
function redirectWithMessage($msg, $type = "success") {
    $_SESSION['flash_message'] = ["text" => $msg, "type" => $type];
    header("Location: admin.php#staff");
    exit;
}

// ✅ If form is submitted → Update staff
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    $StaffID = intval($_POST['StaffID']);
    $StaffName = trim($_POST['StaffName']);
    $StaffSurname = trim($_POST['StaffSurname']);
    $Username = trim($_POST['Username']);
    $Email = trim($_POST['Email']);
    $Gender = trim($_POST['Gender']);
    $Type = trim($_POST['Type']);

    // Update query
    $update = $conn->prepare("
        UPDATE staff 
        SET StaffName = ?, StaffSurname = ?, Username = ?, Email = ?, Gender = ?, Type = ?
        WHERE StaffID = ?
    ");
    
    $update->bind_param("ssssssi", $StaffName, $StaffSurname, $Username, $Email, $Gender, $Type, $StaffID);

    if ($update->execute()) {
        redirectWithMessage("Staff updated successfully");
    } else {
        redirectWithMessage("Failed to update staff", "error");
    }
}

// ✅ If GET request → Show edit form
if (isset($_GET['id'])) {
    $StaffID = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM staff WHERE StaffID = ?");
    $stmt->bind_param("i", $StaffID);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();

    if (!$staff) {
        redirectWithMessage("Staff not found", "error");
    }
} else {
    redirectWithMessage("No staff selected", "error");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Staff</title>
    <style>
        body {
            background: #625d5d;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            text-align: center;
            padding: 20px;
        }
        form {
            background: #fff;
            color: #000;
            padding: 20px;
            border-radius: 12px;
            max-width: 600px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }
        input, select {
            width: 100%; /* All fields same length */
            max-width: 500px; /* Optional max width for large screens */
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            background: #625d5d;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            width: 100%;
            max-width: 500px;
        }
        button:hover {
            background: #4a4545;
        }
    </style>
</head>
<body>
    <h1>Edit Staff</h1>
    <form action="editStaff.php" method="post">
        <input type="hidden" name="StaffID" value="<?= $staff['StaffID'] ?>">

        <input type="text" name="StaffName" value="<?= htmlspecialchars($staff['StaffName']) ?>" placeholder="First Name" required>
        <input type="text" name="StaffSurname" value="<?= htmlspecialchars($staff['StaffSurname']) ?>" placeholder="Surname" required>
        <input type="text" name="Username" value="<?= htmlspecialchars($staff['Username']) ?>" placeholder="Username" required>
        <input type="email" name="Email" value="<?= htmlspecialchars($staff['Email']) ?>" placeholder="Email" required>

        <!-- Gender dropdown -->
        <select name="Gender" required>
            <option value="">Select Gender</option>
            <option value="Male" <?= $staff['Gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= $staff['Gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= $staff['Gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>

        <!-- Type dropdown -->
        <select name="Type" required>
            <option value="">Select Type</option>
            <option value="Admin" <?= $staff['Type'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
            <option value="Staff" <?= $staff['Type'] === 'Staff' ? 'selected' : '' ?>>Staff</option>
            <option value="Manager" <?= $staff['Type'] === 'Manager' ? 'selected' : '' ?>>Manager</option>
        </select>

        <button type="submit" name="submit">Update Staff</button>
    </form>
</body>
</html>
