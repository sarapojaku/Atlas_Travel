<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer</title>
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
      flex-wrap: wrap; /* allow wrapping on smaller screens */
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

    /* Mobile layout */
    @media (max-width: 600px) {
      .footer-inner {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
      }
      .footer-right {
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
      }
    }
</style>

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