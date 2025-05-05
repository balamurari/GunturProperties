<?php
/**
 * Admin - Delete Agent
 * Handles the deletion of an agent record and associated data.
 * Expects agent ID via GET parameter 'id'.
 */

// Enable error reporting for debugging (remove or comment out in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Dependencies ---
require_once '../includes/config.php'; // Defines DB constants, BASE_URL etc.
require_once '../includes/database.php'; // Defines Database class
require_once '../includes/functions.php'; // Defines helper functions

// --- Session & Authentication ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Require admin privileges - redirects if user is not an admin
requireAdmin(); // Make sure this function exists and works in functions.php

// --- Main Logic ---
$db = new Database();
$agent_id_to_delete = null;
$redirect_page = 'index.php'; // Page to redirect back to

// 1. Get and Validate Agent ID from URL parameter
if (isset($_GET['id']) && is_numeric($_GET['id']) && (int)$_GET['id'] > 0) {
    $agent_id_to_delete = (int)$_GET['id'];
} else {
    setFlashMessage('error', 'Invalid or missing Agent ID provided.');
    redirect($redirect_page);
    exit;
}

// 2. Check if the Agent exists and get associated user_id
try {
    $db->query("SELECT id, user_id FROM agents WHERE id = :id");
    $db->bind(':id', $agent_id_to_delete);
    $agent_data = $db->single();

    if (!$agent_data) {
        setFlashMessage('error', 'Agent with ID ' . $agent_id_to_delete . ' not found.');
        redirect($redirect_page);
        exit;
    }
    $user_id_to_update = $agent_data['user_id']; // Store user_id for later update

} catch (Exception $e) {
     error_log("Error checking agent existence (ID: {$agent_id_to_delete}): " . $e->getMessage());
     setFlashMessage('error', 'An error occurred while verifying the agent.');
     redirect($redirect_page);
     exit;
}


// 3. Check if agent has properties assigned (Safety Check)
try {
    $db->query("SELECT COUNT(*) as count FROM properties WHERE agent_id = :agent_id");
    $db->bind(':agent_id', $agent_id_to_delete);
    $property_check_result = $db->single();
    $property_count = $property_check_result ? $property_check_result['count'] : 0;

    if ($property_count > 0) {
        setFlashMessage('error', 'Cannot delete agent (ID: ' . $agent_id_to_delete . ') because they have ' . $property_count . ' assigned properties. Please reassign properties first.');
        redirect($redirect_page);
        exit;
    }
} catch (Exception $e) {
     error_log("Error checking agent properties (ID: {$agent_id_to_delete}): " . $e->getMessage());
     setFlashMessage('error', 'An error occurred while checking agent properties.');
     redirect($redirect_page);
     exit;
}


// 4. Proceed with Deletion inside a Transaction
try {
    // Start the transaction
    if (!$db->beginTransaction()) {
         throw new Exception("Failed to start database transaction.");
    }

    // Define tables related to agents that need cleanup
    $related_tables = [
        'agent_reviews', 'agent_specialization_mapping', 'agent_certifications',
        'agent_awards', 'agent_gallery', 'enquiries' // Add any other relevant tables
    ];

    // Delete related records first
    foreach ($related_tables as $table) {
        $db->query("DELETE FROM {$table} WHERE agent_id = :agent_id");
        $db->bind(':agent_id', $agent_id_to_delete);
        if (!$db->execute()) {
             throw new Exception("Failed to delete related data from table: {$table}");
        }
    }

    // Delete the agent record itself
    $db->query("DELETE FROM agents WHERE id = :id");
    $db->bind(':id', $agent_id_to_delete);
    if (!$db->execute()) {
         throw new Exception("Failed to delete agent record.");
    }


    // Update the associated user's role to 'user' (or delete if appropriate)
    if ($user_id_to_update) {
         $db->query("UPDATE users SET role = 'user' WHERE id = :user_id AND role = 'agent'");
         $db->bind(':user_id', $user_id_to_update);
         if (!$db->execute()) {
              // Log this failure, but maybe don't stop the whole process? Or do? Depends on requirements.
              error_log("Failed to update user role for user ID {$user_id_to_update} after deleting agent ID {$agent_id_to_delete}");
              // Decide if this failure should cause a rollback
              // throw new Exception("Failed to update user role.");
         }
    }

    // If all queries succeeded, commit the transaction using YOUR class method name
    if (!$db->endTransaction()) { // *** USE endTransaction() ***
         throw new Exception("Failed to commit database transaction.");
    }

    // Set success message
    setFlashMessage('success', 'Agent (ID: ' . $agent_id_to_delete . ') and related data deleted successfully!');

} catch (Exception $e) {
    // An error occurred, rollback the transaction using YOUR class method name
    $db->cancelTransaction(); // *** USE cancelTransaction() ***

    // Log the specific error for debugging
    error_log("Agent deletion failed (ID: {$agent_id_to_delete}): " . $e->getMessage());

    // Set a user-friendly error message
    setFlashMessage('error', 'Failed to delete agent due to a server error. The operation was rolled back.');
}

// 5. Redirect back to the agents list page
redirect($redirect_page);
exit;

?>