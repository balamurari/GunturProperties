<?php
/**
 * Admin Sidebar Include
 * This file contains the sidebar navigation for all admin pages
 */

// Determine current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <h3>Guntur Properties</h3>
        </div>
        <!-- Mobile sidebar close button -->
        <button class="sidebar-close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="sidebar-menu">
        <ul>
            <li>
                <a href="../dashboard/dashboard.php" <?php echo $current_page == 'dashboard.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="../properties/index.php" <?php echo $current_dir == 'properties' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-home"></i> Properties
                </a>
            </li>
            <li>
                <a href="../agents/index.php" <?php echo $current_dir == 'agents' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-users"></i> Agents
                </a>
            </li>
            <!-- <li>
                <a href="../enquiries/index.php" <?php echo $current_dir == 'enquiries' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-envelope"></i> Enquiries
                </a>
            </li> -->
            <?php if (isAdmin()): ?>
            <li>
                <a href="../users/index.php" <?php echo $current_dir == 'users' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-user-cog"></i> User Management
                </a>
            </li>
            <!-- <li>
                <a href="../dashboard/settings.php" <?php echo $current_page == 'settings.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li> -->
            <?php endif; ?>
            <li>
                <a href="../dashboard/profile.php" <?php echo $current_page == 'profile.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-user"></i> My Profile
                </a>
            </li>
            <li>
                <a href="../dashboard/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>