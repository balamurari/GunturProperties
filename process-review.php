<?php
/**
 * Process Review Form
 * Handles review form submissions for agents
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

// Process agent review form
if ($form_type === 'agent_review') {
    // Get form data
    $agent_id = isset($_POST['agent_id']) ? (int)$_POST['agent_id'] : 0;
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $title = isset($_POST['title']) ? sanitize($_POST['title']) : '';
    $review = isset($_POST['review']) ? sanitize($_POST['review']) : '';
    $terms = isset($_POST['terms']) ? true : false;
    
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
    
    if (empty($rating) || $rating < 1 || $rating > 5) {
        $errors[] = 'Valid rating is required';
    }
    
    if (empty($title)) {
        $errors[] = 'Review title is required';
    }
    
    if (empty($review)) {
        $errors[] = 'Review content is required';
    }
    
    if (!$terms) {
        $errors[] = 'You must agree to the terms and conditions';
    }
    
    // If no errors, process form
    if (empty($errors)) {
        // Connect to database
        $db = new Database();
        
        try {
            // Insert into agent_reviews table
            $db->query("INSERT INTO agent_reviews (agent_id, name, email, rating, title, review, status, created_at)
                       VALUES (:agent_id, :name, :email, :rating, :title, :review, 'pending', NOW())");
            
            $db->bind(':agent_id', $agent_id);
            $db->bind(':name', $name);
            $db->bind(':email', $email);
            $db->bind(':rating', $rating);
            $db->bind(':title', $title);
            $db->bind(':review', $review);
            
            $db->execute();
            
            // Redirect with success message
            setFlashMessage('success', 'Your review has been submitted and is pending approval. Thank you for your feedback!');
            header('Location: agent-details.php?id=' . $agent_id);
            exit;
        } catch (Exception $e) {
            // Log error and redirect with error message
            error_log('Error submitting review: ' . $e->getMessage());
            setFlashMessage('error', 'There was an error submitting your review. Please try again later.');
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

// Redirect if invalid form type
else {
    header('Location: index.php');
    exit;
}
