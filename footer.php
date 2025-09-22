<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer</title>
    <link rel="stylesheet" href="style.css"/>
<style>
    .footer {
      border-top: 1px solid var(--border);
      padding: 1.5rem 0;
      background: var(--bg);
    }
    .footer-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .footer-left {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 900;
      color: #ffffff;
    }
    .footer-left img {
      width: 32px;
      height: 32px;
    }
    .footer-right {
      display: flex;
      align-items: center;
      gap: 2rem;
    }
    .footer a {
      color: #ffffff;
      font-weight: 700;
      text-decoration: none;
    }
    </style>
</head>
<body>
    <footer class="footer">
  <div class="container footer-inner">
    <div class="footer-left">
      <img src="images/logo.png" alt="TravelAgency Logo" />
      <span>Atlas Travel</span>
    </div>
    <div class="footer-right">
      <a href="tel:+355693575102" class="muted">+355 69 357 5102</a>
      <a href="https://www.instagram.com/whyhatesara/">Instagram</a>
      <a href="#">Facebook</a>
    </div>
  </div>
</footer>
</body>
</html>