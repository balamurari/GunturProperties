<?php
/**
 * Agent Dashboard Header Include
 */

// Ensure functions are available and require agent login
require_once __DIR__ . '/../../includes/functions.php'; // Path relative to agent/includes/header.php
requireAgentLogin(); // This function checks session and agent role

// Ensure config is loaded for ROOT_URL etc.
require_once __DIR__ . '/../../includes/config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Agent Dashboard</title>

    <?php if (defined('ROOT_URL')): ?>
        <base href="<?php echo ROOT_URL; ?>">
    <?php endif; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo ROOT_URL; ?>assets/css/admin-style.css"> <link rel="stylesheet" href="<?php echo ROOT_URL; ?>assets/css/responsive.css">

</head>
<body>
    <button class="mobile-menu-toggle d-lg-none">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar-overlay d-lg-none"></div>

    <div class="admin-layout"> <?php include_once __DIR__ . '/sidebar.php'; ?>

        <div class="main-content">
            <div class="dashboard-header">
                 <h1 class="h4 mb-0 text-truncate"><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Agent Dashboard'; ?></h1>

                 <div class="user-dropdown ms-auto">
                    <div class="user-dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                        <?php
                           $profilePicRelative = $_SESSION['user_profile_pic'] ?? null;
                           $profilePicUrl = getAssetPath($profilePicRelative ?? 'images/agent-placeholder.jpg'); // Use function
                        ?>
                        <img src="<?php echo htmlspecialchars($profilePicUrl); ?>" alt="User" class="rounded-circle me-2" width="32" height="32" onerror="this.onerror=null; this.src='<?php echo getAssetPath('images/agent-placeholder.jpg'); ?>';">
                        <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Agent'); ?></span>
                        <i class="fas fa-chevron-down fa-xs ms-1"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                         <li><a class="dropdown-item" href="<?php echo ROOT_URL; ?>agent/profile.php"><i class="fas fa-user-edit fa-fw me-2"></i>Edit Profile</a></li>
                         <li><a class="dropdown-item" href="<?php echo ROOT_URL; ?>agent-details.php?id=<?php echo $_SESSION['agent_id'] ?? ''; ?>" target="_blank"><i class="fas fa-eye fa-fw me-2"></i>View Public Profile</a></li>
                         <li><hr class="dropdown-divider"></li>
                         <li><a class="dropdown-item" href="<?php echo ROOT_URL; ?>logout.php"><i class="fas fa-sign-out-alt fa-fw me-2"></i>Logout</a></li>
                     </ul>
                </div>
            </div>

            <div class="container-fluid mt-3"> <?php displayFlashMessage(); ?>
            </div>

            <div class="container-fluid mt-3"> ```

**2. Agent Sidebar (`agent/includes/sidebar.php`)**

This defines the navigation links specific to the agent.

```php
<?php
/**
 * Agent Dashboard Sidebar Include
 */

// Determine current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF'])); // Should be 'agent'

// Ensure config is loaded for ROOT_URL
if (!defined('ROOT_URL')) {
    require_once __DIR__ . '/../../includes/config.php';
}
?>
<div class="sidebar"> <div class="sidebar-header">
        <div class="sidebar-logo">
            <a href="<?php echo ROOT_URL; ?>agent/dashboard.php" class="text-decoration-none">
                 <h3 class="text-white">Agent Panel</h3> </a>
        </div>
        <button class="sidebar-close d-lg-none">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="sidebar-menu">
        <ul>
            <li <?php echo $current_page == 'dashboard.php' ? 'class="active"' : ''; ?>>
                <a href="<?php echo ROOT_URL; ?>agent/dashboard.php">
                    <i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard
                </a>
            </li>
            <li <?php echo $current_page == 'properties.php' || $current_page == 'property-add.php' || $current_page == 'property-edit.php' ? 'class="active"' : ''; ?>>
                <a href="<?php echo ROOT_URL; ?>agent/properties.php">
                    <i class="fas fa-home fa-fw me-2"></i> My Properties
                </a>
            </li>
             <li <?php echo $current_page == 'property-add.php' ? 'class="active"' : ''; ?>>
                <a href="<?php echo ROOT_URL; ?>agent/property-add.php">
                    <i class="fas fa-plus-circle fa-fw me-2"></i> Add Property
                </a>
            </li>
            <li <?php echo $current_page == 'profile.php' ? 'class="active"' : ''; ?>>
                <a href="<?php echo ROOT_URL; ?>agent/profile.php">
                    <i class="fas fa-user-edit fa-fw me-2"></i> Edit Profile
                </a>
            </li>
             <li>
                <a href="<?php echo ROOT_URL; ?>agent-details.php?id=<?php echo $_SESSION['agent_id'] ?? ''; ?>" target="_blank">
                    <i class="fas fa-eye fa-fw me-2"></i> View Public Profile
                </a>
            </li>
            <li>
                <a href="<?php echo ROOT_URL; ?>logout.php">
                    <i class="fas fa-sign-out-alt fa-fw me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>