<?php
/**
 * Admin Sidebar Include
 * This file contains the sidebar navigation for all admin pages
 */
?>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <h3>Guntur Properties</h3>
        </div>
        <button class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <div class="sidebar-menu">
        <ul>
            <li>
                <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="properties/index.php" <?php echo strpos($_SERVER['PHP_SELF'], 'properties/') !== false ? 'class="active"' : ''; ?>>
                    <i class="fas fa-home"></i> Properties
                </a>
            </li>
            <li>
                <a href="agents/index.php" <?php echo strpos($_SERVER['PHP_SELF'], 'agents/') !== false ? 'class="active"' : ''; ?>>
                    <i class="fas fa-users"></i> Agents
                </a>
            </li>
            <li>
                <a href="inquiries/index.php" <?php echo strpos($_SERVER['PHP_SELF'], 'inquiries/') !== false ? 'class="active"' : ''; ?>>
                    <i class="fas fa-envelope"></i> Inquiries
                </a>
            </li>
            <?php if (isAdmin()): ?>
            <li>
                <a href="users/index.php" <?php echo strpos($_SERVER['PHP_SELF'], 'users/') !== false ? 'class="active"' : ''; ?>>
                    <i class="fas fa-user-cog"></i> User Management
                </a>
            </li>
            <li>
                <a href="settings.php" <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="profile.php" <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-user"></i> My Profile
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>