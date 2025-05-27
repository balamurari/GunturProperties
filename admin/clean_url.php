<?php
/**
 * Standalone Agent Image Fix Script
 * This script includes ALL necessary functions and won't interfere with your existing code
 */
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Only allow admin access
if (!isLoggedIn() || !hasRole(['admin'])) {
    die('Access denied. Admin only.');
}

/**
 * STANDALONE FUNCTIONS - These are defined in this file only
 */

/**
 * Normalize ONLY agent image paths (standalone version)
 */
function fix_normalizeAgentImagePath($image_path) {
    if (empty($image_path)) {
        return '';
    }
    
    // If it's already a clean relative path, return as-is
    if (strpos($image_path, 'assets/images/agents/') === 0) {
        return $image_path;
    }
    
    // Handle full server paths (like /home/u900599714/domains/...)
    if (strpos($image_path, '/home/') === 0) {
        // Extract everything after 'public_html/'
        if (preg_match('/public_html\/(.+)$/', $image_path, $matches)) {
            return $matches[1];
        }
        // Fallback: extract just the filename and rebuild agent path
        $filename = basename($image_path);
        if (strpos($image_path, 'agents/') !== false) {
            return 'assets/images/agents/' . $filename;
        }
    }
    
    // Handle paths that start with '/'
    if (strpos($image_path, '/') === 0) {
        $image_path = ltrim($image_path, '/');
    }
    
    // If it's an old assets/images/users/ path, convert to agents
    if (strpos($image_path, 'assets/images/users/') === 0) {
        $filename = basename($image_path);
        return 'assets/images/agents/' . $filename;
    }
    
    // If path doesn't start with assets/, assume it's just a filename
    if (strpos($image_path, 'assets/') !== 0) {
        if (strpos($image_path, '/') === false) {
            // It's just a filename, put it in agents folder
            return 'assets/images/agents/' . $image_path;
        }
    }
    
    return $image_path;
}

/**
 * Enhanced agent image URL function (standalone version)
 */
function fix_getAgentImageUrl($image_path, $default = null) {
    // Set default image if none provided
    if ($default === null) {
        $default = 'assets/images/agent-placeholder.jpg';
    }
    
    // Return default if path is empty
    if (empty($image_path)) {
        return ROOT_URL . $default;
    }
    
    // Normalize the agent path
    $normalized_path = fix_normalizeAgentImagePath($image_path);
    
    // If normalization failed, use default
    if (empty($normalized_path)) {
        return ROOT_URL . $default;
    }
    
    // Build the full URL
    $full_url = ROOT_URL . $normalized_path;
    
    // Check if file exists on server
    $server_path = $_SERVER['DOCUMENT_ROOT'];
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        $server_path .= '/gunturProperties/';
    } else {
        $server_path .= '/';
    }
    $server_path .= $normalized_path;
    
    if (!file_exists($server_path)) {
        return ROOT_URL . $default;
    }
    
    return $full_url;
}

