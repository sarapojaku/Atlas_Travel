<?php
session_start();
include 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: client_login.php");
    exit;
}

$username = $_SESSION['username'];

// Fetch client details
$stmt = $conn->prepare("SELECT * FROM Client WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

// Default profile picture path
$defaultImage = "images/default-profile.png";

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Remove picture → reset to default
    if (isset($_POST['removeImage'])) {
        $stmt = $conn->prepare("UPDATE Client SET ProfileImage=? WHERE ClientID=?");
        $stmt->bind_param("si", $defaultImage, $client['ClientID']);
        $stmt->execute();
        header("Location: edit_profile.php");
        exit;
    }

    $name = $_POST['ClientName'];
    $surname = $_POST['ClientSurname'];
    $email = $_POST['Email'];
    $phone = $_POST['Phone'];
    $gender = $_POST['Gender'];
    $profileImage = $client['ProfileImage'] ?: $defaultImage;

    // Handle new upload
    if (!empty($_FILES['ProfileImage']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $targetFile = $targetDir . uniqid() . "_" . basename($_FILES['ProfileImage']['name']);
        move_uploaded_file($_FILES['ProfileImage']['tmp_name'], $targetFile);
        $profileImage = $targetFile;
    }

    $stmt = $conn->prepare("UPDATE Client SET ClientName=?, ClientSurname=?, Email=?, Phone=?, Gender=?, ProfileImage=? WHERE ClientID=?");
    $stmt->bind_param("ssssssi", $name, $surname, $email, $phone, $gender, $profileImage, $client['ClientID']);
    $stmt->execute();

    header("Location: myprofile.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f3f4f6;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 600px;
      margin: 2rem auto;
      background: #fff;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    h2 {
      margin-bottom: 1.5rem;
      color: #333;
    }
    label {
      display: block;
      margin-top: 1rem;
      font-weight: bold;
      color: #444;
    }
    input, select {
      width: 100%;
      padding: 0.6rem;
      margin-top: 0.5rem;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      margin-top: 1.2rem;
      padding: 0.7rem 1.2rem;
      background: #2563eb;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1rem;
    }
    button:hover {
      background: #1d4ed8;
    }
    img {
      margin-top: 1rem;
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #2563eb;
    }
    .picture {
      display: flex;
      align-items: center;
      gap: 2rem;
    }
    .pic {
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .actions {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      margin-top: 50px;
    }
    .remove-btn {
      background: #dc3545;
      color: white;
      border: none;
      margin-right: 140px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      padding: 8px;
      margin-top: 5px;
    }
    .remove-btn:hover {
      background: #b02a37;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Edit Profile</h2>
    <form action="" method="POST" enctype="multipart/form-data">
      <div class="picture">
        <div class="pic">
          <label>Profile Picture</label>
          <img src="<?= htmlspecialchars($client['ProfileImage'] ?: $defaultImage) ?>" alt="Current Picture">
        </div>
        <div class="actions">
          <input type="file" name="ProfileImage" accept="image/*">
          <?php if ($client['ProfileImage'] && $client['ProfileImage'] !== $defaultImage): ?>
            <button type="submit" name="removeImage" class="remove-btn">Remove Picture</button>
          <?php endif; ?>
        </div>
      </div>

      <label>First Name</label>
      <input type="text" name="ClientName" value="<?= htmlspecialchars($client['ClientName']) ?>" required>

      <label>Last Name</label>
      <input type="text" name="ClientSurname" value="<?= htmlspecialchars($client['ClientSurname']) ?>" required>

      <label>Username</label>
      <input type="text" name="Username" value="<?= htmlspecialchars($client['Username']) ?>" required>

      <label>Email</label>
      <input type="email" name="Email" value="<?= htmlspecialchars($client['Email']) ?>" required>

      <label>Phone</label>
      <input type="text" name="Phone" value="<?= htmlspecialchars($client['Phone']) ?>">

      <label>Gender</label>
      <select name="Gender">
        <option value="Male" <?= $client['Gender']=="Male"?"selected":"" ?>>Male</option>
        <option value="Female" <?= $client['Gender']=="Female"?"selected":"" ?>>Female</option>
        <option value="Other" <?= $client['Gender']=="Other"?"selected":"" ?>>Other</option>
      </select>


      <button type="submit">Save Changes</button>
    </form>
  </div>
</body>
</html>
