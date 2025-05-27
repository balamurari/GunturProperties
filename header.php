<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guntur Properties</title>
    <link rel="icon" href="assets/images/logo.jpg" type="image/x-icon">
    <link rel="apple-touch-icon" href="assets/images/logo.jpg">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* ===== RESET & BASE STYLES ===== */
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      /* Real estate themed colors */
      --primary-color: #3f51b5;
      --primary-dark: #303f9f;
      --primary-light: #5c6bc0;
      --secondary-color: #4caf50;
      --accent-color: #ff9800;
      
      /* Neutral colors */
      --white: #ffffff;
      --light-gray: #f5f7fa;
      --medium-gray: #e0e0e0;
      --text-dark: #333333;
      --text-light: #ffffff;
      --border-color: rgba(255, 255, 255, 0.2);
      
      /* Spacing */
      --space-xs: 4px;
      --space-sm: 8px;
      --space-md: 16px;
      --space-lg: 24px;
      --space-xl: 32px;
      
      /* Navbar heights */
      --navbar-height: 80px;
      --mobile-header-height: 50px;
      
      /* Other */
      --border-radius: 8px;
      --shadow-light: 0 2px 8px rgba(0, 0, 0, 0.1);
      --shadow-medium: 0 4px 20px rgba(0, 0, 0, 0.15);
      --shadow-heavy: 0 8px 30px rgba(0, 0, 0, 0.2);
      --transition-fast: 0.2s ease;
      --transition-smooth: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.6;
      color: var(--text-dark);
      background-color: var(--white);
      padding-top: var(--navbar-height);
    }

    a {
      text-decoration: none;
      color: inherit;
      transition: var(--transition-smooth);
    }

    ul {
      list-style: none;
    }

    img {
      max-width: 100%;
      height: auto;
      display: block;
    }

    button {
      cursor: pointer;
      background: none;
      border: none;
      font-family: inherit;
      transition: var(--transition-smooth);
    }

    /* ===== DESKTOP NAVBAR ===== */
    .navbar {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      box-shadow: var(--shadow-medium);
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
      height: var(--navbar-height);
      backdrop-filter: blur(10px);
    }

    .navbar::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.1) 50%, transparent 100%);
      opacity: 0;
      transition: var(--transition-smooth);
    }

    .navbar:hover::before {
      opacity: 1;
    }

    .navbar-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 var(--space-lg);
      display: flex;
      justify-content: space-between;
      align-items: center;
      height: var(--navbar-height);
    }

    /* Logo */
    .navbar-logo {
      display: flex;
      align-items: center;
      z-index: 10;
     
    }

    .navbar-logo a {
      display: flex;
      align-items: center;
      padding: var(--space-sm);
      border-radius: var(--border-radius);
      transition: var(--transition-smooth);
    }

    .navbar-logo a:hover {
      background-color: rgba(255, 255, 255, 0.1);
      transform: scale(1.05);
    }

    .navbar-logo img {
      height: 50px;
      width: auto;
      filter: brightness(1.1);
       border-radius: 50%;
    }

    /* Main Menu */
    .navbar-menu {
      display: flex;
      align-items: center;
      margin: 0 var(--space-lg);
      flex: 1;
      justify-content: center;
    }

    .navbar-item {
      position: relative;
      margin: 0 var(--space-xs);
    }

    .navbar-link {
      display: flex;
      align-items: center;
      padding: var(--space-md) var(--space-lg);
      height: calc(var(--navbar-height) - 20px);
      color: var(--text-light);
      font-weight: 500;
      font-size: 0.95rem;
      border-radius: var(--border-radius);
      position: relative;
      overflow: hidden;
      transition: var(--transition-smooth);
    }

    .navbar-link::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: var(--transition-smooth);
    }

    .navbar-link:hover::before {
      left: 100%;
    }

    .navbar-link i {
      margin-left: var(--space-sm);
      font-size: 0.75rem;
      transition: var(--transition-smooth);
    }

    .navbar-link:hover,
    .navbar-link:focus {
      color: var(--white);
      background-color: var(--primary-light);
      transform: translateY(-2px);
      box-shadow: var(--shadow-light);
    }

    .navbar-link:hover i {
      transform: rotate(180deg);
    }

    /* Dropdown Menus */
    .navbar-dropdown {
      position: absolute;
      top: calc(100% + 10px);
      left: 50%;
      transform: translateX(-50%);
      min-width: 240px;
      background: linear-gradient(145deg, var(--white) 0%, #fafafa 100%);
      box-shadow: var(--shadow-heavy);
      border-radius: var(--border-radius);
      opacity: 0;
      visibility: hidden;
      transform: translateX(-50%) translateY(-10px);
      transition: var(--transition-smooth);
      z-index: 100;
      border: 1px solid var(--medium-gray);
      overflow: hidden;
    }

    .navbar-dropdown::before {
      content: '';
      position: absolute;
      top: -8px;
      left: 50%;
      transform: translateX(-50%);
      width: 16px;
      height: 16px;
      background: var(--white);
      border: 1px solid var(--medium-gray);
      border-bottom: none;
      border-right: none;
      transform: translateX(-50%) rotate(45deg);
    }

    .navbar-dropdown.active {
      opacity: 1;
      visibility: visible;
      transform: translateX(-50%) translateY(0);
    }

    .dropdown-item {
      display: flex;
      align-items: center;
      padding: var(--space-md) var(--space-lg);
      color: var(--text-dark);
      border-bottom: 1px solid var(--light-gray);
      position: relative;
      transition: var(--transition-smooth);
    }

    .dropdown-item:last-child {
      border-bottom: none;
    }

    .dropdown-item::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      width: 4px;
      height: 100%;
      background: var(--primary-color);
      transform: scaleY(0);
      transition: var(--transition-smooth);
    }

    .dropdown-item i {
      margin-right: var(--space-md);
      color: var(--primary-color);
      font-size: 1rem;
      width: 20px;
      transition: var(--transition-smooth);
    }

    .dropdown-item:hover {
      background: linear-gradient(90deg, var(--light-gray) 0%, rgba(63, 81, 181, 0.05) 100%);
      color: var(--primary-color);
      padding-left: calc(var(--space-lg) + 8px);
    }

    .dropdown-item:hover::before {
      transform: scaleY(1);
    }

    .dropdown-item:hover i {
      transform: scale(1.2);
      color: var(--accent-color);
    }

    /* Agent Login Button */
    .navbar-actions {
      display: flex;
      align-items: center;
    }

    .post-property-btn {
      padding: var(--space-md) var(--space-xl);
      background: linear-gradient(135deg, var(--secondary-color) 0%, #45a049 100%);
      color: var(--white);
      border-radius: var(--border-radius);
      font-weight: 600;
      font-size: 0.95rem;
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
      transition: var(--transition-smooth);
      box-shadow: var(--shadow-light);
    }

    .post-property-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: var(--transition-smooth);
    }

    .post-property-btn:hover::before {
      left: 100%;
    }

    .post-property-btn:hover {
      background: linear-gradient(135deg, var(--accent-color) 0%, #f57c00 100%);
      transform: translateY(-3px);
      box-shadow: var(--shadow-medium);
    }

    /* ===== MOBILE HEADER ===== */
    .mobile-header {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      box-shadow: var(--shadow-medium);
      z-index: 1000;
    }

    .mobile-header-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: var(--space-md) var(--space-lg);
      height: 60px;
    }

    .mobile-menu-toggle {
      width: 44px;
      height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--white);
      font-size: 1.3rem;
      background-color: rgba(255, 255, 255, 0.15);
      border-radius: var(--border-radius);
      transition: var(--transition-smooth);
    }

    .mobile-menu-toggle:hover {
      background-color: var(--accent-color);
      transform: scale(1.1);
    }

    .mobile-logo img {
      height: 40px;
      width: auto;
    }

    /* Mobile Menu */
    .mobile-menu {
      display: none;
      background: linear-gradient(180deg, var(--white) 0%, #fafafa 100%);
      border-top: 1px solid var(--border-color);
      max-height: calc(100vh - var(--mobile-header-height));
      overflow-y: auto;
    }

    .mobile-menu.active {
      display: block;
      animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .mobile-menu-list li {
      border-bottom: 1px solid var(--light-gray);
    }

    .mobile-menu-list li a {
      display: block;
      padding: var(--space-lg);
      color: var(--text-dark);
      font-weight: 500;
      transition: var(--transition-smooth);
      position: relative;
    }

    .mobile-menu-list li a::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      width: 4px;
      height: 100%;
      background: var(--primary-color);
      transform: scaleY(0);
      transition: var(--transition-smooth);
    }

    .mobile-menu-list li a:hover {
      background: linear-gradient(90deg, var(--light-gray) 0%, rgba(63, 81, 181, 0.05) 100%);
      color: var(--primary-color);
      padding-left: calc(var(--space-lg) + 12px);
    }

    .mobile-menu-list li a:hover::before {
      transform: scaleY(1);
    }

    .has-submenu {
      position: relative;
    }

    .submenu-toggle {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
    }

    .submenu-toggle i {
      transition: var(--transition-smooth);
      color: var(--primary-color);
    }

    .has-submenu.open .submenu-toggle i {
      transform: rotate(180deg);
      color: var(--accent-color);
    }

    .mobile-submenu {
      display: none;
      background: linear-gradient(180deg, var(--light-gray) 0%, #f0f0f0 100%);
      padding-left: var(--space-lg);
    }

    .has-submenu.open .mobile-submenu {
      display: block;
      animation: slideDown 0.3s ease-out;
    }

    .mobile-submenu li a {
      padding: var(--space-md) var(--space-lg);
      display: flex;
      align-items: center;
      font-size: 0.9rem;
    }

    .mobile-submenu li a i {
      margin-right: var(--space-md);
      font-size: 0.9rem;
      color: var(--primary-color);
      width: 20px;
    }

    .agent-login-mobile {
      padding: var(--space-lg);
      background: linear-gradient(180deg, #fafafa 0%, var(--light-gray) 100%);
    }

    .post-property-btn-mobile {
      display: block;
      padding: var(--space-md);
      background: linear-gradient(135deg, var(--secondary-color) 0%, #45a049 100%);
      color: var(--white) !important;
      text-align: center;
      border-radius: var(--border-radius);
      font-weight: 600;
      transition: var(--transition-smooth);
      box-shadow: var(--shadow-light);
    }

    .post-property-btn-mobile:hover {
      background: linear-gradient(135deg, var(--accent-color) 0%, #f57c00 100%);
      transform: translateY(-2px);
      box-shadow: var(--shadow-medium);
    }

    /* ===== RESPONSIVE STYLES ===== */
    @media (max-width: 1200px) {
      .navbar-container {
        padding: 0 var(--space-md);
      }
      
      .navbar-link {
        padding: var(--space-sm) var(--space-md);
        font-size: 0.9rem;
      }
    }

    @media (max-width: 1024px) {
      .navbar-menu {
        margin: 0 var(--space-md);
      }
      
      .navbar-item {
        margin: 0 2px;
      }
      
      .navbar-link {
        padding: var(--space-sm) var(--space-md);
        font-size: 0.85rem;
      }
      
      .post-property-btn {
        padding: var(--space-sm) var(--space-lg);
        font-size: 0.9rem;
      }
    }

    @media (max-width: 900px) {
      .navbar-link {
        padding: var(--space-xs) var(--space-sm);
        font-size: 0.8rem;
      }
    }

    @media (max-width: 768px) {
     
      
      .navbar {
        display: none;
      }
      
      .mobile-header {
        display: block;
      }
    }

    /* Extra small devices */
    @media (max-width: 480px) {
      .mobile-header-top {
        padding: var(--space-md);
      }
      
      .mobile-logo img {
        height: 50px;
        border-radius: 50%;
      }
      
      .mobile-menu-toggle {
        width: 40px;
        height: 40px;
        font-size: 1.1rem;
      }
      
      .mobile-menu-list li a {
        padding: var(--space-md);
      }
      
      .post-property-btn-mobile {
        padding: var(--space-md);
      }
    }

    /* Demo content styles */
    .demo-content {
      padding: var(--space-xl);
      max-width: 1200px;
      margin: 0 auto;
      text-align: center;
    }

    .demo-content h1 {
      color: var(--primary-color);
      margin-bottom: var(--space-lg);
      font-size: 2.5rem;
    }

    .demo-content p {
      font-size: 1.1rem;
      color: var(--text-dark);
      margin-bottom: var(--space-lg);
    }
  </style>
</head>
<body>
  <!-- Desktop Navbar -->
  <nav class="navbar">
    <div class="navbar-container">
      <!-- Logo -->
      <div class="navbar-logo">
        <a href="index.php">
          <img src="assets/images/logo.jpg" alt="Guntur Properties">
        </a>
      </div>
      
      <!-- Main Menu -->
      <ul class="navbar-menu">
        <li class="navbar-item">
          <a href="index.php" class="navbar-link">Home</a>
        </li>
        <li class="navbar-item">
          <a href="properties.php" class="navbar-link">Properties</a>
        </li>
        <li class="navbar-item">
          <a href="agents.php" class="navbar-link">Agents</a>
        </li>
        <li class="navbar-item">
          <a href="#" class="navbar-link dropdown-trigger">About Us <i class="fas fa-chevron-down"></i></a>
          <div class="navbar-dropdown">
            <a href="ourstory.php" class="dropdown-item">
              <i class="fas fa-book-open"></i> Our Story
            </a>
            <a href="our-work.php" class="dropdown-item">
              <i class="fas fa-briefcase"></i> Our Work
            </a>
            <a href="our-clients.php" class="dropdown-item">
              <i class="fas fa-users"></i> Our Clients
            </a>
          </div>
        </li>
        <li class="navbar-item">
          <a href="#" class="navbar-link dropdown-trigger">Services <i class="fas fa-chevron-down"></i></a>
          <div class="navbar-dropdown">
            <a href="strategic-marketing.php" class="dropdown-item">
              <i class="fas fa-chart-line"></i> Strategic Marketing
            </a>
            <a href="negotiation.php" class="dropdown-item">
              <i class="fas fa-handshake"></i> Negotiation
            </a>
            <a href="closing-success.php" class="dropdown-item">
              <i class="fas fa-check-circle"></i> Closing Success
            </a>
          </div>
        </li>
        <li class="navbar-item">
          <a href="#" class="navbar-link dropdown-trigger">Contact <i class="fas fa-chevron-down"></i></a>
          <div class="navbar-dropdown">
            <a href="contact-us.php" class="dropdown-item">
              <i class="fas fa-envelope"></i> Contact Us
            </a>
            <a href="our-office.php" class="dropdown-item">
              <i class="fas fa-building"></i> Our Office
            </a>
          </div>
        </li>
      </ul>
      
      <!-- Right Actions -->
      <div class="navbar-actions">
        <!-- Agent Button -->
        <a href="admin" class="post-property-btn">
          Agent Login
        </a>
      </div>
    </div>
  </nav>

  <!-- Mobile Header -->
  <header class="mobile-header">
    <!-- Top Bar -->
    <div class="mobile-header-top">
      <!-- Hamburger Menu Toggle -->
      <button class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
      </button>
      
      <!-- Logo -->
      <a href="index.php" class="mobile-logo">
        <img src="assets/images/logo.jpg" alt="Guntur Properties">
      </a>
    </div>
    
    <!-- Mobile Menu (Hidden by default) -->
    <div class="mobile-menu">
      <ul class="mobile-menu-list">
        <li><a href="index.php">Home</a></li>
        <li><a href="properties.php">Properties</a></li>
        <li><a href="agents.php">Agents</a></li>
        <li class="has-submenu">
          <a href="#" class="submenu-toggle">About Us <i class="fas fa-chevron-down"></i></a>
          <ul class="mobile-submenu">
            <li><a href="ourstory.php"><i class="fas fa-book-open"></i> Our Story</a></li>
            <li><a href="our-work.php"><i class="fas fa-briefcase"></i> Our Work</a></li>
            <li><a href="our-clients.php"><i class="fas fa-users"></i> Our Clients</a></li>
          </ul>
        </li>
        <li class="has-submenu">
          <a href="#" class="submenu-toggle">Services <i class="fas fa-chevron-down"></i></a>
          <ul class="mobile-submenu">
            <li><a href="strategic-marketing.php"><i class="fas fa-chart-line"></i> Strategic Marketing</a></li>
            <li><a href="negotiation.php"><i class="fas fa-handshake"></i> Negotiation</a></li>
            <li><a href="closing-success.php"><i class="fas fa-check-circle"></i> Closing Success</a></li>
          </ul>
        </li>
        <li class="has-submenu">
          <a href="#" class="submenu-toggle">Contact <i class="fas fa-chevron-down"></i></a>
          <ul class="mobile-submenu">
            <li><a href="contact-us.php"><i class="fas fa-envelope"></i> Contact Us</a></li>
            <li><a href="our-office.php"><i class="fas fa-building"></i> Our Office</a></li>
          </ul>
        </li>
        <li class="agent-login-mobile">
          <a href="admin" class="post-property-btn-mobile">Agent Login</a>
        </li>
      </ul>
    </div>
  </header>


  <!-- Scripts -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Mobile menu toggle
      const menuToggle = document.querySelector('.mobile-menu-toggle');
      const mobileMenu = document.querySelector('.mobile-menu');
      
      if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', function() {
          mobileMenu.classList.toggle('active');
          
          // Animate hamburger icon
          const icon = this.querySelector('i');
          if (mobileMenu.classList.contains('active')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
          } else {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
          }
        });
      }
      
      // Submenu toggles for mobile
      const submenuToggles = document.querySelectorAll('.submenu-toggle');
      
      submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
          e.preventDefault();
          this.parentElement.classList.toggle('open');
        });
      });
      
      // Desktop dropdown hover
      const dropdownTriggers = document.querySelectorAll('.dropdown-trigger');
      
      dropdownTriggers.forEach(trigger => {
        const parent = trigger.parentElement;
        const dropdown = parent.querySelector('.navbar-dropdown');
        
        parent.addEventListener('mouseenter', function() {
          dropdown.classList.add('active');
        });
        
        parent.addEventListener('mouseleave', function() {
          dropdown.classList.remove('active');
        });
        
        // For touch devices
        trigger.addEventListener('click', function(e) {
          if (window.innerWidth > 768) {
            e.preventDefault();
            
            // Close other dropdowns
            document.querySelectorAll('.navbar-dropdown.active').forEach(item => {
              if (item !== dropdown) {
                item.classList.remove('active');
              }
            });
            
            dropdown.classList.toggle('active');
          }
        });
      });

      // Close mobile menu when clicking outside
      document.addEventListener('click', function(event) {
        if (!event.target.closest('.mobile-header') && mobileMenu.classList.contains('active')) {
          mobileMenu.classList.remove('active');
          const icon = menuToggle.querySelector('i');
          icon.classList.remove('fa-times');
          icon.classList.add('fa-bars');
        }
      });

      // Close dropdowns when clicking outside
      document.addEventListener('click', function(event) {
        if (!event.target.closest('.navbar-item')) {
          document.querySelectorAll('.navbar-dropdown.active').forEach(dropdown => {
            dropdown.classList.remove('active');
          });
        }
      });
    });
  </script>
</body>
</html>