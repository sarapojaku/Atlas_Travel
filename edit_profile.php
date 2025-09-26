<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: client_login.php");
    exit;
}

$username = $_SESSION['username'];

// --- Handle delete account first ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteAction']) && $_POST['deleteAction'] === 'yes') {
    // Fetch client to get ProfileImage
    $stmt = $conn->prepare("SELECT ClientID, ProfileImage FROM client WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $clientDelete = $stmt->get_result()->fetch_assoc();

    if ($clientDelete) {
        // Remove profile image if exists
        if (!empty($clientDelete['ProfileImage']) && $clientDelete['ProfileImage'] !== "images/default-profile.png" && file_exists($clientDelete['ProfileImage'])) {
            unlink($clientDelete['ProfileImage']);
        }

        // Delete client record
        $stmt = $conn->prepare("DELETE FROM client WHERE ClientID = ?");
        $stmt->bind_param("i", $clientDelete['ClientID']);
        $stmt->execute();

        // Logout and redirect
        session_unset();
        session_destroy();
        header("Location: index.php?deleted=1");
        exit;
    } else {
        $error = "User not found or already deleted.";
    }
}

// --- Fetch client info only if not deleted ---
$stmt = $conn->prepare("SELECT * FROM client WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

// If somehow client doesn't exist (deleted manually), log out safely
if (!$client) {
    session_unset();
    session_destroy();
    header("Location: client_login.php");
    exit;
}

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

    $stmt = $conn->prepare("UPDATE client 
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
body { font-family: Arial, sans-serif; background: #625d5d; margin: 0; padding: 0; }
.container { max-width: 700px; margin: 2rem auto; background: #fff; padding: 2rem; border-radius: 12px; }
h2 { margin-bottom: 1.5rem; color: #333; text-align: center; }
img { margin-top: 1rem; width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #2563eb; }

.picture { display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem; }
.pic { display: flex; flex-direction: column; align-items: center; }
.actions { display: flex; flex-direction: column; gap: 0.5rem; margin-top: 50px; }
.remove-btn { background: #dc3545; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; padding: 8px; margin-top: 5px; }
.remove-btn:hover { background: #b02a37; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem 2rem; margin-top: 1rem; }
.form-group { display: flex; flex-direction: column; }
.form-group label { font-weight: bold; margin-bottom: 0.3rem; color: #444; }
.form-group input, .form-group select { padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; }

.change-password, .delete-profile { grid-column: span 2; display: flex; align-items: center; justify-content: center; gap: 10px; font-weight: bold; color: #000; }
.change-password{ margin-top: 15px; }
.change-password a { padding: 6px 12px; background: #2563eb; color: #fff; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: lighter; }
.change-password a:hover { background: #1d4ed8; }

.delete-btn { padding: 6px 12px; background: #c40202ff; color: #fff; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; }
.delete-btn:hover { background: #8a0000ff; }

.save-btn { grid-column: span 2; margin-top: 1.5rem; padding: 0.8rem 1.2rem; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; }
.save-btn:hover { background: #1d4ed8; }

.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center; animation: fadeIn 0.3s; }
.modal-content { background: #fff; padding: 2rem; border-radius: 12px; text-align: center; box-shadow: 0 8px 20px rgba(0,0,0,0.2); transform: scale(0.7); animation: scaleUp 0.3s forwards; }
@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
@keyframes scaleUp { from { transform: scale(0.7); } to { transform: scale(1); } }
.modal-content h3 { margin-bottom: 1rem; }
.btn-yes, .btn-no { padding: 10px 25px; margin: 10px; border: none; border-radius: 6px; cursor: pointer; color: #fff; font-size: 14px; }
.btn-yes { background: #d9534f; }
.btn-yes:hover { background: #bb241f; transform: scale(1.05); }
.btn-no { background: #5cb85c; }
.btn-no:hover { background: #29b329; transform: scale(1.05); }
</style>
</head>
<body>
<div class="container">
<h2>Edit Profile</h2>
<form action="" method="POST" enctype="multipart/form-data">
    <!-- Profile picture -->
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

    <!-- Form grid -->
    <div class="form-grid">
        <div class="form-group"><label>First Name</label><input type="text" name="ClientName" value="<?= htmlspecialchars($client['ClientName']) ?>" required></div>
        <div class="form-group"><label>Last Name</label><input type="text" name="ClientSurname" value="<?= htmlspecialchars($client['ClientSurname']) ?>" required></div>
        <div class="form-group"><label>Username</label><input type="text" name="Username" value="<?= htmlspecialchars($client['Username']) ?>" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="Email" value="<?= htmlspecialchars($client['Email']) ?>" required></div>
        <div class="form-group"><label>Phone</label><input type="text" name="Phone" value="<?= htmlspecialchars($client['Phone']) ?>"></div>
        <div class="form-group"><label>Gender</label>
            <select name="Gender">
                <option value="Male" <?= $client['Gender']=="Male"?"selected":"" ?>>Male</option>
                <option value="Female" <?= $client['Gender']=="Female"?"selected":"" ?>>Female</option>
                <option value="Other" <?= $client['Gender']=="Other"?"selected":"" ?>>Other</option>
            </select>
        </div>

        <div class="change-password"><span>Do you want to change your password?</span><a href="changePassword.php" class="change-btn">Change Password</a></div>
        <div class="delete-profile"><span>Do you want to delete your profile?</span><button type="button" id="deleteBtn" class="delete-btn">Delete My Account</button></div>
        <button type="submit" class="save-btn">Save Changes</button>
    </div>
</form>
</div>

<!-- Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3>Are you sure you want to delete your account?</h3>
        <form method="post">
            <button type="submit" name="deleteAction" value="yes" class="btn-yes">Yes</button>
            <button type="button" id="cancelDelete" class="btn-no">No</button>
        </form>
    </div>
</div>

<script>
const deleteBtn = document.getElementById('deleteBtn');
const modal = document.getElementById('deleteModal');
const cancelBtn = document.getElementById('cancelDelete');
deleteBtn.addEventListener('click', () => modal.style.display = 'flex');
cancelBtn.addEventListener('click', () => modal.style.display = 'none');
window.addEventListener('click', (e) => { if(e.target == modal) modal.style.display = 'none'; });
</script>
</body>
</html>
