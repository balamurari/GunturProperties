<?php
/**
 * Function to get featured properties from the database
 * 
 * @param int $limit Number of properties to fetch (default 5)
 * @return array Array of featured properties
 */
function getFeaturedProperties($limit = 5) {
    global $conn;
    
    // Query to get featured properties
    $sql = "SELECT p.*, pt.name as property_type, a.user_id, u.name as agent_name, 
           (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
           FROM properties p
           LEFT JOIN property_types pt ON p.type_id = pt.id
           LEFT JOIN agents a ON p.agent_id = a.id
           LEFT JOIN users u ON a.user_id = u.id
           WHERE p.featured = 1
           ORDER BY p.updated_at DESC
           LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $properties = [];
    while ($row = $result->fetch_assoc()) {
        // If no primary image is found, set a default image path
        if (empty($row['primary_image'])) {
            $row['primary_image'] = 'assets/images/properties/default-property.jpg';
        }
        
        // Format price with commas for Indian numbering format
        $row['formatted_price'] = number_format($row['price'], 2);
        
        // Get property features
        $row['features'] = getPropertyFeatures($row['id']);
        
        $properties[] = $row;
    }
    
    return $properties;
}

/**
 * Function to get property features
 * 
 * @param int $propertyId The property ID
 * @return array Array of property features
 */
function getPropertyFeatures($propertyId) {
    global $conn;
    
    $sql = "SELECT pf.name, pf.icon, pfm.value 
            FROM property_feature_mapping pfm
            JOIN property_features pf ON pfm.feature_id = pf.id
            WHERE pfm.property_id = ?
            LIMIT 5";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $features = [];
    while ($row = $result->fetch_assoc()) {
        $features[] = $row;
    }
    
    return $features;
}
?>