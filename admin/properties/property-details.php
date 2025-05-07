<?php
/**
 * Property Details Page
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Set page title
$page_title = 'Property Details';

// Check if property ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid property ID.');
    redirect('properties.php');
}

$property_id = (int)$_GET['id'];

// Get property details
$db = new Database();
$db->query("SELECT p.*, pt.name AS type_name 
            FROM properties p
            LEFT JOIN property_types pt ON p.type_id = pt.id
            WHERE p.id = :id");
$db->bind(':id', $property_id);
$property = $db->single();

// Check if property exists
if (!$property) {
    setFlashMessage('error', 'Property not found.');
    redirect('properties.php');
}

// Get property images
$db->query("SELECT * FROM property_images WHERE property_id = :id ORDER BY is_primary DESC, sort_order ASC");
$db->bind(':id', $property_id);
$property_images = $db->resultSet();

// Get primary image
$primary_image = null;
foreach ($property_images as $image) {
    if ($image['is_primary']) {
        $primary_image = $image;
        break;
    }
}

// If no primary image, use the first image
if (!$primary_image && !empty($property_images)) {
    $primary_image = $property_images[0];
}

// Get property features
$db->query("SELECT pf.name, pf.icon, pfm.value 
            FROM property_feature_mapping pfm
            JOIN property_features pf ON pfm.feature_id = pf.id
            WHERE pfm.property_id = :id");
$db->bind(':id', $property_id);
$property_features = $db->resultSet();

// Get agent details if assigned
$agent = null;
if ($property['agent_id']) {
    $db->query("SELECT a.*, u.name, u.email, u.phone, u.profile_pic 
                FROM agents a
                JOIN users u ON a.user_id = u.id
                WHERE a.id = :id");
    $db->bind(':id', $property['agent_id']);
    $agent = $db->single();

    // Get agent specializations
    if ($agent) {
        $db->query("SELECT s.name 
                    FROM agent_specialization_mapping m
                    JOIN agent_specializations s ON m.specialization_id = s.id
                    WHERE m.agent_id = :id");
        $db->bind(':id', $agent['id']);
        $specializations = $db->resultSet();
        
        $agent['specializations'] = array_column($specializations, 'name');
    }
}

// Include header
include_once '../includes/header.php';

// Fix image path by adding the correct base URL
$base_url = '/gunturProperties/';
?>

<div class="container">
    <!-- Breadcrumb navigation -->
    <div class="breadcrumb">
        <a href="../index.php">Dashboard</a>&gt;<a href="properties.php">Properties</a>&gt;<?php echo htmlspecialchars($property['title']); ?>
    </div>

    <!-- Property Details Header -->
    <h1><?php echo htmlspecialchars($property['title']); ?></h1>
    <p>ID: <?php echo $property['id']; ?> <?php echo ucfirst($property['status']); ?></p>
    
    <div class="action-buttons">
        <a href="edit.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Property
        </a>
        <a href="delete.php" class="btn btn-danger" onclick="confirmDelete(<?php echo $property['id']; ?>, '<?php echo htmlspecialchars($property['title']); ?>')">
            <i class="fas fa-trash"></i> Delete Property
        </a>
        <a href="properties.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <!-- Property Content -->
    <div class="row mt-4">
        <!-- Left Column: Images -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2>Property Images</h2>
                </div>
                <div class="card-body">
                            <?php if (!empty($property_images)): ?>
                                <?php if ($primary_image): ?>
                                    <img src="<?php echo $base_url . $primary_image['image_path']; ?>" alt="Primary Property Image" class="img-fluid mb-3 w-100">
                                <?php endif; ?>

                                <div class="secondary-images-container d-flex flex-wrap"> 
                                    <?php foreach($property_images as $index => $image): ?>
                                        <?php if ($primary_image && $image['id'] != $primary_image['id']): ?>
                                            <div class="secondary-image-item p-1"> 
                                                <img src="<?php echo $base_url . $image['image_path']; ?>" 
                                                     alt="Property Image <?php echo $index + 1; ?>" 
                                                     class="img-thumbnail"
                                                     style="width: 150px; height: 125px; object-fit: fill; margin-bottom: 0.5rem;"> <?php /* Fixed width, height & object-fit */ ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>

                            <?php else: ?>
                                <div class="no-images">
                                    <p>No images available for this property.</p>
                                </div>
                            <?php endif; ?>
                        </div>
            </div>
        </div>
        
        <!-- Right Column: Details -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2>Property Details</h2>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <tr>
                            <th>Price</th>
                            <td><?php echo formatPrice($property['price']); ?></td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td><?php echo htmlspecialchars($property['type_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><?php echo ucfirst($property['status']); ?></td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td><?php echo htmlspecialchars($property['address']); ?></td>
                        </tr>
                        <tr>
                            <th>City</th>
                            <td><?php echo htmlspecialchars($property['city']); ?></td>
                        </tr>
                        <tr>
                            <th>State</th>
                            <td><?php echo htmlspecialchars($property['state']); ?></td>
                        </tr>
                        <tr>
                            <th>Bedrooms</th>
                            <td><?php echo $property['bedrooms'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Bathrooms</th>
                            <td><?php echo $property['bathrooms'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Area</th>
                            <td><?php echo $property['area'] ? number_format($property['area'], 2) . ' ' . $property['area_unit'] : 'N/A'; ?></td>
                        </tr>
                        <?php if ($property['featured']): ?>
                        <tr>
                            <th>Featured</th>
                            <td>Yes</td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Facing</th>
                            <td><?php echo $property['facing'] ? htmlspecialchars($property['facing']) : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Created Date</th>
                            <td><?php echo formatDate($property['created_at']); ?></td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td><?php echo formatDate($property['updated_at']); ?></td>
                        </tr>
                    </table>
                    
                </div>
                
            </div>
            <div class="card mt-4">
                <div class="card-header">
                    <h2>Description</h2>
                </div>
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Description Section -->
    
    
    <!-- Features Section -->
    <?php if (!empty($property_features)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h2>Property Features</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($property_features as $feature): ?>
                <div class="col-md-3 mb-2">
                    <div class="feature-item">
                        <?php if ($feature['icon']): ?>
                            <i class="<?php echo $feature['icon']; ?>"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($feature['name']); ?>
                        <?php if ($feature['value'] && $feature['value'] !== 'yes'): ?>
                            <span class="feature-value"><?php echo htmlspecialchars($feature['value']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Agent Section (if assigned) -->
    <?php if ($agent): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h2>Property Agent</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <?php if ($agent['profile_pic']): ?>
                        <img src="<?php echo $base_url . $agent['profile_pic']; ?>" alt="Agent Photo" class="img-fluid rounded-circle">
                    <?php else: ?>
                        <div class="no-image">No agent photo available</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-10">
                    <h3><?php echo htmlspecialchars($agent['name']); ?></h3>
                    <p><strong>Position:</strong> <?php echo htmlspecialchars($agent['position']); ?></p>
                    <p><strong>Experience:</strong> <?php echo $agent['experience']; ?> years</p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($agent['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($agent['phone']); ?></p>
                    
                    <?php if (!empty($agent['specializations'])): ?>
                    <p><strong>Specializations:</strong> <?php echo implode(', ', $agent['specializations']); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($agent['description']): ?>
                    <p><strong>About:</strong> <?php echo htmlspecialchars($agent['description']); ?></p>
                    <?php endif; ?>
                    
                    <a href="../agents/agent-details.php?id=<?php echo $agent['id']; ?>" class="btn btn-primary mt-2">View Agent Profile</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <span id="propertyName"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="post" action="delete-property.php">
                    <input type="hidden" name="id" id="propertyId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('propertyId').value = id;
    document.getElementById('propertyName').textContent = name;
    $('#deleteModal').modal('show');
}
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>