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
        $clientMail = new PHPMailer(true);
        $clientMail->isSMTP();
        $clientMail->Host       = 'smtp.gmail.com';
        $clientMail->SMTPAuth   = true;
        $clientMail->Username   = 'travelatlas24@gmail.com';
        $clientMail->Password   = 'vupphjsnmwupiuvd'; // Use App Password
        $clientMail->SMTPSecure = 'tls';
        $clientMail->Port       = 587;

        $clientMail->setFrom('travelatlas24@gmail.com', 'Travel Atlas');
        $clientMail->addAddress($email, $ClientName . ' ' . $ClientSurname);

        $clientMail->isHTML(true);
        $clientMail->Subject = 'Booking Confirmation - Travel Atlas';
        $clientMail->Body = "
            <h2 style='color:#2E86C1;'>Booking Confirmation</h2>
            <p>Dear <strong>{$ClientName} {$ClientSurname}</strong>,</p>

            <p>We are pleased to confirm your booking with <strong>Travel Atlas</strong>. Here are your booking details:</p>
            <ul>
                <li><strong>Total Cost:</strong> €{$price}</li>
                <li><strong>Destination ID:</strong> {$DestinationID}</li>
            </ul>

            <p>Our team is committed to ensuring you have a smooth and enjoyable travel experience. 
            If you have any questions or require assistance prior to your trip, please do not hesitate to contact us.</p>

            <p>Thank you for choosing <strong>Travel Atlas</strong>. We look forward to welcoming you!</p>

            <p>Warm regards,<br>
            <strong>Travel Atlas Team</strong></p>
        ";

        $clientMail->AltBody = "Dear {$ClientName} {$ClientSurname},\n\n
            We are pleased to confirm your booking with Travel Atlas.\n
            Booking Details:\n
            - Total Cost: €{$price}\n
            - Destination ID: {$DestinationID}\n\n
            Our team is committed to ensuring you have a smooth and enjoyable travel experience. 
            If you have any questions or need assistance, please contact us.\n\n
            Thank you for choosing Travel Atlas. We look forward to welcoming you!\n
            Warm regards,\n
            Travel Atlas Team";

        $clientMail->send();
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
        $adminMail->Subject = "New Booking Notification - {$ClientName} {$ClientSurname}";
        $adminMail->Body = "
            <h2 style='color:#C0392B;'>New Booking Received</h2>
            <p><strong>Client:</strong> {$ClientName} {$ClientSurname}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Destination ID:</strong> {$DestinationID}</p>
            <p><strong>Total Price:</strong> €{$price}</p>
        ";
        $adminMail->AltBody = "New Booking Received\n
            Client: {$ClientName} {$ClientSurname}\n
            Email: {$email}\n
            Destination ID: {$DestinationID}\n
            Total Price: €{$price}";

        $adminMail->send();
        $status .= "Admin notified.";

        return $status;

    } catch (Exception $e) {
        return "Mailer Error: {$e->getMessage()}";
    }
}
?>
