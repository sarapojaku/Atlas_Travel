<?php
session_start();
include 'db_connect.php';

// Function to show messages at the top
function redirectWithMessage($msg, $type = "success") {
    $_SESSION['flash_message'] = ["text" => $msg, "type" => $type];
    header("Location: admin.php#countries");
    exit;
}

// ✅ If form is submitted → Update country
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    $CountryID = intval($_POST['CountryID']);
    $CountryName = trim($_POST['CountryName']);
    $CountryInfo = trim($_POST['CountryInfo']);
    $CountryImage = null;

    // Check if another country with the same name exists
    $stmt = $conn->prepare("SELECT * FROM country WHERE CountryName = ? AND CountryID != ?");
    $stmt->bind_param("si", $CountryName, $CountryID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        redirectWithMessage("Another country with this name already exists", "error");
    }

    // ✅ Handle file upload
    if (!empty($_FILES['CountryImage']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // create folder if not exists
        }

        $fileName = time() . "_" . basename($_FILES["CountryImage"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["CountryImage"]["tmp_name"], $targetFilePath)) {
                $CountryImage = $fileName; // save only filename in DB
            } else {
                redirectWithMessage("Failed to upload image", "error");
            }
        } else {
            redirectWithMessage("Only JPG, JPEG, PNG, GIF files allowed", "error");
        }
    } else {
        // Keep old image if no new file uploaded
        $old = $conn->prepare("SELECT CountryImage FROM country WHERE CountryID = ?");
        $old->bind_param("i", $CountryID);
        $old->execute();
        $oldResult = $old->get_result()->fetch_assoc();
        $CountryImage = $oldResult['CountryImage'];
    }

    // Update country
    $update = $conn->prepare("UPDATE country SET CountryName = ?, CountryInfo = ?, CountryImage = ? WHERE CountryID = ?");
    $update->bind_param("sssi", $CountryName, $CountryInfo, $CountryImage, $CountryID);

    if ($update->execute()) {
        redirectWithMessage("Country updated successfully");
    } else {
        redirectWithMessage("Failed to update country", "error");
    }
}

// ✅ If GET request → Show edit form
if (isset($_GET['id'])) {
    $CountryID = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM country WHERE CountryID = ?");
    $stmt->bind_param("i", $CountryID);
    $stmt->execute();
    $result = $stmt->get_result();
    $country = $result->fetch_assoc();

    if (!$country) {
        redirectWithMessage("Country not found", "error");
    }
} else {
    redirectWithMessage("No country selected", "error");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Country</title>
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
        img.preview {
            max-width: 200px;
            margin: 10px auto;
            display: block;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <h1>Edit Country</h1>
    <form action="editCountry.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="CountryID" value="<?= $country['CountryID'] ?>">

        <input type="text" name="CountryName" value="<?= htmlspecialchars($country['CountryName']) ?>" required>
        <textarea name="CountryInfo" rows="4" required><?= htmlspecialchars($country['CountryInfo']) ?></textarea>

        <p>Current Image:</p>
        <?php if (!empty($country['CountryImage'])): ?>
            <img src="uploads/<?= htmlspecialchars($country['CountryImage']) ?>" class="preview">
        <?php else: ?>
            <p>No image uploaded</p>
        <?php endif; ?>

        <input type="file" name="CountryImage" accept="image/*">

        <br>
        <button type="submit" name="submit">Update Country</button>
    </form>
</body>
</html>
