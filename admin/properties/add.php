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

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = sanitize($_POST['price']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = !empty($_POST['state']) ? sanitize($_POST['state']) : 'Andhra Pradesh';
    $zip_code = sanitize($_POST['zip_code']);
    $bedrooms = isset($_POST['bedrooms']) ? (int)$_POST['bedrooms'] : null;
    $bathrooms = isset($_POST['bathrooms']) ? (int)$_POST['bathrooms'] : null;
    $area = isset($_POST['area']) ? (float)$_POST['area'] : null;
    $facing = sanitize($_POST['facing']);
    $area_unit = sanitize($_POST['area_unit'] ?? 'sq ft');
    $type_id = (int)$_POST['type_id'];
    $agent_id = !empty($_POST['agent_id']) ? (int)$_POST['agent_id'] : null;
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = sanitize($_POST['status']);
    
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
    
    // if (empty($state)) {
    //     $errors[] = 'State is required';
    // }
    
    // if (empty($zip_code)) {
    //     $errors[] = 'ZIP code is required';
    // }
    
    if (!$type_id) {
        $errors[] = 'Property type is required';
    }
    
    // Process if no errors
    if (empty($errors)) {
        try {
            // Start transaction
            $db->beginTransaction();
            
            // Insert property
            $db->query("INSERT INTO properties (title, description, price, address, city, state, zip_code, 
                        bedrooms, bathrooms,facing, area, area_unit, type_id, agent_id, featured, status) 
                        VALUES (:title, :description, :price, :address, :city, :state, :zip_code, 
                        :bedrooms, :bathrooms, :facing, :area, :area_unit, :type_id, :agent_id, :featured, :status)");
            
            $db->bind(':title', $title);
            $db->bind(':description', $description);
            $db->bind(':price', $price);
            $db->bind(':address', $address);
            $db->bind(':city', $city);
            $db->bind(':state', $state);
            $db->bind(':zip_code', $zip_code);
            $db->bind(':bedrooms', $bedrooms);
            $db->bind(':bathrooms', $bathrooms);
            $db->bind(':facing', $facing);
            $db->bind(':area', $area);
            $db->bind(':area_unit', $area_unit);
            $db->bind(':type_id', $type_id);
            $db->bind(':agent_id', $agent_id);
            $db->bind(':featured', $featured);
            $db->bind(':status', $status);
            
            $db->execute();
            
            $property_id = $db->lastInsertId();
            
            // Handle property images
            $has_primary = false;
            
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
                    }
                }
            }
            
            // Handle property features
            if (!empty($_POST['features'])) {
                foreach ($_POST['features'] as $feature_id => $value) {
                    if (empty($value)) {
                        continue;
                    }
                    
                    $db->query("INSERT INTO property_feature_mapping (property_id, feature_id, value) 
                               VALUES (:property_id, :feature_id, :value)");
                    
                    $db->bind(':property_id', $property_id);
                    $db->bind(':feature_id', $feature_id);
                    $db->bind(':value', $value);
                    
                    $db->execute();
                }
            }
            
            // Commit transaction
            $db->endTransaction();
            
            $success = true;
            setFlashMessage('success', 'Property added successfully!');
            redirect('index.php');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->cancelTransaction();
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
                            <option value="<?php echo $agent['id']; ?>">
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
                    <label for="facing">Facing<span class="required">*</span></label>
                    <input type="text" id="facing" name="facing" required>
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
                    <label for="zip_code">ZIP Code </label>
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
                    <!-- Image upload slots will be added dynamically by JavaScript -->
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
                    
                    <!-- Add more empty slots as needed -->
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
</script>
<script>
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
    }
    
    // Add event listeners
    priceDisplay.addEventListener('input', updateActualPrice);
    priceDenomination.addEventListener('change', updateActualPrice);
    
    // Initialize with default values
    updateActualPrice();
    
    // Before form submission, ensure the hidden price field has the correct value
    document.querySelector('form').addEventListener('submit', function(e) {
        updateActualPrice();
    });
});
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>