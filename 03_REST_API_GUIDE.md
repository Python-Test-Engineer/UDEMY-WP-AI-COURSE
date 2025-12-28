# WordPress REST API Development Guide

## Introduction

The WordPress REST API allows you to interact with your WordPress site remotely by sending and receiving JSON data. This guide covers creating custom endpoints with proper security and parameter handling.

## Basic Concepts

### What is a REST API Endpoint?

An endpoint is a URL that responds to HTTP requests (GET, POST, PUT, DELETE). For example:
- `GET /wp-json/myplugin/v1/items` - Retrieve items
- `POST /wp-json/myplugin/v1/items` - Create an item
- `DELETE /wp-json/myplugin/v1/items/123` - Delete item with ID 123

### WordPress REST API Structure

```
https://example.com/wp-json/{namespace}/{version}/{resource}
                              └────┬────┘ └──┬──┘ └────┬────┘
                                   │        │        │
                            your plugin  version   endpoint
```

## Creating Your First REST API Endpoint

### Step 1: Register the Route

Add this to your plugin's main class or in an `includes/class-api.php` file:

```php
<?php
/**
 * REST API Class
 */
class My_Plugin_API {
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Namespace format: plugin-name/version
        $namespace = 'myplugin/v1';
        
        // Register a simple GET endpoint
        register_rest_route($namespace, '/items', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_items'),
            'permission_callback' => array($this, 'get_items_permissions_check'),
        ));
    }
    
    /**
     * Get items callback
     */
    public function get_items($request) {
        $items = array(
            array('id' => 1, 'name' => 'Item 1'),
            array('id' => 2, 'name' => 'Item 2'),
        );
        
        return new WP_REST_Response($items, 200);
    }
    
    /**
     * Check permissions for GET requests
     */
    public function get_items_permissions_check($request) {
        // Public endpoint - anyone can access
        return true;
    }
}

// Hook into WordPress
add_action('rest_api_init', function() {
    $api = new My_Plugin_API();
    $api->register_routes();
});
```

**Test it:** Visit `https://yoursite.com/wp-json/myplugin/v1/items` in your browser.

### Step 2: Understanding register_rest_route()

```php
register_rest_route(
    $namespace,    // 'myplugin/v1'
    $route,        // '/items' or '/items/(?P<id>\d+)'
    $args          // Array of configuration options
);
```

**Key arguments:**

| Argument | Type | Description |
|----------|------|-------------|
| `methods` | string/array | HTTP methods: 'GET', 'POST', 'PUT', 'DELETE' |
| `callback` | callable | Function to execute when endpoint is called |
| `permission_callback` | callable | Function to check if user can access endpoint |
| `args` | array | Parameter validation and sanitization rules |

## HTTP Methods (CRUD Operations)

### GET - Retrieve Data

```php
register_rest_route('myplugin/v1', '/items', array(
    'methods'             => WP_REST_Server::READABLE, // or 'GET'
    'callback'            => array($this, 'get_items'),
    'permission_callback' => '__return_true', // Public endpoint
));

public function get_items($request) {
    // Get query parameters
    $per_page = $request->get_param('per_page') ?: 10;
    $page = $request->get_param('page') ?: 1;
    
    // Fetch data
    $items = get_posts(array(
        'post_type'      => 'my_custom_type',
        'posts_per_page' => $per_page,
        'paged'          => $page,
    ));
    
    return rest_ensure_response($items);
}
```

**Access:** `GET /wp-json/myplugin/v1/items?per_page=5&page=2`

### POST - Create Data

```php
register_rest_route('myplugin/v1', '/items', array(
    'methods'             => WP_REST_Server::CREATABLE, // or 'POST'
    'callback'            => array($this, 'create_item'),
    'permission_callback' => array($this, 'create_item_permissions_check'),
    'args'                => array(
        'name' => array(
            'required'          => true,
            'validate_callback' => function($param) {
                return is_string($param);
            },
            'sanitize_callback' => 'sanitize_text_field',
        ),
        'description' => array(
            'required'          => false,
            'sanitize_callback' => 'sanitize_textarea_field',
        ),
    ),
));

public function create_item($request) {
    $name = $request->get_param('name');
    $description = $request->get_param('description');
    
    // Create post
    $post_id = wp_insert_post(array(
        'post_title'   => $name,
        'post_content' => $description,
        'post_type'    => 'my_custom_type',
        'post_status'  => 'publish',
    ));
    
    if (is_wp_error($post_id)) {
        return new WP_Error('creation_failed', 'Failed to create item', array('status' => 500));
    }
    
    return new WP_REST_Response(array(
        'id'      => $post_id,
        'name'    => $name,
        'message' => 'Item created successfully'
    ), 201);
}

public function create_item_permissions_check($request) {
    return current_user_can('edit_posts');
}
```

