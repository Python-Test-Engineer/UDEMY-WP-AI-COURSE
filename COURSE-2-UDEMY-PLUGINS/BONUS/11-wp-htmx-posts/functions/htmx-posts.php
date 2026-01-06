<?php

// Enqueue HTMX
function htmx_demo_enqueue_scripts() {
    wp_enqueue_script('htmx', 'https://unpkg.com/htmx.org@1.9.10', array(), '1.9.10', true);
}
add_action('wp_enqueue_scripts', 'htmx_demo_enqueue_scripts');

// Admin page content
function htmx_demo_admin_page() {
    ?>
    <div class="wrap">
        <h1>HTMX Demo Plugin</h1>
        <div style="background: white; padding: 20px; margin-top: 20px; border: 1px solid #ccc; border-radius: 5px;">
            <h2>How to Use</h2>
            <p>Add the following shortcode to any page or post to display the HTMX demos:</p>
            <code style="background: #f5f5f5; padding: 10px; display: block; margin: 10px 0;">[htmx_demo]</code>
            
            <h2 style="margin-top: 30px;">Features</h2>
            <ul>
                <li><strong>Random Post Loader</strong> - Load a random post without page refresh</li>
                <li><strong>Live Search</strong> - Search posts as you type</li>
                <li><strong>Load More Posts</strong> - Infinite scroll-style pagination</li>
            </ul>

            <h2 style="margin-top: 30px;">HTMX Version</h2>
            <p>This plugin uses HTMX version 1.9.10 from unpkg CDN.</p>
        </div>
    </div>
    <?php
}

// Enqueue HTMX in admin for future admin demos
function htmx_demo_enqueue_admin_scripts($hook) {
    if ($hook !== 'toplevel_page_htmx-demo') {
        return;
    }
    wp_enqueue_script('htmx', 'https://unpkg.com/htmx.org@1.9.10', array(), '1.9.10', true);
}
add_action('admin_enqueue_scripts', 'htmx_demo_enqueue_admin_scripts');


// AJAX Handler: Random Post
function htmx_ajax_random_post() {
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 1,
        'orderby' => 'rand'
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo '<h3>' . get_the_title() . '</h3>';
            echo '<p>' . get_the_excerpt() . '</p>';
            echo '<a href="' . get_permalink() . '" style="color: #0073aa;">Read More →</a>';
        }
        wp_reset_postdata();
    } else {
        echo '<p>No posts found.</p>';
    }
    
    wp_die();
}
add_action('wp_ajax_htmx_random_post', 'htmx_ajax_random_post');
add_action('wp_ajax_nopriv_htmx_random_post', 'htmx_ajax_random_post');

// AJAX Handler: Search Posts
function htmx_ajax_search_posts() {
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    
    if (empty($search)) {
        echo '<em>Start typing to search...</em>';
        wp_die();
    }
    
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 5,
        's' => $search
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        echo '<ul style="list-style: none; padding: 0;">';
        while ($query->have_posts()) {
            $query->the_post();
            echo '<li style="padding: 10px; border-bottom: 1px solid #eee;">';
            echo '<strong>' . get_the_title() . '</strong><br>';
            echo '<small>' . get_the_date() . '</small>';
            echo '</li>';
        }
        echo '</ul>';
        wp_reset_postdata();
    } else {
        echo '<p>No posts found matching "' . esc_html($search) . '"</p>';
    }
    
    wp_die();
}
add_action('wp_ajax_htmx_search_posts', 'htmx_ajax_search_posts');
add_action('wp_ajax_nopriv_htmx_search_posts', 'htmx_ajax_search_posts');




// Helper: Get posts list with pagination
function htmx_get_posts_list($page = 1) {
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 3,
        'paged' => $page
    );
    
    $query = new WP_Query($args);
    $output = '';
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $output .= '<div style="padding: 15px; background: #f9f9f9; margin-bottom: 10px; border-radius: 3px;">';
            $output .= '<h4 style="margin-top: 0;">' . get_the_title() . '</h4>';
            $output .= '<p>' . get_the_excerpt() . '</p>';
            $output .= '</div>';
        }
        
        if ($query->max_num_pages > $page) {
            $output .= '<button 
                hx-get="' . admin_url('admin-ajax.php?action=htmx_load_more&page=' . ($page + 1)) . '"
                hx-target="#posts-list"
                hx-swap="beforeend"
                style="padding: 10px 20px; background: #0073aa; color: white; border: none; border-radius: 3px; cursor: pointer; margin-top: 10px;">
                Load More Posts
            </button>';
        }
        
        wp_reset_postdata();
    } else {
        $output = '<p>No posts found.</p>';
    }
    
    return $output;
}
