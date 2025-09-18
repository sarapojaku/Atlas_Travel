<?php
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="styles.css"/>
</head>
<body>
    
    <!-- Contact Us Section -->
    <section id="contact" class="section">
        <div class="container contact">
            <div class="section-head">
                <h1>Contact Us!</h1>
                <h4>For any additional information do not hesitate to reach out!</h4>
                <form
                id="contact-form"
                class="contact-form"
                action="contact_submit.php"
                method="post"
                >        
                <div class="form-row">
                    <input type="text" name="name" placeholder="Your Name" required />
                    <input type="text" name="contact" placeholder="Your Number or Email" required />
                </div>
                <textarea name="message" placeholder="Your Message" required></textarea>
                <button type="submit" class="btn block">Send Message</button>
                <div id="contact-response"></div>
            </form>
        </div>
    </div>
</section>

</body>
</html>