# MySQL Full Text Search: Natural Language Mode Technical Summary

## Overview

MySQL Full Text Search (FTS) is a built-in search capability designed for efficient text searching in large datasets. It provides three search modes: Natural Language, Boolean, and Query Expansion. Natural Language mode is the default and most commonly used approach for text-based queries.

## Natural Language Search Mode


### Core Functionality

Natural Language mode treats the search string as a natural phrase in human language and finds relevant documents based on relevance scoring. It automatically:

- Removes common stopwords (e.g., "the", "and", "is")
- Ignores words shorter than the minimum word length (default: 4 characters for InnoDB, 3 for MyISAM)
- Performs case-insensitive matching
- Returns results ranked by relevance score

### Syntax

```sql
SELECT * FROM table_name
WHERE MATCH(column1, column2) AGAINST('search phrase' IN NATURAL LANGUAGE MODE);

-- Simplified (defaults to Natural Language):
SELECT * FROM table_name
WHERE MATCH(column1, column2) AGAINST('search phrase');
```

### Relevance Scoring

Natural Language mode calculates relevance scores using a modified TF-IDF (Term Frequency-Inverse Document Frequency) algorithm:

**Factors affecting relevance:**
- **Term Frequency (TF)**: How often the search term appears in a document
- **Inverse Document Frequency (IDF)**: How rare the term is across all documents
- **Document Length**: Shorter documents with matches score higher
- **Word Proximity**: Terms appearing closer together increase relevance

**Retrieving relevance scores:**
```sql
SELECT *, MATCH(content) AGAINST('search phrase') AS relevance
FROM articles
WHERE MATCH(content) AGAINST('search phrase')
ORDER BY relevance DESC;
```

## Index Requirements

### Creating Full Text Indexes

```sql
-- At table creation:
CREATE TABLE articles (
    id INT PRIMARY KEY,
    title VARCHAR(200),
    content TEXT,
    FULLTEXT KEY idx_content (content),
    FULLTEXT KEY idx_title_content (title, content)
);

-- Adding to existing table:
ALTER TABLE articles ADD FULLTEXT INDEX idx_content (content);
CREATE FULLTEXT INDEX idx_title ON articles (title);
```

### Supported Column Types

- `CHAR`
- `VARCHAR`
- `TEXT` (including `TINYTEXT`, `MEDIUMTEXT`, `LONGTEXT`)

### Storage Engine Requirements

- **InnoDB**: Supported from MySQL 5.6+
- **MyISAM**: Supported in all versions

## Configuration Parameters

### Key System Variables

```sql
-- View current settings:
SHOW VARIABLES LIKE 'ft_%';
SHOW VARIABLES LIKE 'innodb_ft_%';
```

**Important parameters:**

| Parameter | Default | Description |
|-----------|---------|-------------|
| `ft_min_word_len` | 4 (MyISAM) | Minimum word length to index |
| `innodb_ft_min_token_size` | 3 (InnoDB) | Minimum word length for InnoDB |
| `ft_max_word_len` | 84 (MyISAM) | Maximum word length to index |
| `innodb_ft_max_token_size` | 84 (InnoDB) | Maximum word length for InnoDB |
| `ft_stopword_file` | built-in | Custom stopword file path |
| `innodb_ft_enable_stopword` | ON | Enable/disable stopword filtering |

### Modifying Configuration

```sql
-- In my.cnf or my.ini:
[mysqld]
innodb_ft_min_token_size=2
ft_min_word_len=2

-- Rebuild index after changes:
ALTER TABLE articles DROP INDEX idx_content;
ALTER TABLE articles ADD FULLTEXT INDEX idx_content (content);
```

## Stopwords

### Built-in Stopwords

MySQL includes approximately 500+ common stopwords in English (e.g., "the", "is", "at", "which"). These are automatically excluded from searches and indexes.

### Custom Stopword Lists

```sql
-- View current stopword table:
SELECT * FROM INFORMATION_SCHEMA.INNODB_FT_DEFAULT_STOPWORD;

-- Create custom stopword table:
CREATE TABLE my_stopwords (value VARCHAR(30));
INSERT INTO my_stopwords VALUES ('custom'), ('exclude'), ('words');

-- Configure MySQL to use custom stopwords:
SET GLOBAL innodb_ft_server_stopword_table = 'mydb/my_stopwords';

-- Rebuild indexes after changing stopwords
```

## Limitations and Behavior

### Search Restrictions

1. **50% Threshold Rule**: Terms appearing in more than 50% of rows are ignored (considered too common)
2. **Minimum Rows**: FTS requires at least 3 rows in the table to function
3. **No Partial Matching**: "test" won't match "testing" (use Boolean mode with wildcards for this)
4. **Word Boundaries**: Searches respect word boundaries; "cat" won't match "catalog"

### Performance Considerations

**Advantages:**
- Significantly faster than `LIKE '%term%'` on large datasets
- Indexed lookups provide O(log n) complexity
- Relevance ranking built-in

