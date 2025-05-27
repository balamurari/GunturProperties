<?php
/**
 * Properties Listing Page with Single Line Filters
 * Display all properties with search and filter functionality
 */
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'Properties';

// Get database connection
$db = new Database();

// Get all property types for filter dropdown
$db->query("SELECT * FROM property_types ORDER BY name ASC");
$property_types = $db->resultSet();

// Initialize search parameters
$search_params = [
    'keyword' => $_GET['keyword'] ?? '',
    'type_id' => $_GET['type_id'] ?? '',
    'price_range' => $_GET['price_range'] ?? '',
    'area_range' => $_GET['area_range'] ?? '',
    'bedrooms' => $_GET['bedrooms'] ?? '',
    'bathrooms' => $_GET['bathrooms'] ?? '',
    'city' => $_GET['city'] ?? '',
    'status' => $_GET['status'] ?? '',
    'featured' => $_GET['featured'] ?? '',
    'facing' => $_GET['facing'] ?? '',
    'sort_by' => $_GET['sort_by'] ?? 'created_at',
    'sort_order' => $_GET['sort_order'] ?? 'DESC'
];

// Debug: Log all search parameters
error_log("=== SEARCH DEBUG ===");
error_log("All GET parameters: " . print_r($_GET, true));
error_log("Search parameters: " . print_r($search_params, true));

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$properties_per_page = 12;
$offset = ($page - 1) * $properties_per_page;

// Build WHERE clause based on search parameters
$where_conditions = [];
$bind_params = [];

// Status filter - if no status selected, show buy and rent properties
if (!empty($search_params['status'])) {
    if ($search_params['status'] == 'buy') {
        $where_conditions[] = "p.status IN ('buy')";
    } elseif ($search_params['status'] == 'rent') {
        $where_conditions[] = "p.status IN ('rent')";
    } else {
        $where_conditions[] = "p.status = :status";
        $bind_params[':status'] = $search_params['status'];
    }
} else {
    $where_conditions[] = "p.status IN ('buy', 'rent')";
}

// Title search
if (!empty($search_params['keyword'])) {
    $search_keyword = trim($search_params['keyword']);
    $where_conditions[] = "p.title LIKE :keyword";
    $bind_params[':keyword'] = '%' . $search_keyword . '%';
}

// Property type filter
if (!empty($search_params['type_id'])) {
    $where_conditions[] = "p.type_id = :type_id";
    $bind_params[':type_id'] = $search_params['type_id'];
}

// Price range filter
if (!empty($search_params['price_range'])) {
    switch ($search_params['price_range']) {
        case 'below_30l':
            $where_conditions[] = "p.price < 3000000";
            break;
        case '30l_to_60l':
            $where_conditions[] = "p.price >= 3000000 AND p.price <= 6000000";
            break;
        case '60l_to_1cr':
            $where_conditions[] = "p.price >= 6000000 AND p.price <= 10000000";
            break;
        case 'above_1cr':
            $where_conditions[] = "p.price > 10000000";
            break;
    }
}

// Area range filter
if (!empty($search_params['area_range'])) {
    switch ($search_params['area_range']) {
        case 'below_500':
            $where_conditions[] = "p.area < 500";
            break;
        case '500_to_1000':
            $where_conditions[] = "p.area >= 500 AND p.area <= 1000";
            break;
        case '1000_to_2000':
            $where_conditions[] = "p.area >= 1000 AND p.area <= 2000";
            break;
        case '2000_to_5000':
            $where_conditions[] = "p.area >= 2000 AND p.area <= 5000";
            break;
        case 'above_5000':
            $where_conditions[] = "p.area > 5000";
            break;
    }
}

// Bedrooms filter
if (!empty($search_params['bedrooms'])) {
    if ($search_params['bedrooms'] === '4+') {
        $where_conditions[] = "p.bedrooms >= 4";
    } else {
        $where_conditions[] = "p.bedrooms = :bedrooms";
        $bind_params[':bedrooms'] = $search_params['bedrooms'];
    }
}

