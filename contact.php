<?php
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
<style>
     body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background: #f8f8f8;
    }
    .section-head h1,
    .section-head h4 {
        text-align: center;
        margin: 0.3rem 0;
    }
    .section-head h4 {
        font-weight: lighter;
        margin-bottom: 1rem;
    }
    .contact-form {
        margin: 1rem auto;
        display: grid;
        gap: 0.9rem;
        width: 80%;
        max-width: 600px;
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.9rem;
    }
    .contact-form input,
    .contact-form select,
    .contact-form textarea {
        width: 100%;
        padding: 0.8rem 0.9rem;
        border: 1px solid #ccc;
        border-radius: 6px;
        background: #fff;
        font-size: 0.95rem;
        box-sizing: border-box;
    }
    .contact-form textarea {
        min-height: 100px;
        resize: vertical;
    }
    .contact-form button {
        background: #625d5d;
        color: #ffffff;
        border-radius: 6px;
        padding: 10px;
        font-size: 15px;
        font-weight: bold;
        border: none;
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .contact-form button:hover {
        background: #767778;
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    #contact-response {
        padding: 10px;
        border-radius: 5px;
        transition: opacity 0.5s ease;
    }
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        .contact-form {
            width: 90%;
        }
    }
    @media (max-width: 480px) {
        .contact-form input,
        .contact-form select,
        .contact-form textarea,
        .contact-form button {
            font-size: 14px;
            padding: 0.7rem;
        }
    }

</style>
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