**Disadvantages:**
- Index maintenance overhead on INSERT/UPDATE operations
- Larger disk space requirements for indexes
- Rebuilding indexes can be time-intensive for large tables

## Practical Examples

### Basic Search

```sql
-- Simple natural language search:
SELECT title, content
FROM articles
WHERE MATCH(title, content) AGAINST('database optimization');
```

### Relevance-Based Ranking

```sql
-- Get top 10 most relevant results:
SELECT 
    title,
    MATCH(title, content) AGAINST('mysql performance') AS score
FROM articles
WHERE MATCH(title, content) AGAINST('mysql performance')
ORDER BY score DESC
LIMIT 10;
```

### Multi-Column Search

```sql
-- Search across multiple columns with different weights:
SELECT 
    title,
    author,
    (MATCH(title) AGAINST('python') * 2 +
     MATCH(content) AGAINST('python')) AS relevance
FROM articles
WHERE MATCH(title, content) AGAINST('python')
ORDER BY relevance DESC;
```

### Combining with WHERE Clauses

```sql
-- Full text search with additional filters:
SELECT title, created_at
FROM articles
WHERE MATCH(content) AGAINST('machine learning')
  AND created_at > '2024-01-01'
  AND status = 'published'
ORDER BY created_at DESC;
```

## Natural Language vs Other Modes

### Comparison

| Feature | Natural Language | Boolean Mode | Query Expansion |
|---------|-----------------|--------------|-----------------|
| Default Mode | Yes | No | No |
| Relevance Scoring | Yes | Optional | Yes |
| Operators (+, -, *) | No | Yes | No |
| Wildcards | No | Yes | No |
| Phrase Matching | Yes | Yes | Yes |
| Auto Query Refinement | No | No | Yes |

### When to Use Natural Language Mode

**Best for:**
- General text searches where relevance matters
- User-facing search features
- When you want automatic stopword filtering
- Ranking results by best match
- Simple, intuitive search queries

**Not ideal for:**
- Exact phrase matching with operators
- Searches requiring wildcards
- Very short search terms (< min word length)
- Partial word matching

## Optimization Tips

1. **Index Strategy**: Create targeted indexes on frequently searched columns
2. **Minimize Index Scope**: Don't include unnecessary columns in FULLTEXT indexes
3. **Monitor ft_boolean_syntax**: For mixed search strategies
4. **Use Query Cache**: Enable query caching for repeated searches
5. **Partition Large Tables**: Consider partitioning for massive datasets
6. **Regular OPTIMIZE TABLE**: Defragment and rebuild indexes periodically

```sql
-- Optimize full text indexes:
OPTIMIZE TABLE articles;
```

## Debugging and Analysis

```sql
-- Check index usage:
EXPLAIN SELECT * FROM articles 
WHERE MATCH(content) AGAINST('search term');

-- View full text index statistics:
SHOW INDEX FROM articles WHERE Index_type = 'FULLTEXT';

-- Check InnoDB full text index cache:
SHOW STATUS LIKE 'Innodb_ft%';
```

## WordPress Integration Example

### Adding Full Text Search to WordPress Posts

WordPress by default uses simple LIKE queries for search, which can be slow on large sites. Here's how to implement Full Text Search on the `wp_posts` table.

#### Step 1: Add Full Text Index

```sql
-- Add FULLTEXT index to post_title and post_content
ALTER TABLE wp_posts 
ADD FULLTEXT INDEX idx_post_search (post_title, post_content);

-- Or just for post_title:
ALTER TABLE wp_posts 
ADD FULLTEXT INDEX idx_post_title (post_title);
```

#### Step 2: Create Custom Search Function

Add this to your theme's `functions.php`:

```php
/**
 * Replace default WordPress search with Full Text Search
 */
function custom_fts_search($search, $wp_query) {
    global $wpdb;
    
    // Only modify main query search
    if (empty($search) || !$wp_query->is_main_query() || !$wp_query->is_search()) {
        return $search;
    }
    
    $search_term = $wp_query->get('s');
    
    if (empty($search_term)) {
        return $search;
    }
    
    // Sanitize search term
    $search_term = $wpdb->esc_like($search_term);
    $search_term = esc_sql($search_term);
    
    // Build Full Text Search query
    $search = " AND (
        MATCH({$wpdb->posts}.post_title, {$wpdb->posts}.post_content) 
        AGAINST('{$search_term}' IN NATURAL LANGUAGE MODE)
    ) ";
    
    return $search;
}
add_filter('posts_search', 'custom_fts_search', 10, 2);
```

#### Step 3: Add Relevance Scoring

```php
/**
 * Add relevance score to search results and order by it
 */
function custom_fts_search_orderby($orderby, $wp_query) {
    global $wpdb;
    
    if (!$wp_query->is_main_query() || !$wp_query->is_search()) {
        return $orderby;
    }
    
    $search_term = $wp_query->get('s');
    
    if (empty($search_term)) {
        return $orderby;
    }
    
    $search_term = esc_sql($search_term);
    
    // Order by relevance score
    $orderby = "MATCH({$wpdb->posts}.post_title, {$wpdb->posts}.post_content) 
                AGAINST('{$search_term}' IN NATURAL LANGUAGE MODE) DESC";
    
    return $orderby;
}
add_filter('posts_orderby', 'custom_fts_search_orderby', 10, 2);
```

