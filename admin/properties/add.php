<?php
/**
 * Add Property Page
 * Form to add a new property
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Set page title
$page_title = 'Add New Property';

// Enable better debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Debug function
function debug_log($message) {
    error_log('[PROPERTY DEBUG] ' . $message);
}

// Get database connection
$db = new Database();

// Get property types
$db->query("SELECT * FROM property_types ORDER BY name ASC");
$property_types = $db->resultSet();

// Get agents
$agents = getAgents();

// Get features
$db->query("SELECT * FROM property_features ORDER BY name ASC");
$features = $db->resultSet();

// Ensure upload directories exist and are writable
foreach ([PROPERTY_IMG_PATH, AGENT_IMG_PATH] as $path) {
    if (!file_exists($path)) {
        // Try to create the directory if it doesn't exist
        if (!mkdir($path, 0755, true)) {
            die("Failed to create directory: " . $path . ". Please create it manually with proper permissions.");
        }
    }
    
    if (!is_writable($path)) {
        die("Directory not writable: " . $path . ". Please check file permissions.");
    }
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log("Form submitted: " . print_r($_POST, true));
    
    // Sanitize and validate inputs
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = !empty($_POST['state']) ? sanitize($_POST['state']) : 'Andhra Pradesh';
    $zip_code = sanitize($_POST['zip_code']);
    $type_id = (int)$_POST['type_id'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = sanitize($_POST['status']);
    
    // Optional fields - careful NULL handling
    // Bedrooms
    if (isset($_POST['bedrooms']) && $_POST['bedrooms'] !== '') {
        $bedrooms = (int)$_POST['bedrooms'];
        debug_log("Bedrooms set to: $bedrooms");
    } else {
        $bedrooms = null;
        debug_log("Bedrooms set to NULL");
    }
    
    // Bathrooms
    if (isset($_POST['bathrooms']) && $_POST['bathrooms'] !== '') {
        $bathrooms = (float)$_POST['bathrooms'];
        debug_log("Bathrooms set to: $bathrooms");
    } else {
        $bathrooms = null;
        debug_log("Bathrooms set to NULL");
    }
    
    // Facing
    if (isset($_POST['facing']) && !empty($_POST['facing'])) {
        $facing = sanitize($_POST['facing']);
        debug_log("Facing set to: $facing");
    } else {
        $facing = null;
        debug_log("Facing set to NULL");
    }
    
    // Area
    if (isset($_POST['area']) && $_POST['area'] !== '') {
        $area = (float)$_POST['area'];
        debug_log("Area set to: $area");
    } else {
        $area = null;
        debug_log("Area set to NULL");
    }
    
    // Area Unit
    $area_unit = !empty($_POST['area_unit']) ? sanitize($_POST['area_unit']) : 'sq ft';
    debug_log("Area unit set to: $area_unit");
    
    // Agent ID - special handling
    if (!empty($_POST['agent_id'])) {
        $agent_id = (int)$_POST['agent_id'];
        
        // Verify agent exists
        $db->query("SELECT id FROM agents WHERE id = :id");
        $db->bind(':id', $agent_id);
        $agent_exists = $db->single();
        
        if ($agent_exists) {
            debug_log("Agent ID set to: $agent_id");
        } else {
            $agent_id = null;
            debug_log("Agent ID set to NULL (agent not found)");
        }
    } else {
        $agent_id = null;
        debug_log("Agent ID set to NULL (empty selection)");
    }
    
    // Validate required fields
    if (empty($title)) {
        $errors[] = 'Property title is required';
    }
    
    if (empty($description)) {
        $errors[] = 'Property description is required';
    }
    
    if (!is_numeric($price) || $price <= 0) {
        $errors[] = 'Valid property price is required';
    }
    
    if (empty($address)) {
        $errors[] = 'Property address is required';
    }
    
    if (empty($city)) {
        $errors[] = 'City is required';
    }
    
    if (!$type_id) {
        $errors[] = 'Property type is required';
    }
    
    // Process if no errors
    if (empty($errors)) {
        try {
            // Start transaction
            $db->beginTransaction();
            
            // Improved query construction with NULL handling
            $query = "INSERT INTO properties (
                title, description, price, address, city, state, zip_code, 
                bedrooms, bathrooms, facing, area, area_unit, type_id,
                " . ($agent_id === null ? "" : "agent_id, ") . "
                featured, status
            ) VALUES (
                :title, :description, :price, :address, :city, :state, :zip_code,
                " . ($bedrooms === null ? "NULL" : ":bedrooms") . ",
                " . ($bathrooms === null ? "NULL" : ":bathrooms") . ",
                " . ($facing === null ? "NULL" : ":facing") . ",
                " . ($area === null ? "NULL" : ":area") . ",
                :area_unit, :type_id,
                " . ($agent_id === null ? "" : ":agent_id, ") . "
                :featured, :status
            )";
            
            debug_log("SQL Query: " . $query);
            
            $db->query($query);
            
            // Bind required parameters
            $db->bind(':title', $title);
            $db->bind(':description', $description);
            $db->bind(':price', $price);
            $db->bind(':address', $address);
            $db->bind(':city', $city);
            $db->bind(':state', $state);
            $db->bind(':zip_code', $zip_code);
            $db->bind(':area_unit', $area_unit);
            $db->bind(':type_id', $type_id);
            $db->bind(':featured', $featured);
            $db->bind(':status', $status);
            
            // Selectively bind optional parameters
            if ($bedrooms !== null) {
                $db->bind(':bedrooms', $bedrooms);
                debug_log("Binding bedrooms: $bedrooms");
            }
            
            if ($bathrooms !== null) {
                $db->bind(':bathrooms', $bathrooms);
                debug_log("Binding bathrooms: $bathrooms");
            }
            
            if ($facing !== null) {
                $db->bind(':facing', $facing);
                debug_log("Binding facing: $facing");
            }
            
            if ($area !== null) {
                $db->bind(':area', $area);
                debug_log("Binding area: $area");
            }
            
            if ($agent_id !== null) {
                $db->bind(':agent_id', $agent_id);
                debug_log("Binding agent_id: $agent_id");
            }
            
            // Execute the query
            $result = $db->execute();
            
            if (!$result) {
                throw new Exception("Failed to insert property into database");
            }
            
            // Get the newly inserted property ID
            $property_id = $db->lastInsertId();
            debug_log("Property inserted with ID: $property_id");
            
            // Handle property features
            if (!empty($_POST['features'])) {
                foreach ($_POST['features'] as $feature_id => $value) {
                    if (empty($value)) {
                        $value = "Yes"; // Default value if checkbox is checked but no text entered
                    }
                    
                    $db->query("INSERT INTO property_feature_mapping (property_id, feature_id, value) 
                              VALUES (:property_id, :feature_id, :value)");
                    
                    $db->bind(':property_id', $property_id);
                    $db->bind(':feature_id', $feature_id);
                    $db->bind(':value', $value);
                    
                    $db->execute();
                }
            }
            
            // Handle property images
            $has_primary = false;
            $image_upload_error = false;
            
            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['name'] as $key => $name) {
                    // Skip empty files
                    if (empty($name)) {
                        continue;
                    }
                    
                    $file = [
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $_FILES['images']['tmp_name'][$key],
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key]
                    ];
                    
                    // Check if primary image
                    $is_primary = isset($_POST['primary_image']) && $_POST['primary_image'] == $key ? 1 : 0;
                    
                    // If no primary image selected yet, make first image primary
                    if (!$has_primary && $key === 0) {
                        $is_primary = 1;
                    }
                    
                    if ($is_primary) {
                        $has_primary = true;
                    }
                    
                    // Upload image
                    $upload_result = uploadFile($file, PROPERTY_IMG_PATH, ALLOWED_EXTENSIONS, MAX_FILE_SIZE);
                    
                    if ($upload_result) {
                        // Insert image
                        $db->query("INSERT INTO property_images (property_id, image_path, is_primary, sort_order) 
                                  VALUES (:property_id, :image_path, :is_primary, :sort_order)");
                        
                        $db->bind(':property_id', $property_id);
                        $db->bind(':image_path', $upload_result['path']);
                        $db->bind(':is_primary', $is_primary);
                        $db->bind(':sort_order', $key);
                        
                        $db->execute();
                    } else {
                        debug_log("Failed to upload image: " . $name);
                        $image_upload_error = true;
                    }
                }
            }
            
            // Commit transaction
            $db->endTransaction();
            debug_log("Transaction committed successfully");
            
            // Set success message and redirect
            if ($image_upload_error) {
                setFlashMessage('warning', 'Property added but some images could not be uploaded.');
            } else {
                setFlashMessage('success', 'Property added successfully!');
            }
            
            redirect('index.php');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->cancelTransaction();
            debug_log("Error adding property: " . $e->getMessage());
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Add New Property</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
            <!-- Basic Information -->
            <h3 class="form-section-title">Basic Information</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Property Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="type_id">Property Type <span class="required">*</span></label>
                    <select id="type_id" name="type_id" required>
                        <option value="">Select Property Type</option>
                        <?php foreach ($property_types as $type): ?>
                            <option value="<?php echo $type['id']; ?>">
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description <span class="required">*</span></label>
                <textarea id="description" name="description" rows="6" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price-display">Price <span class="required">*</span></label>
                    <div class="price-input-container">
                        <div class="input-with-icon">
                            <i class="fas fa-rupee-sign"></i>
                            <input type="text" id="price-display" required>
                        </div>
                        <select id="price-denomination">
                            <option value="1">Exact Amount</option>
                            <option value="100000" selected>Lakhs</option>
                            <option value="10000000">Crores</option>
                        </select>
                        <!-- Hidden field to store actual price in rupees -->
                        <input type="hidden" id="price" name="price">
                    </div>
                    <small class="price-helper-text">Enter amount: <span id="price-preview">₹0</span></small>
                </div>
                
                <div class="form-group">
                    <label for="status">Status <span class="required">*</span></label>
                    <select id="status" name="status" required>
                        <option value="available">Available</option>
                        <option value="sold">Sold</option>
                        <option value="pending">Pending</option>
                        <option value="rented">Rented</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="agent_id">Assign Agent</label>
                    <select id="agent_id" name="agent_id">
                        <option value="">Select Agent</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?php echo $agent['id']; ?>" 
                                    <?php echo (isset($property['agent_id']) && $property['agent_id'] == $agent['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($agent['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Property Details -->
            <h3 class="form-section-title">Property Details</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="bedrooms">Bedrooms</label>
                    <input type="number" id="bedrooms" name="bedrooms" min="0">
                </div>
                
                <div class="form-group">
                    <label for="bathrooms">Bathrooms</label>
                    <input type="number" id="bathrooms" name="bathrooms" min="0" step="0.5">
                </div>
                <div class="form-group">
                    <label for="facing">Facing</label>
                    <input type="text" id="facing" name="facing">
                </div>
                <div class="form-group">
                    <label for="area">Area</label>
                    <input type="number" id="area" name="area" min="0" step="0.01">
                </div>
                
                <div class="form-group">
                    <label for="area_unit">Area Unit</label>
                    <select id="area_unit" name="area_unit">
                        <option value="sq ft">Square Feet</option>
                        <option value="sq m">Square Meters</option>
                        <option value="acres">Acres</option>
                        <option value="hectares">Hectares</option>
                    </select>
                </div>
            </div>
            
            <!-- Location -->
            <h3 class="form-section-title">Location</h3>
            
            <div class="form-group">
                <label for="address">Address <span class="required">*</span></label>
                <input type="text" id="address" name="address" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="city">City <span class="required">*</span></label>
                    <input type="text" id="city" name="city" placeholder="Eg: Guntur/Amaravathi" required>
                </div>
                
                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" placeholder="Default Value: Andhra Pradesh If left empty" name="state" >
                </div>
                
                <div class="form-group">
                    <label for="zip_code">ZIP Code</label>
                    <input type="text" id="zip_code" name="zip_code">
                </div>
            </div>
            
            <!-- Features -->
            <h3 class="form-section-title">Features</h3>
            
            <div class="features-container">
                <?php foreach ($features as $feature): ?>
                    <div class="feature-item">
                        <label>
                            <input type="checkbox" class="feature-checkbox" data-toggle="<?php echo $feature['id']; ?>">
                            <?php echo htmlspecialchars($feature['name']); ?>
                        </label>
                        <input type="text" name="features[<?php echo $feature['id']; ?>]" class="feature-value" id="feature-<?php echo $feature['id']; ?>" placeholder="Yes" disabled>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Property Images -->
            <h3 class="form-section-title">Property Images</h3>
            
            <div class="image-upload-container">
                <div class="image-upload-instruction">
                    <p>Upload up to 10 images for this property. The first image will be used as the primary image unless specified otherwise.</p>
                </div>
                
                <div class="image-upload-grid" id="imageUploadGrid">
                    <!-- First two initial slots -->
                    <div class="image-upload-slot">
                        <input type="file" name="images[]" class="image-input" accept="image/jpeg, image/png, image/webp" data-preview="previewImage0">
                        <div class="image-preview">
                            <img id="previewImage0" src="../assets/images/upload-placeholder.jpg" alt="Preview">
                        </div>
                        <div class="image-controls">
                            <label class="primary-label">
                                <input type="radio" name="primary_image" value="0" checked>
                                Set as Primary
                            </label>
                        </div>
                    </div>
                    
                    <div class="image-upload-slot empty">
                        <input type="file" name="images[]" class="image-input" accept="image/jpeg, image/png, image/webp" data-preview="previewImage1">
                        <div class="image-preview">
                            <img id="previewImage1" src="../assets/images/upload-placeholder.jpg" alt="Preview">
                        </div>
                        <div class="image-controls">
                            <label class="primary-label">
                                <input type="radio" name="primary_image" value="1">
                                Set as Primary
                            </label>
                        </div>
                    </div>
                    
                    <!-- Additional 8 slots (initially hidden) -->
                    <?php for($i = 2; $i < 10; $i++): ?>
                    <div class="image-upload-slot empty hidden-slot">
                        <input type="file" name="images[]" class="image-input" accept="image/jpeg, image/png, image/webp" data-preview="previewImage<?php echo $i; ?>">
                        <div class="image-preview">
                            <img id="previewImage<?php echo $i; ?>" src="../assets/images/upload-placeholder.jpg" alt="Preview">
                        </div>
                        <div class="image-controls">
                            <label class="primary-label">
                                <input type="radio" name="primary_image" value="<?php echo $i; ?>">
                                Set as Primary
                            </label>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
                
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-outline" id="addMoreImagesBtn">
                        <i class="fas fa-plus"></i> Add More Images
                    </button>
                    <small class="d-block mt-2 text-muted">You can add up to 10 images total</small>
                </div>
            </div>
            
            <!-- Additional Settings -->
            <h3 class="form-section-title">Additional Settings</h3>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" id="featured" name="featured">
                <label for="featured">Mark as Featured Property</label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Property</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.current-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.current-image-item {
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.current-image-item .image-preview {
    height: 150px;
    overflow: hidden;
}

.current-image-item .image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.current-image-item .image-controls {
    padding: 10px;
    display: flex;
    justify-content: space-between;
    background-color: #f5f5f5;
}

.delete-label {
    color: var(--error-color);
}

.primary-label {
    color: var(--primary-color);
}

.features-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.feature-item {
    display: flex;
    flex-direction: column;
    padding: 10px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
}

.feature-value {
    margin-top: 5px;
}

.image-upload-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.image-upload-slot {
    position: relative;
    border: 2px dashed var(--border-color);
    border-radius: var(--border-radius);
    padding: 10px;
    text-align: center;
}

.image-upload-slot input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 10;
}

.image-preview {
    width: 100%;
    height: 150px;
    overflow: hidden;
    margin-bottom: 10px;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-upload-instruction {
    margin: 15px 0;
}

/* Additional styles for price input */
.price-input-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.price-input-container .input-with-icon {
    flex: 1;
}

