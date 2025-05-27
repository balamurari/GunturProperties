<?php
/**
 * COMPLETE UPDATED Homepage - Enhanced Search Implementation
 * All fixes applied: FULLTEXT search, Always show sections, AJAX ready
 * SWIPER FIXED VERSION
 */
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'Find Your Dream Home';

// IMPORTANT: Initialize database connection FIRST
$db = new Database();

// Enhanced search parameters - More comprehensive than before
$search_params = [
    'keyword' => $_GET['keyword'] ?? '',
    'type_id' => $_GET['type_id'] ?? '',
    'price_range' => $_GET['price_range'] ?? '',  // Changed from min/max to ranges
    'area_range' => $_GET['area_range'] ?? '',    // Added area ranges
    'bedrooms' => $_GET['bedrooms'] ?? '',
    'bathrooms' => $_GET['bathrooms'] ?? '',
    'city' => $_GET['city'] ?? '',
    'status' => $_GET['status'] ?? '',
    'featured' => $_GET['featured'] ?? '',
    'facing' => $_GET['facing'] ?? '',           // Added facing filter
    'sort_by' => $_GET['sort_by'] ?? 'created_at',
    'sort_order' => $_GET['sort_order'] ?? 'DESC'
];

// Enhanced search detection
$is_search = !empty($search_params['keyword']) || 
             !empty($search_params['type_id']) || 
             !empty($search_params['status']) || 
             !empty($search_params['city']) || 
             !empty($search_params['price_range']) ||
             !empty($search_params['area_range']) ||
             !empty($search_params['bedrooms']) ||
             !empty($search_params['bathrooms']) ||
             !empty($search_params['featured']) ||
             !empty($search_params['facing']);

// Initialize search results variables
$search_properties = [];
$total_search_results = 0;

// Get all property types for search filter - AFTER database initialization
try {
    $db->query("SELECT * FROM property_types ORDER BY name ASC");
    $types_result = $db->resultSet();
    $property_types = $types_result ? $types_result : [];
} catch (Exception $e) {
    $property_types = [];
    error_log("Property types query error: " . $e->getMessage());
}

// Get unique cities for filter dropdown - AFTER database initialization
try {
    $db->query("SELECT DISTINCT city, COUNT(*) as count FROM properties WHERE city IS NOT NULL AND city != '' AND status IN ('buy', 'rent') GROUP BY city ORDER BY count DESC, city ASC");
    $cities_result = $db->resultSet();
    $cities = $cities_result ? $cities_result : [];
} catch (Exception $e) {
    $cities = [];
    error_log("Cities query error: " . $e->getMessage());
}

