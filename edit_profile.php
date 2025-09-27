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
$stmt = $conn->prepare("SELECT * FROM client WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

// Default profile picture path
$defaultImage = "images/default-profile.png";

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Remove picture → reset to default
    if (isset($_POST['removeImage'])) {
        $stmt = $conn->prepare("UPDATE client SET ProfileImage=? WHERE ClientID=?");
        $stmt->bind_param("si", $defaultImage, $client['ClientID']);
        $stmt->execute();
        header("Location: edit_profile.php");
        exit;
    }

    $name = $_POST['ClientName'];
    $surname = $_POST['ClientSurname'];
    $usernameNew = $_POST['Username'];
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

    $stmt = $conn->prepare("UPDATE Client 
        SET ClientName=?, ClientSurname=?, Username=?, Email=?, Phone=?, Gender=?, ProfileImage=? 
        WHERE ClientID=?");
    $stmt->bind_param("sssssssi", $name, $surname, $usernameNew, $email, $phone, $gender, $profileImage, $client['ClientID']);
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
  <link rel="icon" href="images/logo.png" type="image/png" />
  <link rel="shortcut icon" href="images/logo.png" type="image/png" />
  <style>
      body {
        font-family: Arial, sans-serif;
        background: #625d5d;
        margin: 0;
        padding: 0;
      }
      .container {
        max-width: 700px;
        margin: 1rem auto;
        background: #ffffff;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      }
      h2 {
        margin-bottom: 1.2rem;
        color: #333;
        text-align: center;
      }
      img {
        margin-top: 1rem;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #2563eb;
      }
      /* Picture section */
      .picture {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap; /* ✅ Wraps on mobile */
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
        margin-top: 10px;
      }
      .remove-btn {
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        padding: 8px;
      }
      .remove-btn:hover {
        background: #b02a37;
      }
      /* Grid form layout */
      .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem 1.5rem;
        margin-top: 1rem;
      }
      .form-group {
        display: flex;
        flex-direction: column;
      }
      .form-group label {
        font-weight: bold;
        margin-bottom: 0.3rem;
        color: #444;
      }
      .form-group input,
      .form-group select {
        padding: 0.6rem;
        border: 1px solid #ccc;
        border-radius: 6px;
      }
      /* Change password & delete */
      .change-password,
      .delete-profile {
        grid-column: span 2;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-weight: bold;
        color: #000;
        text-align: center;
        flex-wrap: wrap;
      }
      .change-password a,
      .delete-profile a {
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        font-weight: lighter;
      }
      .change-password a {
        background: #2563eb;
        color: #fff;
      }
      .change-password a:hover {
        background: #1d4ed8;
      }
      .delete-profile a {
        background: #c40202ff;
        color: #fff;
      }
      .delete-profile a:hover {
        background: #8a0000ff;
      }
      /* Save button */
      .save-btn {
        grid-column: span 2;
        margin-top: 1.5rem;
        padding: 0.8rem 1.2rem;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1rem;
      }
      .save-btn:hover {
        background: #1d4ed8;
      }
      /* ✅ Mobile responsiveness */
      @media (max-width: 768px) {
        .form-grid {
          grid-template-columns: 1fr; /* one column */
        }
        .save-btn {
          grid-column: span 1;
        }
        .change-password,
        .delete-profile {
          grid-column: span 1;
          flex-direction: column;
          text-align: center;
        }
        .picture {
          flex-direction: column; /* stack picture + actions */
          gap: 1rem;
        }
      }
  </style>
</head>
<body>
  <div class="container">
    <h2>Edit Profile</h2>
    <form action="" method="POST" enctype="multipart/form-data">
      <!-- Profile picture section -->
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

      <!-- Grid fields -->
      <div class="form-grid">
        <div class="form-group">
          <label>First Name</label>
          <input type="text" name="ClientName" value="<?= htmlspecialchars($client['ClientName']) ?>" required>
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" name="ClientSurname" value="<?= htmlspecialchars($client['ClientSurname']) ?>" required>
        </div>
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="Username" value="<?= htmlspecialchars($client['Username']) ?>" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="Email" value="<?= htmlspecialchars($client['Email']) ?>" required>
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="text" name="Phone" value="<?= htmlspecialchars($client['Phone']) ?>">
        </div>
        <div class="form-group">
          <label>Gender</label>
          <select name="Gender">
            <option value="Male" <?= $client['Gender']=="Male"?"selected":"" ?>>Male</option>
            <option value="Female" <?= $client['Gender']=="Female"?"selected":"" ?>>Female</option>
            <option value="Other" <?= $client['Gender']=="Other"?"selected":"" ?>>Other</option>
          </select>
        </div>
        <div class="change-password">
          <span>Do you want to change your password?</span>
          <a href="changePassword.php" class="change-btn">Change Password</a>
        </div>

        <div class="delete-profile">
          <span>Do you want to delete your profile?</span>
          <a href="deleteProfile.php" class="delete-btn">Delete My Account</a>
        </div>
        <button type="submit" class="save-btn">Save Changes</button>
      </div>
    </form>
  </div>
</body>
</html>