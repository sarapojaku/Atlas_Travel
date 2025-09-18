<?php
session_start();
include 'db_connect.php';

// Function to show messages at the top
function redirectWithMessage($msg, $type = "success") {
    $_SESSION['flash_message'] = ["text" => $msg, "type" => $type];
    header("Location: admin.php#destinations");
    exit;
}

// ✅ If form is submitted → Update destination
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    $DestinationID   = intval($_POST['DestinationID']);
    $DestinationName = trim($_POST['DestinationName']);
    $DestinationInfo = trim($_POST['DestinationInfo']);
    $DestinationPrice = floatval($_POST['DestinationPrice']);
    $StartDate       = trim($_POST['StartDate']);
    $EndDate         = trim($_POST['EndDate']);

    // Update query
    $update = $conn->prepare("
        UPDATE destination 
        SET DestinationName = ?, DestinationInfo = ?, DestinationPrice = ?, StartDate = ?, EndDate = ?
        WHERE DestinationID = ?
        ");
    
    $update->bind_param("ssdssi", $DestinationName, $DestinationInfo, $DestinationPrice, $StartDate, $EndDate, $DestinationID);

    if ($update->execute()) {
        redirectWithMessage("Destination updated successfully");
    } else {
        redirectWithMessage("Failed to update destination", "error");
    }
}

// ✅ If GET request → Show edit form
if (isset($_GET['id'])) {
    $DestinationID = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM destination WHERE DestinationID = ?");
    $stmt->bind_param("i", $DestinationID);
    $stmt->execute();
    $result = $stmt->get_result();
    $destination = $result->fetch_assoc();

    if (!$destination) {
        redirectWithMessage("Destination not found", "error");
    }
} else {
    redirectWithMessage("No destination selected", "error");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Destination</title>
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
        }
        input, textarea {
            width: 90%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            background: #625d5d;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover {
            background: #4a4545;
        }
    </style>
</head>
<body>
    <h1>Edit Destination</h1>
    <form action="editDest.php" method="post">
        <input type="hidden" name="DestinationID" value="<?= $destination['DestinationID'] ?>">

        <input type="text" name="DestinationName" value="<?= htmlspecialchars($destination['DestinationName']) ?>" required>
        <textarea name="DestinationInfo" rows="4" required><?= htmlspecialchars($destination['DestinationInfo']) ?></textarea>
        
        <input type="number" step="0.01" name="DestinationPrice" value="<?= htmlspecialchars($destination['DestinationPrice']) ?>" required>
        
        <input type="date" name="StartDate" value="<?= htmlspecialchars($destination['StartDate']) ?>" required>
        
        <input type="date" name="EndDate" value="<?= htmlspecialchars($destination['EndDate']) ?>" required>

        <br>
        <button type="submit" name="submit">Update Destination</button>
    </form>
</body>
</html>