**Access (using curl):**
```bash
curl -X POST https://yoursite.com/wp-json/myplugin/v1/items \
  -H "Content-Type: application/json" \
  -d '{"name":"New Item","description":"Item description"}'
```

### PUT/PATCH - Update Data

```php
register_rest_route('myplugin/v1', '/items/(?P<id>\d+)', array(
    'methods'             => WP_REST_Server::EDITABLE, // or 'PUT', 'PATCH'
    'callback'            => array($this, 'update_item'),
    'permission_callback' => array($this, 'update_item_permissions_check'),
    'args'                => array(
        'id' => array(
            'validate_callback' => function($param) {
                return is_numeric($param);
            },
        ),
        'name' => array(
            'required'          => false,
            'sanitize_callback' => 'sanitize_text_field',
        ),
    ),
));

public function update_item($request) {
    $id = $request->get_param('id');
    $name = $request->get_param('name');
    
    // Check if post exists
    $post = get_post($id);
    if (!$post) {
        return new WP_Error('not_found', 'Item not found', array('status' => 404));
    }
    
    // Update post
    $updated = wp_update_post(array(
        'ID'         => $id,
        'post_title' => $name,
    ));
    
    if (is_wp_error($updated)) {
        return new WP_Error('update_failed', 'Failed to update item', array('status' => 500));
    }
    
    return new WP_REST_Response(array(
        'id'      => $id,
        'name'    => $name,
        'message' => 'Item updated successfully'
    ), 200);
}

public function update_item_permissions_check($request) {
    $post = get_post($request->get_param('id'));
    return current_user_can('edit_post', $post->ID);
}
```

### DELETE - Remove Data

```php
register_rest_route('myplugin/v1', '/items/(?P<id>\d+)', array(
    'methods'             => WP_REST_Server::DELETABLE, // or 'DELETE'
    'callback'            => array($this, 'delete_item'),
    'permission_callback' => array($this, 'delete_item_permissions_check'),
    'args'                => array(
        'id' => array(
            'validate_callback' => function($param) {
                return is_numeric($param);
            },
        ),
    ),
));

public function delete_item($request) {
    $id = $request->get_param('id');
    
    // Check if post exists
    $post = get_post($id);
    if (!$post) {
        return new WP_Error('not_found', 'Item not found', array('status' => 404));
    }
    
    // Delete post
    $deleted = wp_delete_post($id, true); // true = force delete, skip trash
    
    if (!$deleted) {
        return new WP_Error('delete_failed', 'Failed to delete item', array('status' => 500));
    }
    
    return new WP_REST_Response(array(
        'deleted' => true,
        'message' => 'Item deleted successfully'
    ), 200);
}

public function delete_item_permissions_check($request) {
    $post = get_post($request->get_param('id'));
    return current_user_can('delete_post', $post->ID);
}
```

## Parameter Handling

### Parameter Types

Parameters can come from three sources:

1. **URL Parameters** - `/items/(?P<id>\d+)` - captured from the route
2. **Query Parameters** - `?page=2&per_page=10` - GET parameters
3. **Body Parameters** - JSON data in POST/PUT requests

### Accessing Parameters

```php
public function my_callback($request) {
    // All parameters (URL, query, body)
    $all_params = $request->get_params();
    
    // Specific parameter
    $id = $request->get_param('id');
    
    // With default value
    $per_page = $request->get_param('per_page') ?: 10;
    
    // URL parameters only
    $url_params = $request->get_url_params();
    
    // Query parameters only
    $query_params = $request->get_query_params();
    
    // Body parameters only (POST/PUT data)
    $body_params = $request->get_body_params();
    
    // JSON body (if Content-Type: application/json)
    $json_params = $request->get_json_params();
}
```

### Parameter Validation & Sanitization

Define parameter rules in the `args` array:

