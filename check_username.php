<?php
include 'db_connect.php';

if (isset($_POST['username'])) {
    $username = $_POST['username'];
    $stmt = $conn->prepare("SELECT ClientID FROM Client WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    echo ($result->num_rows > 0) ? "taken" : "available";
}
?>
