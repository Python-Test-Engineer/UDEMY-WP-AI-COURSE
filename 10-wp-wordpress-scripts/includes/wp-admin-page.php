<?php
/**
 * Admin page template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <p class="description">
        This is a custom JavaScript application built with @wordpress/scripts using WP's implementation of React.
    </p>
    
    <!-- This is where the React app will be mounted -->
    <div id="my-custom-app-root"></div>
    <!-- This is where the React app will be mounted -->

</div>
