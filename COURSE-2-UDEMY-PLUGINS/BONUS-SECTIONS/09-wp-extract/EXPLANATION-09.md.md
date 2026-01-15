# 10-wp-extract Plugin Explainer

## Overview

The **10-wp-extract** plugin is a sophisticated WordPress plugin that uses OpenAI's GPT-4o-mini to intelligently match user queries with relevant WordPress categories and tags, then retrieves associated content from a custom RAG (Retrieval-Augmented Generation) posts table.

## Plugin Details

- **Plugin Name**: ✅ 10 UDEMY UDEMY EXTRACT
- **Description**: Uses OpenAI to match user queries with relevant WordPress categories and tags, then retrieves associated title/content from a custom RAG posts table
- **Version**: 1.0
- **Author**: Craig West
- **Admin Menu Position**: Level 4 (under "✅ 10 UDEMY UDEMY EXTRACT")

## Core Functionality

### 1. Query Matching Process

The plugin follows a sophisticated workflow to match user queries with relevant content:

1. **User Input**: User enters a query in the admin interface
2. **Taxonomy Extraction**: Plugin retrieves all available categories and tags from the custom RAG table
3. **AI Matching**: Sends the query and available taxonomies to OpenAI for intelligent analysis
4. **Post ID Retrieval**: Finds matching post IDs based on selected categories/tags
5. **Content Retrieval**: Extracts and concatenates titles and content from matched posts
6. **Result Assembly**: Returns structured JSON with all required data

### 2. Database Schema

The plugin works with a custom table named `wp_rag_posts` (using WordPress prefix):

```sql
CREATE TABLE wp_rag_posts (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    guid varchar(255) NOT NULL,
    post_id bigint(20) NOT NULL,
    post_title text NOT NULL,
    post_content longtext NOT NULL,
    categories text,
    tags text,
    custom_meta_data longtext,
    embedding longtext,
    last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_embedded datetime DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY guid (guid),
    KEY post_id (post_id)
);
```

### 3. Response Format

The plugin returns a structured JSON response:

```json
{
    "categories": ["kitchen appliances", "cordless tools"],
    "tags": ["knives", "blenders"],
    "post_id": [33, 55, 65],
    "context": "Title: Product Name\n\nContent: Product description...\n\n---\n\nTitle: Another Product\n\nContent: Another description...\n\n---\n"
}
```

## Technical Implementation

### Main Class: RAG_Category_Tag_Matcher

The plugin is built around a single main class that handles all functionality:

#### Key Properties
- `$openai_api_key`: Stores the OpenAI API key from WordPress options
- `$table_name`: Stores the custom table name with proper WordPress prefix

#### Core Methods

##### `__construct()`
- Initializes the plugin
- Sets up WordPress hooks for admin menu and AJAX
- Loads OpenAI API key from options

##### `add_admin_menu()`
- Adds admin menu item at position 4
- Uses `dashicons-search` icon
- Requires `manage_options` capability

##### `render_admin_page()`
- Displays the main admin interface
- Shows OpenAI API key settings
- Displays database status information
- Provides query testing interface
- Handles settings save functionality

##### `handle_ajax_request()`
- Processes AJAX requests for query matching
- Validates security with nonce
- Calls the main matching function
- Saves results to output.json
- Returns JSON response

##### `match_query_to_taxonomies()`
- Main orchestration function
- Validates API key availability
- Retrieves all taxonomies from database
- Calls OpenAI for intelligent matching
- Gets post IDs and context
- Returns structured result

### Database Operations

#### `get_all_categories_from_rag_table()`
- Queries distinct categories from the custom table
- Handles comma-separated category lists
- Removes duplicates and empty values

#### `get_all_tags_from_rag_table()`
- Queries distinct tags from the custom table
- Handles comma-separated tag lists
- Removes duplicates and empty values

#### `get_post_ids_from_matched_taxonomies()`
- Takes matched category and tag names
- Builds dynamic SQL with LIKE clauses
- Returns array of post_id values
- Uses prepared statements for security

#### `get_context_from_post_ids()`
- Takes array of post IDs
- Retrieves titles and content for those posts
- Formats as concatenated text
- Each post formatted as "Title: ...\n\nContent: ...\n\n---\n"