```php
'args' => array(
    'email' => array(
        'required'          => true,
        'type'              => 'string',
        'description'       => 'User email address',
        'validate_callback' => function($param, $request, $key) {
            return is_email($param);
        },
        'sanitize_callback' => function($param, $request, $key) {
            return sanitize_email($param);
        },
    ),
    'age' => array(
        'required'          => false,
        'type'              => 'integer',
        'minimum'           => 0,
        'maximum'           => 120,
        'default'           => 0,
        'validate_callback' => function($param) {
            return is_numeric($param) && $param >= 0 && $param <= 120;
        },
        'sanitize_callback' => 'absint',
    ),
    'status' => array(
        'required'          => false,
        'type'              => 'string',
        'enum'              => array('active', 'inactive', 'pending'),
        'default'           => 'active',
        'validate_callback' => function($param) {
            return in_array($param, array('active', 'inactive', 'pending'));
        },
    ),
    'tags' => array(
        'required'          => false,
        'type'              => 'array',
        'items'             => array('type' => 'string'),
        'validate_callback' => function($param) {
            return is_array($param);
        },
        'sanitize_callback' => function($param) {
            return array_map('sanitize_text_field', $param);
        },
    ),
)
```

### Common Validation Functions

```php
// WordPress built-in
is_email($value)           // Valid email
absint($value)             // Positive integer
sanitize_text_field($val)  // Remove HTML/PHP tags
sanitize_textarea_field()  // Allow line breaks
sanitize_url($url)         // Clean URL
wp_kses_post($content)     // Allow safe HTML

// Custom validation
function validate_phone($phone) {
    return preg_match('/^\+?[0-9]{10,15}$/', $phone);
}

function validate_slug($slug) {
    return preg_match('/^[a-z0-9-]+$/', $slug);
}

function validate_date($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
```

## Security Best Practices

### 1. Always Use Permission Callbacks

**NEVER** use `__return_true` for endpoints that modify data!

```php
// ❌ BAD - Anyone can delete posts!
'permission_callback' => '__return_true'

// ✅ GOOD - Check user capabilities
'permission_callback' => function() {
    return current_user_can('delete_posts');
}
```

### 2. Capability Checks

```php
public function permissions_check($request) {
    // Check basic capability
    if (!current_user_can('edit_posts')) {
        return new WP_Error(
            'rest_forbidden',
            'You do not have permission to access this endpoint',
            array('status' => 403)
        );
    }
    
    // Check ownership (for updates/deletes)
    $post_id = $request->get_param('id');
    if (!current_user_can('edit_post', $post_id)) {
        return new WP_Error(
            'rest_forbidden',
            'You do not have permission to edit this item',
            array('status' => 403)
        );
    }
    
    return true;
}
```

Common capabilities:
- `read` - Basic read access
- `edit_posts` - Create/edit own posts
- `edit_others_posts` - Edit others' posts
- `publish_posts` - Publish posts
- `delete_posts` - Delete own posts
- `manage_options` - Admin-level access
- `edit_users` - Manage users

### 3. Nonce Verification (for Cookie Authentication)

When making AJAX requests from WordPress admin:

```php
public function permissions_check($request) {
    // Verify nonce
    $nonce = $request->get_header('X-WP-Nonce');
    
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error(
            'rest_cookie_invalid_nonce',
            'Cookie nonce is invalid',
            array('status' => 403)
        );
    }
    
    return current_user_can('edit_posts');
}
```

**JavaScript (in admin area):**

```javascript
fetch('/wp-json/myplugin/v1/items', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce // WordPress provides this
    },
    body: JSON.stringify({
        name: 'New Item'
    })
});
```

### 4. Application Passwords (for External Access)

For external applications, use Application Passwords:

```bash
# Create application password in WordPress admin
# User → Profile → Application Passwords

# Use with curl
curl -X POST https://yoursite.com/wp-json/myplugin/v1/items \
  --user "username:xxxx xxxx xxxx xxxx xxxx xxxx" \
  -H "Content-Type: application/json" \
  -d '{"name":"New Item"}'
```

### 5. Rate Limiting

Implement basic rate limiting:

```php
public function permissions_check($request) {
    $user_id = get_current_user_id();
    $transient_key = 'api_rate_limit_' . $user_id;
    
    $requests = get_transient($transient_key) ?: 0;
    
    if ($requests >= 100) {
        return new WP_Error(
            'rest_rate_limit_exceeded',
            'Rate limit exceeded. Try again later.',
            array('status' => 429)
        );
    }
    
    set_transient($transient_key, $requests + 1, HOUR_IN_SECONDS);
    
    return true;
}
```

### 6. Input Sanitization & Validation

**Always** sanitize and validate user input:

