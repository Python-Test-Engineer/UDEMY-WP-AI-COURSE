<?php
/**
 * Plugin Name: ✅ IWS DELETE ALL COMMENTS
 * Plugin URI: https://example.com
 * Description: Adds an admin page to delete all comments from your WordPress site with one click
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
add_action('admin_menu', 'dac_add_admin_menu');

function dac_add_admin_menu() {
    add_comments_page(
        'DELETE ALL COMMENTS',
        'DELETE ALL COMMENTS',
        'manage_options',
        'delete-all-comments',
        'dac_admin_page'
    );
}

// Admin page content
function dac_admin_page() {
    ?>
    <div class="wrap">
        <h1>Delete All Comments</h1>
        
        <?php
        // Handle form submission
        if (isset($_POST['dac_delete_comments']) && check_admin_referer('dac_delete_action', 'dac_nonce')) {
            $result = dac_delete_all_comments();
            
            if ($result !== false) {
                echo '<div class="notice notice-success"><p><strong>Success!</strong> Deleted ' . $result . ' comments.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p><strong>Error!</strong> Could not delete comments.</p></div>';
            }
        }
        
        // Get comment count
        $comment_count = wp_count_comments();
        $total_comments = $comment_count->total_comments;
        ?>
        
        <div class="card">
            <h2>Comment Statistics</h2>
            <p><strong>Total Comments:</strong> <?php echo $total_comments; ?></p>
            <p><strong>Approved:</strong> <?php echo $comment_count->approved; ?></p>
            <p><strong>Pending:</strong> <?php echo $comment_count->moderated; ?></p>
            <p><strong>Spam:</strong> <?php echo $comment_count->spam; ?></p>
            <p><strong>Trash:</strong> <?php echo $comment_count->trash; ?></p>
        </div>
        
        <?php if ($total_comments > 0): ?>
        <div class="card" style="margin-top: 20px; border-left: 4px solid #dc3232;">
            <h2 style="color: #dc3232;">⚠️ Warning</h2>
            <p><strong>This action is irreversible!</strong></p>
            <p>Clicking the button below will permanently delete ALL comments from your database, including:</p>
            <ul>
                <li>All approved comments</li>
                <li>All pending comments</li>
                <li>All spam comments</li>
                <li>All trashed comments</li>
            </ul>
            <p>Make sure you have a backup of your database before proceeding.</p>
            
            <form method="post" onsubmit="return confirm('Are you absolutely sure you want to delete ALL comments? This cannot be undone!');">
                <?php wp_nonce_field('dac_delete_action', 'dac_nonce'); ?>
                <p>
                    <input type="submit" 
                           name="dac_delete_comments" 
                           class="button button-primary button-large" 
                           value="Delete All <?php echo $total_comments; ?> Comments" 
                           style="background-color: #dc3232; border-color: #dc3232;">
                </p>
            </form>
        </div>
        <?php else: ?>
        <div class="card" style="margin-top: 20px;">
            <p>There are no comments to delete.</p>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

// Function to delete all comments
function dac_delete_all_comments() {
    global $wpdb;
    
    // Get count before deletion
    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments}");
    
    // Delete all comment meta
    $wpdb->query("TRUNCATE TABLE {$wpdb->commentmeta}");
    
    // Delete all comments
    $result = $wpdb->query("TRUNCATE TABLE {$wpdb->comments}");
    
    // Update comment count for all posts
    $wpdb->query("UPDATE {$wpdb->posts} SET comment_count = 0");
    
    // Clear comment cache
    wp_cache_flush();
    
    return ($result !== false) ? $count : false;
}
?>