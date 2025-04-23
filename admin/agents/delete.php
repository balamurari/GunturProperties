<?php
/**
 * Delete Agent Script
 * Deletes an agent and redirects back to agents list
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Only admins can delete agents
requireAdmin();

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid agent ID.');
    redirect('index.php');
}

$agent_id = $_GET['id'];

// Get database connection
$db = new Database();

// Check if agent has properties assigned
$db->query("SELECT COUNT(*) as count FROM properties WHERE agent_id = :agent_id");
$db->bind(':agent_id', $agent_id);
$property_count = $db->single()['count'];

if ($property_count > 0) {
    setFlashMessage('error', 'Cannot delete agent with assigned properties. Please reassign properties first.');
    redirect('index.php');
}

// Get agent data to delete profile image
$db->query("SELECT profile_pic FROM users WHERE id = :id AND role = 'agent'");
$db->bind(':id', $agent_id);
$agent = $db->single();

// Delete agent
$db->query("DELETE FROM users WHERE id = :id AND role = 'agent'");
$db->bind(':id', $agent_id);

if ($db->execute()) {
    // Delete agent profile image if exists
    if (!empty($agent['profile_pic'])) {
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/guntur-properties/' . $agent['profile_pic'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    setFlashMessage('success', 'Agent deleted successfully!');
} else {
    setFlashMessage('error', 'Failed to delete agent.');
}

// Redirect back to agents list
redirect('index.php');
?>