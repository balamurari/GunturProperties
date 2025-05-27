<?php
/**
 * Admin - Agents Management
 * Lists all agents with filtering, search, and management capabilities
 */

// --- Dependencies ---
require_once '../includes/config.php'; // Define DB constants, BASE_URL, etc.
require_once '../includes/database.php'; // Contains Database class
require_once '../includes/functions.php'; // Contains helper functions

// --- Session & Authentication ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}



// --- Page Setup ---
$page_title = 'Manage Agents';
$db = new Database(); // Establish database connection

// --- Action Handling (Status Toggle ONLY) ---
// DELETE logic is now handled by delete.php

// Handle Status Toggle (Remains in index.php)
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $agent_id_to_toggle = (int)$_GET['toggle_status'];

    // Get associated user ID and current status
    $db->query("SELECT u.id as user_id, u.status FROM agents a JOIN users u ON a.user_id = u.id WHERE a.id = :agent_id");
    $db->bind(':agent_id', $agent_id_to_toggle);
    $user_info = $db->single();

    if ($user_info) {
        $new_status = $user_info['status'] ? 0 : 1; // Toggle 0 <=> 1
        $user_id_to_toggle = $user_info['user_id'];

        try {
            // Use transaction for consistency, though less critical than delete
            $db->beginTransaction();
            $db->query("UPDATE users SET status = :status WHERE id = :user_id");
            $db->bind(':status', $new_status);
            $db->bind(':user_id', $user_id_to_toggle);
            $success = $db->execute();
            $db->endTransaction(); // Use endTransaction (commit)

            if ($success) {
                setFlashMessage('success', 'Agent status updated successfully!');
            } else {
                setFlashMessage('error', 'Failed to update agent status.');
            }
        } catch (Exception $e) {
             $db->cancelTransaction(); // Use cancelTransaction (rollback)
             error_log("Agent status toggle failed (User ID: {$user_id_to_toggle}): " . $e->getMessage());
             setFlashMessage('error', 'An error occurred while updating agent status.');
        }
    } else {
        setFlashMessage('error', 'Agent not found or user association missing.');
    }
    redirect('index.php'); // Redirect to self to clear GET params
    exit;
}


// --- Data Fetching (Filters, Pagination, Query) ---

// Get filter/search parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) && in_array($_GET['status'], ['1', '0']) ? $_GET['status'] : '';

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10; // Records per page
$offset = ($page - 1) * $limit;

// Build the SQL query parts
$base_select = "SELECT
                a.id AS agent_id, a.position, a.experience, a.properties_sold, a.featured, a.rating,
                u.id AS user_id, u.name, u.email, u.phone, u.profile_pic, u.status, u.created_at,
                COALESCE(pc.property_count, 0) AS listed_property_count";

$base_from = " FROM agents a
           LEFT JOIN users u ON a.user_id = u.id
           LEFT JOIN (
               SELECT agent_id, COUNT(*) as property_count
               FROM properties
               WHERE agent_id IS NOT NULL
               GROUP BY agent_id
           ) pc ON a.id = pc.agent_id";

$where_conditions = " WHERE 1=1"; // Base WHERE clause

// Apply search filter
if (!empty($search)) {
    $where_conditions .= " AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search OR a.position LIKE :search)";
}

// Apply status filter (using users.status)
if ($status_filter !== '') {
    $where_conditions .= " AND u.status = :status";
}

// Count total matching agents for pagination
$count_sql = "SELECT COUNT(a.id) as total " . $base_from . $where_conditions;
$db->query($count_sql);

// Bind parameters for count query
if (!empty($search)) {
    $db->bind(':search', '%' . $search . '%');
}
if ($status_filter !== '') {
    $db->bind(':status', $status_filter);
}

// Get total count for pagination
$total_count_result = $db->single();
$total_count = $total_count_result ? $total_count_result['total'] : 0;
$total_pages = $limit > 0 ? ceil($total_count / $limit) : 1;

// Build the final query to fetch agents for the current page
$fetch_sql = $base_select . $base_from . $where_conditions;
$fetch_sql .= " ORDER BY a.id ASC"; // Or u.name ASC, etc.
$fetch_sql .= " LIMIT :limit OFFSET :offset";

$db->query($fetch_sql);

// Bind parameters for fetch query
if (!empty($search)) {
    $db->bind(':search', '%' . $search . '%');
}
if ($status_filter !== '') {
    $db->bind(':status', $status_filter);
}

