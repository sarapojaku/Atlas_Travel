<?php 
session_start();
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="styles.css"/>
    <style>
    @media (max-width: 600px) {
      .nav-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
      }
      .nav-links {
        display: inline-block;
        font-size: 15px;
        align-items: flex-start;
      }
    }
    </style>
</head>
<body>
    <header class="navbar">
      <div class="container nav-inner">
        <a href="index.php" class="logo">
          <div class="logo-icon"><img src="images/logo.png" /></div>
          <span>Atlas Travel</span>
        </a>
        <nav class="nav-links">
          <a href="#deal">Destinations</a>
          <a href="#contact">Contact Us</a>
          <?php if (isset($_SESSION['username'])): ?>
            <span class="user-links">
              <a href="myprofile.php" class="btn username-link"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
              <?php else: ?>
                <a href="client_login.php" class="btn">Log In</a>
                <?php endif; ?>
        </nav>
      </div>
    </header>
</body>
</html>