<?php

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