// Bind pagination parameters
$db->bind(':limit', $limit, PDO::PARAM_INT);
$db->bind(':offset', $offset, PDO::PARAM_INT);

// Fetch the results
$agents = $db->resultSet();
// --- Include Header ---
// This file should contain HTML head, Bootstrap CSS links, etc.
include_once '../includes/header.php';
?>

<div class="admin-container">
    <?php // include_once '../includes/sidebar.php'; ?>

    <div class="admin-content">
        <div class="container-fluid">
            <div class="admin-content-header d-flex justify-content-between align-items-center mb-4">
                <!-- <h1><?php echo htmlspecialchars($page_title); ?></h1> -->
                <a href="add.php" class="btn btn-success"> <i class="fas fa-user-plus"></i> Add New Agent
                </a>
            </div>

            <?php displayFlashMessage(); // Call the function from functions.php ?>

            <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Agents</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="index.php" class="filter-form">
                        <div class="row align-items-end">
                            <!-- <div class="col-lg-5 mb-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search"
                                       value="<?php echo htmlspecialchars($search); ?>"
                                       placeholder="Name, email, phone, position...">
                            </div> -->
                            <div class="col-lg-4 mb-3">
                                <label for="status" class="form-label">Account Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-lg-3 mb-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Filter / Search
                                </button>
                            </div>
                        </div>
                        <?php if (!empty($search) || $status_filter !== ''): ?>
                        <div class="mt-2">
                            <a href="index.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times-circle me-1"></i> Reset Filters
                            </a>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Agents List</h5>
                    <span class="badge bg-primary rounded-pill"><?php echo $total_count; ?> Total Agents</span>
                </div>
                <div class="card-body p-0"> <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Position</th>
                                    <th scope="col">Contact</th>
                                    <th scope="col" class="text-center">Properties</th>
                                    <th scope="col" class="text-center">Exp. (Yrs)</th>
                                    <th scope="col" class="text-center">Rating</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col" style="width: 160px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($agents)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            No agents found<?php echo (!empty($search) || $status_filter !== '') ? ' matching your criteria' : ''; ?>.
                                            <a href="add.php" class="ms-2">Add one?</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $i=1;foreach ($agents as $agent): ?>
                                        <tr>
                                            <td><?php echo $i;$i++; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                <div class="me-2 flex-shrink-0">
                                                    <?php
                                                    if (!empty($agent['profile_pic'])) {
                                                        // Extract filename from path
                                                        $filename = basename($agent['profile_pic']);
                                                        // Create proper URL using constant
                                                        $profile_pic_url = AGENT_IMAGES_URL . $filename;
                                                    } else {
                                                        $profile_pic_url = DEFAULT_IMAGE_URL;
                                                    }
                                                    ?>
                                                    <img src="<?php echo $profile_pic_url; ?>"
                                                        alt="<?php echo htmlspecialchars($agent['name'] ?? 'Agent'); ?>"
                                                        class="rounded-circle" width="40" height="40" style="object-fit: cover;"
                                                        onerror="this.onerror=null; this.src='<?php echo DEFAULT_IMAGE_URL; ?>';">
                                                </div>
                                                    <div>
                                                        <?php echo htmlspecialchars($agent['name'] ?? 'N/A'); ?>
                                                        <?php if (!empty($agent['featured'])): ?>
                                                            <span class="badge bg-warning text-dark ms-1" title="Featured Agent"><i class="fas fa-star fa-xs"></i></span>
                                                        <?php endif; ?>
                                                        <!-- <small class="d-block text-muted">User ID: <?php echo $agent['user_id'] ?? 'N/A'; ?></small> -->
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($agent['position'] ?? 'N/A'); ?></td>
                                            <td>
                                                <small class="d-block">
                                                    <?php if (!empty($agent['email'])): ?>
                                                        <i class="fas fa-envelope fa-fw text-muted me-1" title="Email"></i><?php echo htmlspecialchars($agent['email']); ?>
                                                    <?php endif; ?>
                                                </small>
                                                 <small class="d-block">
                                                    <?php if (!empty($agent['phone'])): ?>
                                                        <i class="fas fa-phone fa-fw text-muted me-1" title="Phone"></i><?php echo htmlspecialchars($agent['phone']); ?>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info me-1" title="Properties currently listed"><?php echo $agent['listed_property_count'] ?? 0; ?> Listed</span>
                                                <span class="badge bg-success" title="Total properties sold"><?php echo $agent['properties_sold'] ?? 0; ?> Sold</span>
                                            </td>
                                            <td class="text-center"><?php echo $agent['experience'] ?? 0; ?></td>
                                            <td class="text-center">
                                                <span title="<?php echo number_format($agent['rating'] ?? 0.0, 2); ?> average rating">
                                                <?php echo number_format($agent['rating'] ?? 0.0, 1); ?> <i class="fas fa-star text-warning"></i>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                $status = isset($agent['status']) ? (int)$agent['status'] : 0;
                                                $status_class = $status ? 'success' : 'danger';
                                                $status_text = $status ? 'Active' : 'Inactive';
                                                ?>
                                                <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                            <td class="text-center action-buttons">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="agent-details.php?id=<?php echo $agent['agent_id']; ?>" class="btn btn-outline-secondary" title="View Public Profile" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $agent['agent_id']; ?>" class="btn btn-outline-primary" title="Edit Agent Details">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="index.php?toggle_status=<?php echo $agent['agent_id']; ?>" class="btn btn-outline-<?php echo $status ? 'warning' : 'success'; ?>" title="<?php echo $status ? 'Deactivate Account' : 'Activate Account'; ?>">
                                                        <i class="fas fa-<?php echo $status ? 'user-slash' : 'user-check'; ?>"></i>
                                                    </a>
                                                    <?php $listed_count = $agent['listed_property_count'] ?? 0; ?>
                                                    <?php if ($listed_count == 0): ?>
                                                        <a href="delete.php?id=<?php echo $agent['agent_id']; ?>" class="btn btn-outline-danger delete-confirm" title="Delete Agent"
                                                           data-confirm="Are you sure you want to permanently delete agent '<?php echo htmlspecialchars($agent['name'] ?? 'this agent'); ?>'? Related data will also be removed. This action cannot be undone.">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-outline-secondary" title="Cannot delete - Agent has <?php echo $listed_count; ?> listed properties" disabled>
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div> </div> <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-light">
                    <nav aria-label="Agent pagination">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>
                            </li>
                            <?php
                            // Pagination Links Logic (Example: Show 2 links around current page)
                            $range = 2;
                            $start_loop = max(1, $page - $range);
                            $end_loop = min($total_pages, $page + $range);

                            // Ellipsis and First Page Link
                            if ($start_loop > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1&search='.urlencode($search).'&status='.$status_filter.'">1</a></li>';
                                if ($start_loop > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }

                            // Page Number Links
                            for ($i = $start_loop; $i <= $end_loop; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>" aria-current="<?php echo ($page == $i) ? 'page' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor;

                             // Ellipsis and Last Page Link
                             if ($end_loop < $total_pages) {
                                 if ($end_loop < $total_pages - 1) {
                                     echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                 }
                                 echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'&search='.urlencode($search).'&status='.$status_filter.'">'.$total_pages.'</a></li>';
                             }
                            ?>
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
                            </li>
                        </ul>
                    </nav>
                </div> <?php endif; ?>

            </div> </div> </div> </div> <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmMessage">Are you sure you want to permanently delete this agent? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" class="btn btn-danger" id="confirmDeleteBtn">Delete Agent</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteModalElement = document.getElementById('deleteConfirmModal');
    if (deleteModalElement) {
        try {
            // Ensure bootstrap object exists before trying to use it
            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
                const deleteModal = new bootstrap.Modal(deleteModalElement);
                const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
                const deleteConfirmMessage = document.getElementById('deleteConfirmMessage');

                document.querySelectorAll('.delete-confirm').forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault(); // Stop link navigation
                        const href = this.getAttribute('href');
                        const message = this.getAttribute('data-confirm') || 'Are you sure you want to delete this item? This action cannot be undone.';

                        // Update modal content before showing
                        deleteConfirmMessage.textContent = message;
                        confirmDeleteBtn.setAttribute('href', href);

                        deleteModal.show(); // Show the modal
                    });
                });
             } else {
                 console.error('Bootstrap Modal component not found. Ensure Bootstrap JS is loaded.');
             }
        } catch (error) {
             console.error('Error initializing delete confirmation modal:', error);
        }
    } else {
        console.error("Delete confirmation modal element '#deleteConfirmModal' not found in the DOM.");
    }
});
</script>

<?php
// --- Include Footer ---
// This file should contain closing body/html tags, Bootstrap JS bundle, etc.
include_once '../includes/footer.php';
?>