// Bathrooms filter
if (!empty($search_params['bathrooms'])) {
    if ($search_params['bathrooms'] === '3+') {
        $where_conditions[] = "p.bathrooms >= 3";
    } else {
        $where_conditions[] = "p.bathrooms = :bathrooms";
        $bind_params[':bathrooms'] = $search_params['bathrooms'];
    }
}

// City filter
if (!empty($search_params['city'])) {
    $where_conditions[] = "p.city LIKE :city";
    $bind_params[':city'] = '%' . $search_params['city'] . '%';
}

// Featured filter
if (!empty($search_params['featured']) && $search_params['featured'] == '1') {
    $where_conditions[] = "p.featured = 1";
}

// Facing filter
if (!empty($search_params['facing'])) {
    $where_conditions[] = "p.facing = :facing";
    $bind_params[':facing'] = $search_params['facing'];
}

// Build the complete WHERE clause
$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Validate sort parameters
$valid_sort_columns = ['created_at', 'price', 'title', 'bedrooms', 'area'];
$valid_sort_orders = ['ASC', 'DESC'];

if (!in_array($search_params['sort_by'], $valid_sort_columns)) {
    $search_params['sort_by'] = 'created_at';
}
if (!in_array($search_params['sort_order'], $valid_sort_orders)) {
    $search_params['sort_order'] = 'DESC';
}

$order_clause = "ORDER BY p.featured DESC, p.{$search_params['sort_by']} {$search_params['sort_order']}";

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total 
                FROM properties p 
                LEFT JOIN property_types pt ON p.type_id = pt.id 
                $where_clause";

$db->query($count_query);
foreach ($bind_params as $param => $value) {
    $db->bind($param, $value);
}

$count_result = $db->single();
$total_properties = 0;

if ($count_result && is_array($count_result) && isset($count_result['total'])) {
    $total_properties = (int)$count_result['total'];
}

$total_pages = $total_properties > 0 ? ceil($total_properties / $properties_per_page) : 1;

