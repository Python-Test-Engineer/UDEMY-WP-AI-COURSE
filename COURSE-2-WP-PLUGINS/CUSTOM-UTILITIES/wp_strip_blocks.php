<?php
/**
 * Removes all WordPress block markup and HTML tags, returning only plain text
 * 
 * @param string $content The content with WordPress blocks and HTML
 * @return string Plain text content only
 */
function strip_wordpress_blocks($content) {
    // Remove block comments (<!-- wp:block-name ... --> and <!-- /wp:block-name -->)
    $content = preg_replace('/<!--\s*\/?wp:.*?-->/s', '', $content);
    
    // Strip all HTML tags
    $content = strip_tags($content);
    
    // Decode HTML entities
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Replace multiple spaces/newlines with single space
    $content = preg_replace('/\s+/', ' ', $content);
    
    // Trim whitespace from beginning and end
    $content = trim($content);
    
    return $content;
}

// Example usage:
$block_content = '<!-- wp:paragraph -->
<p>This is a paragraph.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2>This is a heading</h2>
<!-- /wp:heading -->

<!-- wp:image {"id":123} -->
<figure class="wp-block-image"><img src="example.jpg" alt="Example"/></figure>
<!-- /wp:image -->';

$clean_content = strip_wordpress_blocks($block_content);
echo $clean_content;

// Output:
// This is a paragraph. This is a heading
?>