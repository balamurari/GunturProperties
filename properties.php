<?php
/**
 * Properties Page
 * Display all properties with search and filter functionality
 */
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'All Properties';

// Get database connection
$db = new Database();

// Initialize search and filter variables
$search = '';
$property_type = '';
$price_min = '';
$price_max = '';
$size_min = '';
$size_max = '';
$min_bedrooms = isset($_GET['min_bedrooms']) ? (int)$_GET['min_bedrooms'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12; // Number of properties per page
$offset = ($page - 1) * $per_page;

// Build the base query
$base_query = "SELECT p.*, pt.name as property_type, 
               (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
               FROM properties p
               LEFT JOIN property_types pt ON p.type_id = pt.id
               WHERE 1=1";

$count_query = "SELECT COUNT(*) as total FROM properties p WHERE 1=1";

$query_params = [];

// Handle search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = sanitize($_GET['search']);
    $search_condition = " AND (p.title LIKE :search OR p.address LIKE :search OR p.city LIKE :search OR p.zip_code LIKE :search)";
    $base_query .= $search_condition;
    $count_query .= $search_condition;
    $query_params[':search'] = '%' . $search . '%';
}

// Handle property type filter
if (isset($_GET['property_type']) && !empty($_GET['property_type'])) {
    $property_type = (int)$_GET['property_type'];
    $type_condition = " AND p.type_id = :property_type";
    $base_query .= $type_condition;
    $count_query .= $type_condition;
    $query_params[':property_type'] = $property_type;
}

// Handle price range filter
if (isset($_GET['price_min']) && !empty($_GET['price_min'])) {
    $price_min = (float)$_GET['price_min'];
    $price_min_condition = " AND p.price >= :price_min";
    $base_query .= $price_min_condition;
    $count_query .= $price_min_condition;
    $query_params[':price_min'] = $price_min;
}

if (isset($_GET['price_max']) && !empty($_GET['price_max'])) {
    $price_max = (float)$_GET['price_max'];
    $price_max_condition = " AND p.price <= :price_max";
    $base_query .= $price_max_condition;
    $count_query .= $price_max_condition;
    $query_params[':price_max'] = $price_max;
}

// Handle size range filter
if (isset($_GET['size_min']) && !empty($_GET['size_min'])) {
    $size_min = (float)$_GET['size_min'];
    $size_min_condition = " AND p.area >= :size_min";
    $base_query .= $size_min_condition;
    $count_query .= $size_min_condition;
    $query_params[':size_min'] = $size_min;
}

if (isset($_GET['size_max']) && !empty($_GET['size_max'])) {
    $size_max = (float)$_GET['size_max'];
    $size_max_condition = " AND p.area <= :size_max";
    $base_query .= $size_max_condition;
    $count_query .= $size_max_condition;
    $query_params[':size_max'] = $size_max;
}

// Handle bedrooms filter
if (isset($_GET['min_bedrooms']) && !empty($_GET['min_bedrooms'])) {
    $min_bedrooms = (int)$_GET['min_bedrooms'];
    $bedrooms_condition = " AND p.bedrooms >= :min_bedrooms";
    $base_query .= $bedrooms_condition;
    $count_query .= $bedrooms_condition;
    $query_params[':min_bedrooms'] = $min_bedrooms;
}

// Only show available properties by default (can be overridden with status filter)
if (!isset($_GET['status'])) {
    $status_condition = " AND p.status = 'available'";
    $base_query .= $status_condition;
    $count_query .= $status_condition;
} else {
    $status = sanitize($_GET['status']);
    $status_condition = " AND p.status = :status";
    $base_query .= $status_condition;
    $count_query .= $status_condition;
    $query_params[':status'] = $status;
}

// Add sorting
$base_query .= " ORDER BY p.featured DESC, p.created_at DESC";

// Add pagination
$base_query .= " LIMIT :offset, :per_page";
$query_params[':offset'] = $offset;
$query_params[':per_page'] = $per_page;

// Get total count for pagination
$db->query($count_query);
foreach ($query_params as $param => $value) {
    // Skip pagination parameters
    if ($param != ':offset' && $param != ':per_page') {
        $db->bind($param, $value);
    }
}
$total_count = $db->single()['total'];
$total_pages = ceil($total_count / $per_page);

// Get properties
$db->query($base_query);
foreach ($query_params as $param => $value) {
    $db->bind($param, $value);
}
$properties = $db->resultSet();

// Get property types for filter
$db->query("SELECT * FROM property_types ORDER BY name ASC");
$property_types = $db->resultSet();

// Include header
include "header.php";

// Helper function to format price in Indian currency format
function formatIndianPrice($price) {
    if ($price >= 10000000) { // 1 crore
        return round($price / 10000000, 2) . ' Cr';
    } elseif ($price >= 100000) { // 1 lakh
        return round($price / 100000, 2) . ' L';
    } else {
        return '₹' . number_format($price);
    }
}

// Helper function to build filter URLs
function buildFilterUrl($param, $value) {
    $params = $_GET;
    $params[$param] = $value;
    
    // Reset page when filter changes
    if ($param !== 'page') {
        $params['page'] = 1;
    }
    
    return '?' . http_build_query($params);
}

// Helper function to check if a filter is active
function isFilterActive($param, $value) {
    return isset($_GET[$param]) && $_GET[$param] == $value;
}

// Helper function to maintain current filters when changing page
function buildPaginationUrl($page_num) {
    $params = $_GET;
    $params['page'] = $page_num;
    return '?' . http_build_query($params);
}

// Helper function to get the correct image URL
function getPropertyImageUrl($image_path) {
    if (empty($image_path)) {
        return DEFAULT_IMAGE_URL;
    }
    
    // Check if the image path already contains the full URL
    if (strpos($image_path, 'http://') === 0 || strpos($image_path, 'https://') === 0) {
        return $image_path;
    }
    
    // Check if the image path has a leading slash
    if (strpos($image_path, '/') === 0) {
        $image_path = substr($image_path, 1);
    }
    
    // If the image path contains 'assets/images/properties/', extract just the filename
    if (strpos($image_path, 'assets/images/properties/') !== false) {
        $parts = explode('assets/images/properties/', $image_path);
        $image_path = end($parts);
    }
    
    // Return the full URL to the image
    return PROPERTY_IMAGES_URL . $image_path;
}
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>All Properties</h1>
        <p>Explore our extensive collection of premium real estate properties</p>
        <?php if ($search): ?>
        <div class="search-results-info">
            Showing results for: <strong><?php echo htmlspecialchars($search); ?></strong>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Search Section with Improved Filters -->
<section class="search-section">
    <div class="container">
        <div class="search-container">
            <form action="properties.php" method="GET" id="searchForm">
                <!-- Search Input -->
                <div class="search-input">
                    <input type="text" name="search" placeholder="Search by address, city or zip code" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    <button type="submit" class="search-btn">
                        Find Property
                    </button>
                </div>
                
                <!-- Filter Section Label -->
                <h3 class="search-filters-label">Filter Properties</h3>
                
                
                
                <!-- Search Filters -->
                <div class="search-filters">
                    <!-- Property Type Filter -->
                    <div class="filter-dropdown">
                        <button type="button" class="dropdown-btn" id="propertyTypeBtn">
                            <i class="fas fa-home"></i> Property Type <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content" id="propertyTypeDropdown">
                            <div class="filter-option">
                                <input type="radio" id="type-all" name="property_type" value="" 
                                       <?php echo empty($property_type) ? 'checked' : ''; ?>>
                                <label for="type-all">All Property Types</label>
                            </div>
                            <?php foreach ($property_types as $type): ?>
                            <div class="filter-option">
                                <input type="radio" id="type-<?php echo $type['id']; ?>" name="property_type" 
                                       value="<?php echo $type['id']; ?>" 
                                       <?php echo $property_type == $type['id'] ? 'checked' : ''; ?>>
                                <label for="type-<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Price Range Filter -->
                    <div class="filter-dropdown">
                        <button type="button" class="dropdown-btn" id="priceRangeBtn">
                            <i class="fas fa-rupee-sign"></i> Price Range <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content" id="priceRangeDropdown">
                            <div class="range-filter">
                                <div class="range-filter-title">Select Price Range</div>
                                <div class="range-inputs">
                                    <div class="range-group">
                                        <label for="price_min">Minimum Price (₹)</label>
                                        <input type="number" id="price_min" name="price_min" min="0" step="100000" 
                                               value="<?php echo $price_min ?? ''; ?>" placeholder="Min Price">
                                    </div>
                                    <div class="range-group">
                                        <label for="price_max">Maximum Price (₹)</label>
                                        <input type="number" id="price_max" name="price_max" min="0" step="100000" 
                                               value="<?php echo $price_max ?? ''; ?>" placeholder="Max Price">
                                    </div>
                                </div>
                                <div class="quick-ranges">
                                    <button type="button" class="quick-range-btn" data-min="0" data-max="5000000">Under 50L</button>
                                    <button type="button" class="quick-range-btn" data-min="5000000" data-max="10000000">50L - 1Cr</button>
                                    <button type="button" class="quick-range-btn" data-min="10000000" data-max="20000000">1Cr - 2Cr</button>
                                    <button type="button" class="quick-range-btn" data-min="20000000" data-max="">Above 2Cr</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Property Size Filter -->
                    <div class="filter-dropdown">
                        <button type="button" class="dropdown-btn" id="propertySizeBtn">
                            <i class="fas fa-ruler-combined"></i> Property Size <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content" id="propertySizeDropdown">
                            <div class="range-filter">
                                <div class="range-filter-title">Select Size Range</div>
                                <div class="range-inputs">
                                    <div class="range-group">
                                        <label for="size_min">Minimum Size (sq ft)</label>
                                        <input type="number" id="size_min" name="size_min" min="0" step="100" 
                                               value="<?php echo $size_min ?? ''; ?>" placeholder="Min Size">
                                    </div>
                                    <div class="range-group">
                                        <label for="size_max">Maximum Size (sq ft)</label>
                                        <input type="number" id="size_max" name="size_max" min="0" step="100" 
                                               value="<?php echo $size_max ?? ''; ?>" placeholder="Max Size">
                                    </div>
                                </div>
                                <div class="quick-ranges">
                                    <button type="button" class="quick-range-btn" data-min="0" data-max="1000">Under 1000 sq ft</button>
                                    <button type="button" class="quick-range-btn" data-min="1000" data-max="2000">1000 - 2000 sq ft</button>
                                    <button type="button" class="quick-range-btn" data-min="2000" data-max="3000">2000 - 3000 sq ft</button>
                                    <button type="button" class="quick-range-btn" data-min="3000" data-max="">Above 3000 sq ft</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- More Filters Dropdown (Optional) -->
                    <div class="filter-dropdown">
                        <button type="button" class="dropdown-btn" id="moreFiltersBtn">
                            <i class="fas fa-sliders-h"></i> More Filters(Eg: BHK) <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content" id="moreFiltersDropdown">
                            <!-- Bedrooms Filter -->
                            <div class="range-filter-title">Bedrooms</div>
                            <div class="quick-ranges">
                                <button type="button" class="quick-range-btn bedroom-btn" data-value="1">1+ BHK</button>
                                <button type="button" class="quick-range-btn bedroom-btn" data-value="2">2+ BHK</button>
                                <button type="button" class="quick-range-btn bedroom-btn" data-value="3">3+ BHK</button>
                                <button type="button" class="quick-range-btn bedroom-btn" data-value="4">4+ BHK</button>
                                <button type="button" class="quick-range-btn bedroom-btn" data-value="5">5+ BHK</button>
                            </div>
                            <input type="hidden" name="min_bedrooms" id="min_bedrooms" value="<?php echo $min_bedrooms ?? ''; ?>">
                            
                            <!-- Property Status Filter -->
                            <div class="range-filter-title" style="margin-top: 15px;">Property Status</div>
                            <div class="filter-option">
                                <input type="checkbox" id="status-available" name="status[]" value="available" 
                                       <?php echo isset($status) && in_array('available', $status) ? 'checked' : ''; ?>>
                                <label for="status-available">Available</label>
                            </div>
                            <div class="filter-option">
                                <input type="checkbox" id="status-pending" name="status[]" value="pending" 
                                       <?php echo isset($status) && in_array('pending', $status) ? 'checked' : ''; ?>>
                                <label for="status-pending">Pending</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Action Buttons -->
                <div class="filter-actions">
                    <button type="submit" class="apply-filters-btn">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="properties.php" class="reset-filters-btn">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>


<!-- Properties Grid -->
<section class="properties-grid">
    <div class="container">
        <div class="properties-count">
            <span><?php echo $total_count; ?> Properties Found</span>
        </div>
        <div class="grid-container">
            <?php if (!empty($properties)): ?>
                <?php foreach ($properties as $property): ?>
                <div class="property-card">
                    <div class="property-images">
                        <img src="<?php echo getPropertyImageUrl($property['primary_image']); ?>" 
                             alt="<?php echo htmlspecialchars($property['title']); ?>">
                    </div>
                    
                    <?php if (!empty($property['status'])): ?>
                    <div class="property-status status-<?php echo strtolower($property['status']); ?>">
                        <?php echo ucfirst($property['status']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($property['featured']) && $property['featured'] == 1): ?>
                    <div class="property-featured"></div>
                    <?php endif; ?>
                    
                    <div class="property-info">
                        <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                        <div class="property-price"><?php echo formatIndianPrice($property['price']); ?></div>
                        <div class="property-details-row">
                            <?php if (!empty($property['bedrooms'])): ?>
                            <span><i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> Beds</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($property['bathrooms'])): ?>
                            <span><i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> Baths</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($property['area'])): ?>
                            <span><i class="fas fa-ruler-combined"></i> <?php echo $property['area']; ?> <?php echo $property['area_unit']; ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="property-location">
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['address']); ?>
                        </p>
                        <a href="property-details.php?id=<?php echo $property['id']; ?>" class="view-details-btn">View Details</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-properties-found">
                    <p>No properties found matching your criteria. Try adjusting your filters.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="<?php echo buildPaginationUrl($page - 1); ?>" class="prev"><i class="fas fa-chevron-left"></i> Previous</a>
            <?php endif; ?>
            
            <?php
            // Show a limited number of pagination links
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            if ($start_page > 1) {
                echo '<a href="' . buildPaginationUrl(1) . '">1</a>';
                if ($start_page > 2) {
                    echo '<span class="pagination-dots">...</span>';
                }
            }
            
            for ($i = $start_page; $i <= $end_page; $i++) {
                echo '<a href="' . buildPaginationUrl($i) . '"' . ($i == $page ? ' class="active"' : '') . '>' . $i . '</a>';
            }
            
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                    echo '<span class="pagination-dots">...</span>';
                }
                echo '<a href="' . buildPaginationUrl($total_pages) . '">' . $total_pages . '</a>';
            }
            ?>
            
            <?php if ($page < $total_pages): ?>
            <a href="<?php echo buildPaginationUrl($page + 1); ?>" class="next">Next <i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- JavaScript for Filter Functionality -->
