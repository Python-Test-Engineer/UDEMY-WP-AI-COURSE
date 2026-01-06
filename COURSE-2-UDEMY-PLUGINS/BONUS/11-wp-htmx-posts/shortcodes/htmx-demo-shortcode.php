<?php
// Create shortcode for demo page
function htmx_demo_shortcode() {
    ob_start();
    ?>
    <div class="htmx-demo-container" style="max-width: 800px; margin: 20px auto; font-family: Arial, sans-serif;">
        <h1>HTMX + WordPress Demos</h1>
        
        <!-- Demo 1: Load Random Post -->
        <div style="border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px;">
            <h2>Demo 1: Load Random Post</h2>
            <button 
                hx-get="<?php echo admin_url('admin-ajax.php?action=htmx_random_post'); ?>"
                hx-target="#random-post"
                hx-swap="innerHTML"
                style="padding: 10px 20px; background: #0073aa; color: white; border: none; border-radius: 3px; cursor: pointer;">
                Load Random Post
            </button>
            <div id="random-post" style="margin-top: 15px; padding: 15px; background: #f5f5f5; border-radius: 3px;">
                <em>Click the button to load a random post</em>
            </div>
        </div>

        <!-- Demo 2: Search Posts -->
        <div style="border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px;">
            <h2>Demo 2: Search Posts</h2>
            <input 
                type="search" 
                name="search"
                placeholder="Type to search posts..."
                hx-get="<?php echo admin_url('admin-ajax.php?action=htmx_search_posts'); ?>"
                hx-trigger="keyup changed delay:500ms"
                hx-target="#search-results"
                hx-include="[name='search']"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px;">
            <div id="search-results" style="margin-top: 15px;">
                <em>Start typing to search...</em>
            </div>
        </div>

        <!-- Demo 3: Load More Posts -->
        <div style="border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px;">
            <h2>Demo 3: Load More Posts</h2>
            <div id="posts-list">
                <?php echo htmx_get_posts_list(1); ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('htmx_demo', 'htmx_demo_shortcode');