.price-input-container select {
    width: auto;
}

.price-helper-text {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 0.9em;
}

#price-preview {
    font-weight: bold;
}

.image-controls {
    margin-top: 8px;
    text-align: center;
}

/* New styles for the multi-image upload functionality */
.hidden-slot {
    display: none;
}

.text-center {
    text-align: center;
}

.mt-3 {
    margin-top: 15px;
}

.d-block {
    display: block;
}

.mt-2 {
    margin-top: 10px;
}

.text-muted {
    color: #6c757d;
}
</style>

<script>
// Feature checkboxes toggle
document.querySelectorAll('.feature-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        const featureId = this.getAttribute('data-toggle');
        const valueInput = document.getElementById('feature-' + featureId);
        
        if (this.checked) {
            valueInput.disabled = false;
            valueInput.focus();
        } else {
            valueInput.disabled = true;
            valueInput.value = '';
        }
    });
});

// Image preview on file selection
document.querySelectorAll('.image-input').forEach(function(input) {
    input.addEventListener('change', function() {
        const previewId = this.getAttribute('data-preview');
        const preview = document.getElementById(previewId);
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.parentElement.parentElement.classList.remove('empty');
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });
});

// Add more images functionality
document.addEventListener('DOMContentLoaded', function() {
    const addMoreBtn = document.getElementById('addMoreImagesBtn');
    const hiddenSlots = document.querySelectorAll('.hidden-slot');
    let visibleSlotCount = 2; // We start with 2 visible slots
    
    addMoreBtn.addEventListener('click', function() {
        // Show next 2 slots (or however many are left)
        for (let i = 0; i < 2 && visibleSlotCount < 10; i++) {
            if (hiddenSlots[visibleSlotCount - 2]) {
                hiddenSlots[visibleSlotCount - 2].classList.remove('hidden-slot');
                visibleSlotCount++;
            }
        }
        
        // Hide button if we've reached the maximum
        if (visibleSlotCount >= 10) {
            addMoreBtn.style.display = 'none';
        }
        
        // Update the button text to show how many more can be added
        const remaining = 10 - visibleSlotCount;
        if (remaining > 0) {
            addMoreBtn.innerHTML = `<i class="fas fa-plus"></i> Add More Images (${remaining} remaining)`;
        }
    });
});

