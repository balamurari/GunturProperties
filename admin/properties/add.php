<?php
/**
 * Add Property Page - Enhanced with User Guidance
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

// Debug the upload paths to help troubleshoot
debug_log("PROPERTY_IMG_PATH: " . PROPERTY_IMG_PATH);
debug_log("PROPERTY_IMAGES_URL: " . PROPERTY_IMAGES_URL);
debug_log("Document Root: " . $_SERVER['DOCUMENT_ROOT']);

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
    debug_log("Checking directory: " . $path);
    if (!file_exists($path)) {
        // Try to create the directory if it doesn't exist
        debug_log("Directory doesn't exist, creating: " . $path);
        if (!mkdir($path, 0755, true)) {
            debug_log("Failed to create directory: " . $path);
            die("Failed to create directory: " . $path . ". Please create it manually with proper permissions.");
        } else {
            debug_log("Directory created successfully: " . $path);
        }
    } else {
        debug_log("Directory exists: " . $path);
    }
    
    if (!is_writable($path)) {
        debug_log("Directory not writable: " . $path . " - Permissions: " . substr(sprintf('%o', fileperms($path)), -4));
        die("Directory not writable: " . $path . ". Please check file permissions.");
    } else {
        debug_log("Directory is writable: " . $path);
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
    
    // New fields: Instagram URL and Phone Number - use empty string instead of null
    $instagram_url = !empty($_POST['instagram_url']) ? sanitize($_POST['instagram_url']) : '';
    $phone_number = !empty($_POST['phone_number']) ? sanitize($_POST['phone_number']) : '';

    // Validate Instagram URL
    if (!empty($instagram_url)) {
        // Ensure it's a valid URL format
        if (!filter_var($instagram_url, FILTER_VALIDATE_URL)) {
            $errors[] = 'Instagram URL must be a valid URL';
        }
        
        // Check if the URL contains instagram.com
        if (strpos($instagram_url, 'instagram.com') === false) {
            $errors[] = 'Instagram URL must be from instagram.com';
        }
        
        // Check length - Instagram URLs shouldn't exceed 255 chars
        if (strlen($instagram_url) > 255) {
            $errors[] = 'Instagram URL is too long (max 255 characters)';
        }
    }

    // Validate Phone Number
    if (!empty($phone_number)) {
        // Strip non-numeric characters for validation
        $stripped_phone = preg_replace('/[^0-9]/', '', $phone_number);
        
        // Check phone number length (India: typically 10 digits)
        if (strlen($stripped_phone) < 10 || strlen($stripped_phone) > 12) {
            $errors[] = 'Phone number must be 10-12 digits';
        }
        
        // Verify string length for database
        if (strlen($phone_number) > 20) {
            $errors[] = 'Phone number is too long (max 20 characters)';
        }
    }
    
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
            
            // Improved query construction with NULL handling - always include instagram_url and phone_number
            $query = "INSERT INTO properties (
                title, description, price, address, city, state, zip_code, 
                bedrooms, bathrooms, facing, area, area_unit, type_id,
                " . ($agent_id === null ? "" : "agent_id, ") . "
                instagram_url, phone_number,
                featured, status
            ) VALUES (
                :title, :description, :price, :address, :city, :state, :zip_code,
                " . ($bedrooms === null ? "NULL" : ":bedrooms") . ",
                " . ($bathrooms === null ? "NULL" : ":bathrooms") . ",
                " . ($facing === null ? "NULL" : ":facing") . ",
                " . ($area === null ? "NULL" : ":area") . ",
                :area_unit, :type_id,
                " . ($agent_id === null ? "" : ":agent_id, ") . "
                :instagram_url, :phone_number,
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
            
            // Bind Instagram and phone parameters (before execute)
            $db->bind(':instagram_url', $instagram_url);
            debug_log("Binding instagram_url: $instagram_url");
            
            $db->bind(':phone_number', $phone_number);
            debug_log("Binding phone_number: $phone_number");
            
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
                debug_log("Found image uploads: " . count(array_filter($_FILES['images']['name'])));
                
                foreach ($_FILES['images']['name'] as $key => $name) {
                    // Skip empty files
                    if (empty($name)) {
                        continue;
                    }
                    
                    debug_log("Processing image #$key: $name");
                    
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
                    
                    // Check upload file error
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $error_messages = [
                            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini',
                            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in the HTML form',
                            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
                        ];
                        debug_log("Upload error: " . ($error_messages[$file['error']] ?? 'Unknown error'));
                        $image_upload_error = true;
                        continue;
                    }
                    
                    // Upload image with enhanced error handling
                    debug_log("Attempting to upload file to: " . PROPERTY_IMG_PATH);
                    $upload_result = uploadFile($file, PROPERTY_IMG_PATH, ALLOWED_EXTENSIONS, MAX_FILE_SIZE);
                    
                    if ($upload_result) {
                        debug_log("Upload successful, path: " . $upload_result['path']);
                        
                        // Insert image
                        $db->query("INSERT INTO property_images (property_id, image_path, is_primary, sort_order) 
                                  VALUES (:property_id, :image_path, :is_primary, :sort_order)");
                        
                        $db->bind(':property_id', $property_id);
                        $db->bind(':image_path', $upload_result['path']);
                        $db->bind(':is_primary', $is_primary);
                        $db->bind(':sort_order', $key);
                        
                        $result = $db->execute();
                        if (!$result) {
                            debug_log("Failed to save image record to database");
                            $image_upload_error = true;
                        }
                    } else {
                        debug_log("Failed to upload image: " . $name);
                        $image_upload_error = true;
                    }
                }
            } else {
                debug_log("No images were submitted with the form");
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
        <h2><i class="fas fa-plus-circle"></i> Add New Property</h2>
        <p class="card-subtitle">Fill in the details below to list a new property</p>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <h4><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h4>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- System Limits Info Box -->
        <div class="info-box info-box-primary mb-4">
            <div class="info-box-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="info-box-content">
                <h4>System Limits & Guidelines</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Image Size:</strong> Max <?php echo round(MAX_FILE_SIZE / 1048576); ?>MB per image
                    </div>
                    <div class="info-item">
                        <strong>Image Types:</strong> JPG, PNG, WebP
                    </div>
                    <div class="info-item">
                        <strong>Max Images:</strong> 10 images per property
                    </div>
                    <div class="info-item">
                        <strong>Phone Format:</strong> 10-12 digits (e.g., +91 9876543210)
                    </div>
                </div>
            </div>
        </div>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
            <!-- Basic Information -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-home"></i> Basic Information
                    <span class="form-section-subtitle">Essential property details</span>
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Property Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title" required 
                               placeholder="e.g., Luxury 3BHK Apartment in Guntur"
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                        <small class="form-help">Enter a descriptive title that attracts buyers</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="type_id">Property Type <span class="required">*</span></label>
                        <select id="type_id" name="type_id" required>
                            <option value="">Select Property Type</option>
                            <?php foreach ($property_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>" 
                                        <?php echo (isset($_POST['type_id']) && $_POST['type_id'] == $type['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-help">Choose the most appropriate property category</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" rows="6" required 
                              placeholder="Describe the property features, location benefits, amenities, and any unique selling points..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    <small class="form-help">
                        <i class="fas fa-lightbulb"></i> 
                        <strong>Tip:</strong> Include nearby amenities, transportation, and special features to attract more buyers
                    </small>
                </div>
            </div>
            
            <!-- Pricing & Status -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-money-bill-wave"></i> Pricing & Status
                    <span class="form-section-subtitle">Set your property price and availability</span>
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price-display">Price <span class="required">*</span></label>
                        <div class="price-input-container">
                            <div class="input-with-icon">
                                <i class="fas fa-rupee-sign"></i>
                                <input type="text" id="price-display" required 
                                       placeholder="Enter amount">
                            </div>
                            <select id="price-denomination">
                                <option value="1">Exact Amount</option>
                                <option value="100000" selected>Lakhs (L)</option>
                                <option value="10000000">Crores (Cr)</option>
                            </select>
                            <!-- Hidden field to store actual price in rupees -->
                            <input type="hidden" id="price" name="price">
                        </div>
                        <div class="price-preview-container">
                            <small class="price-helper-text">
                                <strong>Final Price:</strong> <span id="price-preview">₹0</span>
                            </small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Availability Status <span class="required">*</span></label>
                        <select id="status" name="status" required>
                            <option value="buy" <?php echo (isset($_POST['status']) && $_POST['status'] == 'buy') ? 'selected' : ''; ?>>Available To Buy</option>
                            <option value="rent" <?php echo (isset($_POST['status']) && $_POST['status'] == 'rent') ? 'selected' : ''; ?>>Available To Rent</option>
                            <option value="sold" <?php echo (isset($_POST['status']) && $_POST['status'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                            <option value="pending" <?php echo (isset($_POST['status']) && $_POST['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="rented" <?php echo (isset($_POST['status']) && $_POST['status'] == 'rented') ? 'selected' : ''; ?>>Rented</option>
                        </select>
                        <small class="form-help">Select current availability status</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="agent_id">Assign Agent</label>
                        <select id="agent_id" name="agent_id">
                            <option value="">No Agent Assigned</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?php echo $agent['id']; ?>" 
                                        <?php echo (isset($_POST['agent_id']) && $_POST['agent_id'] == $agent['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($agent['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-help">Optional: Assign a specific agent to handle this property</small>
                    </div>
                </div>
            </div>
            
            <!-- Property Details -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-ruler-combined"></i> Property Details
                    <span class="form-section-subtitle">Specifications and features</span>
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="bedrooms">Bedrooms</label>
                        <input type="number" id="bedrooms" name="bedrooms" min="0" max="20" 
                               placeholder="e.g., 3"
                               value="<?php echo isset($_POST['bedrooms']) ? $_POST['bedrooms'] : ''; ?>">
                        <small class="form-help">Number of bedrooms (leave empty if not applicable)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="bathrooms">Bathrooms</label>
                        <input type="number" id="bathrooms" name="bathrooms" min="0" max="20" step="0.5" 
                               placeholder="e.g., 2.5"
                               value="<?php echo isset($_POST['bathrooms']) ? $_POST['bathrooms'] : ''; ?>">
                        <small class="form-help">Number of bathrooms (0.5 for half bath)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="facing">Facing Direction</label>
                        <select id="facing" name="facing">
                            <option value="">Select Facing</option>
                            <option value="North" <?php echo (isset($_POST['facing']) && $_POST['facing'] == 'North') ? 'selected' : ''; ?>>North</option>
                            <option value="South" <?php echo (isset($_POST['facing']) && $_POST['facing'] == 'South') ? 'selected' : ''; ?>>South</option>
                            <option value="East" <?php echo (isset($_POST['facing']) && $_POST['facing'] == 'East') ? 'selected' : ''; ?>>East</option>
                            <option value="West" <?php echo (isset($_POST['facing']) && $_POST['facing'] == 'West') ? 'selected' : ''; ?>>West</option>
                            <option value="North-East" <?php echo (isset($_POST['facing']) && $_POST['facing'] == 'North-East') ? 'selected' : ''; ?>>North-East</option>
                            <option value="North-West" <?php echo (isset($_POST['facing']) && $_POST['facing'] == 'North-West') ? 'selected' : ''; ?>>North-West</option>
                            <option value="South-East" <?php echo (isset($_POST['facing']) && $_POST['facing'] == 'South-East') ? 'selected' : ''; ?>>South-East</option>
                            <option value="South-West" <?php echo (isset($_POST['facing']) && $_POST['facing'] == 'South-West') ? 'selected' : ''; ?>>South-West</option>
                        </select>
                        <small class="form-help">Property facing direction (important for Vastu)</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="area">Property size</label>
                        <input type="number" id="area" name="area" min="0" step="0.01" 
                               placeholder="e.g., 1200"
                               value="<?php echo isset($_POST['area']) ? $_POST['area'] : ''; ?>">
                        <small class="form-help">Property area size</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="area_unit">Area Unit</label>
                        <select id="area_unit" name="area_unit">
                            <option value="sq ft" <?php echo (isset($_POST['area_unit']) && $_POST['area_unit'] == 'sq ft') ? 'selected' : ''; ?>>Square Feet</option>
                            <option value="sq m" <?php echo (isset($_POST['area_unit']) && $_POST['area_unit'] == 'sq m') ? 'selected' : ''; ?>>Square Meters</option>
                            <option value="acres" <?php echo (isset($_POST['area_unit']) && $_POST['area_unit'] == 'acres') ? 'selected' : ''; ?>>Acres</option>
                            <option value="hectares" <?php echo (isset($_POST['area_unit']) && $_POST['area_unit'] == 'hectares') ? 'selected' : ''; ?>>Hectares</option>
                        </select>
                        <small class="form-help">Unit of measurement for area</small>
                    </div>
                </div>
            </div>
            
            <!-- Location -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-map-marker-alt"></i> Location Details
                    <span class="form-section-subtitle">Property address and location</span>
                </h3>
                
                <div class="form-group">
                    <label for="address">Full Address <span class="required">*</span></label>
                    <input type="text" id="address" name="address" required 
                           placeholder="e.g., Plot No. 123, Gandhi Nagar, Near City Mall"
                           value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                    <small class="form-help">Complete street address with landmarks</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">Location <span class="required">*</span></label>
                        <input type="text" id="city" name="city" required 
                               placeholder="e.g., Lakshmipuram, Gardens"
                               value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                        <small class="form-help">City or town name</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" id="state" name="state" 
                               placeholder="Andhra Pradesh (default)"
                               value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>">
                        <small class="form-help">Will default to "Andhra Pradesh" if left empty</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="zip_code">PIN Code</label>
                        <input type="text" id="zip_code" name="zip_code" 
                               placeholder="e.g., 522001" pattern="[0-9]{6}"
                               value="<?php echo isset($_POST['zip_code']) ? htmlspecialchars($_POST['zip_code']) : ''; ?>">
                        <small class="form-help">6-digit PIN code</small>
                    </div>
                </div>
            </div>
            
            <!-- Features -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-star"></i> Property Features
                    <span class="form-section-subtitle">Select available amenities and features</span>
                </h3>
                
                <div class="features-container">
                    <?php foreach ($features as $feature): ?>
                        <div class="feature-item">
                            <label class="feature-label">
                                <input type="checkbox" class="feature-checkbox" data-toggle="<?php echo $feature['id']; ?>">
                                <i class="<?php echo $feature['icon']; ?>"></i>
                                <?php echo htmlspecialchars($feature['name']); ?>
                            </label>
                            <input type="text" name="features[<?php echo $feature['id']; ?>]" 
                                   class="feature-value" id="feature-<?php echo $feature['id']; ?>" 
                                   placeholder="Yes / Details" disabled>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="form-help">
                    <i class="fas fa-info-circle"></i> 
                    Check the amenities available and add specific details if needed
                </small>
            </div>
            
           <!-- Property Images Section with Fixed Layout -->
<div class="form-section">
    <h3 class="form-section-title">
        <i class="fas fa-camera"></i> Property Images
        <span class="form-section-subtitle">Upload high-quality photos (5 cards per row)</span>
    </h3>
    
    <!-- Image Upload Guidelines -->
    <div class="image-guidelines">
        <div class="guideline-grid">
            <div class="guideline-item">
                <i class="fas fa-file-image"></i>
                <strong>Max Size:</strong> <?php echo round(MAX_FILE_SIZE / 1048576); ?>MB per image
            </div>
            <div class="guideline-item">
                <i class="fas fa-images"></i>
                <strong>Max Images:</strong> 10 photos
            </div>
            <div class="guideline-item">
                <i class="fas fa-check-circle"></i>
                <strong>Formats:</strong> JPG, PNG, WebP
            </div>
            <div class="guideline-item">
                <i class="fas fa-star"></i>
                <strong>Tip:</strong> First image is featured
            </div>
        </div>
    </div>
    
    <div class="image-upload-container">
        <div class="image-upload-grid" id="imageUploadGrid">
            <!-- First two initial slots -->
            <div class="image-upload-slot">
                <input type="file" name="images[]" class="image-input" 
                       accept="image/jpeg,image/png,image/webp" data-preview="previewImage0">
                <div class="image-preview">
                    <div class="upload-placeholder">
                        <i class="fas fa-camera"></i>
                        <p>Click to upload<br><small>Primary Image</small></p>
                    </div>
                    <img id="previewImage0" style="display: none;" alt="Preview">
                </div>
                <div class="image-controls">
                    <label class="primary-label">
                        <input type="radio" name="primary_image" value="0" checked>
                        <i class="fas fa-star"></i> Primary
                    </label>
                </div>
            </div>
            
            <div class="image-upload-slot empty">
                <input type="file" name="images[]" class="image-input" 
                       accept="image/jpeg,image/png,image/webp" data-preview="previewImage1">
                <div class="image-preview">
                    <div class="upload-placeholder">
                        <i class="fas fa-camera"></i>
                        <p>Click to upload<br><small>Additional Image</small></p>
                    </div>
                    <img id="previewImage1" style="display: none;" alt="Preview">
                </div>
                <div class="image-controls">
                    <label class="primary-label">
                        <input type="radio" name="primary_image" value="1">
                        <i class="far fa-star"></i> Set as Primary
                    </label>
                </div>
            </div>
            
            <!-- Additional 8 slots (initially hidden) -->
            <?php for($i = 2; $i < 10; $i++): ?>
            <div class="image-upload-slot empty hidden-slot">
                <input type="file" name="images[]" class="image-input" 
                       accept="image/jpeg,image/png,image/webp" data-preview="previewImage<?php echo $i; ?>">
                <div class="image-preview">
                    <div class="upload-placeholder">
                        <i class="fas fa-camera"></i>
                        <p>Click to upload<br><small>Image <?php echo $i + 1; ?></small></p>
                    </div>
                    <img id="previewImage<?php echo $i; ?>" style="display: none;" alt="Preview">
                </div>
                <div class="image-controls">
                    <label class="primary-label">
                        <input type="radio" name="primary_image" value="<?php echo $i; ?>">
                        <i class="far fa-star"></i> Set as Primary
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
</div>

            
            <!-- Contact Information -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-phone"></i> Contact Information
                    <span class="form-section-subtitle">How buyers can reach you</span>
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone_number">Contact Phone</label>
                        <input type="tel" id="phone_number" name="phone_number" 
                               placeholder="e.g., +91 9876543210" pattern="[0-9+\s()-]{10,20}"
                               value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>">
                        <small class="form-help">
                            <i class="fas fa-info-circle"></i> 
                            10-12 digits, Indian format recommended
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="instagram_url">Instagram Profile</label>
                        <div class="input-with-icon">
                            <i class="fab fa-instagram"></i>
                            <input type="url" id="instagram_url" name="instagram_url" 
                                   placeholder="https://www.instagram.com/username/"
                                   value="<?php echo isset($_POST['instagram_url']) ? htmlspecialchars($_POST['instagram_url']) : ''; ?>">
                        </div>
                        <small class="form-help">
                            <i class="fas fa-info-circle"></i> 
                            Optional: Link to Instagram for property photos/videos
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Additional Settings -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-cog"></i> Additional Settings
                    <span class="form-section-subtitle">Final property options</span>
                </h3>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="featured" name="featured" 
                           <?php echo (isset($_POST['featured']) && $_POST['featured']) ? 'checked' : ''; ?>>
                    <label for="featured">
                        <i class="fas fa-star"></i> Mark as Featured Property
                    </label>
                    <small class="form-help">Featured properties appear first in listings and get more visibility</small>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle"></i> Add Property
                </button>
                <a href="index.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript remains the same -->
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

// Enhanced image preview on file selection
document.querySelectorAll('.image-input').forEach(function(input) {
    input.addEventListener('change', function() {
        const previewId = this.getAttribute('data-preview');
        const preview = document.getElementById(previewId);
        const placeholder = this.parentElement.querySelector('.upload-placeholder');
        
        if (this.files && this.files[0]) {
            const file = this.files[0];
            
            // Check file size
            if (file.size > <?php echo MAX_FILE_SIZE; ?>) {
                alert('File size exceeds <?php echo round(MAX_FILE_SIZE / 1048576); ?>MB limit. Please choose a smaller image.');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
                preview.parentElement.parentElement.classList.remove('empty');
            };
            
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            placeholder.style.display = 'flex';
            preview.parentElement.parentElement.classList.add('empty');
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

// Validate Instagram URL and Phone Number
document.getElementById('instagram_url').addEventListener('blur', function() {
    const input = this;
    const value = input.value.trim();
    
    if (value && (!value.includes('instagram.com') || !value.startsWith('http'))) {
        input.setCustomValidity('Please enter a valid Instagram URL starting with http:// or https:// and containing instagram.com');
    } else {
        input.setCustomValidity('');
    }
});

document.getElementById('phone_number').addEventListener('blur', function() {
    const input = this;
    const value = input.value.trim();
    
    // Remove non-numeric characters for validation
    const numericValue = value.replace(/[^0-9]/g, '');
    
    if (value && (numericValue.length < 10 || numericValue.length > 12)) {
        input.setCustomValidity('Phone number must be 10-12 digits');
    } else {
        input.setCustomValidity('');
    }
});
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>

<style>
/* Enhanced form styling */

