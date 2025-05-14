<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Guntur Properties - Find Your Dream Home</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/responsive.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="icon" href="assets\images\favicon.png" type="image/x-icon">
    <link rel="apple-touch-icon" href="assets\images\favicon.png">
  </head>
  <?php
// Get the current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>
  <body>
    <!-- Header Section -->
    <header>
      <div class="container">
        <div class="logo-icon">
          <div class="animated-element">
            <div class="building-animation">
              <div class="floor floor-1"></div>
              <div class="floor floor-2"></div>
              <div class="floor floor-3"></div>
              <div class="floor floor-4"></div>
              <div class="floor floor-5"></div>
              <div class="roof"></div>
              <div class="crane">
                <div class="crane-base"></div>
                <div class="crane-arm"></div>
                <div class="crane-line"></div>
              </div>
            </div>
          </div>
          <div class="logo">
          <a href="index.php">
            <h1>Guntur Properties</h1>
          </a>
        </div>
        </div>
        
        
        <nav>
        <ul>
            <li><a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a></li>
            <li><a href="properties.php" class="<?php echo ($current_page == 'properties.php') ? 'active' : ''; ?>">Properties</a></li>
            <li><a href="agents.php" class="<?php echo ($current_page == 'agents.php') ? 'active' : ''; ?>">Agents</a></li>
            <li><a href="about-us.php" class="<?php echo ($current_page == 'about-us.php') ? 'active' : ''; ?>">About Us</a></li>
        </ul>
        </nav>
        <div class="contact-btn">
          <a href="contact_us.php" class="btn btn-primary">Contact Us</a>
          <a href="admin" class="btn btn-primary">Agent Login</a>
        </div>
        <!-- Updated mobile toggle button -->
        <button class="mobile-menu-toggle" aria-label="Toggle mobile menu">
          <i class="fas fa-bars"></i>
        </button>
      </div>
    </header>