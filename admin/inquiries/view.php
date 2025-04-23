<?php
/**
 * View Inquiry Page
 * Displays inquiry details and allows status updates
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Set page title
$page_title = 'View Inquiry';

// Get database connection
$db = new Database();

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid inquiry ID.');
    redirect('index.php');
}

$inquiry_id = $_GET['id'];

// Get inquiry details
$db->query("SELECT e.*, p.title AS property_title, p.id AS property_id, 
           u.name AS agent_name, u.email AS agent_email, u.phone AS agent_phone, u.id AS agent_id 
           FROM enquiries e
           LEFT JOIN properties p ON e.property_id = p.id
           LEFT JOIN users u ON e.agent_id = u.id
           WHERE e.id = :id");
$db->bind(':id', $inquiry_id);
$inquiry = $db->single();

if (!$inquiry) {
    setFlashMessage('error', 'Inquiry not found.');
    redirect('index.php');
}

// Get agents for assignment
$agents = getAgents();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = sanitize($_POST['status']);
    $agent_id = !empty($_POST['agent_id']) ? (int)$_POST['agent_id'] : null;
    
    $db->query("UPDATE enquiries SET status = :status, agent_id = :agent_id, updated_at = NOW() WHERE id = :id");
    $db->bind(':status', $status);
    $db->bind(':agent_id', $agent_id);
    $db->bind(':id', $inquiry_id);
    
    if ($db->execute()) {
        setFlashMessage('success', 'Inquiry updated successfully!');
        redirect("view.php?id=$inquiry_id");
    } else {
        setFlashMessage('error', 'Failed to update inquiry.');
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-header-actions">
            <h2 class="mb-0">Inquiry #<?php echo $inquiry['id']; ?></h2>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Inquiries
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="inquiry-detail-grid">
            <div class="inquiry-info">
                <h3>Inquiry Information</h3>
                
                <div class="info-group">
                    <span class="info-label">Status</span>
                    <span class="status-badge status-<?php echo $inquiry['status']; ?>">
                        <?php 
                            echo $inquiry['status'] == 'new' ? 'New' : 
                                ($inquiry['status'] == 'in_progress' ? 'In Progress' : 'Closed'); 
                        ?>
                    </span>
                </div>
                
                <div class="info-group">
                    <span class="info-label">Date</span>
                    <span><?php echo formatDate($inquiry['created_at'], 'd M, Y h:i A'); ?></span>
                </div>
                
                <div class="info-group">
                    <span class="info-label">Subject</span>
                    <span><?php echo htmlspecialchars($inquiry['subject']); ?></span>
                </div>
                
                <div class="info-group">
                    <span class="info-label">Property</span>
                    <span>
                        <?php if ($inquiry['property_id']): ?>
                            <a href="../properties/edit.php?id=<?php echo $inquiry['property_id']; ?>">
                                <?php echo htmlspecialchars($inquiry['property_title']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">General Inquiry</span>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="info-group">
                    <span class="info-label">Assigned Agent</span>
                    <span>
                        <?php if ($inquiry['agent_id']): ?>
                            <?php echo htmlspecialchars($inquiry['agent_name']); ?>
                            <small>(<?php echo htmlspecialchars($inquiry['agent_email']); ?>)</small>
                        <?php else: ?>
                            <span class="text-muted">Unassigned</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            
            <div class="customer-info">
                <h3>Customer Information</h3>
                
                <div class="info-group">
                    <span class="info-label">Name</span>
                    <span><?php echo htmlspecialchars($inquiry['name']); ?></span>
                </div>
                
                <div class="info-group">
                    <span class="info-label">Email</span>
                    <span>
                        <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>">
                            <?php echo htmlspecialchars($inquiry['email']); ?>
                        </a>
                    </span>
                </div>
                
                <div class="info-group">
                    <span class="info-label">Phone</span>
                    <span>
                        <a href="tel:<?php echo htmlspecialchars($inquiry['phone']); ?>">
                            <?php echo htmlspecialchars($inquiry['phone']); ?>
                        </a>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="inquiry-message">
            <h3>Message</h3>
            <div class="message-content">
                <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?>
            </div>
        </div>
        
        <div class="inquiry-actions">
            <h3>Update Inquiry</h3>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $inquiry_id); ?>" class="update-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="new" <?php echo $inquiry['status'] == 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="in_progress" <?php echo $inquiry['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="closed" <?php echo $inquiry['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="agent_id">Assign to Agent</label>
                        <select id="agent_id" name="agent_id">
                            <option value="">Unassigned</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?php echo $agent['id']; ?>" <?php echo $inquiry['agent_id'] == $agent['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($agent['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Inquiry</button>
            </form>
        </div>
    </div>
</div>

<style>
.inquiry-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.info-group {
    margin-bottom: 15px;
}

.info-label {
    font-weight: 600;
    display: block;
    margin-bottom: 5px;
    color: var(--text-light);
}

.message-content {
    background-color: var(--bg-light);
    padding: 15px;
    border-radius: var(--border-radius);
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .inquiry-detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
// Include footer
include_once '../includes/footer.php';
?>