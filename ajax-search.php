<?php
/**
 * AJAX Search Endpoint
 * Create this file as: ajax-search.php
 */

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['ajax_search'])) {
    http_response_code(405);
    exit('Method not allowed');
}

// Set JSON header
header('Content-Type: application/json');

try {
    require_once 'includes/config.php';
    require_once 'includes/database.php';
    require_once 'includes/functions.php';
    
    // Initialize database connection
    $db = new Database();
    
    // Get search parameters from POST
    $search_params = [
        'keyword' => $_POST['keyword'] ?? '',
        'type_id' => $_POST['type_id'] ?? '',
        'price_range' => $_POST['price_range'] ?? '',
        'area_range' => $_POST['area_range'] ?? '',
        'bedrooms' => $_POST['bedrooms'] ?? '',
        'bathrooms' => $_POST['bathrooms'] ?? '',
        'city' => $_POST['city'] ?? '',
        'status' => $_POST['status'] ?? '',
        'featured' => $_POST['featured'] ?? '',
        'facing' => $_POST['facing'] ?? '',
        'sort_by' => $_POST['sort_by'] ?? 'created_at',
        'sort_order' => $_POST['sort_order'] ?? 'DESC'
    ];
    
    // Check if search is active
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
    
    $search_properties = [];
    $total_search_results = 0;
    
    if ($is_search) {
        // Build WHERE clause
        $where_conditions = [];
        $bind_params = [];
        
        // Status filter
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
        
        // FIXED: Keyword search with correct FULLTEXT columns
        if (!empty($search_params['keyword'])) {
            $search_keyword = trim($search_params['keyword']);
            
            try {
                // Test FULLTEXT search
                $where_conditions[] = "MATCH(p.title, p.description, p.city) AGAINST(:keyword_fulltext IN BOOLEAN MODE)";
                $bind_params[':keyword_fulltext'] = $search_keyword;
            } catch (Exception $e) {
                // Fallback to LIKE search
                $where_conditions[] = "(p.title LIKE :keyword OR p.description LIKE :keyword_desc OR p.address LIKE :keyword_addr OR p.city LIKE :keyword_city)";
                $bind_params[':keyword'] = '%' . $search_keyword . '%';
                $bind_params[':keyword_desc'] = '%' . $search_keyword . '%';
                $bind_params[':keyword_addr'] = '%' . $search_keyword . '%';
                $bind_params[':keyword_city'] = '%' . $search_keyword . '%';
            }
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
        
        // Facing filter
        if (!empty($search_params['facing'])) {
            $where_conditions[] = "p.facing = :facing";
            $bind_params[':facing'] = $search_params['facing'];
        }
        
        // Build complete WHERE clause
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        // Sort validation
        $valid_sort_columns = ['created_at', 'price', 'title', 'bedrooms', 'area', 'bathrooms'];
        $valid_sort_orders = ['ASC', 'DESC'];
        
        if (!in_array($search_params['sort_by'], $valid_sort_columns)) {
            $search_params['sort_by'] = 'created_at';
        }
        if (!in_array($search_params['sort_order'], $valid_sort_orders)) {
            $search_params['sort_order'] = 'DESC';
        }
        
        $order_clause = "ORDER BY p.featured DESC, p.{$search_params['sort_by']} {$search_params['sort_order']}";
        
        // Get total count
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
        
        // Get search results (limit to 50 for AJAX)
        $search_query = "SELECT p.*, pt.name as property_type,
                         (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                         (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count
                         FROM properties p 
                         LEFT JOIN property_types pt ON p.type_id = pt.id 
                         $where_clause 
                         $order_clause 
                         LIMIT 50";
        
        $db->query($search_query);
        foreach ($bind_params as $param => $value) {
            $db->bind($param, $value);
        }
        
        $search_result = $db->resultSet();
        $search_properties = $search_result ? $search_result : [];
    }
    
    // Helper functions (needed for formatting)
    function formatIndianPrice($price) {
        if ($price >= 10000000) {
            return '₹' . round($price / 10000000, 2) . ' Cr';
        } elseif ($price >= 100000) {
            return '₹' . round($price / 100000, 2) . ' L';
        } else {
            return '₹' . number_format($price);
        }
    }
    
    function formatRentPrice($price) {
        if ($price >= 100000) {
            return '₹' . round($price / 100000, 2) . 'L/mo';
        } else {
            return '₹' . number_format($price/1000, 1) . 'K/mo';
        }
    }
    
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
    
    // Format properties for JSON response
    $formatted_properties = [];
    foreach ($search_properties as $property) {
        $status_info = getStatusInfo($property['status']);
        $is_rent = ($property['status'] === 'rent');
        
        $formatted_properties[] = [
            'id' => $property['id'],
            'title' => htmlspecialchars($property['title']),
            'address' => htmlspecialchars($property['address']),
            'city' => htmlspecialchars($property['city']),
            'price' => $property['price'],
            'formatted_price' => $is_rent ? formatRentPrice($property['price']) : formatIndianPrice($property['price']),
            'bedrooms' => $property['bedrooms'],
            'bathrooms' => $property['bathrooms'],
            'area' => $property['area'],
            'area_unit' => $property['area_unit'],
            'status' => $property['status'],
            'status_text' => $status_info['text'],
            'status_class' => $status_info['class'],
            'featured' => $property['featured'],
            'primary_image' => getPropertyImageUrl($property['primary_image']),
            'image_count' => $property['image_count'],
            'instagram_url' => $property['instagram_url']
        ];
    }
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'properties' => $formatted_properties,
        'total_results' => $total_search_results,
        'search_params' => $search_params,
        'is_search' => $is_search
    ]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Search failed. Please try again.',
        'error' => $e->getMessage() // Remove this in production
    ]);
}
?>