### OpenAI Integration

#### `call_openai_for_matching()`
- Constructs detailed system prompt for consistent responses
- Sends user query + available taxonomies to OpenAI
- Uses GPT-4o-mini model for cost-effectiveness
- Sets temperature to 0.3 for consistent results
- Handles API errors and response validation
- Parses and validates JSON response
- Returns categories and tags arrays

**System Prompt**:
```
You are a helpful assistant that analyzes user queries and determines which categories and tags from a content database would contain relevant information to answer that query. You must respond ONLY with valid JSON in the exact format specified, with no additional text, markdown formatting, or explanations.
```

### File Operations

#### `save_output_json()`
- Saves query results to `output.json` file in plugin directory
- Uses pretty-printed JSON formatting
- Includes error logging for debugging
- Stores complete response including context

## Admin Interface Features

### 1. Settings Section
- OpenAI API key input field
- Secure nonce protection
- Settings save functionality
- Success/error notifications

### 2. Database Status Section
- Shows custom table name
- Displays total record count
- Warns if no records exist
- Shows output.json file information

### 3. Query Testing Section
- Large textarea for query input
- Test query button with loading indicator
- Clear results functionality
- Keyboard shortcut (Enter key) support
- Results display area with JSON and context preview

### 4. JavaScript Functionality
- AJAX form submission
- Loading state management
- JSON response parsing
- Context text truncation for display
- Error handling and user feedback

## Security Features

1. **Nonce Verification**: All AJAX requests use WordPress nonces
2. **Capability Checks**: Admin pages require `manage_options` capability
3. **Input Sanitization**: All user inputs are properly sanitized
4. **Prepared Statements**: Database queries use prepared statements
5. **Direct Access Prevention**: `ABSPATH` check prevents direct file access

## API Configuration

The plugin requires an OpenAI API key to function. The key is stored in WordPress options and used for all API calls. The plugin uses:
- **Model**: gpt-4o-mini (cost-effective)
- **Temperature**: 0.3 (consistent results)
- **Max Tokens**: 500 (sufficient for response)
- **Timeout**: 30 seconds

## Error Handling

The plugin includes comprehensive error handling:
- Missing API key validation
- Empty database table checks
- API connection failures
- Invalid JSON responses
- Database query errors
- File write failures

## Usage Example

1. **Setup**: Configure OpenAI API key in plugin settings
2. **Populate Database**: Add records to the `wp_rag_posts` table
3. **Test Query**: Enter a user query like "Tell me about kitchen utensils you stock, particularly cordless ones"
4. **View Results**: See matched categories, tags, post IDs, and concatenated context
5. **Access Output**: Check `output.json` file for complete results

## Key Benefits

1. **Intelligent Matching**: Uses AI to understand query intent and match relevant taxonomies
2. **Flexible Database**: Works with custom RAG table for content storage
3. **Rich Context**: Returns concatenated titles and content for AI processing
4. **User-Friendly Interface**: Clean admin interface for testing and configuration
5. **Extensible Design**: Well-structured code for future enhancements
6. **Security-First**: Comprehensive security measures and validation

## File Structure

```
COURSE-2-UDEMY-PLUGINS/BONUS/10-wp-extract/
├── wp_rag_plugin.php      # Main plugin file
├── PROMPT.md              # Original requirements and examples
└── output.json           # Generated query results (created at runtime)
```

## Dependencies

- WordPress 5.0+
- PHP 7.4+
- OpenAI API key
- Custom database table (`wp_rag_posts`)

## Future Enhancement Opportunities

1. **REST API Endpoint**: Expose functionality via WordPress REST API
2. **Caching Layer**: Implement result caching for performance
3. **Batch Processing**: Support multiple queries at once
4. **Export Features**: Add CSV/Excel export functionality
5. **Advanced Filtering**: Additional filtering options for results
6. **Embedding Integration**: Use the embedding column for vector search
7. **Webhook Support**: Trigger external services with results

This plugin represents a sophisticated approach to content retrieval and AI-powered taxonomy matching in WordPress, providing a foundation for intelligent content discovery and RAG-based applications.
