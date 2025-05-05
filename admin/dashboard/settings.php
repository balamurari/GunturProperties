<?php
/**
 * Admin Settings Page
 * Manage website settings
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Set page title
$page_title = 'Site Settings';

// Only admins can access this page
requireAdmin();

// Get database connection
$db = new Database();

// Get settings
$db->query("SELECT * FROM settings");
$settings_arr = $db->resultSet();

// Convert to associative array
$settings = [];
foreach ($settings_arr as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// Handle form submission
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Update each setting
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $setting_key = substr($key, 8); // Remove 'setting_' prefix
                $setting_value = sanitize($value);
                
                $db->query("INSERT INTO settings (setting_key, setting_value) 
                            VALUES (:key, :value)
                            ON DUPLICATE KEY UPDATE setting_value = :value");
                $db->bind(':key', $setting_key);
                $db->bind(':value', $setting_value);
                $db->execute();
                
                // Update local settings array
                $settings[$setting_key] = $setting_value;
            }
        }
        
        // Commit transaction
        $db->endTransaction();
        
        $success = true;
        setFlashMessage('success', 'Settings updated successfully!');
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->cancelTransaction();
        $error = 'Error: ' . $e->getMessage();
        setFlashMessage('error', $error);
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Site Settings</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="settings-form">
            <!-- General Settings -->
            <h3 class="form-section-title">General Settings</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="setting_site_name">Site Name</label>
                    <input type="text" id="setting_site_name" name="setting_site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Guntur Properties'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_site_email">Site Email</label>
                    <input type="email" id="setting_site_email" name="setting_site_email" value="<?php echo htmlspecialchars($settings['site_email'] ?? 'info@gunturproperties.com'); ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="setting_site_phone">Contact Phone</label>
                    <input type="text" id="setting_site_phone" name="setting_site_phone" value="<?php echo htmlspecialchars($settings['site_phone'] ?? '+91 123 456 7890'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_site_address">Office Address</label>
                    <input type="text" id="setting_site_address" name="setting_site_address" value="<?php echo htmlspecialchars($settings['site_address'] ?? '123 Real Estate Avenue, Guntur City, 522002'); ?>">
                </div>
            </div>
            
            <!-- Social Media Settings -->
            <h3 class="form-section-title">Social Media</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="setting_facebook_url">Facebook URL</label>
                    <input type="url" id="setting_facebook_url" name="setting_facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_instagram_url">Instagram URL</label>
                    <input type="url" id="setting_instagram_url" name="setting_instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="setting_linkedin_url">LinkedIn URL</label>
                    <input type="url" id="setting_linkedin_url" name="setting_linkedin_url" value="<?php echo htmlspecialchars($settings['linkedin_url'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_youtube_url">YouTube URL</label>
                    <input type="url" id="setting_youtube_url" name="setting_youtube_url" value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>">
                </div>
            </div>
            
            <!-- Content Settings -->
            <h3 class="form-section-title">Content Settings</h3>
            
            <div class="form-group">
                <label for="setting_footer_text">Footer Text</label>
                <input type="text" id="setting_footer_text" name="setting_footer_text" value="<?php echo htmlspecialchars($settings['footer_text'] ?? '©2025 Guntur Properties. All Rights Reserved.'); ?>">
            </div>
            
            <div class="form-group">
                <label for="setting_meta_description">Meta Description</label>
                <textarea id="setting_meta_description" name="setting_meta_description" rows="3"><?php echo htmlspecialchars($settings['meta_description'] ?? 'Guntur Properties - Find your dream property in Guntur. We offer a wide range of residential and commercial properties.'); ?></textarea>
                <small class="form-text">This description appears in search engine results.</small>
            </div>
            
            <div class="form-group">
                <label for="setting_meta_keywords">Meta Keywords</label>
                <input type="text" id="setting_meta_keywords" name="setting_meta_keywords" value="<?php echo htmlspecialchars($settings['meta_keywords'] ?? 'real estate, property, buy, sell, rent, guntur, andhra pradesh'); ?>">
                <small class="form-text">Comma-separated keywords for SEO.</small>
            </div>
            
            <!-- Email Settings -->
            <h3 class="form-section-title">Email Settings</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="setting_admin_email">Admin Email</label>
                    <input type="email" id="setting_admin_email" name="setting_admin_email" value="<?php echo htmlspecialchars($settings['admin_email'] ?? 'admin@gunturproperties.com'); ?>">
                    <small class="form-text">Email address for admin notifications.</small>
                </div>
                
                <div class="form-group">
                    <label for="setting_enquiry_email">Inquiry Email</label>
                    <input type="email" id="setting_enquiry_email" name="setting_enquiry_email" value="<?php echo htmlspecialchars($settings['enquiry_email'] ?? 'info@gunturproperties.com'); ?>">
                    <small class="form-text">Email address where contact form submissions will be sent.</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="setting_email_footer">Email Footer Text</label>
                <textarea id="setting_email_footer" name="setting_email_footer" rows="3"><?php echo htmlspecialchars($settings['email_footer'] ?? 'Thank you for choosing Guntur Properties. If you have any questions, please contact us at info@gunturproperties.com or call +91 123 456 7890.'); ?></textarea>
                <small class="form-text">This text will appear at the bottom of automated emails.</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Settings</button>
                <button type="reset" class="btn btn-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?>