.card-subtitle {
    color: #666;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.info-box {
    border-radius: 8px;
    padding: 1.5rem;
    border-left: 4px solid;
}

.info-box-primary {
    background-color: #e3f2fd;
    border-color: #2196f3;
}

.info-box-icon {
    float: left;
    margin-right: 1rem;
    font-size: 2rem;
    color: #2196f3;
}

.info-box-content {
    overflow: hidden;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.info-item {
    font-size: 0.9rem;
}

.form-section {
    margin-bottom: 3rem;
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.form-section-title {
    margin-bottom: 1.5rem;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.5rem;
}

.form-section-subtitle {
    font-size: 0.85rem;
    font-weight: normal;
    color: #6c757d;
    display: block;
    margin-top: 0.25rem;
}

.form-help {
    display: block;
    margin-top: 0.25rem;
    color: #6c757d;
    font-size: 0.85rem;
}

.required {
    color: #dc3545;
}

.price-preview-container {
    margin-top: 0.5rem;
}

.price-helper-text {
    background: #e8f5e8;
    padding: 0.5rem;
    border-radius: 4px;
    border: 1px solid #d4edda;
}

.image-guidelines {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.guideline-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.guideline-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.guideline-item i {
    color: #f39c12;
    width: 20px;
}

.image-upload-slot {
    position: relative;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    transition: all 0.3s ease;
}

.image-upload-slot:hover {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 150px;
    color: #6c757d;
}

.upload-placeholder i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
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
    border-radius: 4px;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-controls {
    margin-top: 0.5rem;
}

.primary-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #007bff;
    font-size: 0.85rem;
    cursor: pointer;
}

.feature-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-weight: 500;
}

.features-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.feature-item {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 1rem;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .guideline-grid {
        grid-template-columns: 1fr;
    }
    
    .features-container {
        grid-template-columns: 1fr;
    }
    
    .form-section {
        padding: 1rem;
    }
}
.image-upload-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr); /* Exactly 5 columns */
    gap: 1rem;
    margin: 1.5rem 0;
}

