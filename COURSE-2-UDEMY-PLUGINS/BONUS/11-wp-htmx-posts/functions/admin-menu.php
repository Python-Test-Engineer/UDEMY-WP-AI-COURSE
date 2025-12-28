<?php
// Add admin menu
function htmx_demo_admin_menu() {
    add_menu_page(
        'HTMX DEMO',           // Page title
        '11 HTMX DEMO',           // Menu title
        'manage_options',      // Capability
        'htmx-demo',           // Menu slug
        'htmx_demo_admin_page', // Callback function
        'dashicons-admin-generic', // Icon
        4.7                   // Position
    );
}
add_action('admin_menu', 'htmx_demo_admin_menu');