// Price input handling for Indian currency format
document.addEventListener('DOMContentLoaded', function() {
    const priceDisplay = document.getElementById('price-display');
    const priceDenomination = document.getElementById('price-denomination');
    const actualPrice = document.getElementById('price');
    const pricePreview = document.getElementById('price-preview');
    
    // Format number with commas (Indian format: 1,23,45,678)
    function formatIndianPrice(num) {
        num = num.toString();
        let afterPoint = '';
        if(num.indexOf('.') > 0) {
            afterPoint = num.substring(num.indexOf('.'), num.length);
            num = parseInt(num);
            num = num.toString();
        }
        let lastThree = num.substring(num.length-3);
        let otherNumbers = num.substring(0, num.length-3);
        if(otherNumbers != '')
            lastThree = ',' + lastThree;
        let formattedNumber = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + afterPoint;
        
        return formattedNumber;
    }
    
    function updateActualPrice() {
        const displayValue = parseFloat(priceDisplay.value) || 0;
        const multiplier = parseFloat(priceDenomination.value);
        const calculatedPrice = displayValue * multiplier;
        
        // Update hidden price field with calculated value
        actualPrice.value = calculatedPrice.toFixed(2);
        
        // Update preview text
        if (priceDenomination.value === '100000') {
            pricePreview.textContent = '₹' + formatIndianPrice(calculatedPrice.toFixed(2)) + ' (' + priceDisplay.value + ' Lakhs)';
        } else if (priceDenomination.value === '10000000') {
            pricePreview.textContent = '₹' + formatIndianPrice(calculatedPrice.toFixed(2)) + ' (' + priceDisplay.value + ' Crores)';
        } else {
            pricePreview.textContent = '₹' + formatIndianPrice(calculatedPrice.toFixed(2));
        }
        
        // Log the price calculation for debugging
        console.log('Price calculated:', {
            display: displayValue,
            multiplier: multiplier,
            calculated: calculatedPrice,
            field: actualPrice.value
        });
    }
    
    // Add event listeners
    priceDisplay.addEventListener('input', updateActualPrice);
    priceDenomination.addEventListener('change', updateActualPrice);
    
    // Initialize with default values
    updateActualPrice();
    
    // Force price calculation right before form submission
    document.querySelector('form').addEventListener('submit', function(e) {
        // Force price calculation one last time
        updateActualPrice();
        
        // Add a small delay to ensure the value is set
        setTimeout(function() {
            console.log('Final price value before submit:', actualPrice.value);
        }, 10);
        
        // Validation check
        if (!actualPrice.value || parseFloat(actualPrice.value) <= 0) {
            e.preventDefault();
            alert('Please enter a valid price');
            priceDisplay.focus();
            return false;
        }
        
        return true;
    });
});
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>