// Get properties with pagination
$properties_query = "SELECT p.*, pt.name as property_type,
                     (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                     (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count
                     FROM properties p 
                     LEFT JOIN property_types pt ON p.type_id = pt.id 
                     $where_clause 
                     $order_clause 
                     LIMIT :limit OFFSET :offset";

$db->query($properties_query);
foreach ($bind_params as $param => $value) {
    $db->bind($param, $value);
}
$db->bind(':limit', $properties_per_page);
$db->bind(':offset', $offset);

try {
    $properties = $db->resultSet();
    if (!$properties) {
        $properties = [];
    }
} catch (Exception $e) {
    $properties = [];
    error_log("Properties query error: " . $e->getMessage());
}

// Get unique cities for filter dropdown
try {
    $db->query("SELECT DISTINCT city FROM properties WHERE city IS NOT NULL AND city != '' ORDER BY city ASC");
    $cities_result = $db->resultSet();
    $cities = $cities_result ? $cities_result : [];
} catch (Exception $e) {
    $cities = [];
    error_log("Cities query error: " . $e->getMessage());
}

// Helper functions
function formatIndianPrice($price) {
    if ($price >= 10000000) {
        return '₹' . round($price / 10000000, 2) . ' Cr';
    } elseif ($price >= 100000) {
        return '₹' . round($price / 100000, 2) . ' L';
    } else {
        return '₹' . number_format($price);
    }
}


function getStatusInfo($status) {
    $status_info = [
        'buy' => ['text' => 'For Sale', 'class' => 'buy'],
        'rent' => ['text' => 'For Rent', 'class' => 'rent'],
        'pending' => ['text' => 'Pending', 'class' => 'pending'],
        'sold' => ['text' => 'Sold', 'class' => 'sold'],
        'rented' => ['text' => 'Rented', 'class' => 'rented']
    ];
    
    return $status_info[$status] ?? ['text' => ucfirst($status), 'class' => $status];
}

function buildPaginationUrl($search_params, $page) {
    $params = $search_params;
    $params['page'] = $page;
    
    $params = array_filter($params, function($value) {
        return $value !== '' && $value !== null;
    });
    
    return 'properties.php?' . http_build_query($params);
}

include "header.php";
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Properties</h1>
        <p>Find your perfect property from our extensive collection</p>
    </div>
</section>

<!-- Search Filters Section -->
<section class="search-filters-section" id="search-filters">
    <div class="container">
        <form class="search-filters-form" method="GET" action="properties.php">
            <div class="filters-wrapper">
                <div class="filters-container" id="filters-container">
                    <!-- Search Input -->
                    <div class="filter-group search-input">
                        <input type="text" name="keyword" placeholder="Search properties..." 
                               value="<?php echo htmlspecialchars($search_params['keyword']); ?>" class="form-control">
                    </div>
                    
                    <!-- Property Type -->
                    <div class="filter-group">
                        <select name="type_id" class="form-control">
                            <option value="">Property Type</option>
                            <?php foreach ($property_types as $type): ?>
                            <option value="<?php echo $type['id']; ?>" 
                                    <?php echo ($search_params['type_id'] == $type['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- City -->
                    <div class="filter-group">
                        <select name="city" class="form-control">
                            <option value="">City</option>
                            <?php foreach ($cities as $city): ?>
                            <option value="<?php echo htmlspecialchars($city['city']); ?>" 
                                    <?php echo ($search_params['city'] == $city['city']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($city['city']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Status -->
                    <div class="filter-group">
                        <select name="status" class="form-control">
                            <option value="">Buy / Rent</option>
                            <option value="buy" <?php echo ($search_params['status'] == 'buy') ? 'selected' : ''; ?>>For Sale</option>
                            <option value="rent" <?php echo ($search_params['status'] == 'rent') ? 'selected' : ''; ?>>For Rent</option>
                        </select>
                    </div>
                    
                    <!-- Bedrooms -->
                    <div class="filter-group">
                        <select name="bedrooms" class="form-control">
                            <option value="">BHK</option>
                            <option value="1" <?php echo ($search_params['bedrooms'] == '1') ? 'selected' : ''; ?>>1 BHK</option>
                            <option value="2" <?php echo ($search_params['bedrooms'] == '2') ? 'selected' : ''; ?>>2 BHK</option>
                            <option value="3" <?php echo ($search_params['bedrooms'] == '3') ? 'selected' : ''; ?>>3 BHK</option>
                            <option value="4+" <?php echo ($search_params['bedrooms'] == '4+') ? 'selected' : ''; ?>>4+ BHK</option>
                        </select>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="filter-group">
                        <select name="price_range" class="form-control">
                            <option value="">Price Range</option>
                            <option value="below_30l" <?php echo ($search_params['price_range'] == 'below_30l') ? 'selected' : ''; ?>>Below ₹30L</option>
                            <option value="30l_to_60l" <?php echo ($search_params['price_range'] == '30l_to_60l') ? 'selected' : ''; ?>>₹30L - ₹60L</option>
                            <option value="60l_to_1cr" <?php echo ($search_params['price_range'] == '60l_to_1cr') ? 'selected' : ''; ?>>₹60L - ₹1Cr</option>
                            <option value="above_1cr" <?php echo ($search_params['price_range'] == 'above_1cr') ? 'selected' : ''; ?>>Above ₹1Cr</option>
                        </select>
                    </div>
                    
                    <!-- Area Range -->
                    <div class="filter-group">
                        <select name="area_range" class="form-control">
                            <option value="">Area Range</option>
                            <option value="below_500" <?php echo ($search_params['area_range'] == 'below_500') ? 'selected' : ''; ?>>Below 500 sq ft</option>
                            <option value="500_to_1000" <?php echo ($search_params['area_range'] == '500_to_1000') ? 'selected' : ''; ?>>500-1000 sq ft</option>
                            <option value="1000_to_2000" <?php echo ($search_params['area_range'] == '1000_to_2000') ? 'selected' : ''; ?>>1000-2000 sq ft</option>
                            <option value="2000_to_5000" <?php echo ($search_params['area_range'] == '2000_to_5000') ? 'selected' : ''; ?>>2000-5000 sq ft</option>
                            <option value="above_5000" <?php echo ($search_params['area_range'] == 'above_5000') ? 'selected' : ''; ?>>Above 5000 sq ft</option>
                        </select>
                    </div>
                    
                    <!-- Facing -->
                    <div class="filter-group">
                        <select name="facing" class="form-control">
                            <option value="">Facing</option>
                            <option value="North" <?php echo ($search_params['facing'] == 'North') ? 'selected' : ''; ?>>North</option>
                            <option value="South" <?php echo ($search_params['facing'] == 'South') ? 'selected' : ''; ?>>South</option>
                            <option value="East" <?php echo ($search_params['facing'] == 'East') ? 'selected' : ''; ?>>East</option>
                            <option value="West" <?php echo ($search_params['facing'] == 'West') ? 'selected' : ''; ?>>West</option>
                            <option value="North-East" <?php echo ($search_params['facing'] == 'North-East') ? 'selected' : ''; ?>>North-East</option>
                            <option value="North-West" <?php echo ($search_params['facing'] == 'North-West') ? 'selected' : ''; ?>>North-West</option>
                            <option value="South-East" <?php echo ($search_params['facing'] == 'South-East') ? 'selected' : ''; ?>>South-East</option>
                            <option value="South-West" <?php echo ($search_params['facing'] == 'South-West') ? 'selected' : ''; ?>>South-West</option>
                        </select>
                    </div>
                    
                    <!-- Sort -->
                    <div class="filter-group">
                        <select name="sort_by" class="form-control" onchange="this.form.submit()">
                            <option value="created_at" <?php echo ($search_params['sort_by'] == 'created_at') ? 'selected' : ''; ?>>Latest First</option>
                            <option value="price" <?php echo ($search_params['sort_by'] == 'price') ? 'selected' : ''; ?>>Price</option>
                            <option value="area" <?php echo ($search_params['sort_by'] == 'area') ? 'selected' : ''; ?>>Area</option>
                            <option value="bedrooms" <?php echo ($search_params['sort_by'] == 'bedrooms') ? 'selected' : ''; ?>>BHK</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <select name="sort_order" class="form-control" onchange="this.form.submit()">
                            <option value="DESC" <?php echo ($search_params['sort_order'] == 'DESC') ? 'selected' : ''; ?>>High to Low Price</option>
                            <option value="ASC" <?php echo ($search_params['sort_order'] == 'ASC') ? 'selected' : ''; ?>>Low to High Price</option>
                        </select>
                    </div>
                </div>
                
                <!-- Action Buttons - Fixed on Right -->
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> <span class="btn-text">Search</span>
                    </button>
                    <a href="properties.php" class="btn btn-outline">
                        <i class="fas fa-redo"></i> <span class="btn-text">Reset</span>
                    </a>
                </div>
                 <!-- <div class="scroll-hint" id="scroll-hint">
                Scroll for more filters →
            </div> -->
            </div>
            
           
        </form>
        
    </div>
    
</section>


<!-- Properties Results Section -->
<section class="properties-results-section">
    <div class="container">
        <!-- Results Header -->
        <div class="results-header">
            <div class="results-info">
                <h3>
                    <?php if (!empty($search_params['keyword']) || !empty($search_params['type_id']) || !empty($search_params['city'])): ?>
                        Search Results
                    <?php else: ?>
                        All Properties
                    <?php endif; ?>
                </h3>
                <p>Showing <?php echo count($properties); ?> of <?php echo $total_properties; ?> properties
                <?php if ($page > 1): ?>
                    (Page <?php echo $page; ?> of <?php echo $total_pages; ?>)
                <?php endif; ?>
                </p>
            </div>
            
            <?php if (!empty($search_params['keyword'])): ?>
            <div class="search-summary">
                <span class="search-term">Search: "<?php echo htmlspecialchars($search_params['keyword']); ?>"</span>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (empty($properties)): ?>
        <!-- No Results -->
        <div class="no-results">
            <div class="no-results-content">
                <i class="fas fa-search"></i>
                <h3>No Properties Found</h3>
                <p>We couldn't find any properties matching your search criteria.</p>
                <p>Try adjusting your filters or <a href="properties.php">browse all properties</a>.</p>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Properties Grid -->
        <div class="properties-grid">
            <?php foreach ($properties as $property): ?>
            <div class="property-card">
                <?php if (!empty($property['featured']) && $property['featured'] == 1): ?>
                <div class="property-badge featured-badge">Featured</div>
                <?php endif; ?>
                
                <?php 
                $status_info = getStatusInfo($property['status']);
                ?>
                <div class="property-status status-<?php echo $status_info['class']; ?>">
                    <?php echo $status_info['text']; ?>
                </div>
                
                <div class="property-images">
                    <img src="<?php echo getPropertyImageUrl($property['primary_image']); ?>" 
                         alt="<?php echo htmlspecialchars($property['title']); ?>"
                         onerror="this.onerror=null; this.src='assets/images/no-image.jpg';">
                    
                    <?php if (!empty($property['image_count']) && $property['image_count'] > 1): ?>
                    <div class="image-count">
                        <i class="fas fa-camera"></i> <?php echo $property['image_count']; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="property-info">
                    <div class="property-header">
                        <h3 class="property-title">
                            <a href="property-details.php?id=<?php echo $property['id']; ?>">
                                <?php echo htmlspecialchars($property['title']); ?>
                            </a>
                        </h3>
                        <div class="property-price">
                            <?php echo formatIndianPrice($property['price']); ?>
                            <?php if ($property['status'] == 'rent'): ?>
                            <span class="rent-period">/ month</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <!-- <?php echo htmlspecialchars($property['address']); ?>,  -->
                        <?php echo htmlspecialchars($property['city']); ?>
                        <!-- <?php echo htmlspecialchars($property['state']); ?> -->
                    </div>
                    
                    <div class="property-details-row">
             
                        
                        <?php if (!empty($property['bedrooms'])): ?>
                        <div class="property-detail">
                            <i class="fas fa-bed"></i>
                            <?php echo $property['bedrooms']; ?> Bed<?php echo ($property['bedrooms'] > 1) ? 's' : ''; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($property['bathrooms'])): ?>
                        <div class="property-detail">
                            <i class="fas fa-bath"></i>
                            <?php echo $property['bathrooms']; ?> Bath<?php echo ($property['bathrooms'] > 1) ? 's' : ''; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($property['area'])): ?>
                        <div class="property-detail">
                            <i class="fas fa-ruler-combined"></i>
                            <?php echo $property['area']; ?> <?php echo $property['area_unit']; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($property['facing'])): ?>
                    <div class="property-facing">
                        <i class="fas fa-compass"></i>
                        <?php echo htmlspecialchars($property['facing']); ?> Facing
                    </div>
                    <?php endif; ?>
                    

                    
                    <div class="property-actions">
                        <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-primary view-details-btn">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    
                                <?php if ($property['status'] == 'rent'): ?>
                        <button class="btn btn-outline share-btn" data-property-id="<?php echo $property['id']; ?>" title="Share">
                            <i class="fas fa-share-alt"></i>
                        </button>
                        <?php else: ?>
                        <?php if (!empty($property['instagram_url'])): ?>
                        <a href="<?php echo htmlspecialchars($property['instagram_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline instagram-btn" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <?php endif; ?>
                        <?php endif; ?>
                       
                        
             
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <nav class="pagination-nav">
                <ul class="pagination">
                    <!-- Previous Page -->
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo buildPaginationUrl($search_params, $page - 1); ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo buildPaginationUrl($search_params, 1); ?>">1</a>
                    </li>
                    <?php if ($start_page > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo buildPaginationUrl($search_params, $i); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo buildPaginationUrl($search_params, $total_pages); ?>"><?php echo $total_pages; ?></a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Next Page -->
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo buildPaginationUrl($search_params, $page + 1); ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
</section>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sticky filters on scroll
    const filtersSection = document.getElementById('search-filters');
    const filtersTop = filtersSection.offsetTop;
    let isSticky = false;
    
    function handleScroll() {
        if (window.pageYOffset > filtersTop + 100) {
            if (!isSticky) {
                filtersSection.classList.add('sticky-filters');
                isSticky = true;
            }
        } else {
            if (isSticky) {
                filtersSection.classList.remove('sticky-filters');
                isSticky = false;
            }
        }
    }
    
    window.addEventListener('scroll', handleScroll);
    
    // Hide scroll hint after user scrolls
    const filtersContainer = document.getElementById('filters-container');
    const scrollHint = document.getElementById('scroll-hint');
    
    if (filtersContainer && scrollHint) {
        filtersContainer.addEventListener('scroll', function() {
            filtersContainer.classList.add('scrolled');
        }, { once: true });
        
        // Auto-hide scroll hint after 4 seconds
        setTimeout(() => {
            if (scrollHint) {
                scrollHint.style.opacity = '0';
            }
        }, 4000);
    }
    
    // Share functionality
    const shareBtns = document.querySelectorAll('.share-btn');
    shareBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const propertyId = this.getAttribute('data-property-id');
            shareProperty(propertyId);
        });
    });
});

