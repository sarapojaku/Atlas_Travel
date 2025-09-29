Atlas Travel Agency – Project Overview

📅 Project Start Date: 05.09.2025
📅 Project End Date: 29.09.2025

📌 Project Description

Atlas Travel Agency is a web-based platform designed for managing clients, staff, bookings, and destinations. It provides admins and staff with tools to monitor and manage the agency’s operations, while clients can view and manage their profile, track upcoming trips, and review past trips.

The system is built with PHP, MySQL, and HTML/CSS, with PHPMailer integrated for contact form email notifications. All pages are fully responsive and look good on mobile devices, tablets, and desktops.

🏗 Features

1. Admin Dashboard

Overview cards

- Total clients
- Total staff
- Total revenue
- Total destinations
- Top 5 clients by spending
- Top 5 destinations by revenue
- Fully interactive charts for quick insights

2. Client Management

- Add, edit, delete client accounts
- Secure login & registration

2.1. Profile management

- Edit personal info (name, email, phone, gender)
- Upload/change/remove profile picture
- Spending overview and trip history
- Upcoming and past trips displayed with images and paid amounts
- Reviews for past trips

3. Staff Management

- Add, edit, delete staff
- Staff roles: Admin, Staff, Manager
- Secure authentication for staff accounts
- Admin can manage all staff details

4. Destination Management

- Add, edit, delete destinations
- Track revenue per destination
- Display destination images and trip dates

5. Bookings

- Manage client bookings linked to destinations
- Track spendings per client
- Automatically categorize trips into upcoming and past

6. Contact Form

- Clients and visitors can submit inquiries

PHPMailer sends

- Email to agency
- Thank-you email to sender
- Saves all messages in contact_messages table

⚡ Key Technologies

- PHP – Server-side scripting
- MySQL / MariaDB – Database
- HTML5 & CSS3 – Frontend
- PHPMailer – Email notifications
- JavaScript – Optional charts and interactivity

🚀 Setup Instructions

Clone the repository
Import the agencydb database SQL file
Configure database credentials in db_connect.php
Ensure uploads/ folder is writable (chmod 777 if needed)

Install PHPMailer via Composer:

composer install

Set your SMTP credentials in contact_submit.php
Launch the project in your PHP server or XAMPP

🔒 Security & Best Practices

- Passwords are hashed with password_hash()
- Prepared statements prevent SQL injection
- File uploads are validated for type and size
- Sessions used for authentication

📌 Notes

All pages are responsive and work well on mobile devices
Default images are used if no profile/destination image is provided
Admin can manage all users, staff, destinations, and bookings
Client dashboard is fully interactive and showcases spending and trip history

🛠 Future Improvements

- Add a Bill button under Upcoming Trips in myProfile.php, allowing users to download their bill directly from there.
- Enable clients to select their own travel dates.
- Allow clients to save their payment cards for future bookings (optional).
- Introduce exclusive special offers for loyal customers.
- Display available airlines for each destination.
