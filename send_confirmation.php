<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

/**
 * Send booking confirmation email to client
 * Also notifies admin of the new booking
 */
function sendConfirmationEmail($ClientName, $ClientSurname, $email, $DestinationID, $price) {
    $status = '';
    try {
        // ---------------- Client Email ----------------
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'travelatlas24@gmail.com';
        $mail->Password   = 'vupphjsnmwupiuvd'; // Use App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('travelatlas24@gmail.com', 'Travel Atlas');
        $mail->addAddress($email, $ClientName . ' ' . $ClientSurname);

        $mail->isHTML(true);
        $mail->Subject = 'Booking Confirmation - Travel Atlas';
        $mail->Body    = "
            <h2>Booking Confirmation</h2>
            <p>Dear <strong>{$ClientName} {$ClientSurname}</strong>,</p>
            <p>Thank you for booking with <strong>Travel Atlas</strong>.</p>
            <p>Your booking has been confirmed. The total cost is: <strong>€{$price}</strong>.</p>
            <p>We look forward to your trip!</p>
        ";
        $mail->AltBody = "Dear {$ClientName} {$ClientSurname},\n\n
        Thank you for booking with Travel Atlas.\n
        Your booking has been confirmed.\n 
        The total cost is: €{$price}.\n
        We look forward to your trip!";

        $mail->send();
        $status .= "Client email sent to {$email}. ";

        // ---------------- Admin Notification ----------------
        $adminMail = new PHPMailer(true);
        $adminMail->isSMTP();
        $adminMail->Host       = 'smtp.gmail.com';
        $adminMail->SMTPAuth   = true;
        $adminMail->Username   = 'travelatlas24@gmail.com';
        $adminMail->Password   = 'vupphjsnmwupiuvd';
        $adminMail->SMTPSecure = 'tls';
        $adminMail->Port       = 587;

        $adminMail->setFrom('travelatlas24@gmail.com', 'Travel Atlas');
        $adminMail->addAddress('travelatlas24@gmail.com', 'Admin'); 

        $adminMail->isHTML(true);
        $adminMail->Subject = "New Booking: {$ClientName} {$ClientSurname}";
        $adminMail->Body    = "
            <h2>New Booking Notification</h2>
            <p>Client: <strong>{$ClientName} {$ClientSurname}</strong></p>
            <p>Email: <strong>{$email}</strong></p>
            <p>Destination ID: <strong>{$DestinationID}</strong></p>
            <p>Total Price: <strong>€{$price}</strong></p>
        ";
        $adminMail->AltBody = "Client: {$ClientName} {$ClientSurname}\n
        Email: {$email}\n
        DestinationID: {$DestinationID}\n
        Total Price: €{$price}";

        $adminMail->send();
        $status .= "Admin notified.";

        return $status;

    } catch (Exception $e) {
        return "Mailer Error: {$e->getMessage()}";
    }
}
?>
