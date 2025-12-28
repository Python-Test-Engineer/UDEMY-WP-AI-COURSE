<?php
/**
 * Plugin Name: ✅ IWS DISABLE ALL COMMENTS
 * Plugin URI: https://example.com
 * Description: Unsets 'allow comments' on all posts and pages with one click
 * Version: 1.0
 * Author: CRAIG WEST
 * Author URI: https://example.com
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add menu item to admin
add_action('admin_menu', 'dcap_add_admin_menu');

function dcap_add_admin_menu() {
    add_comments_page(
        'DISABLE COMMENTS',
        'DISABLE COMMENTS',
        'manage_options',
        'disable-all-comments',
        'dcap_admin_page'
    );
}

// Admin page content
function dcap_admin_page() {
    ?>
    <div class="wrap">
        <h1>Disable Comments on All Posts</h1>
        
        <?php
        // Handle form submission
        if (isset($_POST['dcap_disable_comments']) && check_admin_referer('dcap_disable_action', 'dcap_nonce')) {
            $result = dcap_disable_all_comments();
            
            if ($result !== false) {
                echo '<div class="notice notice-success"><p><strong>Success!</strong> Disabled comments on ' . $result . ' posts/pages.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p><strong>Error!</strong> Could not disable comments.</p></div>';
            }
        }
        
        if (isset($_POST['dcap_enable_comments']) && check_admin_referer('dcap_enable_action', 'dcap_enable_nonce')) {
            $result = dcap_enable_all_comments();
            
            if ($result !== false) {
                echo '<div class="notice notice-success"><p><strong>Success!</strong> Enabled comments on ' . $result . ' posts/pages.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p><strong>Error!</strong> Could not enable comments.</p></div>';
            }
        }
        
        // Get statistics
        $stats = dcap_get_comment_status_stats();
        ?>
        
        <div class="card">
            <h2>Comment Status Statistics</h2>
            <p><strong>Posts/Pages with comments ENABLED:</strong> <?php echo $stats['enabled']; ?></p>
            <p><strong>Posts/Pages with comments DISABLED:</strong> <?php echo $stats['disabled']; ?></p>
            <p><strong>Total Posts/Pages:</strong> <?php echo $stats['total']; ?></p>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Disable Comments</h2>
            <p>This will unset the "Allow comments" option on all posts and pages.</p>
            <p>Existing comments will remain in the database but new comments cannot be submitted.</p>
            
            <form method="post">
                <?php wp_nonce_field('dcap_disable_action', 'dcap_nonce'); ?>
                <p>
                    <input type="submit" 
                           name="dcap_disable_comments" 
                           class="button button-primary button-large" 
                           value="Disable Comments on All Posts/Pages">
                </p>
            </form>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Enable Comments</h2>
            <p>This will set the "Allow comments" option on all posts and pages.</p>
            
            <form method="post">
                <?php wp_nonce_field('dcap_enable_action', 'dcap_enable_nonce'); ?>
                <p>
                    <input type="submit" 
                           name="dcap_enable_comments" 
                           class="button button-secondary button-large" 
                           value="Enable Comments on All Posts/Pages">
                </p>
            </form>
        </div>
        
        <div class="card" style="margin-top: 20px; background-color: #f0f6fc; border-left: 4px solid #0073aa;">
            <h3>💡 Tip: Prevent Future Posts from Having Comments</h3>
            <p>To disable comments on all future posts by default:</p>
            <ol>
                <li>Go to <strong>Settings → Discussion</strong></li>
                <li>Uncheck <strong>"Allow people to submit comments on new posts"</strong></li>
                <li>Click <strong>Save Changes</strong></li>
            </ol>
        </div>
    </div>
    <?php
}

// Function to disable comments on all posts
function dcap_disable_all_comments() {
    global $wpdb;
    
    $result = $wpdb->query(
        "UPDATE {$wpdb->posts} 
        SET comment_status = 'closed', ping_status = 'closed' 
        WHERE post_status = 'publish' 
        AND post_type IN ('post', 'page')"
    );
    
    // Clear cache
    wp_cache_flush();
    
    return $result;
}

// Function to enable comments on all posts
function dcap_enable_all_comments() {
    global $wpdb;
    
    $result = $wpdb->query(
        "UPDATE {$wpdb->posts} 
        SET comment_status = 'open', ping_status = 'open' 
        WHERE post_status = 'publish' 
        AND post_type IN ('post', 'page')"
    );
    
    // Clear cache
    wp_cache_flush();
    
    return $result;
}

// Function to get comment status statistics
function dcap_get_comment_status_stats() {
    global $wpdb;
    
    $enabled = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts} 
        WHERE post_status = 'publish' 
        AND post_type IN ('post', 'page')
        AND comment_status = 'open'"
    );
    
    $disabled = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts} 
        WHERE post_status = 'publish' 
        AND post_type IN ('post', 'page')
        AND comment_status = 'closed'"
    );
    
    $total = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts} 
        WHERE post_status = 'publish' 
        AND post_type IN ('post', 'page')"
    );
    
    return array(
        'enabled' => $enabled,
        'disabled' => $disabled,
        'total' => $total
    );
}
?>