```php
public function create_item($request) {
    // Get parameters
    $email = $request->get_param('email');
    $age = $request->get_param('age');
    
    // Validate
    if (!is_email($email)) {
        return new WP_Error(
            'invalid_email',
            'Please provide a valid email address',
            array('status' => 400)
        );
    }
    
    if (!is_numeric($age) || $age < 0 || $age > 120) {
        return new WP_Error(
            'invalid_age',
            'Age must be between 0 and 120',
            array('status' => 400)
        );
    }
    
    // Sanitize
    $email = sanitize_email($email);
    $age = absint($age);
    
    // Process...
}
```

### 7. SQL Injection Prevention

Use `$wpdb->prepare()` for database queries:

```php
global $wpdb;

// ❌ BAD - SQL injection vulnerability!
$id = $request->get_param('id');
$result = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}my_table WHERE id = $id");

// ✅ GOOD - Prepared statement
$id = $request->get_param('id');
$result = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}my_table WHERE id = %d",
    $id
));
```

## Response Formatting

### Success Responses

```php
// Simple response
return new WP_REST_Response(array(
    'success' => true,
    'data'    => $items
), 200);

// Or use helper
return rest_ensure_response($items);
```

### Error Responses

```php
// Standard error
return new WP_Error(
    'error_code',
    'Human-readable error message',
    array('status' => 400)
);

// Detailed error
return new WP_Error(
    'validation_failed',
    'Validation failed',
    array(
        'status' => 400,
        'errors' => array(
            'email' => 'Invalid email format',
            'age'   => 'Age must be a number'
        )
    )
);
```

### HTTP Status Codes

| Code | Meaning | Use Case |
|------|---------|----------|
| 200 | OK | Successful GET/PUT/PATCH |
| 201 | Created | Successful POST |
| 204 | No Content | Successful DELETE (no body) |
| 400 | Bad Request | Invalid parameters |
| 401 | Unauthorized | Not logged in |
| 403 | Forbidden | Logged in but no permission |
| 404 | Not Found | Resource doesn't exist |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

## Complete Example: Task Manager API

```php
<?php
/**
 * Task Manager REST API
 */
class Task_Manager_API {
    
    private $namespace = 'taskmanager/v1';
    
    public function register_routes() {
        // GET /tasks - List all tasks
        register_rest_route($this->namespace, '/tasks', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_tasks'),
            'permission_callback' => array($this, 'check_read_permission'),
            'args'                => array(
                'status' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'enum'              => array('pending', 'completed'),
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'per_page' => array(
                    'required'          => false,
                    'type'              => 'integer',
                    'default'           => 10,
                    'minimum'           => 1,
                    'maximum'           => 100,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
        
        // POST /tasks - Create a task
        register_rest_route($this->namespace, '/tasks', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'create_task'),
            'permission_callback' => array($this, 'check_create_permission'),
            'args'                => array(
                'title' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function($param) {
                        return !empty(trim($param));
                    },
                ),
                'description' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'due_date' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'validate_callback' => function($param) {
                        return strtotime($param) !== false;
                    },
                ),
            ),
        ));
        
        // GET /tasks/{id} - Get single task
        register_rest_route($this->namespace, '/tasks/(?P<id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_task'),
            'permission_callback' => array($this, 'check_read_permission'),
            'args'                => array(
                'id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    },
                ),
            ),
        ));
        
        // PUT /tasks/{id} - Update task
        register_rest_route($this->namespace, '/tasks/(?P<id>\d+)', array(
            'methods'             => 'PUT',
            'callback'            => array($this, 'update_task'),
            'permission_callback' => array($this, 'check_update_permission'),
            'args'                => array(
                'id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    },
                ),
                'title' => array(
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'status' => array(
                    'required'          => false,
                    'enum'              => array('pending', 'completed'),
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        // DELETE /tasks/{id} - Delete task
        register_rest_route($this->namespace, '/tasks/(?P<id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array($this, 'delete_task'),
            'permission_callback' => array($this, 'check_delete_permission'),
        ));
    }
    
    // Callbacks
    
    public function get_tasks($request) {
        $status = $request->get_param('status');
        $per_page = $request->get_param('per_page');
        
        $args = array(
            'post_type'      => 'task',
            'posts_per_page' => $per_page,
        );
        
        if ($status) {
            $args['meta_query'] = array(
                array(
                    'key'   => 'task_status',
                    'value' => $status,
                ),
            );
        }
        
        $tasks = get_posts($args);
        $formatted_tasks = array();
        
        foreach ($tasks as $task) {
            $formatted_tasks[] = array(
                'id'          => $task->ID,
                'title'       => $task->post_title,
                'description' => $task->post_content,
                'status'      => get_post_meta($task->ID, 'task_status', true),
                'due_date'    => get_post_meta($task->ID, 'task_due_date', true),
                'created_at'  => $task->post_date,
            );
        }
        
        return new WP_REST_Response($formatted_tasks, 200);
    }
    
    public function create_task($request) {
        $title = $request->get_param('title');
        $description = $request->get_param('description');
        $due_date = $request->get_param('due_date');
        
        $post_id = wp_insert_post(array(
            'post_title'   => $title,
            'post_content' => $description,
            'post_type'    => 'task',
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id(),
        ));
        
        if (is_wp_error($post_id)) {
            return new WP_Error(
                'create_failed',
                'Failed to create task',
                array('status' => 500)
            );
        }
        
        update_post_meta($post_id, 'task_status', 'pending');
        
        if ($due_date) {
            update_post_meta($post_id, 'task_due_date', $due_date);
        }
        
        return new WP_REST_Response(array(
            'id'      => $post_id,
            'message' => 'Task created successfully',
        ), 201);
    }
    
    // Permission callbacks
    
    public function check_read_permission($request) {
        return is_user_logged_in();
    }
    
    public function check_create_permission($request) {
        return current_user_can('edit_posts');
    }
    
    public function check_update_permission($request) {
        $post = get_post($request->get_param('id'));
        
        if (!$post) {
            return new WP_Error('not_found', 'Task not found', array('status' => 404));
        }
        
        return current_user_can('edit_post', $post->ID);
    }
    
    public function check_delete_permission($request) {
        $post = get_post($request->get_param('id'));
        
        if (!$post) {
            return new WP_Error('not_found', 'Task not found', array('status' => 404));
        }
        
        return current_user_can('delete_post', $post->ID);
    }
}

// Initialize
add_action('rest_api_init', function() {
    $api = new Task_Manager_API();
    $api->register_routes();
});
```