function shareProperty(propertyId) {
    const url = window.location.origin + 'property-details.php?id=' + propertyId;
    
    if (navigator.share) {
        navigator.share({
            title: 'Check out this property',
            url: url
        });
    } else {
        navigator.clipboard.writeText(url).then(function() {
            alert('Property link copied to clipboard!');
        });
    }
}
</script>

<?php include 'footer.php'; ?>
<style>
 .property-actions {
        justify-content: flex-end;
        margin-top: 0.75rem;
    }    .property-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
        margin-bottom: 0.75rem;
    }/* Single Line Filters - Desktop & Mobile Responsive CSS */

/* Search Filters Section */
.search-filters-section {
    background: #f8f9fa;
    padding: 1rem 0;
    border-bottom: 1px solid #dee2e6;
    transition: all 0.3s ease;
    z-index: 100;
    position: relative;
}

.search-filters-section.sticky-filters {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: rgba(248, 249, 250, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 20px rgba(0,0,0,0.1);
    padding: 0.75rem 0;
    z-index: 1000;
}

.search-filters-form {
    background: white;
    padding: 1rem;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}

.sticky-filters .search-filters-form {
    padding: 0.75rem;
    border-radius: 8px;
}

/* Single Line Layout for All Devices */
.filters-wrapper {
    display: flex;
    gap: 1rem;
    align-items: center;
    position: relative;
}

.filters-container {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 0.25rem 0;
    flex: 1;
    margin-right: 1rem;
}

.filters-container::-webkit-scrollbar {
    display: none;
}

.filter-group {
    flex-shrink: 0;
    min-width: fit-content;
}

.filter-group.search-input {
    min-width: 200px;
    max-width: 250px;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
    position: relative;
    z-index: 2;
}

.form-control {
    border: 1px solid #007bff;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    background: #fff;
    transition: all 0.3s ease;
    width: 100%;
    min-width: 140px;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.15);
    outline: none;
}

