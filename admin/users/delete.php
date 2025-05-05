<?php
/**
 * Delete User Script
 * Deletes an admin user and redirects back to users list
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Only admins can delete users
requireAdmin();

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid user ID.');
    redirect('index.php');
}

$user_id = $_GET['id'];

// Prevent admin from deleting their own account
if ($user_id == $_SESSION['user_id']) {
    setFlashMessage('error', 'You cannot delete your own account.');
    redirect('index.php');
}

// Get user data to delete profile image
$db = new Database();
$db->query("SELECT profile_pic FROM users WHERE id = :id AND role = 'admin'");
$db->bind(':id', $user_id);
$user = $db->single();

// Delete user
$db->query("DELETE FROM users WHERE id = :id AND role = 'admin'");
$db->bind(':id', $user_id);

if ($db->execute()) {
    // Delete user's profile image if exists
    if (!empty($user['profile_pic'])) {
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/' . $user['profile_pic'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    setFlashMessage('success', 'Admin user deleted successfully!');
} else {
    setFlashMessage('error', 'Failed to delete admin user.');
}

// Redirect back to users list
redirect('index.php');
?>