$db = new Database();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Image Fix - Guntur Properties</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f7fa;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .agent-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
            display: flex;
            align-items: center;
        }
        .agent-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            border: 3px solid #007bff;
        }
        .agent-details h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }
        .agent-details p {
            margin: 5px 0;
            color: #6c757d;
            font-size: 14px;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .status-fixed {
            color: #28a745;
            font-weight: bold;
        }
        .status-ok {
            color: #007bff;
            font-weight: bold;
        }
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
        code {
            background: #f1f3f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Agent Image Fix Tool</h1>
            <p>Standalone tool to fix agent image paths without affecting property images</p>
        </div>

        <?php
        // Step 1: Analyze current situation
        echo "<div class='section'>";
        echo "<h2>📊 Current Situation Analysis</h2>";

        // Get agent statistics
        $db->query("SELECT COUNT(*) as total_agents FROM users WHERE role = 'agent'");
        $total_agents = $db->single()['total_agents'];

        $db->query("SELECT COUNT(*) as agents_with_images FROM users WHERE role = 'agent' AND profile_pic IS NOT NULL AND profile_pic != ''");
        $agents_with_images = $db->single()['agents_with_images'];

        echo "<p><strong>Total Agents:</strong> $total_agents</p>";
        echo "<p><strong>Agents with Profile Pictures:</strong> $agents_with_images</p>";

        // Get all agents with images
        $db->query("SELECT u.id, u.name, u.profile_pic 
                   FROM users u 
                   WHERE u.role = 'agent' 
                   AND u.profile_pic IS NOT NULL 
                   AND u.profile_pic != ''");
        $agents = $db->resultSet();

        if (empty($agents)) {
            echo "<div class='alert alert-warning'>";
            echo "<h4>⚠️ No Agent Images Found</h4>";
            echo "<p>No agents have profile pictures set in the database. This could mean:</p>";
            echo "<ul>";
            echo "<li>Agents haven't uploaded profile pictures yet</li>";
            echo "<li>Profile pictures are stored in a different field</li>";
            echo "<li>The agents table structure is different</li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<h3>🔍 Agents with Profile Pictures:</h3>";
            
            $needs_fixing = 0;
            $already_ok = 0;
            
            echo "<table>";
            echo "<tr><th>Agent Name</th><th>Current Path</th><th>Status</th><th>Preview</th></tr>";
            
            foreach ($agents as $agent) {
                $original_path = $agent['profile_pic'];
                $normalized_path = fix_normalizeAgentImagePath($original_path);
                $needs_fix = ($normalized_path !== $original_path);
                
                if ($needs_fix) {
                    $needs_fixing++;
                } else {
                    $already_ok++;
                }
                
                echo "<tr>";
                echo "<td><strong>" . htmlspecialchars($agent['name']) . "</strong></td>";
                echo "<td><code>" . htmlspecialchars($original_path) . "</code></td>";
                
                if ($needs_fix) {
                    echo "<td class='status-error'>❌ NEEDS FIXING</td>";
                } else {
                    echo "<td class='status-ok'>✅ OK</td>";
                }
                
                $image_url = fix_getAgentImageUrl($original_path);
                echo "<td><img src='$image_url' alt='Agent' style='width: 40px; height: 40px; border-radius: 50%; object-fit: cover;' onerror='this.src=\"" . ROOT_URL . "assets/images/agent-placeholder.jpg\"'></td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<div class='alert alert-" . ($needs_fixing > 0 ? "warning" : "success") . "'>";
            echo "<p><strong>Summary:</strong></p>";
            echo "<ul>";
            echo "<li><strong>Needs Fixing:</strong> $needs_fixing agents</li>";
            echo "<li><strong>Already OK:</strong> $already_ok agents</li>";
            echo "</ul>";
            echo "</div>";
        }
        echo "</div>";

        // Step 2: Fix process
        if (!empty($agents)) {
            echo "<div class='section'>";
            echo "<h2>🔧 Fix Agent Image Paths</h2>";
            
            if (isset($_POST['fix_paths'])) {
                echo "<h3>Processing Fixes...</h3>";
                
                $fixed_count = 0;
                $error_count = 0;
                
                echo "<table>";
                echo "<tr><th>Agent</th><th>Original Path</th><th>New Path</th><th>Result</th></tr>";
                
                foreach ($agents as $agent) {
                    $original_path = $agent['profile_pic'];
                    $normalized_path = fix_normalizeAgentImagePath($original_path);
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($agent['name']) . "</td>";
                    echo "<td><code>" . htmlspecialchars($original_path) . "</code></td>";
                    echo "<td><code>" . htmlspecialchars($normalized_path) . "</code></td>";
                    
                    // Only update if the path actually changed
                    if ($normalized_path !== $original_path && !empty($normalized_path)) {
                        try {
                            $db->query("UPDATE users SET profile_pic = :new_path WHERE id = :id");
                            $db->bind(':new_path', $normalized_path);
                            $db->bind(':id', $agent['id']);
                            
                            if ($db->execute()) {
                                echo "<td class='status-fixed'>✅ FIXED</td>";
                                $fixed_count++;
                            } else {
                                echo "<td class='status-error'>❌ DB ERROR</td>";
                                $error_count++;
                            }
                        } catch (Exception $e) {
                            echo "<td class='status-error'>❌ EXCEPTION</td>";
                            $error_count++;
                        }
                    } else {
                        echo "<td class='status-ok'>✓ NO CHANGE NEEDED</td>";
                    }
                    
                    echo "</tr>";
                }
                echo "</table>";
                
                echo "<div class='alert alert-success'>";
                echo "<h4>🎉 Fix Complete!</h4>";
                echo "<p><strong>Fixed:</strong> $fixed_count agent image paths</p>";
                echo "<p><strong>Errors:</strong> $error_count</p>";
                if ($fixed_count > 0) {
                    echo "<p>Agent images should now display correctly on your website.</p>";
                }
                echo "</div>";
                
            } else {
                echo "<form method='POST'>";
                echo "<p>Click the button below to fix all agent image paths in the database:</p>";
                echo "<button type='submit' name='fix_paths' class='btn'>🔧 Fix Agent Image Paths</button>";
                echo "</form>";
            }
            echo "</div>";
        }

        // Step 3: Test results
        echo "<div class='section'>";
        echo "<h2>🧪 Test Agent Images</h2>";
        
        // Get updated agent data
        $db->query("SELECT u.id, u.name, u.profile_pic 
                   FROM users u 
                   WHERE u.role = 'agent' 
                   ORDER BY u.name ASC");
        $all_agents = $db->resultSet();
        
        if (empty($all_agents)) {
            echo "<p>No agents found in the system.</p>";
        } else {
            foreach ($all_agents as $agent) {
                $image_url = fix_getAgentImageUrl($agent['profile_pic']);
                $has_image = !empty($agent['profile_pic']);
                
                echo "<div class='agent-card'>";
                echo "<img src='$image_url' alt='Agent Image' class='agent-image' onerror='this.src=\"" . ROOT_URL . "assets/images/agent-placeholder.jpg\"; this.style.borderColor=\"#dc3545\";'>";
                echo "<div class='agent-details'>";
                echo "<h4>" . htmlspecialchars($agent['name']) . "</h4>";
                echo "<p><strong>Status:</strong> " . ($has_image ? "Has Profile Picture" : "No Profile Picture") . "</p>";
                if ($has_image) {
                    echo "<p><strong>Path:</strong> <code>" . htmlspecialchars($agent['profile_pic']) . "</code></p>";
                    echo "<p><strong>URL:</strong> <a href='$image_url' target='_blank'>View Image</a></p>";
                }
                echo "</div>";
                echo "</div>";
            }
        }
        echo "</div>";
        ?>

        <div class="section" style="border-left: 4px solid #dc3545;">
            <h3>🗑️ Important - Delete This File</h3>
            <p><strong style="color: #dc3545;">Delete this file (standalone_agent_fix.php) after successfully fixing the agent images.</strong></p>
            <p>This is a one-time fix tool and should not remain on your production server for security reasons.</p>
        </div>

        <div class="section" style="background: #e8f5e8; border: 2px solid #28a745;">
            <h3>✅ Next Steps After Fix</h3>
            <ol>
                <li><strong>Test your agent pages</strong> to verify images are displaying correctly</li>
                <li><strong>Update your agent display code</strong> to use the proper image functions</li>
                <li><strong>Add the enhanced functions</strong> to your functions.php for future uploads</li>
                <li><strong>Delete this file</strong> when everything is working</li>
            </ol>
        </div>
    </div>
</body>
</html>