.form-control::placeholder {
    color: #999;
}

.form-control option {
    padding: 0.5rem;
}

/* Action Buttons */
.filter-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.25rem;
    font-size: 0.9rem;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    border-radius: 8px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    gap: 0.5rem;
    min-width: fit-content;
}

.btn-primary {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.btn-primary:hover {
    background: #0056b3;
    border-color: #0056b3;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

.btn-outline {
    background: transparent;
    color: #007bff;
    border-color: #007bff;
}

.btn-outline:hover {
    background: #007bff;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.2);
}

/* Scroll Indicator */
.scroll-hint {
    position: absolute;
    right: 180px;
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(90deg, transparent, rgba(248,249,250,0.9));
    color: #007bff;
    padding: 0.5rem;
    font-size: 0.8rem;
    pointer-events: none;
    opacity: 0.7;
    border-radius: 4px;
    transition: opacity 0.3s ease;
}

.filters-container.scrolled .scroll-hint {
    opacity: 0;
}

/* Results Section */
.properties-results-section {
    padding: 3rem 0;
}

.sticky-filters ~ .properties-results-section {
    padding-top: 4rem;
}

@media (max-width: 768px) {
    .sticky-filters ~ .properties-results-section {
        padding-top: 5rem;
    }
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.results-info h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
    font-size: 1.5rem;
}

.results-info p {
    margin: 0;
    color: #666;
    font-size: 0.95rem;
}

.search-term {
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

/* Properties Grid */
.properties-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.property-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    cursor: pointer;
}

.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.property-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 2;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.featured-badge {
    background: linear-gradient(45deg, #ff6b6b, #feca57);
    color: white;
}

.property-status {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 2;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-buy {
    background: #d4edda;
    color: #155724;
}

.status-rent {
    background: #cce5ff;
    color: #004085;
}

.status-sold {
    background: #f8d7da;
    color: #721c24;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-rented {
    background: #e2e3e5;
    color: #383d41;
}

.property-images {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.property-images img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.property-card:hover .property-images img {
    transform: scale(1.05);
}

.image-count {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 15px;
    font-size: 0.8rem;
}

.property-info {
    padding: 1.5rem;
}

.property-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.property-title {
    margin: 0;
    font-size: 1.2rem;
    line-height: 1.3;
    flex: 1;
    color: #333;
    transition: color 0.3s ease;
}

.property-card:hover .property-title {
    color: #007bff;
}

.property-price {
    font-size: 1.3rem;
    font-weight: 700;
    color: #28a745;
    white-space: nowrap;
}

.property-location {
    color: #666;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.property-location i {
    color: #007bff;
    margin-top: 0.1rem;
    flex-shrink: 0;
}

.property-details-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}

.property-detail,
.property-type {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    color: #666;
}

.property-detail i,
.property-type i {
    margin-right: 0.25rem;
    color: #007bff;
    width: 14px;
    flex-shrink: 0;
}

.property-facing {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 1rem;
}

.property-facing i {
    margin-right: 0.5rem;
    color: #007bff;
}



.property-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    margin-top: 1rem;
}

.instagram-btn,
.share-btn {
    padding: 0.5rem;
    min-width: 45px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
}

.instagram-btn {
    background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);
    color: white;
    border: none;
}

.instagram-btn:hover {
    transform: translateY(-2px);
    color: white;
}

/* No Results */
.no-results {
    text-align: center;
    padding: 4rem 2rem;
}

.no-results-content i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 1rem;
}

.no-results-content h3 {
    color: #666;
    margin-bottom: 1rem;
}

.no-results-content p {
    color: #888;
    margin-bottom: 0.5rem;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 3rem;
}

.pagination {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: center;
}

.page-item .page-link {
    display: block;
    padding: 0.75rem 1rem;
    background: white;
    border: 1px solid #dee2e6;
    color: #007bff;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    min-width: 45px;
    text-align: center;
}

.page-item:hover .page-link:not(.disabled) {
    background: #007bff;
    color: white;
    border-color: #007bff;
    transform: translateY(-1px);
}

.page-item.active .page-link {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.page-item.disabled .page-link {
    color: #6c757d;
    background: #fff;
    border-color: #dee2e6;
    cursor: not-allowed;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .search-filters-section {
        padding: 0.75rem 0;
    }
    
    .search-filters-section.sticky-filters {
        padding: 0.5rem 0;
    }
    
    .search-filters-form {
        padding: 0.75rem;
        margin: 0 0.5rem;
    }
    
    .sticky-filters .search-filters-form {
        padding: 0.5rem;
    }
    
    .filters-wrapper {
        gap: 0.75rem;
    }
    
    .filters-container {
        gap: 0.5rem;
        margin-right: 0.75rem;
    }
    
    .filter-group.search-input {
        min-width: 150px;
        max-width: 180px;
    }
    
    .form-control {
        padding: 0.6rem 0.8rem;
        font-size: 0.85rem;
        min-width: 120px;
    }
    
    .btn {
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
    }
    
    .btn .btn-text {
        display: none;
    }
    
    .scroll-hint {
        right: 140px;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .properties-results-section {
        padding: 2rem 0;
    }
    
    .sticky-filters ~ .properties-results-section {
        padding-top: 5rem;
    }
    
    .results-header {
        flex-direction: column;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }
    
    .results-info h3 {
        font-size: 1.3rem;
    }
    
    .properties-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin: 0 0.5rem 3rem;
    }
    
    .property-card {
        border-radius: 10px;
    }
    
    .property-images {
        height: 180px;
    }
    
    .property-info {
        padding: 1rem 0.75rem;
    }
    
    .property-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
        margin-bottom: 0.75rem;
    }
    
    .property-title {
        font-size: 1rem;
        line-height: 1.2;
    }
    
    .property-price {
        font-size: 1rem;
        margin-top: 0.25rem;
    }
    
    .property-location {
        font-size: 0.8rem;
        margin-bottom: 0.75rem;
    }
    
    .property-details-row {
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }
    
    .property-detail,
    .property-type {
        font-size: 0.8rem;
    }
    
    .property-facing {
        font-size: 0.8rem;
        margin-bottom: 0.75rem;
    }
    
    .property-actions {
        justify-content: center;
        margin-top: 0.75rem;
    }
    
    .instagram-btn,
    .share-btn {
        display: flex;
        min-width: 35px;
        padding: 0.4rem;
    }
    
    .pagination {
        gap: 0.25rem;
        margin: 0 1rem;
    }
    
    .page-item .page-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
        min-width: 40px;
    }
}

