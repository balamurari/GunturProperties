<?php
/**
 * Delete Property Script
 * Deletes a property and redirects back to properties list
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid property ID.');
    redirect('index.php');
}

$property_id = $_GET['id'];

// Delete property
if (deleteProperty($property_id)) {
    setFlashMessage('success', 'Property deleted successfully!');
} else {
    setFlashMessage('error', 'Failed to delete property.');
}

// Redirect back to properties list
redirect('index.php');
?>