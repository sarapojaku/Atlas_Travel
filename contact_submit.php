<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
    $contact = isset($_POST['contact']) ? $conn->real_escape_string($_POST['contact']) : '';
    $message = isset($_POST['message']) ? $conn->real_escape_string($_POST['message']) : '';

    if (empty($name) || empty($contact) || empty($message)) {
        echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
        exit;
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, contact, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $contact, $message);
       
    if ($stmt->execute()) {
        
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username   = 'travelatlas24@gmail.com';
            $mail->Password   = 'vupphjsnmwupiuvd';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            
            // Recipients
            $mail->setFrom('travelatlas24@gmail.com', 'Atlas Travel');
            $mail->addAddress('travelatlas24@gmail.com', 'Atlas Travel');

            $mail->isHTML(true);
            $mail->Subject = 'New Contact Form Submission';
            $mail->Body = "<b>Name:</b> $name<br>
                           <b>Contact (email/phone):</b> $contact<br>
                           <b>Message:</b> $message";
            $mail->send();

            // Thank-you email to sender
            $mail2 = new PHPMailer(true);
            $mail2->isSMTP();
            $mail2->SMTPAuth = true;
            $mail2->Host = 'smtp.gmail.com';
            $mail2->Password = 'vupphjsnmwupiuvd';
            $mail2->Username = 'travelatlas24@gmail.com';
            $mail2->SMTPSecure = 'tls';
            $mail2->Port = 587;

            $mail2->setFrom('travelatlas24@gmail.com', 'Atlas Travel');
            $mail2->addAddress($contact); // corrected
            $mail2->isHTML(true);
            $mail2->Subject = "Thank you for contacting Atlas Trave!";
            $mail2->Body = "Hello $name,<br><br>Thank you for reaching out! We received your message and will get back to you soon.<br><br>Best regards,<br>Atlas Travel Team";
            $mail2->send();
            echo json_encode(["status" => "success", "message" => "Message sent successfully!"]);


        } catch (Exception $e) {
            echo json_encode(["status" => "success", "message" => "Message saved but email failed: {$mail->ErrorInfo}"]);
        }

    } else {
        echo json_encode(["status" => "error", "message" => "Error saving message: {$conn->error}"]);
    }

    $stmt->close();
}
$conn->close();
?>