@media (max-width: 480px) {
    .search-filters-form {
        margin: 0 0.25rem;
        padding: 0.5rem;
    }
    
    .filters-wrapper {
        gap: 0.5rem;
    }
    
    .filters-container {
        gap: 0.4rem;
        margin-right: 0.5rem;
    }
    
    .filter-group.search-input {
        min-width: 130px;
        max-width: 150px;
    }
    
    .form-control {
        padding: 0.5rem 0.7rem;
        font-size: 0.8rem;
        min-width: 100px;
    }
    
    .btn {
        padding: 0.5rem 0.8rem;
        font-size: 0.8rem;
    }
    
    .scroll-hint {
        right: 120px;
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
    
    .properties-grid {
        margin: 0 0.25rem 3rem;
        gap: 0.75rem;
    }
    
    .property-images {
        height: 160px;
    }
    
    .property-info {
        padding: 0.75rem 0.5rem;
    }
    
    .property-title {
        font-size: 0.9rem;
    }
    
    .property-price {
        font-size: 0.9rem;
    }
    
    .property-location {
        font-size: 0.75rem;
    }
    
    .property-detail,
    .property-type {
        font-size: 0.75rem;
    }
    
    .property-actions {
        margin-top: 0.5rem;
    }
    
    .instagram-btn,
    .share-btn {
        min-width: 30px;
        padding: 0.3rem;
    }
}

/* Medium Screens */
@media (min-width: 769px) and (max-width: 999px) {
    .properties-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }
    
    .property-images {
        height: 220px;
    }
    
    .property-title {
        font-size: 1.1rem;
    }
    
    .property-price {
        font-size: 1.1rem;
    }
}

/* Large Desktop */
@media (min-width: 1000px) and (max-width: 1199px) {
    .properties-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }
    
    .property-images {
        height: 210px;
    }
    
    .property-title {
        font-size: 1.1rem;
    }
    
    .property-price {
        font-size: 1.2rem;
    }
    
    .property-actions {
        justify-content: flex-end;
        margin-top: 0.75rem;
    }
}

/* Large Screens */
@media (min-width: 1200px) {
    .properties-grid {
        grid-template-columns: repeat(5, 1fr);
        gap: 1.5rem;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }
    
    .filter-group.search-input {
        min-width: 250px;
        max-width: 300px;
    }
    
    .property-images {
        height: 200px;
    }
    
    .property-title {
        font-size: 1.1rem;
    }
    
    .property-price {
        font-size: 1.2rem;
    }
}

/* Extra Large Screens */
@media (min-width: 1400px) {
    .properties-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
    }
    
    .property-images {
        height: 220px;
    }
    
    .property-title {
        font-size: 1.2rem;
    }
    
    .property-price {
        font-size: 1.3rem;
    }
}
    </style>