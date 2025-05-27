<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

$db = new Database();

echo "<h2>Testing Parameter Binding Fix</h2>";

// Test the issue: Same parameter used multiple times
echo "<h3>Test 1: Same Parameter (BROKEN)</h3>";
try {
    $db->query("
        SELECT p.id, p.title FROM properties p
        WHERE p.status IN ('buy') 
          AND (p.title LIKE :keyword OR p.description LIKE :keyword)
          AND p.featured = 1
    ");
    $db->bind(':keyword', '%qq%');
    $result1 = $db->resultSet();
    echo "Same parameter result: " . count($result1) . " properties<br>";
} catch (Exception $e) {
    echo "Same parameter error: " . $e->getMessage() . "<br>";
}

// Test the fix: Different parameters
echo "<h3>Test 2: Different Parameters (FIXED)</h3>";
try {
    $db->query("
        SELECT p.id, p.title FROM properties p
        WHERE p.status IN ('buy') 
          AND (p.title LIKE :keyword1 OR p.description LIKE :keyword2)
          AND p.featured = 1
    ");
    $db->bind(':keyword1', '%qq%');
    $db->bind(':keyword2', '%qq%');
    $result2 = $db->resultSet();
    echo "Different parameters result: " . count($result2) . " properties<br>";
    if ($result2) {
        foreach ($result2 as $prop) {
            echo "Found: " . $prop['title'] . "<br>";
        }
    }
} catch (Exception $e) {
    echo "Different parameters error: " . $e->getMessage() . "<br>";
}

// Test 3: Full search conditions with fix
echo "<h3>Test 3: Full Search with Fix</h3>";
$where_conditions = [];
$bind_params = [];

// Status
$where_conditions[] = "p.status IN ('buy')";

// Keyword with different parameters
$search_keyword = 'qq';
$where_conditions[] = "(
    p.title LIKE :keyword1 OR 
    p.description LIKE :keyword2 OR 
    p.address LIKE :keyword3 OR 
    p.city LIKE :keyword4
)";
$bind_params[':keyword1'] = '%' . $search_keyword . '%';
$bind_params[':keyword2'] = '%' . $search_keyword . '%';
$bind_params[':keyword3'] = '%' . $search_keyword . '%';
$bind_params[':keyword4'] = '%' . $search_keyword . '%';

// Featured
$where_conditions[] = "p.featured = 1";

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

$query = "SELECT p.*, pt.name as property_type,
          (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
          (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count
          FROM properties p 
          LEFT JOIN property_types pt ON p.type_id = pt.id 
          $where_clause 
          ORDER BY p.featured DESC, p.created_at DESC";

echo "Query: <pre>" . $query . "</pre>";
echo "Parameters: <pre>" . print_r($bind_params, true) . "</pre>";

try {
    $db->query($query);
    foreach ($bind_params as $param => $value) {
        $db->bind($param, $value);
    }
    $result3 = $db->resultSet();
    echo "<strong>FINAL RESULT: " . count($result3) . " properties found!</strong><br>";
    
    if ($result3) {
        foreach ($result3 as $property) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<strong>Title:</strong> " . $property['title'] . "<br>";
            echo "<strong>Description:</strong> " . $property['description'] . "<br>";
            echo "<strong>Status:</strong> " . $property['status'] . "<br>";
            echo "<strong>Featured:</strong> " . $property['featured'] . "<br>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "Full search error: " . $e->getMessage();
}
?>