.image-upload-slot {
    position: relative;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 0.75rem;
    text-align: center;
    transition: all 0.3s ease;
    background: white;
    min-height: 200px; /* Consistent height */
    display: flex;
    flex-direction: column;
}

.image-upload-slot:hover {
    border-color: #007bff;
    background-color: #f8f9ff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.1);
}

.upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 120px;
    color: #6c757d;
    flex-grow: 1;
}

.upload-placeholder i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #007bff;
}

.upload-placeholder p {
    margin: 0;
    font-size: 0.8rem;
    line-height: 1.2;
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
    flex-grow: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
}

.image-preview img {
    max-width: 100%;
    max-height: 120px;
    object-fit: cover;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.image-controls {
    margin-top: auto;
    padding-top: 0.5rem;
    border-top: 1px solid #eee;
}

.primary-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    color: #007bff;
    font-size: 0.75rem;
    cursor: pointer;
    font-weight: 500;
    padding: 0.25rem;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.primary-label:hover {
    background-color: rgba(0,123,255,0.1);
}

.primary-label input[type="radio"] {
    margin: 0;
}

/* Hide initially hidden slots */
.hidden-slot {
    display: none !important;
}

/* Responsive Design */
@media (max-width: 1400px) {
    .image-upload-grid {
        grid-template-columns: repeat(4, 1fr); /* 4 cards on medium-large screens */
    }
}

@media (max-width: 1200px) {
    .image-upload-grid {
        grid-template-columns: repeat(3, 1fr); /* 3 cards on medium screens */
    }
}

@media (max-width: 768px) {
    .image-upload-grid {
        grid-template-columns: repeat(2, 1fr); /* 2 cards on tablets */
        gap: 0.75rem;
    }
    
    .image-upload-slot {
        min-height: 180px;
        padding: 0.5rem;
    }
    
    .upload-placeholder {
        height: 100px;
    }
    
    .upload-placeholder i {
        font-size: 1.25rem;
    }
}

@media (max-width: 480px) {
    .image-upload-grid {
        grid-template-columns: 1fr; /* 1 card on mobile */
        gap: 0.5rem;
    }
    
    .image-upload-slot {
        min-height: 160px;
    }
    
    .upload-placeholder {
        height: 80px;
    }
}

/* Guidelines styling */
.image-guidelines {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.guideline-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.guideline-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.guideline-item i {
    color: #f39c12;
    width: 20px;
    text-align: center;
}

/* Button styling */
.btn {
    padding: 0.5rem 1rem;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.btn-outline {
    background: white;
    border: 1px solid #007bff;
    color: #007bff;
}

.btn-outline:hover {
    background: #007bff;
    color: white;
}

.mt-3 {
    margin-top: 1rem;
}

.text-center {
    text-align: center;
}

.text-muted {
    color: #6c757d;
}

.d-block {
    display: block;
}
</style>