#### Step 4: Display Relevance Scores (Optional)

```php
/**
 * Add relevance score to query results
 */
function custom_fts_add_relevance($fields, $wp_query) {
    global $wpdb;
    
    if (!$wp_query->is_main_query() || !$wp_query->is_search()) {
        return $fields;
    }
    
    $search_term = $wp_query->get('s');
    
    if (empty($search_term)) {
        return $fields;
    }
    
    $search_term = esc_sql($search_term);
    
    // Add relevance score to SELECT
    $fields .= ", MATCH({$wpdb->posts}.post_title, {$wpdb->posts}.post_content) 
                AGAINST('{$search_term}' IN NATURAL LANGUAGE MODE) AS relevance_score";
    
    return $fields;
}
add_filter('posts_fields', 'custom_fts_add_relevance', 10, 2);
```

#### Step 5: Use in Templates

Display relevance scores in your search results template:

```php
<?php if (have_posts()) : ?>
    <h1>Search Results for: <?php echo get_search_query(); ?></h1>
    
    <?php while (have_posts()) : the_post(); ?>
        <article>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            
            <?php if (isset($post->relevance_score)) : ?>
                <span class="relevance">
                    Relevance: <?php echo round($post->relevance_score, 2); ?>
                </span>
            <?php endif; ?>
            
            <div class="excerpt">
                <?php the_excerpt(); ?>
            </div>
        </article>
    <?php endwhile; ?>
<?php endif; ?>
```

#### Advanced: Title-Only Search with Higher Weight

```php
/**
 * Search only post titles with Full Text Search
 */
function custom_fts_title_search($search, $wp_query) {
    global $wpdb;
    
    if (empty($search) || !$wp_query->is_main_query() || !$wp_query->is_search()) {
        return $search;
    }
    
    $search_term = esc_sql($wp_query->get('s'));
    
    if (empty($search_term)) {
        return $search;
    }
    
    // Search only in post_title
    $search = " AND (
        MATCH({$wpdb->posts}.post_title) 
        AGAINST('{$search_term}' IN NATURAL LANGUAGE MODE)
    ) ";
    
    return $search;
}
add_filter('posts_search', 'custom_fts_title_search', 10, 2);
```

#### Direct Database Query Example

For custom implementations outside the WordPress loop:

```php
global $wpdb;

$search_term = 'wordpress optimization';
$search_term = esc_sql($search_term);

$results = $wpdb->get_results($wpdb->prepare("
    SELECT 
        ID,
        post_title,
        post_date,
        MATCH(post_title, post_content) AGAINST(%s IN NATURAL LANGUAGE MODE) AS relevance
    FROM {$wpdb->posts}
    WHERE post_status = 'publish'
        AND post_type = 'post'
        AND MATCH(post_title, post_content) AGAINST(%s IN NATURAL LANGUAGE MODE)
    ORDER BY relevance DESC
    LIMIT 10
", $search_term, $search_term));

foreach ($results as $post) {
    echo "<h3>{$post->post_title}</h3>";
    echo "<p>Relevance: " . round($post->relevance, 2) . "</p>";
}
```

#### Performance Considerations for WordPress

1. **Index Maintenance**: WordPress updates posts frequently; ensure your hosting can handle index updates
2. **Caching**: Use object caching (Redis/Memcached) to cache search results
3. **Post Status Filter**: Always filter by `post_status = 'publish'` to avoid searching drafts
4. **Post Type Filter**: Limit to specific post types to improve performance
5. **Minimum Word Length**: Remember MySQL's default minimum (3-4 characters)

#### Troubleshooting WordPress FTS

```php
// Test if Full Text index exists
global $wpdb;
$indexes = $wpdb->get_results("SHOW INDEX FROM {$wpdb->posts} WHERE Index_type = 'FULLTEXT'");
var_dump($indexes);

// Test search query directly
$test_query = "
    SELECT post_title, 
           MATCH(post_title) AGAINST('test' IN NATURAL LANGUAGE MODE) AS score
    FROM {$wpdb->posts}
    WHERE MATCH(post_title) AGAINST('test' IN NATURAL LANGUAGE MODE)
    LIMIT 5
";
$results = $wpdb->get_results($test_query);
var_dump($results);
```

## Conclusion

MySQL Full Text Search in Natural Language mode provides a powerful, efficient mechanism for text searching with automatic relevance ranking. While it has limitations around word length, stopwords, and the 50% threshold rule, it significantly outperforms LIKE-based searches on large text datasets and provides an intuitive search experience for end users. The WordPress integration example demonstrates how FTS can dramatically improve search performance on content-heavy sites with thousands of posts.