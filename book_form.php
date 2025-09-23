<?php
// book_form.php
// Expects JSON POST body with ClientName, ClientSurname, number, exDate, cvv
// Query param: ?id={DestinationID}

header('Content-Type: application/json');

include 'db_connect.php'; // must define $conn as mysqli connection

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// Admin email - replace with your real admin email
$adminEmail = 'admin@example.com';

// Helper to respond and exit
function respond($status, $message, $extra = []) {
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $extra));
    exit;
}

// Read JSON payload
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) respond('error', 'Invalid request payload.');

// Basic validation
$ClientName = isset($data['ClientName']) ? trim($data['ClientName']) : '';
$ClientSurname = isset($data['ClientSurname']) ? trim($data['ClientSurname']) : '';
$number = isset($data['number']) ? preg_replace('/\D/', '', $data['number']) : ''; // digits only
$exDate = isset($data['exDate']) ? trim($data['exDate']) : '';
$cvv = isset($data['cvv']) ? trim($data['cvv']) : '';

if (empty($ClientName) || empty($ClientSurname) || empty($number) || empty($exDate) || empty($cvv)) {
    respond('error', 'Please fill in all required fields.');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    respond('error', 'Invalid destination ID.');
}
$DestinationID = intval($_GET['id']);

// Validate card number length (basic Luhn is optional; here we require 12-19 digits)
if (strlen($number) < 12 || strlen($number) > 19) {
    respond('error', 'Invalid card number.');
}

// Validate CVV length
if (!preg_match('/^\d{3,4}$/', $cvv)) {
    respond('error', 'Invalid CVV.');
}

// Validate exDate format - input type month gives YYYY-MM. We'll accept YYYY-MM or MM/YYYY or YYYY/MM
$expiryMonth = '';
$expiryYear = '';
if (preg_match('/^\d{4}-\d{2}$/', $exDate)) { // YYYY-MM
    [$expiryYear, $expiryMonth] = explode('-', $exDate);
} elseif (preg_match('/^\d{2}\/\d{4}$/', $exDate)) { // MM/YYYY
    [$expiryMonth, $expiryYear] = explode('/', $exDate);
} else {
    respond('error', 'Invalid expiry date format.');
}

// Basic expiry check (not expired)
if (!checkdate((int)$expiryMonth, 1, (int)$expiryYear)) {
    respond('error', 'Invalid expiry date.');
}
$expY = (int)$expiryYear;
$expM = (int)$expiryMonth;
$nowY = (int)date('Y');
$nowM = (int)date('n');
if ($expY < $nowY || ($expY === $nowY && $expM < $nowM)) {
    respond('error', 'Card is expired.');
}

// Mask / store only last 4 digits and expiry in MM/YYYY
$card_last4 = substr($number, -4);
$card_expiry = str_pad($expiryMonth, 2, '0', STR_PAD_LEFT) . '/' . $expiryYear;

// -------------- DB operations --------------
// Start transaction
$conn->begin_transaction();