// If search is performed, get enhanced search results
if ($is_search) {
    // Pagination for search results
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $properties_per_page = 12;
    $offset = ($page - 1) * $properties_per_page;
    
    // Enhanced WHERE clause building
    $where_conditions = [];
    $bind_params = [];
    
    // Status filter - Enhanced logic
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
    
    // FIXED: Enhanced FULL-TEXT SEARCH - Multiple fields with correct column names
    if (!empty($search_params['keyword'])) {
        $search_keyword = trim($search_params['keyword']);
        
        // Try full-text search first with correct column names (title, description, city)
        try {
            // Test if FULLTEXT works by running a simple query first
            $test_query = "SELECT COUNT(*) as test_count FROM properties WHERE MATCH(title, description, city) AGAINST(:test_keyword IN BOOLEAN MODE)";
            $db->query($test_query);
            $db->bind(':test_keyword', $search_keyword);
            $test_result = $db->single();
            
            // If no error thrown, FULLTEXT is working
            $where_conditions[] = "MATCH(p.title, p.description, p.city) AGAINST(:keyword_fulltext IN BOOLEAN MODE)";
            $bind_params[':keyword_fulltext'] = $search_keyword;
            
            error_log("Using FULLTEXT search for: " . $search_keyword);
            
        } catch (Exception $e) {
            // FIXED: Enhanced fallback LIKE search across ALL relevant fields
            $where_conditions[] = "(p.title LIKE :keyword OR p.description LIKE :keyword_desc OR p.address LIKE :keyword_addr OR p.city LIKE :keyword_city)";
            $bind_params[':keyword'] = '%' . $search_keyword . '%';
            $bind_params[':keyword_desc'] = '%' . $search_keyword . '%';
            $bind_params[':keyword_addr'] = '%' . $search_keyword . '%';
            $bind_params[':keyword_city'] = '%' . $search_keyword . '%';
            
            error_log("Using LIKE search for: " . $search_keyword . " Error: " . $e->getMessage());
        }
    }
    
    // Property type filter
    if (!empty($search_params['type_id'])) {
        $where_conditions[] = "p.type_id = :type_id";
        $bind_params[':type_id'] = $search_params['type_id'];
    }
    
    // Enhanced Price range filter (similar to properties.php)
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
    
    // Enhanced Area range filter
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
    
    // City filter - Enhanced with partial matching
    if (!empty($search_params['city'])) {
        $where_conditions[] = "p.city LIKE :city";
        $bind_params[':city'] = '%' . $search_params['city'] . '%';
    }
    
    // Enhanced Bedrooms filter
    if (!empty($search_params['bedrooms'])) {
        if ($search_params['bedrooms'] === '4+') {
            $where_conditions[] = "p.bedrooms >= 4";
        } else {
            $where_conditions[] = "p.bedrooms = :bedrooms";
            $bind_params[':bedrooms'] = $search_params['bedrooms'];
        }
    }
    
    // Enhanced Bathrooms filter  
    if (!empty($search_params['bathrooms'])) {
        if ($search_params['bathrooms'] === '3+') {
            $where_conditions[] = "p.bathrooms >= 3";
        } else {
            $where_conditions[] = "p.bathrooms = :bathrooms";
            $bind_params[':bathrooms'] = $search_params['bathrooms'];
        }
    }
    
    // Featured filter
    if (!empty($search_params['featured']) && $search_params['featured'] == '1') {
        $where_conditions[] = "p.featured = 1";
    }
    
    // Facing filter - NEW addition
    if (!empty($search_params['facing'])) {
        $where_conditions[] = "p.facing = :facing";
        $bind_params[':facing'] = $search_params['facing'];
    }
    
    // Build the complete WHERE clause
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Enhanced sort validation
    $valid_sort_columns = ['created_at', 'price', 'title', 'bedrooms', 'area', 'bathrooms'];
    $valid_sort_orders = ['ASC', 'DESC'];
    
    if (!in_array($search_params['sort_by'], $valid_sort_columns)) {
        $search_params['sort_by'] = 'created_at';
    }
    if (!in_array($search_params['sort_order'], $valid_sort_orders)) {
        $search_params['sort_order'] = 'DESC';
    }
    
    // Enhanced sort order - Featured first, then by selected criteria
    $order_clause = "ORDER BY p.featured DESC, p.{$search_params['sort_by']} {$search_params['sort_order']}";
    
    // Get total count for pagination
    try {
        $count_query = "SELECT COUNT(*) as total 
                        FROM properties p 
                        LEFT JOIN property_types pt ON p.type_id = pt.id 
                        $where_clause";
        
        $db->query($count_query);
        foreach ($bind_params as $param => $value) {
            $db->bind($param, $value);
        }
        
        $count_result = $db->single();
        $total_search_results = $count_result && is_array($count_result) ? (int)$count_result['total'] : 0;
    } catch (Exception $e) {
        $total_search_results = 0;
        error_log("Enhanced search count query error: " . $e->getMessage());
    }
    
    // Get enhanced search results
    try {
        $search_query = "SELECT p.*, pt.name as property_type,
                         (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                         (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count
                         FROM properties p 
                         LEFT JOIN property_types pt ON p.type_id = pt.id 
                         $where_clause 
                         $order_clause 
                         LIMIT :limit OFFSET :offset";
        
        $db->query($search_query);
        foreach ($bind_params as $param => $value) {
            $db->bind($param, $value);
        }
        $db->bind(':limit', $properties_per_page);
        $db->bind(':offset', $offset);
        
        $search_result = $db->resultSet();
        $search_properties = $search_result ? $search_result : [];
    } catch (Exception $e) {
        $search_properties = [];
        error_log("Enhanced search properties query error: " . $e->getMessage());
    }
    
    // DEBUG: Add temporary debugging
    if ($is_search) {
        error_log("=== SEARCH DEBUG ===");
        error_log("Search keyword: " . $search_params['keyword']);
        error_log("Where conditions: " . print_r($where_conditions, true));
        error_log("Bind params: " . print_r($bind_params, true));
        error_log("Total results: " . $total_search_results);
        error_log("Properties found: " . count($search_properties));
    }
}

// Get featured properties for hero carousel (top 5) - Always load regardless of search
try {
    $db->query("
        SELECT p.*, pt.name as property_type,
               (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
               (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count
        FROM properties p
        LEFT JOIN property_types pt ON p.type_id = pt.id
        WHERE p.featured = 1 AND p.status IN ('buy', 'rent')
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $hero_result = $db->resultSet();
    $hero_properties = $hero_result ? $hero_result : [];
} catch (Exception $e) {
    $hero_properties = [];
    error_log("Hero properties query error: " . $e->getMessage());
}

// Get featured properties for the main section (8 properties) - only if not searching
$featured_properties = [];
if (!$is_search) {
    try {
        $db->query("
            SELECT p.*, pt.name as property_type,
                   (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                   (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count
            FROM properties p
            LEFT JOIN property_types pt ON p.type_id = pt.id
            WHERE p.featured = 1 AND p.status IN ('buy', 'rent')
            ORDER BY p.created_at DESC
            LIMIT 8
        ");
        $featured_result = $db->resultSet();
        $featured_properties = $featured_result ? $featured_result : [];
    } catch (Exception $e) {
        $featured_properties = [];
        error_log("Featured properties query error: " . $e->getMessage());
    }
}

// FIXED: Get popular localities - ALWAYS load regardless of search
try {
    $db->query("
        SELECT 
            p.city,
            COUNT(*) as property_count,
            MIN(p.price) as min_price,
            MAX(p.price) as max_price,
            AVG(p.area) as avg_area,
            (SELECT image_path FROM property_images pi 
             JOIN properties p2 ON pi.property_id = p2.id 
             WHERE p2.city = p.city AND pi.is_primary = 1 
             ORDER BY p2.featured DESC, p2.created_at DESC 
             LIMIT 1) as city_image
        FROM properties p
        WHERE p.status IN ('buy', 'rent') AND p.city IS NOT NULL AND p.city != ''
        GROUP BY p.city
        HAVING property_count >= 1
        ORDER BY property_count DESC
        LIMIT 6
    ");
    $localities_result = $db->resultSet();
    $popular_localities = $localities_result ? $localities_result : [];
} catch (Exception $e) {
    $popular_localities = [];
    error_log("Popular localities query error: " . $e->getMessage());
}

// Helper function to format price in Indian currency format
function formatIndianPrice($price) {
    if ($price >= 10000000) { // 1 crore
        return '₹' . round($price / 10000000, 2) . ' Cr';
    } elseif ($price >= 100000) { // 1 lakh
        return '₹' . round($price / 100000, 2) . ' L';
    } else {
        return '₹' . number_format($price);
    }
}

// Helper function for monthly rent
function formatRentPrice($price) {
    if ($price >= 100000) {
        return '₹' . round($price / 100000, 2) . 'L/mo';
    } else {
        return '₹' . number_format($price/1000, 1) . 'K/mo';
    }
}

// Helper function to get property image URL
function getPropertyImageUrl($image_path) {
    if (empty($image_path)) {
        return 'assets/images/no-image.jpg';
    }
    
    if (strpos($image_path, 'http://') === 0 || strpos($image_path, 'https://') === 0) {
        return $image_path;
    }
    
    if (strpos($image_path, '/') === 0) {
        $image_path = substr($image_path, 1);
    }
    
    return $image_path;
}

// Helper function to get status info
function getStatusInfo($status) {
    $status_info = [
        'buy' => ['text' => 'For Sale', 'class' => 'sale'],
        'rent' => ['text' => 'For Rent', 'class' => 'rent'],
        'pending' => ['text' => 'Pending', 'class' => 'pending'],
        'sold' => ['text' => 'Sold', 'class' => 'sold'],
        'rented' => ['text' => 'Rented', 'class' => 'rented']
    ];
    
    return $status_info[$status] ?? ['text' => ucfirst($status), 'class' => $status];
}

// Helper function to calculate price per sqft for localities
function calculatePricePerSqft($min_price, $max_price, $avg_area) {
    if ($avg_area <= 0) return 'Price on Request';
    
    $min_per_sqft = round($min_price / $avg_area);
    $max_per_sqft = round($max_price / $avg_area);
    
    $min_formatted = $min_per_sqft >= 1000 ? round($min_per_sqft/1000, 1) . 'K' : $min_per_sqft;
    $max_formatted = $max_per_sqft >= 1000 ? round($max_per_sqft/1000, 1) . 'K' : $max_per_sqft;
    
    return '₹' . $min_formatted . ' - ₹' . $max_formatted . '/sqft';
}

// Enhanced Helper Function for Pagination URLs
function buildPaginationUrl($search_params, $page) {
    $params = $search_params;
    $params['page'] = $page;
    
    // Remove empty parameters for clean URLs
    $params = array_filter($params, function($value) {
        return $value !== '' && $value !== null;
    });
    
    return 'index.php?' . http_build_query($params);
}

// Include header
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Guntur Properties</title>
    
    <!-- FIXED: Single Swiper CSS - Use only one version -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Your Custom CSS -->
    <link rel="stylesheet" href="assets/css/herosection.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- IMPORTANT: Add the enhanced search CSS -->
    <link rel="stylesheet" href="assets/css/enhanced-search.css">
</head>
<body>
    
    <!-- Hero Section with Featured Properties Carousel - Always Independent -->
    <section class="hero-section">
        <main>
            <div>
                <span>Discover</span>
                <h1>Your Dream Property</h1>
                <hr>
                <a href="properties.php?featured=1">Explore Featured Properties</a>
            </div>
            
            <?php if (!empty($hero_properties)): ?>
            <!-- FIXED: Swiper Container with correct classes -->
            <div class="swiper hero-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($hero_properties as $index => $property): ?>
                    <div class="swiper-slide swiper-slide--<?php echo ($index % 5) + 1; ?>" 
                         style="background-image: url('<?php echo getPropertyImageUrl($property['primary_image']); ?>'); background-size: cover; background-position: center;">
                        <div class="hero-slide-overlay">
                            <a href="property-details.php?id=<?php echo $property['id']; ?>" class="hero-slide-link">
                                <div class="hero-slide-content">
                                    <div class="hero-slide-details">
                                        <?php if (!empty($property['bedrooms'])): ?>
                                        <span class="hero-detail-item">
                                            <i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> BEDS
                                        </span>
                                        <?php endif; ?>
                                        <?php if (!empty($property['area'])): ?>
                                        <span class="hero-detail-item">
                                            <i class="fas fa-ruler-combined"></i> <?php echo $property['area']; ?> <?php echo strtoupper($property['area_unit']); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <!-- Add Pagination -->
                <div class="swiper-pagination hero-pagination"></div>
                <!-- Add Navigation -->
                <div class="swiper-button-next hero-nav-next"></div>
                <div class="swiper-button-prev hero-nav-prev"></div>
            </div>
            <?php else: ?>
            <!-- Fallback static content if no featured properties -->
            <div class="swiper hero-swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide swiper-slide--one">
                        <div class="hero-slide-overlay">
                            <div class="hero-slide-content">
                                <h2 class="hero-slide-title">Featured Properties Coming Soon</h2>
                                <p>We're working hard to bring you the best featured properties in Guntur. Check back soon for amazing deals!</p>
                                <a href="properties.php" class="hero-cta-btn">View All Properties</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <img src="assets/images/decorative-house-1.png" alt="" class="bg">
            <img src="assets/images/decorative-house-2.png" alt="" class="bg2">
        </main>
    </section>
    
    <!-- FIXED: Enhanced Search Section -->
    <section class="enhanced-search-section" id="enhanced-search-filters">
        <div class="container">
            <form class="enhanced-search-form" method="GET" action="index.php" id="main-search-form">
                <div class="search-filters-wrapper">
                    <div class="search-filters-container" id="search-filters-container">
                        <!-- Search Input -->
                        <div class="search-filter-group search-input-group">
                            <input type="text" name="keyword" placeholder="Search properties, locations, features..." 
                                   value="<?php echo htmlspecialchars($search_params['keyword']); ?>" 
                                   class="search-form-control">
                        </div>
                        
                        <!-- Property Type -->
                        <div class="search-filter-group">
                            <select name="type_id" class="search-form-control">
                                <option value="">Property Type</option>
                                <?php foreach ($property_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>" 
                                        <?php echo ($search_params['type_id'] == $type['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- City/Location -->
                        <div class="search-filter-group">
                            <select name="city" class="search-form-control">
                                <option value="">All Cities</option>
                                <?php foreach ($cities as $city): ?>
                                <option value="<?php echo htmlspecialchars($city['city']); ?>" 
                                        <?php echo ($search_params['city'] == $city['city']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($city['city']); ?> (<?php echo $city['count']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Buy/Rent Status -->
                        <div class="search-filter-group">
                            <select name="status" class="search-form-control">
                                <option value="">Buy / Rent</option>
                                <option value="buy" <?php echo ($search_params['status'] == 'buy') ? 'selected' : ''; ?>>For Sale</option>
                                <option value="rent" <?php echo ($search_params['status'] == 'rent') ? 'selected' : ''; ?>>For Rent</option>
                            </select>
                        </div>
                        
                        <!-- Bedrooms (BHK) -->
                        <div class="search-filter-group">
                            <select name="bedrooms" class="search-form-control">
                                <option value="">BHK</option>
                                <option value="1" <?php echo ($search_params['bedrooms'] == '1') ? 'selected' : ''; ?>>1 BHK</option>
                                <option value="2" <?php echo ($search_params['bedrooms'] == '2') ? 'selected' : ''; ?>>2 BHK</option>
                                <option value="3" <?php echo ($search_params['bedrooms'] == '3') ? 'selected' : ''; ?>>3 BHK</option>
                                <option value="4+" <?php echo ($search_params['bedrooms'] == '4+') ? 'selected' : ''; ?>>4+ BHK</option>
                            </select>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="search-filter-group">
                            <select name="price_range" class="search-form-control">
                                <option value="">Price Range</option>
                                <option value="below_30l" <?php echo ($search_params['price_range'] == 'below_30l') ? 'selected' : ''; ?>>Below ₹30L</option>
                                <option value="30l_to_60l" <?php echo ($search_params['price_range'] == '30l_to_60l') ? 'selected' : ''; ?>>₹30L - ₹60L</option>
                                <option value="60l_to_1cr" <?php echo ($search_params['price_range'] == '60l_to_1cr') ? 'selected' : ''; ?>>₹60L - ₹1Cr</option>
                                <option value="above_1cr" <?php echo ($search_params['price_range'] == 'above_1cr') ? 'selected' : ''; ?>>Above ₹1Cr</option>
                            </select>
                        </div>
                        
                        <!-- Area Range -->
                        <div class="search-filter-group">
                            <select name="area_range" class="search-form-control">
                                <option value="">Area Range</option>
                                <option value="below_500" <?php echo ($search_params['area_range'] == 'below_500') ? 'selected' : ''; ?>>Below 500 sq ft</option>
                                <option value="500_to_1000" <?php echo ($search_params['area_range'] == '500_to_1000') ? 'selected' : ''; ?>>500-1000 sq ft</option>
                                <option value="1000_to_2000" <?php echo ($search_params['area_range'] == '1000_to_2000') ? 'selected' : ''; ?>>1000-2000 sq ft</option>
                                <option value="2000_to_5000" <?php echo ($search_params['area_range'] == '2000_to_5000') ? 'selected' : ''; ?>>2000-5000 sq ft</option>
                                <option value="above_5000" <?php echo ($search_params['area_range'] == 'above_5000') ? 'selected' : ''; ?>>Above 5000 sq ft</option>
                            </select>
                        </div>
                        
                        <!-- Facing Direction -->
                        <div class="search-filter-group">
                            <select name="facing" class="search-form-control">
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
                        
                        <!-- Sort Options -->
                        <div class="search-filter-group">
                            <select name="sort_by" class="search-form-control" onchange="this.form.submit()">
                                <option value="created_at" <?php echo ($search_params['sort_by'] == 'created_at') ? 'selected' : ''; ?>>Latest First</option>
                                <option value="price" <?php echo ($search_params['sort_by'] == 'price') ? 'selected' : ''; ?>>Price</option>
                                <option value="area" <?php echo ($search_params['sort_by'] == 'area') ? 'selected' : ''; ?>>Area</option>
                                <option value="bedrooms" <?php echo ($search_params['sort_by'] == 'bedrooms') ? 'selected' : ''; ?>>BHK</option>
                            </select>
                        </div>
                        
                        <!-- Sort Order -->
                        <div class="search-filter-group">
                            <select name="sort_order" class="search-form-control" onchange="this.form.submit()">
                                <option value="DESC" <?php echo ($search_params['sort_order'] == 'DESC') ? 'selected' : ''; ?>>High to Low</option>
                                <option value="ASC" <?php echo ($search_params['sort_order'] == 'ASC') ? 'selected' : ''; ?>>Low to High</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Action Buttons - Fixed on Right -->
                    <div class="search-filter-actions">
                        <button type="submit" class="search-btn search-btn-primary">
                            <i class="fas fa-search"></i> <span class="btn-text">Search</span>
                        </button>
                        <a href="index.php" class="search-btn search-btn-outline">
                            <i class="fas fa-redo"></i> <span class="btn-text">Reset</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>
    
    <!-- FIXED: Main Content - Always show all sections -->
    <main class="container">
        
        <?php if ($is_search): ?>
        <!-- Search Results Section -->
        <section class="section search-results-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-search"></i> Search Results
                </h2>
                <div class="search-results-info">
                    <p>Found <?php echo $total_search_results; ?> properties</p>
                    <?php if (!empty($search_params['keyword'])): ?>
                    <span class="search-term">Search: "<?php echo htmlspecialchars($search_params['keyword']); ?>"</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($search_properties)): ?>
            <div class="property-grid">
                <?php foreach ($search_properties as $property): ?>
                <?php 
                $status_info = getStatusInfo($property['status']);
                $is_rent = ($property['status'] === 'rent');
                ?>
                <div class="property-card">
                    <div class="property-card-img">
                        <img src="<?php echo getPropertyImageUrl($property['primary_image']); ?>" 
                             alt="<?php echo htmlspecialchars($property['title']); ?>"
                             onerror="this.onerror=null; this.src='assets/images/no-image.jpg';">
                        
                        <span class="property-card-badge <?php echo $status_info['class']; ?>">
                            <?php echo $status_info['text']; ?>
                        </span>
                        
                        <?php if (!empty($property['featured']) && $property['featured'] == 1): ?>
                        <span class="badge bg-warning text-dark border" style="position: absolute; top: 10px; left: 10px;">Featured</span>
                        <?php endif; ?>
                        
                        <button class="property-card-favorite" onclick="toggleWishlist(<?php echo $property['id']; ?>, this)">
                            <i class="far fa-heart"></i>
                        </button>
                        
                        <?php if ($property['image_count'] > 1): ?>
                        <div class="property-card-media">
                            <i class="fas fa-camera"></i> <?php echo $property['image_count']; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="property-card-body">
                        <div class="property-card-price">
                            <h4>
                                <?php if ($is_rent): ?>
                                    <?php echo formatRentPrice($property['price']); ?>
                                <?php else: ?>
                                    <?php echo formatIndianPrice($property['price']); ?>
                                <?php endif; ?>
                            </h4>
                        </div>
                        
                        <h5 class="property-card-title">
                            <a href="property-details.php?id=<?php echo $property['id']; ?>" style="text-decoration: none; color: inherit;">
                                <?php echo htmlspecialchars($property['title']); ?>
                            </a>
                        </h5>
                        
                        <div class="property-card-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($property['address'] . ', ' . $property['city']); ?></span>
                        </div>
                        
                        <div class="property-card-features">
                            <?php if (!empty($property['area'])): ?>
                            <span class="property-card-feature">
                                <i class="fas fa-ruler-combined"></i> <?php echo $property['area']; ?> <?php echo $property['area_unit']; ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($property['bedrooms'])): ?>
                            <span class="property-card-feature">
                                <i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($property['bathrooms'])): ?>
                            <span class="property-card-feature">
                                <i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="property-card-actions" style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                            <a href="property-details.php?id=<?php echo $property['id']; ?>" 
                               style="flex: 1; background: #007bff; color: white; padding: 0.5rem 1rem; text-align: center; border-radius: 5px; text-decoration: none; font-size: 0.9rem;">
                                View Details
                            </a>
                            
                            <?php if (!empty($property['instagram_url'])): ?>
                            <a href="<?php echo htmlspecialchars($property['instagram_url']); ?>" target="_blank" rel="noopener noreferrer"
                               style="background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%); color: white; padding: 0.5rem; border-radius: 5px; text-decoration: none;">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination for Search Results -->
            <?php if ($total_search_results > $properties_per_page): ?>
            <div class="pagination-container">
                <nav class="pagination-nav">
                    <ul class="pagination">
                        <?php
                        $total_pages = ceil($total_search_results / $properties_per_page);
                        $current_page = $page ?? 1;
                        
                        // Previous Page
                        if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl($search_params, $current_page - 1); ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        // Page Numbers
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo buildPaginationUrl($search_params, $i); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php
                        // Next Page
                        if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl($search_params, $current_page + 1); ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <!-- No Search Results -->
            <div style="text-align: center; padding: 3rem; color: #666; background: #f8f9fa; border-radius: 10px;">
                <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                <h3>No Properties Found</h3>
                <p>We couldn't find any properties matching your search criteria.</p>
                <p>Try adjusting your search terms or <a href="index.php">browse all properties</a>.</p>
            </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>
        
        <!-- FIXED: Featured Properties Section - Show when not searching -->
        <?php if (!$is_search && !empty($featured_properties)): ?>
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-star"></i> Featured Properties
                </h2>
                <a href="properties.php?featured=1" class="section-link">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="property-grid">
                <?php foreach ($featured_properties as $property): ?>
                <?php 
                $status_info = getStatusInfo($property['status']);
                $is_rent = ($property['status'] === 'rent');
                ?>
                <div class="property-card">
                    <div class="property-card-img">
                        <img src="<?php echo getPropertyImageUrl($property['primary_image']); ?>" 
                             alt="<?php echo htmlspecialchars($property['title']); ?>"
                             onerror="this.onerror=null; this.src='assets/images/no-image.jpg';">
                        
                        <span class="property-card-badge <?php echo $status_info['class']; ?>">
                            <?php echo $status_info['text']; ?>
                        </span>
                        
                        <button class="property-card-favorite" onclick="toggleWishlist(<?php echo $property['id']; ?>, this)">
                            <i class="far fa-heart"></i>
                        </button>
                        
                        <?php if ($property['image_count'] > 1): ?>
                        <div class="property-card-media">
                            <i class="fas fa-camera"></i> <?php echo $property['image_count']; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="property-card-body">
                        <div class="property-card-price">
                            <h4>
                                <?php if ($is_rent): ?>
                                    <?php echo formatRentPrice($property['price']); ?>
                                <?php else: ?>
                                    <?php echo formatIndianPrice($property['price']); ?>
                                <?php endif; ?>
                            </h4>
                            <span class="badge bg-warning text-dark border">Featured</span>
                        </div>
                        
                        <h5 class="property-card-title">
                            <a href="property-details.php?id=<?php echo $property['id']; ?>" style="text-decoration: none; color: inherit;">
                                <?php echo htmlspecialchars($property['title']); ?>
                            </a>
                        </h5>
                        
                        <div class="property-card-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($property['address'] . ', ' . $property['city']); ?></span>
                        </div>
                        
                        <div class="property-card-features">
                            <?php if (!empty($property['area'])): ?>
                            <span class="property-card-feature">
                                <i class="fas fa-ruler-combined"></i> <?php echo $property['area']; ?> <?php echo $property['area_unit']; ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($property['bedrooms'])): ?>
                            <span class="property-card-feature">
                                <i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($property['bathrooms'])): ?>
                            <span class="property-card-feature">
                                <i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="property-card-actions" style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                            <a href="property-details.php?id=<?php echo $property['id']; ?>" 
                               style="flex: 1; background: #007bff; color: white; padding: 0.5rem 1rem; text-align: center; border-radius: 5px; text-decoration: none; font-size: 0.9rem;">
                                View Details
                            </a>
                            
                            <?php if (!empty($property['instagram_url'])): ?>
                            <a href="<?php echo htmlspecialchars($property['instagram_url']); ?>" target="_blank" rel="noopener noreferrer"
                               style="background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%); color: white; padding: 0.5rem; border-radius: 5px; text-decoration: none;">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- FIXED: Popular Localities Section - ALWAYS SHOW -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-map-marked-alt"></i> Popular Localities
                </h2>
                <a href="properties.php" class="section-link">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <?php if (!empty($popular_localities)): ?>
            <div class="location-grid">
                <?php foreach ($popular_localities as $locality): ?>
                <div class="location-card" onclick="searchByCity('<?php echo htmlspecialchars($locality['city']); ?>')" 
                     style="cursor: pointer; transition: transform 0.3s ease;">
                    <div class="location-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h4><?php echo htmlspecialchars($locality['city']); ?></h4>
                    <p><?php echo calculatePricePerSqft($locality['min_price'], $locality['max_price'], $locality['avg_area']); ?></p>
                    <span class="location-properties"><?php echo $locality['property_count']; ?>+ Properties</span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
        
        <!-- FIXED: Premium Features Section - ALWAYS SHOW -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-crown"></i> Our Premium Features
                </h2>
                <p style="color: #666; margin-top: 0.5rem;">Exclusive tools to make your property journey easier</p>
            </div>
            
            <div class="premium-features-grid">
                <!-- EMI Calculator Feature -->
                <div class="premium-feature-card" onclick="window.location.href='calculator.php'" style="cursor: pointer;">
                    <div class="premium-feature-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="premium-feature-content">
                        <h3>EMI Calculator</h3>
                        <p>Calculate your home loan EMI instantly with our smart calculator. Plan your budget and compare different loan options.</p>
                        <div class="premium-feature-benefits">
                            <span><i class="fas fa-check"></i> Instant Calculations</span>
                            <span><i class="fas fa-check"></i> Multiple Scenarios</span>
                            <span><i class="fas fa-check"></i> Detailed Breakdown</span>
                        </div>
                    </div>
                    <div class="premium-feature-action">
                        <span class="premium-action-text">Calculate Now</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
                
                <!-- Vastu Compliance Feature -->
                <div class="premium-feature-card" onclick="window.location.href='vasthu_compilance.php'" style="cursor: pointer;">
                    <div class="premium-feature-icon">
                        <i class="fas fa-compass"></i>
                    </div>
                    <div class="premium-feature-content">
                        <h3>Vastu Compliance</h3>
                        <p>Check Vastu compliance for your dream home. Get expert guidance on directions, room placements, and energy flow.</p>
                        <div class="premium-feature-benefits">
                            <span><i class="fas fa-check"></i> Expert Analysis</span>
                            <span><i class="fas fa-check"></i> Detailed Reports</span>
                            <span><i class="fas fa-check"></i> Remedial Solutions</span>
                        </div>
                    </div>
                    <div class="premium-feature-action">
                        <span class="premium-action-text">Check Vastu</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <!-- Premium Features Section Styling -->
    <style>
    .premium-features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .premium-feature-card {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .premium-feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        border-color: #007bff;
    }

    .premium-feature-card:hover .premium-feature-icon {
        transform: scale(1.1);
        background: linear-gradient(135deg, #007bff, #0056b3);
    }

    .premium-feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #007bff, #28a745);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .premium-feature-card:hover::before {
        transform: scaleX(1);
    }

    .premium-feature-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #28a745, #20c997);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .premium-feature-icon i {
        font-size: 2rem;
        color: white;
    }

    .premium-feature-content h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 1rem;
    }

    .premium-feature-content p {
        color: #666;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .premium-feature-benefits {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .premium-feature-benefits span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: #555;
    }

    .premium-feature-benefits i {
        color: #28a745;
        font-size: 0.8rem;
    }

    .premium-feature-action {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: rgba(0,123,255,0.1);
        padding: 1rem;
        border-radius: 10px;
        margin-top: 1rem;
        transition: all 0.3s ease;
    }

    .premium-feature-card:hover .premium-feature-action {
        background: rgba(0,123,255,0.15);
    }

    .premium-action-text {
        font-weight: 600;
        color: #007bff;
        font-size: 1rem;
    }

    .premium-feature-action i {
        color: #007bff;
        transition: transform 0.3s ease;
    }

    .premium-feature-card:hover .premium-feature-action i {
        transform: translateX(3px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .premium-features-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .premium-feature-card {
            padding: 1.5rem;
        }
        
        .premium-feature-icon {
            width: 60px;
            height: 60px;
        }
        
        .premium-feature-icon i {
            font-size: 1.5rem;
        }
        
        .premium-feature-content h3 {
            font-size: 1.3rem;
        }
        
        .premium-feature-benefits {
            flex-direction: column;
        }
    }

    @media (max-width: 480px) {
        .premium-feature-card {
            padding: 1rem;
        }
        
        .premium-feature-benefits span {
            font-size: 0.8rem;
        }
    }
    </style>
    
    <!-- FIXED: Single Swiper JS - Use only one version and load after DOM -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <!-- FIXED: Main JavaScript - Proper DOM ready and single Swiper instance -->
    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            
            // FIXED: Initialize Enhanced Hero Swiper with modern syntax
            const heroSwiperElement = document.querySelector('.hero-swiper');
            
            if (heroSwiperElement) {
                const heroSwiper = new Swiper('.hero-swiper', {
                    // Modern Swiper configuration
                    effect: 'coverflow',
                    grabCursor: true,
                    centeredSlides: true,
                    slidesPerView: 'auto',
                    
                    // Coverflow effect
                    coverflowEffect: {
                        rotate: 15,
                        stretch: 0,
                        depth: 300,
                        modifier: 1.5,
                        slideShadows: true
                    },
                    
                    // Interaction
                    keyboard: {
                        enabled: true
                    },
                    mousewheel: {
                        thresholdDelta: 70
                    },
                    
                    // Loop and autoplay
                    loop: true,
                    autoplay: {
                        delay: 6000,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true
                    },
                    
                    // Navigation elements
                    pagination: {
                        el: '.hero-pagination',
                        clickable: true,
                        dynamicBullets: true
                    },
                    navigation: {
                        nextEl: '.hero-nav-next',
                        prevEl: '.hero-nav-prev'
                    },
                    
                    // Responsive breakpoints
                    breakpoints: {
                        320: {
                            slidesPerView: 1,
                            spaceBetween: 10
                        },
                        640: {
                            slidesPerView: 1.2,
                            spaceBetween: 20
                        },
                        768: {
                            slidesPerView: 1.5,
                            spaceBetween: 30
                        },
                        1024: {
                            slidesPerView: 2,
                            spaceBetween: 40
                        },
                        1200: {
                            slidesPerView: 2.5,
                            spaceBetween: 50
                        }
                    },
                    
                    // Event callbacks
                    on: {
                        init: function() {
                            console.log('Hero Swiper initialized successfully');
                        },
                        slideChange: function() {
                            console.log('Slide changed to:', this.activeIndex);
                        }
                    }
                });
                
                // Add error handling
                heroSwiper.on('error', function(error) {
                    console.error('Swiper error:', error);
                });
                
            } else {
                console.warn('Hero swiper element not found');
            }
            
            // Enhanced search functionality with sticky behavior
            const searchSection = document.getElementById('enhanced-search-filters');
            const searchContainer = document.getElementById('search-filters-container');
            
            if (searchSection) {
                const searchTop = searchSection.offsetTop;
                let isSticky = false;
                
                // Sticky scroll handler
                function handleSearchScroll() {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    
                    if (scrollTop > searchTop + 100) {
                        if (!isSticky) {
                            searchSection.classList.add('sticky-search');
                            isSticky = true;
                            // Add padding to body to prevent jump
                            document.body.style.paddingTop = searchSection.offsetHeight + 'px';
                        }
                    } else {
                        if (isSticky) {
                            searchSection.classList.remove('sticky-search');
                            isSticky = false;
                            // Remove padding from body
                            document.body.style.paddingTop = '0';
                        }
                    }
                }
                
                // Throttled scroll event for better performance
                let scrollTimeout;
                window.addEventListener('scroll', function() {
                    if (!scrollTimeout) {
                        scrollTimeout = setTimeout(function() {
                            handleSearchScroll();
                            scrollTimeout = null;
                        }, 10);
                    }
                });
            }
        });
        
        // Wishlist functionality
        function toggleWishlist(propertyId, button) {
            const icon = button.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                button.style.color = '#dc3545';
                button.title = 'Remove from Wishlist';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                button.style.color = '';
                button.title = 'Add to Wishlist';
            }
            
            console.log('Wishlist toggled for property:', propertyId);
        }
        
        // City search function for locality cards
        function searchByCity(city) {
            const citySelect = document.querySelector('select[name="city"]');
            const form = document.getElementById('main-search-form');
            
            if (citySelect && form) {
                citySelect.value = city;
                form.submit();
            }
        }
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>