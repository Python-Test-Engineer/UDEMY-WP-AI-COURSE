Create a simple WordPress Plugin that has:

1. Admin menu item level 4
2. A function that creates a table `wp_posts_rag` containing the following fields:

    id bigint(20) NOT NULL AUTO_INCREMENT,
    post_id bigint(20) NOT NULL,
    post_title text NOT NULL,
    post_content longtext NOT NULL,
    categories text,
    tags text,
    custom_meta_data longtext,
    embedding longtext,
    last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_embedded datetime DEFAULT NULL,
    PRIMARY KEY  (id),
    UNIQUE KEY guid (guid),
    KEY post_id (post_id)

 

For each post, insert the post_title, post_content, categories, tags inot a row.

For the custom_meta_data field, get all the custom fields from ACF or other means and insert as csv all the values in this field.