try {
    // 0) Check destination and get price & name
    $sql = "SELECT DestinationPrice, DestinationName FROM Destination WHERE DestinationID = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed (dest): " . $conn->error);
    $stmt->bind_param("i", $DestinationID);
    $stmt->execute();
    $stmt->bind_result($price, $DestinationName);
    if (!$stmt->fetch()) {
        $stmt->close();
        throw new Exception("Destination not found.");
    }
    $stmt->close();

    // 1) Find client
    $sql = "SELECT ClientID, Email FROM Client WHERE ClientName = ? AND ClientSurname = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed (client): " . $conn->error);
    $stmt->bind_param("ss", $ClientName, $ClientSurname);
    $stmt->execute();
    $stmt->bind_result($ClientID, $ClientEmail);
    $found = $stmt->fetch();
    $stmt->close();

    if (!$found || !$ClientID) {
        throw new Exception("Client not found. Please register first.");
    }

    // 2) Insert booking into Booking table
    $sql = "INSERT INTO Booking (ClientSpendings, Reviews, ClientID, DestinationID) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed (insert booking): " . $conn->error);
    // Reviews left blank for now
    $emptyReviews = null;
    $priceDecimal = (float)$price;
    $stmt->bind_param("dsii", $priceDecimal, $emptyReviews, $ClientID, $DestinationID);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception("Failed to insert booking: " . $stmt->error);
    }
    $stmt->close();

    // 3) Update client Spending (the DB column is INT in your schema; we'll add the decimal safely)
    $sql = "UPDATE Client SET Spending = COALESCE(Spending, 0) + ? WHERE ClientID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed (update client spending): " . $conn->error);
    $stmt->bind_param("di", $priceDecimal, $ClientID);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception("Failed to update client spending: " . $stmt->error);
    }
    $stmt->close();

    // 4) Update destination Revenue
    $sql = "UPDATE Destination SET Revenue = COALESCE(Revenue, 0) + ? WHERE DestinationID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed (update revenue): " . $conn->error);
    $stmt->bind_param("di", $priceDecimal, $DestinationID);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception("Failed to update destination revenue: " . $stmt->error);
    }
    $stmt->close();

    // 5) Ensure a payments table exists (safe to create if missing). We'll create book_payments.
    $createPayments = "
        CREATE TABLE IF NOT EXISTS book_payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ClientID INT,
            DestinationID INT,
            card_last4 CHAR(4),
            card_expiry VARCHAR(7),
            amount DECIMAL(10,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ClientID) REFERENCES Client(ClientID),
            FOREIGN KEY (DestinationID) REFERENCES Destination(DestinationID)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    if (!$conn->query($createPayments)) {
        throw new Exception("Failed to ensure payments table: " . $conn->error);
    }

    // 6) Insert payment record (no full PAN, no CVV)
    $sql = "INSERT INTO book_payments (ClientID, DestinationID, card_last4, card_expiry, amount) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed (insert payment): " . $conn->error);
    $stmt->bind_param("iissd", $ClientID, $DestinationID, $card_last4, $card_expiry, $priceDecimal);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception("Failed to record payment: " . $stmt->error);
    }
    $stmt->close();

    // Commit transaction
    $conn->commit();

    // ------------- Send emails -------------
    $mailClient = new PHPMailer(true);
    $mailAdmin = new PHPMailer(true);

    // Configure basic SMTP settings? If you have SMTP, configure here.
    // For example (uncomment and fill if you use SMTP):
    foreach ([$mailClient, $mailAdmin] as $m) {
        $m->isSMTP();
        $m->Host = 'smtp.example.com';
        $m->SMTPAuth = true;
        $m->Username = 'travelatlas24@gmail.com';
        $m->Password = 'vupphjsnmwupiuvd';
        $m->SMTPSecure = 'tls';
        $m->Port = 587;
    }

    // CLIENT EMAIL
    try {
        $mailClient->setFrom($adminEmail, 'Agency Admin');
        $mailClient->addAddress($ClientEmail, $ClientName . ' ' . $ClientSurname);
        $mailClient->isHTML(true);
        $mailClient->Subject = "Booking confirmed: " . $DestinationName;
        $clientBody = "
            <p>Hi " . htmlspecialchars($ClientName) . ",</p>
            <p>Thank you — your booking for <strong>" . htmlspecialchars($DestinationName) . "</strong> was successful.</p>
            <ul>
                <li>Amount: €" . number_format($priceDecimal,2) . "</li>
                <li>Card (last 4): **** " . htmlspecialchars($card_last4) . "</li>
                <li>Expiry: " . htmlspecialchars($card_expiry) . "</li>
            </ul>
            <p>If you have questions reply to this email.</p>
        ";
        $mailClient->Body = $clientBody;
        $mailClient->send();
    } catch (Exception $e) {
        // Non-fatal: email failure should NOT rollback transaction but let's log to error log
        error_log("Client email failed: " . $mailClient->ErrorInfo);
    }

    // ADMIN EMAIL
    try {
        $mailAdmin->setFrom($adminEmail, 'Agency System');
        $mailAdmin->addAddress($adminEmail);
        $mailAdmin->isHTML(true);
        $mailAdmin->Subject = "New booking: " . $ClientName . " " . $ClientSurname;
        $adminBody = "
            <p>Admin,</p>
            <p>A new booking was made:</p>
            <ul>
                <li>Client: " . htmlspecialchars($ClientName) . " " . htmlspecialchars($ClientSurname) . "</li>
                <li>Client email: " . htmlspecialchars($ClientEmail) . "</li>
                <li>Destination: " . htmlspecialchars($DestinationName) . " (ID: " . intval($DestinationID) . ")</li>
                <li>Amount: €" . number_format($priceDecimal,2) . "</li>
                <li>Card last4: **** " . htmlspecialchars($card_last4) . "</li>
            </ul>
        ";
        $mailAdmin->Body = $adminBody;
        $mailAdmin->send();
    } catch (Exception $e) {
        error_log("Admin email failed: " . $mailAdmin->ErrorInfo);
    }

    // Final success response
    respond('success', 'Booking successful! Confirmation email sent (if configured).');

} catch (Exception $ex) {
    // Rollback on any error
    $conn->rollback();
    error_log("Booking error: " . $ex->getMessage());
    respond('error', 'Booking failed: ' . $ex->getMessage());
}
