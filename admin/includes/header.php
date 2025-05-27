<?php
/**
 * Admin Header Include
 * This file contains the header for all admin pages
 */

// Require login for all admin pages
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Guntur Properties Admin</title>
    <!-- CSS files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.x.x/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="/path/to/your/local/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="../../assets/images/logo.jpg" type="image/x-icon">
    <link rel="apple-touch-icon" href="../../assets/images/logo.jpg">
</head>
<body>
    <!-- Mobile menu toggle button -->
    <button class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Sidebar overlay for mobile -->
    <div class="sidebar-overlay"></div>
    
    <div class="admin-layout">
        <!-- Sidebar included separately -->
        <?php include_once 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="dashboard-header">
                <h1><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                
                <div class="user-dropdown">
                    <div class="user-dropdown-toggle">
                        <img src="<?php echo isset($_SESSION['user_profile_pic']) && !empty($_SESSION['user_profile_pic']) ? '../' . $_SESSION['user_profile_pic'] : '../assets/images/default-profile.jpg'; ?>" alt="User">
                        <span><?php echo $_SESSION['user_name']; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="user-dropdown-menu">
                        <a href="../dashboard/profile.php"><i class="fas fa-user"></i> My Profile</a>
                        <?php if (isAdmin()): ?>
                        <a href="../dashboard/settings.php"><i class="fas fa-cog"></i> Settings</a>
                        <?php endif; ?>
                        <div class="divider"></div>
                        <a href="../dashboard/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
            
            <?php displayFlashMessage(); ?>