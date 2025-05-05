<?php
/**
 * Process Contact Form
 * Handles contact form submissions from agent pages and property pages
 */
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Get form type
$form_type = isset($_POST['form_type']) ? $_POST['form_type'] : '';

// Process agent contact form
if ($form_type === 'agent_contact') {
    // Get form data
    $agent_id = isset($_POST['agent_id']) ? (int)$_POST['agent_id'] : 0;
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $message = isset($_POST['message']) ? sanitize($_POST['message']) : '';
    
    // Validate required fields
    $errors = [];
    
    if (empty($agent_id)) {
        $errors[] = 'Agent ID is required';
    }
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    // If no errors, process form
    if (empty($errors)) {
        // Connect to database
        $db = new Database();
        
        try {
            // Insert into enquiries table
            $db->query("INSERT INTO enquiries (name, email, phone, subject, message, agent_id, status, created_at)
                       VALUES (:name, :email, :phone, :subject, :message, :agent_id, 'new', NOW())");
            
            $db->bind(':name', $name);
            $db->bind(':email', $email);
            $db->bind(':phone', $phone);
            $db->bind(':subject', 'Agent Contact Form');
            $db->bind(':message', $message);
            $db->bind(':agent_id', $agent_id);
            
            $db->execute();
            
            // Redirect with success message
            setFlashMessage('success', 'Your message has been sent. The agent will contact you soon.');
            header('Location: agent-details.php?id=' . $agent_id);
            exit;
        } catch (Exception $e) {
            // Log error and redirect with error message
            error_log('Error sending message: ' . $e->getMessage());
            setFlashMessage('error', 'There was an error sending your message. Please try again later.');
            header('Location: agent-details.php?id=' . $agent_id);
            exit;
        }
    } else {
        // Redirect with error messages
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header('Location: agent-details.php?id=' . $agent_id);
        exit;
    }
}

// Process property contact form
elseif ($form_type === 'property_contact') {
    // Similar logic for property contact forms
    $property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;
    // ...process property contact form
}

// Redirect if invalid form type
else {
    header('Location: index.php');
    exit;
}