## Testing Your API

### Using Browser (GET only)

Visit: `https://yoursite.com/wp-json/myplugin/v1/items`

### Using cURL

```bash
# GET request
curl https://yoursite.com/wp-json/myplugin/v1/items

# POST request
curl -X POST https://yoursite.com/wp-json/myplugin/v1/items \
  -H "Content-Type: application/json" \
  -d '{"name":"New Item"}'

# With authentication
curl -X POST https://yoursite.com/wp-json/myplugin/v1/items \
  --user "username:application-password" \
  -H "Content-Type: application/json" \
  -d '{"name":"New Item"}'

# PUT request
curl -X PUT https://yoursite.com/wp-json/myplugin/v1/items/123 \
  --user "username:application-password" \
  -H "Content-Type: application/json" \
  -d '{"name":"Updated Item"}'

# DELETE request
curl -X DELETE https://yoursite.com/wp-json/myplugin/v1/items/123 \
  --user "username:application-password"
```

### Using Postman

1. Set URL: `https://yoursite.com/wp-json/myplugin/v1/items`
2. Set method: GET, POST, PUT, DELETE
3. Add header: `Content-Type: application/json`
4. For auth: Use Basic Auth with username and application password
5. Add JSON body for POST/PUT requests

### Using JavaScript (Fetch API)

```javascript
// GET request
fetch('https://yoursite.com/wp-json/myplugin/v1/items')
    .then(response => response.json())
    .then(data => console.log(data));

// POST request (from WordPress admin)
fetch('https://yoursite.com/wp-json/myplugin/v1/items', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce
    },
    body: JSON.stringify({
        name: 'New Item',
        description: 'Item description'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

## Best Practices Summary

✅ **DO:**
- Always validate and sanitize input
- Use permission callbacks for every endpoint
- Use proper HTTP methods and status codes
- Use `$wpdb->prepare()` for custom queries
- Return consistent response formats
- Version your API (`/v1/`, `/v2/`)
- Document your endpoints
- Test with different user roles
- Implement rate limiting for public endpoints

❌ **DON'T:**
- Use `__return_true` for write operations
- Trust user input without validation
- Return sensitive data (passwords, tokens)
- Use deprecated WordPress functions
- Forget to check post ownership
- Expose internal error messages to users
- Create endpoints without clear purpose

## Further Reading

- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [REST API Authentication](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/)
- [Application Passwords](https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/)
- [Security Best Practices](https://developer.wordpress.org/apis