<script>
   // JavaScript to update dropdown button text when filters are selected
document.addEventListener('DOMContentLoaded', function() {
    // Dropdown toggles with improved UI feedback
    const dropdownBtns = document.querySelectorAll('.dropdown-btn');
    dropdownBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle active class for styling
            this.classList.toggle('active');
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-content').forEach(content => {
                if (content.id !== this.id.replace('Btn', 'Dropdown')) {
                    content.classList.remove('show');
                    const otherBtn = document.getElementById(content.id.replace('Dropdown', 'Btn'));
                    if (otherBtn) otherBtn.classList.remove('active');
                }
            });
            
            // Toggle this dropdown
            const dropdownContent = document.getElementById(this.id.replace('Btn', 'Dropdown'));
            if (dropdownContent) {
                dropdownContent.classList.toggle('show');
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.matches('.dropdown-btn') && 
            !event.target.matches('.dropdown-btn *') && 
            !event.target.matches('.dropdown-content') && 
            !event.target.closest('.dropdown-content')) {
            
            document.querySelectorAll('.dropdown-content').forEach(content => {
                content.classList.remove('show');
            });
            
            document.querySelectorAll('.dropdown-btn').forEach(btn => {
                btn.classList.remove('active');
            });
        }
    });
    
    // Property type radio buttons - update button text
    document.querySelectorAll('input[name="property_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const propertyTypeBtn = document.getElementById('propertyTypeBtn');
            if (this.value === '') {
                propertyTypeBtn.innerHTML = '<i class="fas fa-home"></i> Property Type <i class="fas fa-chevron-down"></i>';
            } else {
                const label = document.querySelector('label[for="type-' + this.value + '"]').textContent;
                propertyTypeBtn.innerHTML = '<i class="fas fa-home"></i> ' + label + ' <i class="fas fa-chevron-down"></i>';
            }
            
            // Close the dropdown
            document.getElementById('propertyTypeDropdown').classList.remove('show');
            propertyTypeBtn.classList.remove('active');
        });
    });
    
    // Quick range buttons for price - update button text
    const priceRangeBtns = document.querySelectorAll('#priceRangeDropdown .quick-range-btn');
    priceRangeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons in this group
            this.closest('.quick-ranges').querySelectorAll('.quick-range-btn').forEach(b => {
                b.classList.remove('active');
            });
            
            // Add active class to this button
            this.classList.add('active');
            
            const min = this.getAttribute('data-min');
            const max = this.getAttribute('data-max');
            
            // Update input values
            const minInput = document.getElementById('price_min');
            const maxInput = document.getElementById('price_max');
            
            if (min) minInput.value = min;
            if (max) maxInput.value = max;
            
            // Update button text
            const priceRangeBtn = document.getElementById('priceRangeBtn');
            priceRangeBtn.innerHTML = '<i class="fas fa-rupee-sign"></i> ' + this.textContent + ' <i class="fas fa-chevron-down"></i>';
            
            // Close the dropdown
            document.getElementById('priceRangeDropdown').classList.remove('show');
            priceRangeBtn.classList.remove('active');
        });
    });
    
    // Custom price range inputs - update button text when user enters values
    const priceInputs = document.querySelectorAll('#price_min, #price_max');
    priceInputs.forEach(input => {
        input.addEventListener('change', function() {
            updatePriceButtonText();
        });
        
        input.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                updatePriceButtonText();
                // Close the dropdown
                document.getElementById('priceRangeDropdown').classList.remove('show');
                document.getElementById('priceRangeBtn').classList.remove('active');
            }
        });
    });
    
    function updatePriceButtonText() {
        const minValue = document.getElementById('price_min').value;
        const maxValue = document.getElementById('price_max').value;
        const priceRangeBtn = document.getElementById('priceRangeBtn');
        
        if (minValue && maxValue) {
            priceRangeBtn.innerHTML = '<i class="fas fa-rupee-sign"></i> ₹' + formatIndianPrice(minValue) + ' - ₹' + formatIndianPrice(maxValue) + ' <i class="fas fa-chevron-down"></i>';
        } else if (minValue) {
            priceRangeBtn.innerHTML = '<i class="fas fa-rupee-sign"></i> Min ₹' + formatIndianPrice(minValue) + ' <i class="fas fa-chevron-down"></i>';
        } else if (maxValue) {
            priceRangeBtn.innerHTML = '<i class="fas fa-rupee-sign"></i> Max ₹' + formatIndianPrice(maxValue) + ' <i class="fas fa-chevron-down"></i>';
        } else {
            priceRangeBtn.innerHTML = '<i class="fas fa-rupee-sign"></i> Price Range <i class="fas fa-chevron-down"></i>';
        }
    }
    
    // Quick range buttons for size - update button text
    const sizeRangeBtns = document.querySelectorAll('#propertySizeDropdown .quick-range-btn');
    sizeRangeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons in this group
            this.closest('.quick-ranges').querySelectorAll('.quick-range-btn').forEach(b => {
                b.classList.remove('active');
            });
            
            // Add active class to this button
            this.classList.add('active');
            
            const min = this.getAttribute('data-min');
            const max = this.getAttribute('data-max');
            
            // Update input values
            const minInput = document.getElementById('size_min');
            const maxInput = document.getElementById('size_max');
            
            if (min) minInput.value = min;
            if (max) maxInput.value = max;
            
            // Update button text
            const propertySizeBtn = document.getElementById('propertySizeBtn');
            propertySizeBtn.innerHTML = '<i class="fas fa-ruler-combined"></i> ' + this.textContent + ' <i class="fas fa-chevron-down"></i>';
            
            // Close the dropdown
            document.getElementById('propertySizeDropdown').classList.remove('show');
            propertySizeBtn.classList.remove('active');
        });
    });
    
    // Custom size range inputs - update button text when user enters values
    const sizeInputs = document.querySelectorAll('#size_min, #size_max');
    sizeInputs.forEach(input => {
        input.addEventListener('change', function() {
            updateSizeButtonText();
        });
        
        input.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                updateSizeButtonText();
                // Close the dropdown
                document.getElementById('propertySizeDropdown').classList.remove('show');
                document.getElementById('propertySizeBtn').classList.remove('active');
            }
        });
    });
    
    function updateSizeButtonText() {
        const minValue = document.getElementById('size_min').value;
        const maxValue = document.getElementById('size_max').value;
        const propertySizeBtn = document.getElementById('propertySizeBtn');
        
        if (minValue && maxValue) {
            propertySizeBtn.innerHTML = '<i class="fas fa-ruler-combined"></i> ' + minValue + ' - ' + maxValue + ' sq ft <i class="fas fa-chevron-down"></i>';
        } else if (minValue) {
            propertySizeBtn.innerHTML = '<i class="fas fa-ruler-combined"></i> Min ' + minValue + ' sq ft <i class="fas fa-chevron-down"></i>';
        } else if (maxValue) {
            propertySizeBtn.innerHTML = '<i class="fas fa-ruler-combined"></i> Max ' + maxValue + ' sq ft <i class="fas fa-chevron-down"></i>';
        } else {
            propertySizeBtn.innerHTML = '<i class="fas fa-ruler-combined"></i> Property Size <i class="fas fa-chevron-down"></i>';
        }
    }
    
    // Bedroom buttons - update More Filters button text
    const bedroomBtns = document.querySelectorAll('.bedroom-btn');
    bedroomBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all bedroom buttons
            bedroomBtns.forEach(b => b.classList.remove('active'));
            
            // Add active class to this button
            this.classList.add('active');
            
            // Set the hidden input value
            document.getElementById('min_bedrooms').value = this.getAttribute('data-value');
            
            // Update the More Filters button text
            updateMoreFiltersButtonText();
        });
    });
    
    // Property status checkboxes - update More Filters button text
    document.querySelectorAll('input[name="status[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateMoreFiltersButtonText();
        });
    });
    
    function updateMoreFiltersButtonText() {
        const moreFiltersBtn = document.getElementById('moreFiltersBtn');
        const selectedFilters = [];
        
        // Check bedrooms
        const minBedrooms = document.getElementById('min_bedrooms').value;
        if (minBedrooms) {
            selectedFilters.push(minBedrooms + '+ BHK');
        }
        
        // Check status
        const statusCheckboxes = document.querySelectorAll('input[name="status[]"]:checked');
        if (statusCheckboxes.length > 0) {
            const statusLabels = [];
            statusCheckboxes.forEach(checkbox => {
                statusLabels.push(checkbox.value.charAt(0).toUpperCase() + checkbox.value.slice(1));
            });
            
            if (statusLabels.length === 1) {
                selectedFilters.push(statusLabels[0]);
            } else {
                selectedFilters.push(statusLabels.length + ' Statuses');
            }
        }
        
        if (selectedFilters.length > 0) {
            moreFiltersBtn.innerHTML = '<i class="fas fa-sliders-h"></i> ' + selectedFilters.join(', ') + ' <i class="fas fa-chevron-down"></i>';
        } else {
            moreFiltersBtn.innerHTML = '<i class="fas fa-sliders-h"></i> More Filters <i class="fas fa-chevron-down"></i>';
        }
    }
    
    // Initialize button texts based on current values
    function initializeButtonTexts() {
        // Property Type
        const selectedPropertyType = document.querySelector('input[name="property_type"]:checked');
        if (selectedPropertyType && selectedPropertyType.value !== '') {
            const label = document.querySelector('label[for="type-' + selectedPropertyType.value + '"]').textContent;
            document.getElementById('propertyTypeBtn').innerHTML = '<i class="fas fa-home"></i> ' + label + ' <i class="fas fa-chevron-down"></i>';
        }
        
        // Price Range
        updatePriceButtonText();
        
        // Size Range
        updateSizeButtonText();
        
        // More Filters
        updateMoreFiltersButtonText();
    }
    
    // Helper function to format price for display
    function formatIndianPrice(price) {
        price = parseInt(price);
        if (price >= 10000000) { // 1 crore
            return (price / 10000000).toFixed(2) + ' Cr';
        } else if (price >= 100000) { // 1 lakh
            return (price / 100000).toFixed(2) + ' L';
        } else {
            return new Intl.NumberFormat('en-IN').format(price);
        }
    }
    
    // Initialize with current values
    initializeButtonTexts();
    
    // Initialize filter tags with remove functionality
    document.querySelectorAll('.filter-tag-remove').forEach(btn => {
        btn.addEventListener('click', function() {
            const filterType = this.getAttribute('data-filter');
            if (filterType) {
                if (filterType === 'property_type') {
                    const input = document.querySelector('input[name="property_type"][value=""]');
                    if (input) {
                        input.checked = true;
                        document.getElementById('propertyTypeBtn').innerHTML = '<i class="fas fa-home"></i> Property Type <i class="fas fa-chevron-down"></i>';
                    }
                } else if (filterType === 'price') {
                    document.getElementById('price_min').value = '';
                    document.getElementById('price_max').value = '';
                    document.getElementById('priceRangeBtn').innerHTML = '<i class="fas fa-rupee-sign"></i> Price Range <i class="fas fa-chevron-down"></i>';
                } else if (filterType === 'size') {
                    document.getElementById('size_min').value = '';
                    document.getElementById('size_max').value = '';
                    document.getElementById('propertySizeBtn').innerHTML = '<i class="fas fa-ruler-combined"></i> Property Size <i class="fas fa-chevron-down"></i>';
                } else if (filterType === 'bedrooms') {
                    document.getElementById('min_bedrooms').value = '';
                    updateMoreFiltersButtonText();
                } else if (filterType.startsWith('status-')) {
                    const statusValue = filterType.replace('status-', '');
                    const input = document.querySelector(`input[name="status[]"][value="${statusValue}"]`);
                    if (input) {
                        input.checked = false;
                        updateMoreFiltersButtonText();
                    }
                }
                
                // Submit form after removing filter
                document.getElementById('searchForm').submit();
            }
        });
    });
});
</script>
<style>
    /* 
    * Consolidated and Fixed Filter Styles 
    * Resolves dropdown display issues
    */

    /* Search Section */
    .search-section {
        background-color: #f5f8fa;
        padding: 35px 0;
        margin-bottom: 35px;
        background-image: linear-gradient(to bottom, #e6eef1, #f5f8fa);
    }

    .search-container {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        padding: 30px;
        transition: box-shadow 0.3s ease;
    }

    .search-container:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    }

    /* Search Input */
    .search-input {
        display: flex;
        margin-bottom: 25px;
        position: relative;
    }

    .search-input input {
        flex: 1;
        padding: 14px 20px 14px 40px;
        border: 2px solid #e0e6ed;
        border-radius: 8px 0 0 8px;
        font-size: 16px;
        transition: all 0.3s ease;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .search-input input:focus {
        border-color: #3498db;
        box-shadow: inset 0 1px 3px rgba(52, 152, 219, 0.2);
        outline: none;
    }

    .search-input::before {
        content: "\f002";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #7f8c8d;
        z-index: 1;
        pointer-events: none;
    }

    .search-btn {
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 0 8px 8px 0;
        padding: 0 25px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(52, 152, 219, 0.3);
    }

    .search-btn:hover {
        background-color: #2980b9;
        box-shadow: 0 4px 8px rgba(52, 152, 219, 0.4);
        transform: translateY(-1px);
    }

    .search-btn:active {
        transform: translateY(1px);
        box-shadow: 0 1px 3px rgba(52, 152, 219, 0.3);
    }

    /* Filter Section */
    .search-filters-label {
        display: block;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #34495e;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .search-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: center;
        margin-bottom: 10px;
        position: relative;
    }

    .search-filters:after {
        content: "";
        display: block;
        width: 100%;
        margin-top: 20px;
    }

    /* Filter Dropdowns - FIXED VERSION */
    .filter-dropdown {
        position: relative;
        display: inline-block;
        margin-bottom: 8px;
        flex: 1;
        min-width: 200px;
    }

    .dropdown-btn {
        background-color: #ffffff;
        border: 2px solid #e0e6ed;
        padding: 12px 18px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        width: 100%;
        color: #34495e;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .dropdown-btn:hover {
        border-color: #bdc3c7;
        background-color: #f9f9f9;
    }

    .dropdown-btn.active {
        border-color: #3498db;
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }

    .dropdown-btn i {
        color: #7f8c8d;
    }

    .dropdown-btn i.fa-chevron-down {
        margin-left: auto;
        transition: transform 0.3s ease;
    }

    .dropdown-btn.active i.fa-chevron-down {
        transform: rotate(180deg);
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 250px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        padding: 20px;
        z-index: 20;
        top: calc(100% + 10px);
        left: 0;
        border: 1px solid #e0e6ed;
        width: 100%;
    }

    .dropdown-content.show {
        display: block;
    }

    /* Dropdown Arrow */
    .dropdown-content:before {
        content: "";
        position: absolute;
        top: -8px;
        left: 20px;
        width: 16px;
        height: 16px;
        background-color: white;
        transform: rotate(45deg);
        border-top: 1px solid #e0e6ed;
        border-left: 1px solid #e0e6ed;
    }

    .filter-option {
        margin-bottom: 12px;
        display: flex;
        align-items: center;
    }

    .filter-option:last-child {
        margin-bottom: 0;
    }

    .filter-option input[type="radio"],
    .filter-option input[type="checkbox"] {
        margin-right: 10px;
        appearance: none;
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border: 2px solid #bdc3c7;
        border-radius: 50%;
        outline: none;
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
    }

    .filter-option input[type="checkbox"] {
        border-radius: 4px;
    }

    .filter-option input[type="radio"]:checked,
    .filter-option input[type="checkbox"]:checked {
        border-color: #3498db;
        background-color: #3498db;
    }

    .filter-option input[type="radio"]:checked:after,
    .filter-option input[type="checkbox"]:checked:after {
        content: "";
        position: absolute;
    }

    .filter-option input[type="radio"]:checked:after {
        width: 8px;
        height: 8px;
        background-color: white;
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .filter-option input[type="checkbox"]:checked:after {
        content: "✓";
        font-size: 12px;
        color: white;
        position: absolute;
        top: -1px;
        left: 3px;
    }

    .filter-option label {
        margin-left: 5px;
        font-size: 14px;
        color: #34495e;
        cursor: pointer;
    }

    /* Range Filter */
    .range-filter {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .range-filter-title {
        font-weight: 600;
        font-size: 15px;
        color: #34495e;
        margin-bottom: 10px;
    }

    .range-inputs {
        display: flex;
        gap: 12px;
    }

    .range-group {
        flex: 1;
    }

    .range-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        color: #7f8c8d;
    }

    .range-group input {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e0e6ed;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .range-group input:focus {
        border-color: #3498db;
        outline: none;
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }

    /* Quick Range Buttons */
    .quick-ranges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 5px;
    }

    .quick-range-btn {
        background-color: #f0f5fa;
        border: 1px solid #dbe4ee;
        border-radius: 20px;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #34495e;
    }

    .quick-range-btn:hover {
        background-color: #e4edf5;
        border-color: #bdc9d7;
    }

    .quick-range-btn.active {
        background-color: #3498db;
        color: white;
        border-color: #3498db;
    }

    /* Filter Action Buttons */
    .filter-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 25px;
        gap: 15px;
    }

    .apply-filters-btn {
        background-color: #27ae60;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 600;
        transition: all 0.3s ease;
        flex: 3;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 2px 5px rgba(39, 174, 96, 0.3);
    }

    .apply-filters-btn:hover {
        background-color: #219653;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(39, 174, 96, 0.4);
    }

    .apply-filters-btn:active {
        transform: translateY(1px);
        box-shadow: 0 1px 3px rgba(39, 174, 96, 0.3);
    }

    .reset-filters-btn {
        background-color: #f8f9fa;
        color: #e74c3c;
        border: 2px solid #e74c3c;
        border-radius: 8px;
        padding: 12px 20px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 600;
        transition: all 0.3s ease;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .reset-filters-btn:hover {
        background-color: #fff2f2;
        color: #c0392b;
        border-color: #c0392b;
    }

    .reset-filters-btn i {
        font-size: 14px;
    }

    /* Active Filter Tags */
    .active-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 15px 0;
    }

    .filter-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background-color: #e8f4fd;
        border: 1px solid #c5e2f9;
        border-radius: 20px;
        padding: 6px 14px;
        font-size: 14px;
        color: #2980b9;
    }

    .filter-tag-label {
        font-weight: 600;
    }

    .filter-tag-remove {
        background: none;
        border: none;
        color: #2980b9;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .filter-tag-remove:hover {
        background-color: #c5e2f9;
        color: #2c3e50;
    }

    /* Properties Grid */
    .properties-count {
        margin-bottom: 20px;
        font-size: 16px;
        color: #666;
    }


    .property-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
        position: relative;
    }

    .property-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }



    .property-card:hover .property-images img {
        transform: scale(1.05);
    }

    .property-info {
        padding: 20px;
    }

    .property-info h3 {
        margin: 0 0 10px;
        font-size: 18px;
        line-height: 1.3;
    }

    .property-price {
        color: #27ae60;
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 15px;
    }

    .property-details-row {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        margin-bottom: 15px;
        font-size: 14px;
        color: #777;
    }

    .property-details-row span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .property-location {
        color: #777;
        font-size: 14px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .view-details-btn {
        display: block;
        background-color: #2c3e50;
        color: white;
        text-align: center;
        padding: 10px;
        border-radius: 4px;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .view-details-btn:hover {
        background-color: #1a252f;
    }

    /* Property Status Indicators */
    .property-status {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 5;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        color: white;
    }

    .status-available {
        background-color: #27ae60;
    }

    .status-sold {
        background-color: #e74c3c;
    }

    .status-pending {
        background-color: #f39c12;
    }

    .status-rented {
        background-color: #3498db;
    }

    /* Featured Property Indicator */
    .property-featured {
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        overflow: hidden;
        z-index: 1;
    }

    .property-featured::before {
        content: 'Featured';
        position: absolute;
        display: block;
        width: 150px;
        padding: 5px 0;
        background-color: #e74c3c;
        color: #fff;
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        right: -35px;
        top: 30px;
        transform: rotate(45deg);
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 30px;
    }

    .pagination a, .pagination-dots {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-width: 40px;
        height: 40px;
        margin: 0 5px;
        color: #333;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    .pagination a:not(.prev):not(.next) {
        background-color: #f5f8fa;
        border: 1px solid #ddd;
    }

    .pagination a.active {
        background-color: #2c3e50;
        color: white;
    }

    .pagination a:hover:not(.active) {
        background-color: #e5e5e5;
    }

    .pagination .prev, .pagination .next {
        background-color: transparent;
        font-weight: bold;
    }

    .pagination i {
        margin: 0 5px;
    }



    .search-results-info {
        margin-top: 10px;
        font-size: 16px;
        color: #666;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .search-input {
            flex-direction: column;
        }
        
        .search-input input {
            border-radius: 8px;
            margin-bottom: 12px;
        }
        
        .search-btn {
            border-radius: 8px;
            width: 100%;
            padding: 14px;
        }
        
        .search-filters {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-dropdown {
            width: 100%;
            min-width: 100%;
        }
        
        .dropdown-content {
            width: 100%;
            position: relative;
            top: 10px;
            left: 0;
            right: 0;
        }
        
        .dropdown-content:before {
            display: none;
        }
        
        .range-inputs {
            flex-direction: column;
        }
        
        .filter-actions {
            flex-direction: column;
        }
        
        .apply-filters-btn, .reset-filters-btn {
            width: 100%;
        }
        
        .active-filters {
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-tag {
            width: 100%;
            justify-content: space-between;
        }
    }
    /* Updated Property Card Styles with Fixed Image Size */

/* Property Card Container */
.property-card {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* Fixed Size Property Image Container */
.property-images {
    height: 220px; /* Fixed height */
    overflow: hidden;
    position: relative;
    flex-shrink: 0; /* Prevent image container from shrinking */
    background-color: #f8f8f8; /* Background color for images with transparency */
}

/* Image Fit and Positioning */
.property-images img {
    width: 100%;
    height: 100%;
    object-fit: contain; /* Changed from 'cover' to 'contain' to show full image */
    transition: transform 0.3s ease;
    position: relative;
    z-index: 1;
}

/* Add subtle gradient overlay to bottom of image for better text contrast */
.property-images::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 50px;
    background: linear-gradient(to top, rgba(0,0,0,0.2), transparent);
    z-index: 2;
    pointer-events: none;
}

.property-card:hover .property-images img {
    transform: scale(1.05);
}

/* Property Information */
.property-info {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1; /* Allow info section to grow and fill space */
}

.property-info h3 {
    margin: 0 0 10px;
    font-size: 18px;
    line-height: 1.3;
    color: #2c3e50;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    height: 2.6em; /* Fixed height for title to ensure alignment */
}

.property-price {
    color: #27ae60;
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 15px;
}

/* Property Details Row */
.property-details-row {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-bottom: 15px;
    font-size: 14px;
    color: #777;
    gap: 8px;
}

.property-details-row span {
    display: flex;
    align-items: center;
    gap: 5px;
    flex: 1;
    min-width: 80px;
}

.property-details-row span i {
    color: #3498db;
    font-size: 16px;
    width: 16px;
    text-align: center;
}

/* Property Location */
.property-location {
    color: #777;
    font-size: 14px;
    margin-bottom: 15px;
    display: flex;
    align-items: flex-start;
    gap: 5px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.property-location i {
    color: #e74c3c;
    flex-shrink: 0;
    margin-top: 2px;
}

/* View Details Button */
.view-details-btn {
    display: block;
    background-color: #2c3e50;
    color: white;
    text-align: center;
    padding: 12px;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 600;
    margin-top: auto; /* Push button to bottom */
    border: 2px solid transparent;
}

.view-details-btn:hover {
    background-color: #1a252f;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.view-details-btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Property Status Indicators */
.property-status {
    position: absolute;
    top: 15px;
    left: 15px;
    z-index: 5;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    color: white;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.status-available {
    background-color: #27ae60;
}

.status-sold {
    background-color: #e74c3c;
}

.status-pending {
    background-color: #f39c12;
}

.status-rented {
    background-color: #3498db;
}

/* Featured Property Indicator */
.property-featured {
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    overflow: hidden;
    z-index: 3;
}

.property-featured::before {
    content: 'Featured';
    position: absolute;
    display: block;
    width: 150px;
    padding: 6px 0;
    background-color: #e74c3c;
    color: #fff;
    font-size: 12px;
    font-weight: bold;
    text-align: center;
    right: -35px;
    top: 30px;
    transform: rotate(45deg);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

/* Loading Placeholder for Images */
.property-images.loading {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
}

.property-images.loading::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #f5f5f5;
    z-index: 0;
}

.property-images.loading::after {
    content: '';
    width: 40px;
    height: 40px;
    border: 4px solid #ddd;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    z-index: 1;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive Adjustments */
@media (max-width: 992px) {
    .grid-container {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
}

@media (max-width: 768px) {
    .property-images {
        height: 200px;
    }
    
    .property-info h3 {
        font-size: 16px;
    }
}

@media (max-width: 576px) {
    .property-images {
        height: 180px;
    }
    
    .property-details-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .property-details-row span {
        min-width: 100%;
    }
}

/* No Properties Found Message */
.no-properties-found {
    grid-column: 1 / -1;
    text-align: center;
    padding: 50px 20px;
    color: #777;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.no-properties-found p {
    font-size: 18px;
    margin-bottom: 20px;
}
</style>
<?php include 'footer.php'?>