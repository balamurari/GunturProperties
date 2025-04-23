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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Guntur Properties Admin</title>
    <link rel="stylesheet" href="assets/css/admin-style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar included separately -->
        <?php include_once 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="dashboard-header">
                <h1><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                
                <div class="user-dropdown">
                    <div class="user-dropdown-toggle">
                        <img src="<?php echo isset($_SESSION['user_profile_pic']) && !empty($_SESSION['user_profile_pic']) ? '../' . $_SESSION['user_profile_pic'] : 'assets/images/default-profile.jpg'; ?>" alt="User">
                        <span><?php echo $_SESSION['user_name']; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="user-dropdown-menu">
                        <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                        <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                        <div class="divider"></div>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
            
            <?php displayFlashMessage(); ?>