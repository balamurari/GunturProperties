<?php
/**
 * Homepage - Dynamic Featured Properties Display with Integrated Search
 */
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'Find Your Dream Home';

// Get database connection
$db = new Database();

// Check if search was performed
$is_search = !empty($_GET['keyword']) || !empty($_GET['type_id']) || !empty($_GET['status']) || !empty($_GET['city']) || !empty($_GET['min_price']) || !empty($_GET['max_price']) || !empty($_GET['bedrooms']) || !empty($_GET['bathrooms']);

// Initialize search parameters
$search_params = [
    'keyword' => $_GET['keyword'] ?? '',
    'type_id' => $_GET['type_id'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'bedrooms' => $_GET['bedrooms'] ?? '',
    'bathrooms' => $_GET['bathrooms'] ?? '',
    'city' => $_GET['city'] ?? '',
    'min_area' => $_GET['min_area'] ?? '',
    'max_area' => $_GET['max_area'] ?? '',
    'status' => $_GET['status'] ?? '',
    'featured' => $_GET['featured'] ?? '',
    'facing' => $_GET['facing'] ?? '',
    'sort_by' => $_GET['sort_by'] ?? 'created_at',
    'sort_order' => $_GET['sort_order'] ?? 'DESC'
];

// Search results variables
$search_properties = [];
$total_search_results = 0;

// If search is performed, get search results
if ($is_search) {
    // Pagination for search results
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $properties_per_page = 12;
    $offset = ($page - 1) * $properties_per_page;
    
    // Build WHERE clause for search
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
    
    // Keyword search
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
    if (!empty($search_params['min_price'])) {
        $where_conditions[] = "p.price >= :min_price";
        $bind_params[':min_price'] = $search_params['min_price'];
    }
    if (!empty($search_params['max_price'])) {
        $where_conditions[] = "p.price <= :max_price";
        $bind_params[':max_price'] = $search_params['max_price'];
    }
    
    // City filter
    if (!empty($search_params['city'])) {
        $where_conditions[] = "p.city LIKE :city";
        $bind_params[':city'] = '%' . $search_params['city'] . '%';
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
    
    // Featured filter
    if (!empty($search_params['featured']) && $search_params['featured'] == '1') {
        $where_conditions[] = "p.featured = 1";
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
        error_log("Search count query error: " . $e->getMessage());
    }
    
    // Get search results
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
        error_log("Search properties query error: " . $e->getMessage());
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

// Get popular localities based on property count and average prices
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

// Get all property types for search filter
try {
    $db->query("SELECT * FROM property_types ORDER BY name ASC");
    $types_result = $db->resultSet();
    $property_types = $types_result ? $types_result : [];
} catch (Exception $e) {
    $property_types = [];
    error_log("Property types query error: " . $e->getMessage());
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

// Helper function for pagination URLs
function buildPaginationUrl($search_params, $page) {
    $params = $search_params;
    $params['page'] = $page;
    
    // Remove empty parameters
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
    
    <!-- Favicon -->
    <!-- <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon"> -->
    
    <!-- Google Fonts -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"> -->
    
    <!-- Font Awesome -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> -->
    <!-- Required CSS Dependencies -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.css">

<!-- Your Custom CSS -->
<link rel="stylesheet" href="assets/css/herosection.css">
    
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
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
            <div class="swiper hero-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($hero_properties as $index => $property): ?>
                    <div class="swiper-slide swiper-slide--<?php echo ($index % 5) + 1; ?>" 
                         style="background-image: url('<?php echo getPropertyImageUrl($property['primary_image']); ?>'); background-size: cover; background-position: center;">
                        <div class="hero-slide-overlay">
                            <a href="property-details.php?id=<?php echo $property['id']; ?>" class="hero-slide-link">
                                <div class="hero-slide-content">
                                    <!-- <h2 class="hero-slide-title"><?php echo htmlspecialchars($property['title']); ?></h2> -->
                                    <div class="hero-slide-details">
                                        <?php if (!empty($property['bedrooms'])): ?>
                                        <span class="hero-detail-item">
                                            <i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> BEDS
                                        </span>
                                        <?php endif; ?>
                                        <!-- <?php if (!empty($property['bathrooms'])): ?>
                                        <span class="hero-detail-item">
                                            <i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> BATHS
                                        </span>
                                        <?php endif; ?> -->
                                        <?php if (!empty($property['area'])): ?>
                                        <span class="hero-detail-item">
                                            <i class="fas fa-ruler-combined"></i> <?php echo $property['area']; ?> <?php echo strtoupper($property['area_unit']); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <!-- <div class="hero-slide-price">
                                        <?php echo ($property['status'] === 'rent') ? formatRentPrice($property['price']) : formatIndianPrice($property['price']); ?>
                                    </div> -->
                                    <!-- <div class="hero-slide-location">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['city']); ?>
                                    </div> -->
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
    
    <!-- Search Section -->
    <section class="search-container">
        <div class="search-box">
            <form method="GET" action="index.php" class="search-input-wrapper">
                <div class="search-input-row">
                    <div class="search-input">
                        <input type="text" name="keyword" placeholder="Search for properties..." 
                               value="<?php echo htmlspecialchars($search_params['keyword']); ?>" id="property-search">
                    </div>
                </div>
                <div class="search-filters">
                    <select name="type_id" class="filter-button">
                        <option value="">All Property Types</option>
                        <?php foreach ($property_types as $type): ?>
                        <option value="<?php echo $type['id']; ?>" 
                                <?php echo ($search_params['type_id'] == $type['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="status" class="filter-button">
                        <option value="">Buy or Rent</option>
                        <option value="buy" <?php echo ($search_params['status'] == 'buy') ? 'selected' : ''; ?>>For Sale</option>
                        <option value="rent" <?php echo ($search_params['status'] == 'rent') ? 'selected' : ''; ?>>For Rent</option>
                    </select>
                    
               <select name="city" class="filter-button">
                  <option value="">All Locations</option>
                  <?php foreach ($popular_localities as $locality): ?>
                  <option value="<?php echo htmlspecialchars($locality['city']); ?>" 
                          <?php echo ($search_params['city'] == $locality['city']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($locality['city']); ?> (<?php echo $locality['property_count']; ?>)
                  </option>
                  <?php endforeach; ?>
              </select>
                    
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i> Find Property
                    </button>
                    <?php if ($is_search): ?>
                    <a href="index.php" class="search-button" style="background: #6c757d; text-decoration: none;">
                        <i class="fas fa-times"></i> Clear
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="container">
        
        <?php if ($is_search): ?>
        <!-- Search Results Section -->
        <section class="section">
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
                        
                        // Previous Page
                        if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl($search_params, $page - 1); ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        // Page Numbers
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo buildPaginationUrl($search_params, $i); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php
                        // Next Page
                        if ($page < $total_pages): ?>
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
        
        <?php else: ?>
        <!-- Featured Properties Section - Only show when not searching -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-star"></i> Featured Properties
                </h2>
                <a href="properties.php?featured=1" class="section-link">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <?php if (!empty($featured_properties)): ?>
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
            <?php else: ?>
            <!-- No featured properties message -->
            <div style="text-align: center; padding: 3rem; color: #666; background: #f8f9fa; border-radius: 10px;">
                <i class="fas fa-star" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                <h3>No Featured Properties Available</h3>
                <p>We're currently updating our featured listings. Please check back soon or browse all our properties.</p>
                <a href="properties.php" style="display: inline-block; margin-top: 1rem; background: #007bff; color: white; padding: 0.75rem 1.5rem; border-radius: 5px; text-decoration: none;">
                    View All Properties
                </a>
            </div>
            <?php endif; ?>
        </section>
        
        <!-- Popular Localities Section -->
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
                <div class="location-card" onclick="window.location.href='properties.php?city=<?php echo urlencode($locality['city']); ?>'" 
                     style="cursor: pointer; transition: transform 0.3s ease; position: relative; overflow: hidden;">
                    
                    <!-- <?php if (!empty($locality['city_image'])): ?>
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 60%; background-image: url('<?php echo getPropertyImageUrl($locality['city_image']); ?>'); background-size: cover; background-position: center; opacity: 0.2;"></div>
                    <?php endif; ?> -->
                    
                    <div class="location-icon" style="position: relative; z-index: 2;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h4 style="position: relative; z-index: 2;"><?php echo htmlspecialchars($locality['city']); ?></h4>
                    <p style="position: relative; z-index: 2;"><?php echo calculatePricePerSqft($locality['min_price'], $locality['max_price'], $locality['avg_area']); ?></p>
                    <span class="location-properties" style="position: relative; z-index: 2;"><?php echo $locality['property_count']; ?>+ Properties</span>
                    
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.1)); height: 50%; pointer-events: none;"></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <!-- Fallback to dynamic data from all properties -->
            <?php
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
                         LIMIT 1) as city_image
                    FROM properties p
                    WHERE p.status IN ('buy', 'rent') AND p.city IS NOT NULL AND p.city != ''
                    GROUP BY p.city
                    HAVING property_count >= 1
                    ORDER BY property_count DESC
                    LIMIT 6
                ");
                $fallback_result = $db->resultSet();
                $fallback_localities = $fallback_result ? $fallback_result : [];
            } catch (Exception $e) {
                $fallback_localities = [];
            }
            ?>
            
            <?php if (!empty($fallback_localities)): ?>
            <div class="location-grid">
                <?php foreach ($fallback_localities as $locality): ?>
                <div class="location-card" onclick="window.location.href='properties.php?city=<?php echo urlencode($locality['city']); ?>'" 
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
            <?php else: ?>
            <!-- Final fallback to static content if no data -->
            <div style="text-align: center; padding: 3rem; color: #666; background: #f8f9fa; border-radius: 10px;">
                <i class="fas fa-map-marked-alt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                <h3>Loading Localities...</h3>
                <p>We're updating our location data. Please check back soon.</p>
                <a href="properties.php" style="display: inline-block; margin-top: 1rem; background: #007bff; color: white; padding: 0.75rem 1.5rem; border-radius: 5px; text-decoration: none;">
                    Browse All Properties
                </a>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </section>
        <!-- Our Premium Features Section -->
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

<style>
/* Premium Features Section Styling */
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
        <?php endif; ?>

    </main>
    
    <!-- Additional CSS for Search Results -->
    <style>
    /* Enhanced Hero Carousel Styling */
    .hero-swiper {
        height: 500px !important;
        width: 100%;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    
    .hero-swiper .swiper-slide {
        height: 500px;
        position: relative;
        overflow: hidden;
    }
    
    .hero-slide-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(
            135deg,
            rgba(0,0,0,0.7) 0%,
            rgba(0,0,0,0.4) 50%,
            rgba(0,0,0,0.8) 100%
        );
        display: flex;
        align-items: flex-end;
        padding: 2.5rem;
        transition: all 0.3s ease;
    }
    
    .hero-slide-overlay:hover {
        background: linear-gradient(
            135deg,
            rgba(0,0,0,0.8) 0%,
            rgba(0,0,0,0.5) 50%,
            rgba(0,0,0,0.9) 100%
        );
    }
    
    .hero-slide-link {
        text-decoration: none;
        color: inherit;
        width: 100%;
    }
    
    .hero-slide-content {
        color: white;
        width: 100%;
    }
    
    .hero-slide-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 1rem 0;
        line-height: 1.2;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        transition: all 0.3s ease;
    }
    
    .hero-slide-title:hover {
        color: #007bff;
        transform: translateY(-2px);
    }
    
    .hero-slide-details {
        display: flex;
        gap: 1.5rem;
        margin: 1.5rem 0;
        flex-wrap: wrap;
    }
    
    .hero-detail-item {
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid rgba(255,255,255,0.3);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    
    .hero-detail-item:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-2px);
    }
    
    .hero-detail-item i {
        margin-right: 0.5rem;
        color: #4CAF50;
    }
    
    .hero-slide-price {
        font-size: 2.5rem;
        font-weight: 800;
        color: #4CAF50;
        margin: 1rem 0;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        display: inline-block;
        background: rgba(76, 175, 80, 0.1);
        padding: 0.5rem 1rem;
        border-radius: 10px;
        border: 2px solid rgba(76, 175, 80, 0.3);
    }
    
    .hero-slide-location {
        font-size: 1.1rem;
        opacity: 0.9;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .hero-slide-location i {
        color: #007bff;
        font-size: 1.2rem;
    }
    
    /* Enhanced Pagination */
    .hero-pagination {
        bottom: 20px !important;
    }
    
    .hero-pagination .swiper-pagination-bullet {
        width: 12px;
        height: 12px;
        background: rgba(255,255,255,0.5);
        border: 2px solid white;
        opacity: 1;
        transition: all 0.3s ease;
    }
    
    .hero-pagination .swiper-pagination-bullet-active {
        background: #007bff;
        border-color: #007bff;
        transform: scale(1.2);
    }
    
    /* Enhanced Navigation */
    .hero-nav-next,
    .hero-nav-prev {
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.9);
        border-radius: 50%;
        color: #333;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    
    .hero-nav-next:hover,
    .hero-nav-prev:hover {
        background: #007bff;
        color: white;
        transform: scale(1.1);
    }
    
    .hero-nav-next:after,
    .hero-nav-prev:after {
        font-size: 18px;
        font-weight: bold;
    }
    
    /* CTA Button in fallback */
    .hero-cta-btn {
        display: inline-block;
        background: #007bff;
        color: white;
        padding: 1rem 2rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        margin-top: 1.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,123,255,0.3);
    }
    
    .hero-cta-btn:hover {
        background: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,123,255,0.4);
        color: white;
    }
    
    /* Search Results Styling */
    .search-results-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .search-term {
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.9rem;
    }
    
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
    }
    
    .page-item .page-link {
        display: block;
        padding: 0.75rem 1rem;
        background: white;
        border: 1px solid #dee2e6;
        color: #007bff;
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    
    .page-item:hover .page-link {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .page-item.active .page-link {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .hero-swiper {
            height: 400px !important;
        }
        
        .hero-swiper .swiper-slide {
            height: 400px;
        }
        
        .hero-slide-overlay {
            padding: 1.5rem;
        }
        
        .hero-slide-title {
            font-size: 1.5rem;
        }
        
        .hero-slide-details {
            gap: 1rem;
        }
        
        .hero-detail-item {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }
        
        .hero-slide-price {
            font-size: 2rem;
        }
        
        .hero-nav-next,
        .hero-nav-prev {
            width: 40px;
            height: 40px;
        }
        
        .hero-nav-next:after,
        .hero-nav-prev:after {
            font-size: 14px;
        }
        
        .search-results-info {
            flex-direction: column;
            align-items: flex-start;
        }
    }
    
    @media (max-width: 480px) {
        .hero-swiper {
            height: 350px !important;
        }
        
        .hero-swiper .swiper-slide {
            height: 350px;
        }
        
        .hero-slide-details {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .hero-slide-price {
            font-size: 1.8rem;
        }
    }
    </style>
    
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    
    <!-- Main JavaScript -->
    <script>
        // Initialize Enhanced Hero Swiper - Always active and independent
        var heroSwiper = new Swiper(".hero-swiper", {
            effect: "coverflow",
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: "auto",
            coverflowEffect: {
                rotate: 15,
                stretch: 0,
                depth: 300,
                modifier: 1.5,
                slideShadows: true
            },
            keyboard: {
                enabled: true
            },
            mousewheel: {
                thresholdDelta: 70
            },
            loop: true,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            },
            pagination: {
                el: ".hero-pagination",
                clickable: true,
                dynamicBullets: true
            },
            navigation: {
                nextEl: ".hero-nav-next",
                prevEl: ".hero-nav-prev"
            },
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
            on: {
                slideChange: function () {
                    // Add custom animations or tracking here if needed
                },
                init: function () {
                    // Custom initialization if needed
                }
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
        
        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.querySelector('.search-input-wrapper');
            const searchInput = document.getElementById('property-search');
            
            // Handle Enter key in search input
            searchInput.addEventListener('keyup', function(event) {
                if (event.key === 'Enter') {
                    searchForm.submit();
                }
            });
        });
        
        // Add click handlers for property cards
        document.addEventListener('DOMContentLoaded', function() {
            const propertyCards = document.querySelectorAll('.property-card');
            
            propertyCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't navigate if clicking on action buttons
                    if (!e.target.closest('.property-card-favorite') && 
                        !e.target.closest('.property-card-actions') &&
                        !e.target.closest('a')) {
                        const link = this.querySelector('.property-card-title a');
                        if (link) {
                            window.location.href = link.href;
                        }
                    }
                });
            });
        });
    </script>
    <!-- Required JS Dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.js"></script>

<!-- Your Custom JS -->
<script src="assets/js/herosection.js"></script>
    <?php include 'footer.php'; ?>
</body>
</html>