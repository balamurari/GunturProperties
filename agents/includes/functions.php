<?php
// Add this function to your main includes/functions.php file

/**
 * Requires user to be logged in and have the 'agent' role.
 * Redirects to login page or a 'permission denied' page if not.
 * Assumes user ID and role are stored in the session after login.
 * Also fetches and stores agent_id in session if not already present.
 */
function requireAgentLogin() {
    // Ensure session is started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in and has the 'agent' role
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'agent') {
        setFlashMessage('error', 'Access denied. Please log in as an agent.');
        // Redirect to login page (adjust path if needed)
        // Assuming login.php is in the root directory
        redirect(ROOT_URL . 'login.php');
        exit;
    }

    // Ensure agent_id is stored in session (fetch if missing)
    if (!isset($_SESSION['agent_id'])) {
        global $db; // Use the global Database instance if available
        if (!isset($db)) { // Or instantiate if not global
             try { $db = new Database(); } catch (Exception $e) { /* Handle error */ }
        }

        if (isset($db)) {
            try {
                $db->query("SELECT id FROM agents WHERE user_id = :user_id");
                $db->bind(':user_id', $_SESSION['user_id']);
                $agent = $db->single();
                if ($agent) {
                    $_SESSION['agent_id'] = $agent['id'];
                } else {
                    // User has agent role but no corresponding agent record? Log error and deny access.
                    error_log("User ID {$_SESSION['user_id']} has role 'agent' but no matching record in agents table.");
                    unset($_SESSION['user_id']); // Log them out for safety
                    unset($_SESSION['user_role']);
                    setFlashMessage('error', 'Agent profile not found. Please contact support.');
                    redirect(ROOT_URL . 'login.php');
                    exit;
                }
            } catch (Exception $e) {
                 error_log("Error fetching agent_id for user_id {$_SESSION['user_id']}: " . $e->getMessage());
                 setFlashMessage('error', 'An error occurred verifying your agent profile.');
                 redirect(ROOT_URL . 'login.php');
                 exit;
            }
        } else {
             // Cannot access database to verify agent_id
             setFlashMessage('error', 'Could not verify agent profile (DB unavailable).');
             redirect(ROOT_URL . 'login.php');
             exit;
        }
    }

    // If all checks pass, execution continues
}

?>